<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerTwoFactorNotification extends Notification
{
    use Queueable;

    public function __construct(private string $code) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name').' — code de connexion partenaire')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre code de connexion est :')
            ->line($this->code)
            ->line('Il expire dans 10 minutes et ne peut être utilisé qu’une fois.')
            ->salutation('L’équipe '.config('app.name'));
    }
}
