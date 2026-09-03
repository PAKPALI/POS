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
        $company = $account?->billingCompany?->name ?? 'Entreprise non renseignée';
        $plan = $payment->snapshot['name'] ?? $payment->plan?->name ?? 'Plan non renseigné';
        $months = (int) ($payment->duration_months ?: ($payment->billing_period === 'annual' ? 12 : 1));
        $subscription = $payment->subscription;
        $startsAt = $subscription?->starts_at?->format('d/m/Y H:i') ?? '—';
        $endsAt = $subscription?->ends_at?->format('d/m/Y H:i') ?? '—';
        $amount = number_format((float) $payment->amount, 0, ',', ' ').' '.($payment->currency ?: 'XOF');
        $operation = match ($payment->operation) {
            'upgrade' => 'Montée de plan',
            'renewal' => 'Renouvellement',
            default => ucfirst((string) $payment->operation),
        };

        $mail = (new MailMessage)
            ->subject(config('app.name').' — nouvel abonnement confirmé')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Un abonnement vient d’être confirmé pour l’entreprise '.$company.'.')
            ->line('Plan : '.$plan.' — '.$months.' mois ('.$operation.')')
            ->line('Montant confirmé : '.$amount)
            ->line('Période : du '.$startsAt.' au '.$endsAt)
            ->line('Transaction : '.$payment->transaction_id)
            ->line('Référence de paiement : '.($payment->kpp_reference ?: '—'))
            ->salutation('L’équipe '.config('app.name'));

        if ($notifiable instanceof PlatformAdmin && $notifiable->hasPlatformPermission('platform.admins.manage')) {
            $mail->action('Ouvrir le pré-contrôle', route('platform.subscriptions.preflight'));
        }

        return $mail;
    }
}
