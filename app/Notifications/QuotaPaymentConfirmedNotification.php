<?php

namespace App\Notifications;

use App\Models\PlatformAdmin;
use App\Models\QuotaPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotaPaymentConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(public QuotaPayment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment;
        $company = $payment->company?->name ?? 'Entreprise non renseignée';
        $buyer = $payment->user?->name ?? $payment->user?->email ?? 'Utilisateur non renseigné';
        $amount = number_format((float) $payment->amount, 0, ',', ' ').' '.($payment->currency ?: 'XOF');
        $mail = (new MailMessage)
            ->subject(config('app.name').' — paiement de quotas confirmé')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Un paiement de quotas vient d’être confirmé pour l’entreprise '.$company.'.')
            ->line('Acheteur : '.$buyer)
            ->line('Crédits ajoutés : '.$payment->sms_quantity.' SMS et '.$payment->whatsapp_quantity.' WhatsApp')
            ->line('Montant confirmé : '.$amount)
            ->line('Transaction : '.$payment->transaction_id)
            ->line('Référence de paiement : '.($payment->kpp_reference ?: '—'))
            ->line('Date de confirmation : '.($payment->paid_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')))
            ->salutation('L’équipe '.config('app.name'));

        if ($notifiable instanceof PlatformAdmin && $notifiable->hasPlatformPermission('platform.payments.view')) {
            $mail->action('Voir les paiements', route('platform.payments.index'));
        }

        return $mail;
    }
}
