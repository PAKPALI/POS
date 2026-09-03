<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PlatformAdmin;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Models\SubscriptionPayment;
use App\Notifications\SubscriptionActivatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Throwable;

class SubscriptionSettlementService
{
    public function creditVerified(SubscriptionPayment $payment, array $verified, ?string $eventId = null, ?string $reference = null): bool
    {
        if (strtolower((string) ($verified['status'] ?? '')) !== 'success'
            || (string) ($verified['transaction_currency'] ?? '') !== $payment->currency
            || (int) ($verified['transaction_amount'] ?? -1) !== (int) $payment->amount) {
            throw new RuntimeException('PAYMENT_MISMATCH');
        }

        $settled = DB::transaction(function () use ($payment, $eventId, $reference): bool {
            $p = SubscriptionPayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($p->status === 'paid') return false;

            $current = Subscription::where('subscription_account_id', $p->subscription_account_id)
                ->whereIn('status', ['trial', 'active'])
                ->lockForUpdate()->orderByDesc('ends_at')->first();
            $now = now();
            $upgrade = $current && (int) ($p->snapshot['rank'] ?? 0) > (int) ($current->snapshot['rank'] ?? 0);
            if ($current) $current->update(['status' => 'superseded']);
            $start = $upgrade ? $now : ($current && $current->ends_at->isFuture() ? $current->ends_at : $now);
            $months = (int) $p->duration_months;
            if ($months < 1 || ($months === 1 && $p->billing_period === 'annual')) $months = $p->billing_period === 'annual' ? 12 : 1;
            $end = $start->copy()->addMonths($months);
            $subscription = Subscription::create([
                'subscription_account_id' => $p->subscription_account_id,
                'subscription_plan_id' => $p->subscription_plan_id,
                'status' => 'active', 'billing_period' => $months === 12 ? 'annual' : 'monthly',
                'duration_months' => $months, 'starts_at' => $start, 'ends_at' => $end, 'snapshot' => $p->snapshot,
            ]);
            $companyId = $p->subscriptionAccount->billing_company_id;
            Company::withoutGlobalScopes()->whereKey($companyId)->increment('sms_count', (int) $p->snapshot['sms_quota'] * $months);
            Company::withoutGlobalScopes()->whereKey($companyId)->increment('whatsapp_count', (int) $p->snapshot['whatsapp_quota'] * $months);
            $p->update([
                'status' => 'paid', 'paid_at' => $now, 'event_id' => $eventId,
                'kpp_reference' => $reference ?: $p->kpp_reference, 'subscription_id' => $subscription->id, 'failure_reason' => null,
            ]);
            SubscriptionEvent::firstOrCreate(
                ['subscription_account_id' => $p->subscription_account_id, 'event_key' => 'payment:'.$p->transaction_id],
                ['subscription_id' => $subscription->id, 'payload' => ['operation' => $p->operation], 'occurred_at' => $now]
            );
            return true;
        }, 3);

        // Une panne SMTP ne peut jamais annuler un paiement confirmé.
        $this->notifyPlatformAdmins($payment->fresh(['subscriptionAccount.billingCompany', 'plan', 'subscription']));
        return $settled;
    }

    private function notifyPlatformAdmins(?SubscriptionPayment $payment): void
    {
        if (!$payment || $payment->status !== 'paid' || !$payment->subscription_id) return;
        $configuration = app(PlatformConfigurationService::class);
        $event = SubscriptionEvent::where('subscription_account_id', $payment->subscription_account_id)
            ->where('event_key', 'payment:'.$payment->transaction_id)->first();
        if (!$event) return;
        $admins = PlatformAdmin::query()->where('is_active', true)->whereNotNull('email')->where('email', '<>', '')->get();
        foreach ($admins as $admin) {
            $key = (string) $admin->id;
            if (!$this->claimAdminEmail($event->id, $key, $admin->email)) continue;
            if (!$configuration->channelEnabled('email')) {
                $this->setAdminEmailState($event->id, $key, ['status' => 'disabled', 'email' => $admin->email]);
                continue;
            }
            try {
                Notification::send($admin, new SubscriptionActivatedNotification($payment));
                $this->setAdminEmailState($event->id, $key, ['status' => 'sent', 'email' => $admin->email, 'sent_at' => now()->toIso8601String()]);
            } catch (Throwable $exception) {
                report($exception);
                $this->setAdminEmailState($event->id, $key, ['status' => 'failed', 'email' => $admin->email, 'error' => class_basename($exception)]);
            }
        }
    }

    private function claimAdminEmail(int $eventId, string $adminId, string $email): bool
    {
        $claimed = false;
        DB::transaction(function () use ($eventId, $adminId, $email, &$claimed): void {
            $event = SubscriptionEvent::whereKey($eventId)->lockForUpdate()->first();
            if (!$event) return;
            $payload = $event->payload ?: [];
            $state = $payload['administration_email'][$adminId] ?? [];
            if (($state['status'] ?? null) === 'sent') return;
            if (($state['status'] ?? null) === 'sending' && !empty($state['claimed_at']) && strtotime((string) $state['claimed_at']) > now()->subMinutes(10)->timestamp) return;
            $payload['administration_email'][$adminId] = ['status' => 'sending', 'email' => $email, 'claimed_at' => now()->toIso8601String()];
            $event->update(['payload' => $payload]);
            $claimed = true;
        }, 3);
        return $claimed;
    }

    private function setAdminEmailState(int $eventId, string $adminId, array $state): void
    {
        DB::transaction(function () use ($eventId, $adminId, $state): void {
            $event = SubscriptionEvent::whereKey($eventId)->lockForUpdate()->first();
            if (!$event) return;
            $payload = $event->payload ?: [];
            $payload['administration_email'][$adminId] = $state;
            $event->update(['payload' => $payload]);
        }, 3);
    }

    public function markFailed(SubscriptionPayment $payment, string $reason, ?string $eventId = null): void
    {
        DB::transaction(function () use ($payment, $reason, $eventId): void {
            $locked = SubscriptionPayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'paid') return;
            $updates = ['status' => 'failed', 'failure_reason' => mb_substr($reason ?: 'Paiement échoué', 0, 255), 'failed_at' => now()];
            if ($eventId) $updates['event_id'] = $eventId;
            $locked->update($updates);
        }, 3);
    }

    public function markExpired(SubscriptionPayment $payment): void
    {
        SubscriptionPayment::whereKey($payment->id)->whereIn('status', ['created', 'pending'])->update([
            'status' => 'expired', 'failure_reason' => 'Checkout expiré sans paiement confirmé', 'failed_at' => now(),
        ]);
    }
}
