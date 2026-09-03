<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\PlatformAdmin;
use App\Models\QuotaPayment;
use App\Notifications\QuotaPaymentConfirmedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Throwable;

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

        $settled = DB::transaction(function () use ($payment, $eventId, $kppReference): bool {
            $locked = QuotaPayment::withoutCompanyScope()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'paid') return false;

            CompanySetting::withoutGlobalScopes()->whereKey($locked->company_id)->increment('sms_count', $locked->sms_quantity);
            CompanySetting::withoutGlobalScopes()->whereKey($locked->company_id)->increment('whatsapp_count', $locked->whatsapp_quantity);
            $updates = ['status' => 'paid', 'paid_at' => now(), 'failure_reason' => null];
            if ($eventId) $updates['event_id'] = $eventId;
            if ($kppReference) $updates['kpp_reference'] = $kppReference;
            $locked->update($updates);
            return true;
        }, 3);

        // L’envoi est hors transaction : une panne SMTP ne peut jamais annuler les crédits.
        try {
            $this->notifyPlatformAdmins($payment->id);
        } catch (Throwable $exception) {
            // Le paiement reste crédité même si le suivi d’une notification rencontre une erreur.
            report($exception);
        }
        return $settled;
    }

    private function notifyPlatformAdmins(int $paymentId): void
    {
        $payment = QuotaPayment::withoutCompanyScope()
            ->with(['company', 'user'])
            ->find($paymentId);
        if (!$payment || $payment->status !== 'paid') return;

        $configuration = app(PlatformConfigurationService::class);
        $admins = PlatformAdmin::query()->where('is_active', true)->whereNotNull('email')->where('email', '<>', '')->get();
        foreach ($admins as $admin) {
            $key = (string) $admin->id;
            if (!$this->claimAdminEmail($paymentId, $key, $admin->email)) continue;
            if (!$configuration->channelEnabled('email')) {
                $this->setAdminEmailState($paymentId, $key, ['status' => 'disabled', 'email' => $admin->email]);
                continue;
            }
            try {
                Notification::send($admin, new QuotaPaymentConfirmedNotification($payment));
                $this->setAdminEmailState($paymentId, $key, ['status' => 'sent', 'email' => $admin->email, 'sent_at' => now()->toIso8601String()]);
            } catch (Throwable $exception) {
                report($exception);
                $this->setAdminEmailState($paymentId, $key, ['status' => 'failed', 'email' => $admin->email, 'error' => class_basename($exception)]);
            }
        }
    }

    private function claimAdminEmail(int $paymentId, string $adminId, string $email): bool
    {
        $claimed = false;
        DB::transaction(function () use ($paymentId, $adminId, $email, &$claimed): void {
            $payment = QuotaPayment::withoutCompanyScope()->whereKey($paymentId)->lockForUpdate()->first();
            if (!$payment) return;
            $states = $payment->administration_email_status ?: [];
            $state = $states[$adminId] ?? [];
            if (($state['status'] ?? null) === 'sent') return;
            if (($state['status'] ?? null) === 'sending' && !empty($state['claimed_at']) && strtotime((string) $state['claimed_at']) > now()->subMinutes(10)->timestamp) return;
            $states[$adminId] = ['status' => 'sending', 'email' => $email, 'claimed_at' => now()->toIso8601String()];
            $payment->update(['administration_email_status' => $states]);
            $claimed = true;
        }, 3);
        return $claimed;
    }

    private function setAdminEmailState(int $paymentId, string $adminId, array $state): void
    {
        DB::transaction(function () use ($paymentId, $adminId, $state): void {
            $payment = QuotaPayment::withoutCompanyScope()->whereKey($paymentId)->lockForUpdate()->first();
            if (!$payment) return;
            $states = $payment->administration_email_status ?: [];
            $states[$adminId] = $state;
            $payment->update(['administration_email_status' => $states]);
        }, 3);
    }

    public function markFailed(QuotaPayment $payment, string $reason, ?string $eventId = null): void
    {
        DB::transaction(function () use ($payment, $reason, $eventId): void {
            $locked = QuotaPayment::withoutCompanyScope()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'paid') return;
            $updates = ['status' => 'failed', 'failure_reason' => mb_substr($reason ?: 'Paiement échoué', 0, 255), 'failed_at' => now()];
            if ($eventId) $updates['event_id'] = $eventId;
            $locked->update($updates);
        }, 3);
    }

    public function markExpired(QuotaPayment $payment): void
    {
        QuotaPayment::withoutCompanyScope()->whereKey($payment->id)->whereIn('status', ['created', 'pending'])->update([
            'status' => 'expired', 'failure_reason' => 'Checkout expiré sans paiement confirmé', 'failed_at' => now(),
        ]);
    }
}
