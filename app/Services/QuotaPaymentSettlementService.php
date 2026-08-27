<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\QuotaPayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuotaPaymentSettlementService
{
    public function creditVerified(
        QuotaPayment $payment,
        array $verified,
        ?string $eventId = null,
        ?string $kppReference = null,
    ): bool {
        if (strtolower((string) ($verified['status'] ?? '')) !== 'success'
            || (string) ($verified['transaction_currency'] ?? '') !== $payment->currency
            || (int) ($verified['transaction_amount'] ?? -1) !== (int) $payment->amount) {
            throw new RuntimeException('PAYMENT_MISMATCH');
        }

        return DB::transaction(function () use ($payment, $eventId, $kppReference): bool {
            $locked = QuotaPayment::withoutCompanyScope()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'paid') {
                return false;
            }

            CompanySetting::withoutGlobalScopes()->whereKey($locked->company_id)
                ->increment('sms_count', $locked->sms_quantity);
            CompanySetting::withoutGlobalScopes()->whereKey($locked->company_id)
                ->increment('whatsapp_count', $locked->whatsapp_quantity);

            $updates = [
                'status' => 'paid',
                'paid_at' => now(),
                'failure_reason' => null,
            ];
            if ($eventId) $updates['event_id'] = $eventId;
            if ($kppReference) $updates['kpp_reference'] = $kppReference;
            $locked->update($updates);

            return true;
        }, 3);
    }

    public function markFailed(QuotaPayment $payment, string $reason, ?string $eventId = null): void
    {
        DB::transaction(function () use ($payment, $reason, $eventId): void {
            $locked = QuotaPayment::withoutCompanyScope()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'paid') return;
            $updates = [
                'status' => 'failed',
                'failure_reason' => mb_substr($reason ?: 'Paiement échoué', 0, 255),
                'failed_at' => now(),
            ];
            if ($eventId) $updates['event_id'] = $eventId;
            $locked->update($updates);
        }, 3);
    }

    public function markExpired(QuotaPayment $payment): void
    {
        QuotaPayment::withoutCompanyScope()
            ->whereKey($payment->id)
            ->whereIn('status', ['created', 'pending'])
            ->update([
                'status' => 'expired',
                'failure_reason' => 'Checkout expiré sans paiement confirmé',
                'failed_at' => now(),
            ]);
    }
}
