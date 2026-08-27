<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(config('app.name').' — Réinitialisation du mot de passe')
            ->view('emails.user.resetPassword', [
                'user' => $notifiable,
                'resetUrl' => $url,
                'expiresInMinutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
                'company' => null,
            ]);
    }
}
