<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerWithdrawalConfirmationNotification extends Notification
{
    use Queueable;

    public function __construct(private string $code) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name').' — confirmation de retrait')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre code de confirmation de retrait est :')
            ->line($this->code)
            ->line('Il expire dans 10 minutes. Ne le communiquez à personne.')
            ->salutation('L’équipe '.config('app.name'));
    }
}
