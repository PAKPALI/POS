<?php

namespace App\Console\Commands;

use App\Models\PlatformSystemHeartbeat;
use Illuminate\Console\Command;

class RecordPlatformHeartbeat extends Command
{
    protected $signature = 'platform:heartbeat';
    protected $description = 'Enregistre le passage du planificateur Laravel pour la supervision SaaS';

    public function handle(): int
    {
        PlatformSystemHeartbeat::updateOrCreate(['key' => 'scheduler'], [
            'last_seen_at' => now(),
            'metadata' => ['environment' => app()->environment(), 'php' => PHP_VERSION],
        ]);
        return self::SUCCESS;
    }
}
