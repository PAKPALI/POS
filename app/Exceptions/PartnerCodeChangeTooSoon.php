<?php

namespace App\Exceptions;

use Carbon\CarbonImmutable;
use RuntimeException;

class PartnerCodeChangeTooSoon extends RuntimeException
{
    public function __construct(public readonly CarbonImmutable $availableAt)
    {
        parent::__construct('Votre code partenaire pourra être modifié à partir du '.$availableAt->format('d/m/Y').'.');
    }
}
