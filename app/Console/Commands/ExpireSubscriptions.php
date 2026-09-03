<?php

namespace App\Console\Commands;

use App\Models\CompanyUser;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Notifications\SubscriptionExpiryNotification;
use App\Services\PlatformConfigurationService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expire les abonnements et envoie les rappels e-mail idempotents';

    public function handle(PlatformConfigurationService $configuration): int
    {
        $now = now();
        Subscription::with(['plan', 'subscriptionAccount.owner', 'subscriptionAccount.billingCompany'])->whereIn('status', ['trial', 'active'])->where('ends_at', '<=', $now)->chunkById(100, function ($subscriptions) use ($configuration) {
            foreach ($subscriptions as $subscription) {
                $subscription->update(['status' => 'expired']);
                $event = SubscriptionEvent::firstOrCreate(['subscription_account_id' => $subscription->subscription_account_id, 'event_key' => 'expired:'.$subscription->id], ['subscription_id' => $subscription->id, 'payload' => [], 'occurred_at' => now()]);
                $this->deliver($event, $subscription, 0, $configuration);
            }
        });
        Subscription::with(['plan', 'subscriptionAccount.owner', 'subscriptionAccount.billingCompany'])->whereIn('status', ['trial', 'active'])->whereBetween('ends_at', [$now, $now->copy()->addDays(3)])->chunkById(100, function ($subscriptions) use ($configuration, $now) {
            foreach ($subscriptions as $subscription) {
                $days = (int) $now->copy()->startOfDay()->diffInDays($subscription->ends_at->copy()->startOfDay(), false);
                if (!in_array($days, [1, 2, 3], true)) continue;
                $event = SubscriptionEvent::firstOrCreate(['subscription_account_id' => $subscription->subscription_account_id, 'event_key' => 'reminder:'.$subscription->id.':'.$now->toDateString()], ['subscription_id' => $subscription->id, 'payload' => ['days_remaining' => $days], 'occurred_at' => now()]);
                $this->deliver($event, $subscription, $days, $configuration);
            }
        });
        return self::SUCCESS;
    }

    private function deliver(SubscriptionEvent $event, Subscription $subscription, int $days, PlatformConfigurationService $configuration): void
    {
        $payload = $event->payload ?: [];
        $payload['days_remaining'] = $days;
        $payload['email'] = $payload['email'] ?? [];
        foreach ($this->recipients($subscription) as $recipient) {
            $key = (string) $recipient->id;
            if (($payload['email'][$key]['status'] ?? null) === 'sent') continue;
            if (!$configuration->channelEnabled('email')) { $payload['email'][$key] = ['status' => 'disabled', 'email' => $recipient->email]; continue; }
            try {
                Notification::send($recipient, new SubscriptionExpiryNotification($subscription, $days));
                $payload['email'][$key] = ['status' => 'sent', 'email' => $recipient->email, 'sent_at' => now()->toIso8601String()];
            } catch (Throwable $exception) {
                report($exception);
                $payload['email'][$key] = ['status' => 'failed', 'email' => $recipient->email, 'error' => class_basename($exception)];
            }
        }
        $event->update(['payload' => $payload]);
    }

    private function recipients(Subscription $subscription): Collection
    {
        $account = $subscription->subscriptionAccount;
        $owner = $account?->owner;
        $company = $account?->billingCompany;
        $admins = $company ? CompanyUser::with(['user', 'role'])->where('company_id', $company->id)->where('status', 'active')->get()->filter(fn (CompanyUser $membership) => $membership->role?->key === 'admin')->pluck('user') : collect();
        return collect([$owner])->merge($admins)->filter(fn ($user) => $user && filled($user->email))->unique('id')->values();
    }
}
