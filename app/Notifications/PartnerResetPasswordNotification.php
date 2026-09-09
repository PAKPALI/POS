<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private string $token) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name').' — réinitialisation partenaire')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Une demande de réinitialisation a été faite pour votre espace partenaire.')
            ->action('Choisir un nouveau mot de passe', route('partner.password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line('Ce lien expire dans 60 minutes.')
            ->salutation('L’équipe '.config('app.name'));
    }
}
