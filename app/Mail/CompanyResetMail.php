<?php

namespace App\Mail;

use App\Models\CompanyResetRequest;
use Illuminate\Mail\Mailable;

class CompanyResetMail extends Mailable
{
    public function __construct(public CompanyResetRequest $reset, public ?string $code = null) {}

    public function build(): self
    {
        return $this->subject(config('mail.brand_name').' — '.($this->code ? 'Confirmer la réinitialisation' : 'Entreprise réinitialisée'))
            ->view('emails.company.reset');
    }
}
