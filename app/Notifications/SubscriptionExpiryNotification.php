<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiryNotification extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription, public int $daysRemaining) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $company = $this->subscription->subscriptionAccount?->billingCompany?->name ?? config('app.name');
        $plan = $this->subscription->plan?->name ?? ($this->subscription->snapshot['name'] ?? 'votre abonnement');
        if ($this->daysRemaining > 0) {
            return (new MailMessage)->subject(config('app.name').' — votre abonnement expire dans '.$this->daysRemaining.' jour(s)')
                ->greeting('Bonjour '.$notifiable->name.',')
                ->line('L’abonnement '.$plan.' de '.$company.' arrive à échéance dans '.$this->daysRemaining.' jour(s).')
                ->line('Renouvelez-le depuis le menu Abonnement pour conserver vos fonctionnalités et vos quotas.')
                ->action('Gérer mon abonnement', route('subscriptions.index'))->salutation('L’équipe '.config('app.name'));
        }
        return (new MailMessage)->subject(config('app.name').' — abonnement arrivé à échéance')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('L’abonnement '.$plan.' de '.$company.' est arrivé à échéance.')
            ->line('Les données restent conservées. Renouvelez votre abonnement depuis le menu Abonnement pour rétablir les écritures protégées.')
            ->action('Renouveler mon abonnement', route('subscriptions.index'))->salutation('L’équipe '.config('app.name'));
    }
}
