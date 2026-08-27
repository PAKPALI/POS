<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuotaPayment;
use App\Services\KprimePayService;
use App\Services\QuotaPaymentSettlementService;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class KprimePayWebhookController extends Controller
{
    public function __invoke(Request $request, KprimePayService $kprimePay, QuotaPaymentSettlementService $settlement)
    {
        $webhook = $this->normalizeWebhook($request, $request->all());
        if ($webhook === null) {
            return response()->json(['status' => false, 'message' => 'INVALID_WEBHOOK'], 400);
        }

        $payment = QuotaPayment::withoutCompanyScope()->where('transaction_id', $webhook['transaction_id'])->first();
        if (!$payment) {
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
    private function normalizeWebhook(Request $request, array $payload): ?array
    {
        $transactionId = (string) data_get($payload, 'data.transaction_id', '');
        if ($transactionId === '') {
            return null;
        }

        if (($payload['api_version'] ?? null) === '2.0') {
            $event = (string) ($payload['event'] ?? '');
            $eventId = (string) ($payload['event_id'] ?? '');
            if ($request->header('X-API-BY') !== 'KPRIMESOFT'
                || $request->header('X-KPP-EVENT') !== $event
                || $request->header('X-KPP-EVENT-ID') !== $eventId
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
}
