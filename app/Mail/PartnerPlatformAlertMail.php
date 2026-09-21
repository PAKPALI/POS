<?php

namespace App\Mail;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PartnerPlatformAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $title,
        public string $intro,
        public Partner $partner,
        public array $details,
        public string $actionUrl,
    ) {}

    public function build(): self
    {
        return $this->from(config('mail.from.address'), config('mail.brand_name'))
            ->subject(config('mail.brand_name').' — '.$this->title)
            ->view('emails.platform.partnerAlert');
    }
}
