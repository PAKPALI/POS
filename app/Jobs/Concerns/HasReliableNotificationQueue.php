<?php

namespace App\Jobs\Concerns;

trait HasReliableNotificationQueue
{
    public int $tries = 3;

    public int $timeout = 120;

    public bool $failOnTimeout = true;

    public function backoff(): array
    {
        return [60, 300];
    }
}
