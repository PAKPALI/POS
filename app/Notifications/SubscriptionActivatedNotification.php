<?php

namespace App\Notifications;

use App\Models\PlatformAdmin;
use App\Models\SubscriptionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivatedNotification extends Notification
{
    use Queueable;

    public function __construct(public SubscriptionPayment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment;
        $account = $payment->subscriptionAccount;
        $company = $account?->billingCompany;
        $companyName = $company?->name ?? 'Entreprise non renseignée';
        $plan = $payment->snapshot['name'] ?? $payment->plan?->name ?? 'Plan non renseigné';
        $months = (int) ($payment->duration_months ?: ($payment->billing_period === 'annual' ? 12 : 1));
        $subscription = $payment->subscription;
        $startsAt = $subscription?->starts_at?->format('d/m/Y H:i') ?? '—';
        $endsAt = $subscription?->ends_at?->format('d/m/Y H:i') ?? '—';
        $currency = $payment->currency ?: 'XOF';
        $grossAmount = (int) ($payment->gross_amount ?? data_get($payment->snapshot, 'gross_amount', $payment->amount));
        $discountAmount = (int) ($payment->discount_amount ?? data_get($payment->snapshot, 'discount_amount', 0));
        $netAmount = (int) $payment->amount;
        $operation = match ($payment->operation) {
            'upgrade' => 'Montée de plan',
            'renewal' => 'Renouvellement',
            default => ucfirst((string) $payment->operation),
        };

        $attribution = $account?->partnerAttribution;
        $commission = $payment->partnerCommission;
        $isFirstSubscription = $attribution
            && (int) $attribution->first_subscription_payment_id === (int) $payment->id;
        $actionUrl = null;
        $actionLabel = null;

        if ($notifiable instanceof PlatformAdmin && $notifiable->hasPlatformPermission('platform.admins.manage')) {
            $actionUrl = route('platform.subscriptions.preflight');
            $actionLabel = 'Ouvrir le pré-contrôle';
        }

        $mail = (new MailMessage)
            ->subject(config('mail.brand_name').' — nouvel abonnement confirmé')
            ->view('emails.platform.subscriptionActivated', [
                'payment' => $payment,
                'company' => $company,
                'companyName' => $companyName,
                'plan' => $plan,
                'months' => $months,
                'operation' => $operation,
                'currency' => $currency,
                'grossAmount' => $grossAmount,
                'discountAmount' => $discountAmount,
                'netAmount' => $netAmount,
                'startsAt' => $startsAt,
                'endsAt' => $endsAt,
                'attribution' => $attribution,
                'commission' => $commission,
                'isFirstSubscription' => (bool) $isFirstSubscription,
                'actionUrl' => $actionUrl,
                'actionLabel' => $actionLabel,
            ]);

        return $mail;
    }
}
