<?php

namespace App\Exceptions;

use RuntimeException;

class KprimePayPayoutException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $providerCode = '',
        public readonly int $httpStatus = 0,
        public readonly array $payload = [],
    ) {
        parent::__construct($message);
    }
}
