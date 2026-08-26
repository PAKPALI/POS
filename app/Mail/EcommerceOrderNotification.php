<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EcommerceOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Company $company,
    ) {}

    public function build(): self
    {
        $this->from(config('mail.from.address'), $this->company->name);

        return $this->subject($this->company->name.' — Nouvelle commande #'.$this->order->code)
            ->view('emails.ecommerce.orderNotification');
    }
}
