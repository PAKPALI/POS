<?php

namespace App\Services;

use App\Exceptions\KprimePayPayoutException;
use App\Jobs\ExecutePlatformWithdrawal;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Models\PlatformWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PlatformTreasuryPayoutService
{
    public function __construct(private KprimePayPayoutService $kprimePay) {}

    public function execute(int $withdrawalId): void
    {
        $withdrawal = PlatformWithdrawal::query()->find($withdrawalId);
        if (!$withdrawal || $withdrawal->status !== 'otp_verified') return;
        $withdrawal = $this->markProcessing($withdrawal);
        if ($withdrawal->status !== 'processing') return;

        try {
            $payload = $this->kprimePay->transferPlatformWithdrawal($withdrawal);
            $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
            $withdrawal->update(['provider_status' => strtolower((string) ($data['status'] ?? 'accepted')), 'kpp_reference' => $data['transfer_reference'] ?? $data['kpp_tx_reference'] ?? $withdrawal->kpp_reference]);
        } catch (KprimePayPayoutException $exception) {
            $code = strtoupper($exception->providerCode.' '.$exception->getMessage());
            if (str_contains($code, 'INSUFFICIENT_COLLECTION_BALANCE') || str_contains($code, 'GATEWAY_REJECTED') || str_contains($code, 'INSUFFICIENT_SCOPE')) {
                $this->fail($withdrawal, 'Le prestataire a refusé le retrait : '.$exception->getMessage());
                return;
            }
            $this->unknown($withdrawal, 'Réponse KPrimePay non certaine : '.$exception->getMessage(), $exception->payload);
        } catch (Throwable $exception) {
            report($exception);
            $this->unknown($withdrawal, 'Impossible de confirmer la réponse KPrimePay. Le montant reste réservé.', null);
        }
    }

    /** Confirmation toujours authentifiée via credit-status, webhook ou scheduler. */
    public function reconcile(PlatformWithdrawal $withdrawal): string
    {
        try {
            $verified = $this->kprimePay->transferStatus($withdrawal->transaction_id);
        } catch (KprimePayPayoutException $exception) {
            if (!$this->isTransactionNotFound($exception)) {
                throw $exception;
            }

            $this->unknown(
                $withdrawal,
                'KPrimePay ne connaît pas ce retrait. Une réautorisation contrôlée est requise.',
                $exception->payload,
                'transaction_not_found',
            );

            return 'transaction_not_found';
        }

        $status = $this->providerStatus($verified);
        if ($status === 'success') return $this->settle($withdrawal, $verified);
        if (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled', 'rejected'], true)) {
            $this->fail($withdrawal, (string) ($verified['failure_reason'] ?? $verified['message'] ?? 'Retrait refusé par le prestataire.'));
        } else {
            $withdrawal->update(['provider_status' => $status ?: $withdrawal->provider_status]);
        }
        return $status ?: 'pending';
    }

    /**
     * Réautorise un envoi uniquement après une confirmation authentifiée que
     * KPrimePay ne possède aucune transaction pour l'identifiant interne.
     * La même idempotency key est conservée afin de ne jamais créer un second
     * retrait pour une même demande locale.
     */
    public function retryUnknown(PlatformWithdrawal $withdrawal, PlatformAdmin $actor, Request $request, string $reason): string
    {
        if ($withdrawal->status !== 'unknown' || $withdrawal->kpp_reference) {
            throw new \RuntimeException('PAYOUT_RETRY_NOT_ALLOWED');
        }

        try {
            $verified = $this->kprimePay->transferStatus($withdrawal->transaction_id);
            $status = $this->providerStatus($verified);
            if ($status === 'success') {
                $result = $this->settle($withdrawal, $verified);
                $this->auditRetry($withdrawal, $actor, $request, $reason, $result);
                return $result === 'success' ? 'succeeded' : 'unknown';
            }
            if (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled', 'rejected'], true)) {
                $this->fail($withdrawal, (string) ($verified['failure_reason'] ?? $verified['message'] ?? 'Retrait refusé par le prestataire.'));
                $this->auditRetry($withdrawal, $actor, $request, $reason, 'failed');
                return 'failed';
            }

            throw new \RuntimeException('PAYOUT_RETRY_PROVIDER_PENDING');
        } catch (KprimePayPayoutException $exception) {
            if (!$this->isTransactionNotFound($exception)) {
                throw new \RuntimeException('PAYOUT_RETRY_PROVIDER_UNAVAILABLE', 0, $exception);
            }
        }

        return DB::transaction(function () use ($withdrawal, $actor, $request, $reason): string {
            $locked = PlatformWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'unknown' || $locked->kpp_reference) {
                throw new \RuntimeException('PAYOUT_RETRY_NOT_ALLOWED');
            }

            $old = [
                'status' => $locked->status,
                'provider_status' => $locked->provider_status,
                'failure_reason' => $locked->failure_reason,
                'unknown_at' => $locked->unknown_at?->toIso8601String(),
            ];
            $locked->update([
                'status' => 'otp_verified',
                'provider_status' => 'retry_authorized',
                'failure_reason' => null,
                'processing_at' => null,
                'unknown_at' => null,
            ]);

            PlatformAuditLog::create([
                'platform_admin_id' => $actor->id,
                'action' => 'platform.treasury.withdrawal.retry_authorized',
                'target_type' => PlatformWithdrawal::class,
                'target_id' => (string) $locked->id,
                'old_values' => $old,
                'new_values' => [
                    'status' => $locked->status,
                    'provider_status' => $locked->provider_status,
                    'idempotency_key_reused' => true,
                    'provider_confirmation' => 'TRANSACTION_NOT_FOUND',
                ],
                'reason' => $reason,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'result' => 'success',
            ]);

            ExecutePlatformWithdrawal::dispatch($locked->id)->onQueue('withdrawals')->afterCommit();

            return 'queued';
        }, 3);
    }

    public function handleWebhook(PlatformWithdrawal $withdrawal, array $webhook): string
    {
        if ($withdrawal->event_id === $webhook['event_id']) return 'DUPLICATE';
        try {
            $result = $this->reconcile($withdrawal);
            $withdrawal->update(['event_id' => $webhook['event_id']]);
            return strtoupper($result);
        } catch (Throwable $exception) {
            report($exception);
            return 'VERIFICATION_UNAVAILABLE';
        }
    }

    private function markProcessing(PlatformWithdrawal $withdrawal): PlatformWithdrawal
    {
        return DB::transaction(function () use ($withdrawal): PlatformWithdrawal {
            $locked = PlatformWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'otp_verified') $locked->update(['status' => 'processing', 'processing_at' => now(), 'provider_status' => 'submitted']);
            return $locked;
        }, 3);
    }

    private function settle(PlatformWithdrawal $withdrawal, array $data): string
    {
        $total = $this->totalDebited($withdrawal, $data);
        if ($total === null) {
            $this->unknown($withdrawal, 'La confirmation KPrimePay ne permet pas de vérifier le montant réellement débité.', $data);
            return 'amount_unverified';
        }
        $fees = $total - (int) $withdrawal->amount;
        if ($fees > (int) $withdrawal->estimated_fees) {
            $this->unknown($withdrawal, 'Les frais réels KPrimePay dépassent le plafond réservé. Le montant reste à vérifier.', $data);
            return 'fee_cap_exceeded';
        }
        DB::transaction(function () use ($withdrawal, $data, $fees): void {
            $locked = PlatformWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'succeeded') return;
            $locked->update(['status' => 'succeeded', 'provider_status' => $this->providerStatus($data) ?: 'success', 'kpp_reference' => $data['transfer_reference'] ?? $data['kpp_tx_reference'] ?? $locked->kpp_reference, 'fees' => $fees, 'succeeded_at' => now(), 'failure_reason' => null]);
            $this->audit($locked, 'platform.treasury.withdrawal.succeeded', ['actual_fees' => $fees, 'total_debited' => (int) $locked->amount + $fees]);
        }, 3);
        return 'success';
    }

    private function fail(PlatformWithdrawal $withdrawal, string $reason): void
    {
        DB::transaction(function () use ($withdrawal, $reason): void {
            $locked = PlatformWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, ['succeeded', 'failed'], true)) return;
            $locked->update(['status' => 'failed', 'failure_reason' => $reason, 'failed_at' => now()]);
            $this->audit($locked, 'platform.treasury.withdrawal.failed', ['reason' => $reason]);
        }, 3);
    }

    private function unknown(PlatformWithdrawal $withdrawal, string $reason, ?array $data, ?string $providerStatus = null): void
    {
        DB::transaction(function () use ($withdrawal, $reason, $data, $providerStatus): void {
            $locked = PlatformWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, ['succeeded', 'failed'], true)) return;
            $locked->update(['status' => 'unknown', 'provider_status' => $providerStatus ?: $this->providerStatus($data ?? []) ?: 'unknown', 'failure_reason' => $reason, 'unknown_at' => now()]);
            $this->audit($locked, 'platform.treasury.withdrawal.unknown', ['reason' => $reason]);
        }, 3);
    }

    private function auditRetry(PlatformWithdrawal $withdrawal, PlatformAdmin $actor, Request $request, string $reason, string $result): void
    {
        PlatformAuditLog::create([
            'platform_admin_id' => $actor->id,
            'action' => 'platform.treasury.withdrawal.retry_checked',
            'target_type' => PlatformWithdrawal::class,
            'target_id' => (string) $withdrawal->id,
            'new_values' => ['provider_status' => $withdrawal->fresh()->provider_status, 'result' => $result],
            'reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'result' => $result === 'failed' ? 'failed' : 'success',
        ]);
    }

    private function totalDebited(PlatformWithdrawal $withdrawal, array $data): ?int
    {
        $details = is_array($data['transaction_details'] ?? null) ? $data['transaction_details'] : [];
        $reportedAmount = $data['transaction_amount'] ?? $details['amount'] ?? null;
        if ($reportedAmount !== null && (int) $reportedAmount !== (int) $withdrawal->amount) return null;
        $total = $data['total_amount_debited'] ?? $data['total_amount'] ?? $details['total_amount'] ?? null;
        if ($total === null) $total = (int) $withdrawal->amount + (int) ($data['transaction_fees'] ?? $data['transfer_fees'] ?? $data['fees'] ?? $details['fees'] ?? 0) + (int) ($data['withdrawal_fees'] ?? $details['withdrawal_fees'] ?? 0);
        return (int) $total >= (int) $withdrawal->amount ? (int) $total : null;
    }

    private function providerStatus(array $data): string { return strtolower((string) ($data['status'] ?? $data['transfer_status'] ?? $data['transaction_status'] ?? '')); }
    private function isTransactionNotFound(KprimePayPayoutException $exception): bool
    {
        return str_contains(strtoupper($exception->providerCode.' '.$exception->getMessage()), 'TRANSACTION_NOT_FOUND');
    }

    private function audit(PlatformWithdrawal $withdrawal, string $action, array $values): void
    {
        PlatformAuditLog::create(['platform_admin_id' => $withdrawal->platform_admin_id, 'action' => $action, 'target_type' => PlatformWithdrawal::class, 'target_id' => (string) $withdrawal->id, 'new_values' => $values, 'result' => str_ends_with($action, '.failed') ? 'failed' : 'success']);
    }
}
