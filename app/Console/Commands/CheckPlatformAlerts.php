<?php

namespace App\Console\Commands;

use App\Services\PlatformAlertService;
use Illuminate\Console\Command;

class CheckPlatformAlerts extends Command
{
    protected $signature = 'platform:check-alerts';
    protected $description = 'Détecte et notifie les incidents opérationnels de la plateforme';

    public function handle(PlatformAlertService $service): int
    {
        $this->info($service->inspect().' anomalie(s) active(s) détectée(s).');
        return self::SUCCESS;
    }
}
