<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $user,
        public Company $company,
        public string $roleName = 'Utilisateur',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name').' — nouveau compte utilisateur')
            ->view('emails.platform.userRegistered', [
                'user' => $this->user,
                'company' => $this->company,
                'roleName' => $this->roleName,
                'registeredAt' => $this->user->created_at,
            ]);
    }
}
