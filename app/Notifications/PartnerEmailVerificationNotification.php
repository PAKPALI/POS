<?php

namespace App\Notifications;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PartnerEmailVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(private Partner $partner) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute('partner.email.verify', now()->addMinutes(60), [
            'partner' => $this->partner->id,
            'hash' => sha1($this->partner->getEmailForVerification()),
        ]);

        return (new MailMessage)
            ->subject(config('app.name').' — confirmez votre adresse partenaire')
            ->view('emails.partner.emailVerification', [
                'partner' => $this->partner,
                'verificationUrl' => $url,
                'expiresInMinutes' => 60,
            ]);
    }
}
