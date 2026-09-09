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
            ->greeting('Bonjour '.$this->partner->name.',')
            ->line('Confirmez votre adresse e-mail pour activer votre espace Partenaires Maxanou.')
            ->action('Confirmer mon adresse', $url)
            ->line('Ce lien expire dans 60 minutes.')
            ->salutation('L’équipe '.config('app.name'));
    }
}
