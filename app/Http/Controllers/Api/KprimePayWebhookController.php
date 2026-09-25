<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuotaPayment;
use App\Models\SubscriptionPayment;
use App\Models\PartnerWithdrawal;
use App\Models\PlatformWithdrawal;
use App\Services\KprimePayService;
use App\Services\KprimePayWebhookRelayService;
use App\Services\PartnerPayoutService;
use App\Services\PlatformTreasuryPayoutService;
use App\Services\QuotaPaymentSettlementService;
use App\Services\SubscriptionSettlementService;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class KprimePayWebhookController extends Controller
{
    public function __invoke(Request $request, KprimePayService $kprimePay, QuotaPaymentSettlementService $settlement, SubscriptionSettlementService $subscriptionSettlement, PartnerPayoutService $payouts, PlatformTreasuryPayoutService $treasuryPayouts, KprimePayWebhookRelayService $relay)
    {
        return $this->processWebhook(
            $request->all(),
            $this->providerHeaders($request),
            $kprimePay,
            $settlement,
            $subscriptionSettlement,
            $payouts,
            $treasuryPayouts,
            app()->environment('production') && $relay->enabled(),
            $relay,
        );
    }

    public function relay(Request $request, KprimePayService $kprimePay, QuotaPaymentSettlementService $settlement, SubscriptionSettlementService $subscriptionSettlement, PartnerPayoutService $payouts, PlatformTreasuryPayoutService $treasuryPayouts, KprimePayWebhookRelayService $relay)
    {
        $envelope = $relay->decodeRelayRequest($request);
        if ($envelope === null) {
            return response()->json(['status' => false, 'message' => 'INVALID_RELAY'], 401);
        }

        return $this->processWebhook(
            $envelope['payload'],
            $envelope['provider_headers'],
            $kprimePay,
            $settlement,
            $subscriptionSettlement,
            $payouts,
            $treasuryPayouts,
            false,
            $relay,
        );
    }

    private function processWebhook(array $payload, array $providerHeaders, KprimePayService $kprimePay, QuotaPaymentSettlementService $settlement, SubscriptionSettlementService $subscriptionSettlement, PartnerPayoutService $payouts, PlatformTreasuryPayoutService $treasuryPayouts, bool $allowRelay, KprimePayWebhookRelayService $relay)
    {
        $webhook = $this->normalizeWebhook($payload, $providerHeaders);
        if ($webhook === null) {
            return response()->json(['status' => false, 'message' => 'INVALID_WEBHOOK'], 400);
        }

        $withdrawal = PartnerWithdrawal::where('transaction_id', $webhook['transaction_id'])->first();
        if ($withdrawal) {
            if (!str_starts_with($webhook['event'], 'transfer.')) {
                return response()->json(['status' => true, 'message' => 'IGNORED']);
            }
            $result = $payouts->handleWebhook($withdrawal, $webhook, $payload);
            return response()->json(['status' => $result !== 'VERIFICATION_UNAVAILABLE', 'message' => $result], $result === 'VERIFICATION_UNAVAILABLE' ? 503 : 200);
        }

        $platformWithdrawal = PlatformWithdrawal::where('transaction_id', $webhook['transaction_id'])->first();
        if ($platformWithdrawal) {
            if (!str_starts_with($webhook['event'], 'transfer.')) {
                return response()->json(['status' => true, 'message' => 'IGNORED']);
            }
            $result = $treasuryPayouts->handleWebhook($platformWithdrawal, $webhook);
            return response()->json(['status' => $result !== 'VERIFICATION_UNAVAILABLE', 'message' => $result], $result === 'VERIFICATION_UNAVAILABLE' ? 503 : 200);
        }

        $subscriptionPayment = SubscriptionPayment::where('transaction_id', $webhook['transaction_id'])->first();
        if ($subscriptionPayment) {
            if ($webhook['event'] === 'collection.failed') {
                if ($subscriptionPayment->status !== 'paid') {
                    $subscriptionSettlement->markFailed($subscriptionPayment, $webhook['failure_reason'], $webhook['event_id']);
                }

                return response()->json(['status' => true, 'message' => 'FAILED']);
            }
            if ($webhook['event'] !== 'collection.succeeded') return response()->json(['status'=>true]);
            if ($webhook['currency'] !== $subscriptionPayment->currency || $webhook['amount'] !== (int) $subscriptionPayment->amount) {
                return response()->json(['status' => false, 'message' => 'PAYMENT_MISMATCH'], 422);
            }
            try { $verified=$kprimePay->paymentStatus($webhook['transaction_id']); $subscriptionSettlement->creditVerified($subscriptionPayment,$verified,$webhook['event_id'],$webhook['kpp_reference']); return response()->json(['status'=>true,'message'=>'SETTLED']); }
            catch (\RuntimeException $e) { return response()->json(['status'=>false,'message'=>$e->getMessage()],422); }
            catch (Throwable $e) { report($e); return response()->json(['status'=>false,'message'=>'VERIFICATION_UNAVAILABLE'],503); }
        }
        $payment = QuotaPayment::withoutCompanyScope()->where('transaction_id', $webhook['transaction_id'])->first();
        if (!$payment) {
            if ($allowRelay && str_starts_with($webhook['event'], 'collection.') && $relay->forward($payload, $providerHeaders)) {
                return response()->json(['status' => true, 'message' => 'RELAYED']);
            }

            if ($allowRelay && str_starts_with($webhook['event'], 'collection.') && $relay->enabled()) {
                return response()->json(['status' => false, 'message' => 'STAGING_RELAY_UNAVAILABLE'], 503);
            }

            return response()->json(['status' => true, 'message' => 'IGNORED']);
        }
        if (QuotaPayment::withoutCompanyScope()->where('event_id', $webhook['event_id'])->where('id', '!=', $payment->id)->exists()) {
            return response()->json(['status' => true, 'message' => 'DUPLICATE']);
        }

        if ($webhook['event'] === 'collection.failed') {
            if ($payment->status !== 'paid') {
                $settlement->markFailed($payment, $webhook['failure_reason'], $webhook['event_id']);
            }
            return response()->json(['status' => true]);
        }
        if ($webhook['event'] !== 'collection.succeeded') {
            return response()->json(['status' => true, 'message' => 'IGNORED']);
        }

        try {
            $verified = $kprimePay->paymentStatus($webhook['transaction_id']);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['status' => false, 'message' => 'VERIFICATION_UNAVAILABLE'], 503);
        }

        if ($webhook['currency'] !== $payment->currency || $webhook['amount'] !== (int) $payment->amount) {
            return response()->json(['status' => false, 'message' => 'PAYMENT_MISMATCH'], 422);
        }

        try {
            $settlement->creditVerified($payment, $verified, $webhook['event_id'], $webhook['kpp_reference']);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'PAYMENT_MISMATCH') {
                return response()->json(['status' => false, 'message' => 'PAYMENT_MISMATCH'], 422);
            }
            throw $exception;
        }

        return response()->json(['status' => true, 'message' => 'CREDITED']);
    }

    /** Normalise les callbacks KPrimePay V1 et V2 vers un format interne unique. */
    private function normalizeWebhook(array $payload, array $providerHeaders): ?array
    {
        $transactionId = (string) data_get($payload, 'data.transaction_id', '');
        if ($transactionId === '') {
            return null;
        }

        if (($payload['api_version'] ?? null) === '2.0') {
            $event = (string) ($payload['event'] ?? '');
            $eventId = (string) ($payload['event_id'] ?? '');
            if (($providerHeaders['X-API-BY'] ?? null) !== 'KPRIMESOFT'
                || ($providerHeaders['X-KPP-EVENT'] ?? null) !== $event
                || ($providerHeaders['X-KPP-EVENT-ID'] ?? null) !== $eventId
                || $eventId === '') {
                return null;
            }

            return [
                'event' => $event,
                'event_id' => $eventId,
                'transaction_id' => $transactionId,
                'amount' => (int) data_get($payload, 'data.transaction_details.amount', -1),
                'currency' => (string) data_get($payload, 'data.transaction_details.currency', ''),
                'kpp_reference' => (string) data_get($payload, 'data.kpp_reference', ''),
                'failure_reason' => (string) data_get($payload, 'data.failure_reason', 'Paiement échoué'),
                'provider_status' => (string) data_get($payload, 'data.status', ''),
            ];
        }

        if (($payload['object'] ?? null) !== 'payment' || ($payload['type'] ?? null) !== 'payment.web.checkout') {
            return null;
        }

        $rootStatus = strtolower((string) ($payload['status'] ?? ''));
        $paymentStatus = strtoupper((string) data_get($payload, 'data.payment_status', ''));
        $succeeded = $rootStatus === 'success' && $paymentStatus === 'TRANSACTION-COMPLETED';
        $failed = in_array($rootStatus, ['failed', 'failure', 'error'], true)
            || str_contains($paymentStatus, 'FAILED') || str_contains($paymentStatus, 'CANCEL');
        $fingerprint = implode('|', [
            $transactionId,
            (string) data_get($payload, 'data.kpp_tx_reference', ''),
            $rootStatus,
            $paymentStatus,
            (string) data_get($payload, 'data.payment_date', ''),
        ]);

        return [
            'event' => $succeeded ? 'collection.succeeded' : ($failed ? 'collection.failed' : 'collection.pending'),
            'event_id' => 'v1_'.hash('sha256', $fingerprint),
            'transaction_id' => $transactionId,
            'amount' => (int) data_get($payload, 'data.transaction_amount', -1),
            'currency' => (string) data_get($payload, 'data.transaction_currency', ''),
            'kpp_reference' => (string) data_get($payload, 'data.kpp_tx_reference', ''),
            'failure_reason' => (string) data_get($payload, 'data.failure_reason', 'Paiement échoué'),
        ];
    }

    private function providerHeaders(Request $request): array
    {
        return [
            'X-API-BY' => (string) $request->header('X-API-BY', ''),
            'X-KPP-EVENT' => (string) $request->header('X-KPP-EVENT', ''),
            'X-KPP-EVENT-ID' => (string) $request->header('X-KPP-EVENT-ID', ''),
        ];
    }
}
