<?php

namespace App\Services;

use App\Exceptions\KprimePayPayoutException;
use App\Jobs\ExecutePartnerWithdrawal;
use App\Models\PartnerPayoutEvent;
use App\Models\PartnerWithdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PartnerPayoutService
{
    public function __construct(
        private PartnerWithdrawalService $withdrawals,
        private KprimePayPayoutService $kprimePay,
        private PlatformConfigurationService $configuration,
    ) {}

    /** Approuve automatiquement toute demande éligible dans le plafond configuré. */
    public function approveForAutomaticExecution(PartnerWithdrawal $withdrawal): bool
    {
        $limit = $this->configuration->integer('partners.auto_approval_max_xof', 0);
        if ($limit < 1 || ($withdrawal->amount + $withdrawal->estimated_fees) > $limit) return false;

        return DB::transaction(function () use ($withdrawal): bool {
            $locked = PartnerWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'otp_verified') return false;
            $locked->update(['status' => 'approved', 'approved_at' => now(), 'review_reason' => 'Approbation automatique selon les réglages de la plateforme.']);
            ExecutePartnerWithdrawal::dispatch($locked->id)->onQueue('withdrawals')->afterCommit();
            return true;
        }, 3);
    }

    public function execute(int $withdrawalId): void
    {
        $withdrawal = PartnerWithdrawal::query()->find($withdrawalId);
        if (!$withdrawal || $withdrawal->status !== 'approved') return;

        $withdrawal->loadMissing('account');
        if (!$withdrawal->account || !$this->withdrawals->gatewayIsEnabled($withdrawal->account->country_code, $withdrawal->account->gateway)) {
            $this->withdrawals->failAndRelease($withdrawal, 'Le moyen de retrait a été désactivé par la plateforme avant l’envoi. Aucun versement n’a été lancé.', request());
            return;
        }

        $withdrawal = $this->withdrawals->markProcessing($withdrawal);
        if ($withdrawal->status !== 'processing') return;

        try {
            $payload = $this->kprimePay->transferFromCollectionBalance($withdrawal);
            $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
            $withdrawal->update([
                'provider_status' => strtolower((string) ($data['status'] ?? 'accepted')),
                'kpp_reference' => $data['transfer_reference'] ?? $data['kpp_tx_reference'] ?? $withdrawal->kpp_reference,
            ]);
            // Une réponse 201 accepte l'opération, mais le grand livre n'est soldé
            // qu'après statut crédit vérifié (webhook ou réconciliation).
        } catch (KprimePayPayoutException $exception) {
            $code = strtoupper($exception->providerCode.' '.$exception->getMessage());
            if (str_contains($code, 'INSUFFICIENT_COLLECTION_BALANCE') || str_contains($code, 'GATEWAY_REJECTED') || str_contains($code, 'INSUFFICIENT_SCOPE')) {
                $this->withdrawals->failAndRelease($withdrawal, 'Le prestataire a refusé le retrait : '.$exception->getMessage(), request());
                return;
            }
            $this->withdrawals->markUnknown($withdrawal, 'Réponse KPrimePay non certaine : '.$exception->getMessage(), $exception->payload);
        } catch (Throwable $exception) {
            report($exception);
            $this->withdrawals->markUnknown($withdrawal, 'Impossible de confirmer la réponse KPrimePay. Le solde reste réservé.', null);
        }
    }

    /** Un webhook ne solde jamais un retrait sans interrogation authentifiée de KPrimePay. */
    public function handleWebhook(PartnerWithdrawal $withdrawal, array $webhook, array $rawPayload): string
    {
        $event = PartnerPayoutEvent::firstOrCreate(
            ['event_id' => $webhook['event_id']],
            [
                'partner_withdrawal_id' => $withdrawal->id,
                'event_type' => $webhook['event'],
                'provider_status' => $webhook['provider_status'] ?? null,
                'payload_hash' => hash('sha256', json_encode($this->safePayload($rawPayload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                'payload' => $this->safePayload($rawPayload),
                'received_at' => now(),
            ],
        );
        if ($event->processed_at) return 'DUPLICATE';

        try {
            $verified = $this->kprimePay->transferStatus($withdrawal->transaction_id);
            $status = $this->providerStatus($verified);
            if ($status === 'success') {
                $status = $this->settleVerifiedSuccess($withdrawal, $verified);
            } elseif (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled', 'rejected'], true)) {
                $this->withdrawals->failAndRelease($withdrawal, (string) ($verified['failure_reason'] ?? $verified['message'] ?? 'Retrait refusé par le prestataire.'), request());
            } else {
                $withdrawal->update(['provider_status' => $status]);
            }
            $event->update(['processed_at' => now(), 'processing_error' => null]);
            return strtoupper($status ?: 'PENDING');
        } catch (Throwable $exception) {
            report($exception);
            $event->update(['processing_error' => Str::limit($exception->getMessage(), 1000, '')]);
            return 'VERIFICATION_UNAVAILABLE';
        }
    }

    public function reconcile(PartnerWithdrawal $withdrawal): string
    {
        $verified = $this->kprimePay->transferStatus($withdrawal->transaction_id);
        $status = $this->providerStatus($verified);
        if ($status === 'success') $status = $this->settleVerifiedSuccess($withdrawal, $verified);
        elseif (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled', 'rejected'], true)) $this->withdrawals->failAndRelease($withdrawal, (string) ($verified['failure_reason'] ?? $verified['message'] ?? 'Retrait refusé par le prestataire.'), request());
        else $withdrawal->update(['provider_status' => $status ?: $withdrawal->provider_status]);
        return $status ?: 'pending';
    }

    private function providerStatus(array $data): string
    {
        return strtolower((string) ($data['status'] ?? $data['transfer_status'] ?? $data['transaction_status'] ?? ''));
    }

    /**
     * Solde le retrait avec le coût effectivement débité par KPrimePay.
     * Une estimation peut être supérieure : l'écart revient automatiquement au partenaire.
     */
    public function settleVerifiedSuccess(PartnerWithdrawal $withdrawal, array $data): string
    {
        $settlement = $this->providerSettlement($withdrawal, $data);
        if (!$settlement) {
            $this->withdrawals->markUnknown($withdrawal, 'La confirmation KPrimePay ne permet pas de vérifier le montant réellement débité. Le solde reste protégé.', $data);
            return 'amount_unverified';
        }
        if ($settlement['fees'] > (int) $withdrawal->estimated_fees) {
            $this->withdrawals->markUnknown($withdrawal, 'Les frais réels KPrimePay dépassent le plafond réservé. Le solde reste protégé pour vérification.', $data);
            return 'fee_cap_exceeded';
        }

        $this->withdrawals->settleSucceeded($withdrawal, $data, $settlement['fees']);
        return 'success';
    }

    /**
     * KPrimePay peut renvoyer les détails via le statut ou dans transaction_details du webhook.
     * `total_amount_debited` est prioritaire, car il représente la somme effectivement sortie
     * de la balance de collecte. À défaut, les champs de frais permettent le même calcul.
     */
    private function providerSettlement(PartnerWithdrawal $withdrawal, array $data): ?array
    {
        $details = is_array($data['transaction_details'] ?? null) ? $data['transaction_details'] : [];
        $reportedAmount = $data['transaction_amount'] ?? $details['amount'] ?? null;
        if ($reportedAmount !== null && (int) $reportedAmount !== (int) $withdrawal->amount) {
            return null;
        }

        $total = $data['total_amount_debited'] ?? $data['total_amount'] ?? $details['total_amount'] ?? null;
        if ($total === null) {
            $transactionFees = $data['transaction_fees'] ?? $data['transfer_fees'] ?? $data['fees'] ?? $details['fees'] ?? 0;
            $withdrawalFees = $data['withdrawal_fees'] ?? $details['withdrawal_fees'] ?? 0;
            $total = (int) $withdrawal->amount + (int) $transactionFees + (int) $withdrawalFees;
        }
        $total = (int) $total;
        if ($total < (int) $withdrawal->amount) {
            return null;
        }

        return ['fees' => $total - (int) $withdrawal->amount, 'total' => $total];
    }

    private function safePayload(array $payload): array
    {
        $data = (array) ($payload['data'] ?? []);
        return [
            'api_version' => $payload['api_version'] ?? null,
            'event' => $payload['event'] ?? null,
            'event_id' => $payload['event_id'] ?? null,
            'data' => array_filter([
                'transaction_id' => $data['transaction_id'] ?? null,
                'status' => $data['status'] ?? $data['transfer_status'] ?? null,
                'transfer_reference' => $data['transfer_reference'] ?? null,
                'kpp_reference' => $data['kpp_reference'] ?? null,
                'transaction_details' => $data['transaction_details'] ?? null,
            ], fn ($value) => $value !== null),
        ];
    }
}
