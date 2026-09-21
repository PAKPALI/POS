<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlatformSecurityMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $title,
        public string $intro,
        public ?string $code = null,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public string $expiry = '10 minutes',
    ) {}

    public function build(): self
    {
        return $this->from(config('mail.from.address'), config('mail.brand_name'))
            ->subject(config('mail.brand_name').' — '.$this->title)
            ->view('emails.platform.security');
    }
}
