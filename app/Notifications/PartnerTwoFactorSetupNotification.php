<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerTwoFactorSetupNotification extends Notification
{
    use Queueable;

    public function __construct(private string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('mail.brand_name').' — activation de la double authentification')
            ->view('emails.partner.twoFactorSetup', [
                'name' => $notifiable->name,
                'code' => $this->code,
                'expiry' => '10 minutes',
            ]);
    }
}
