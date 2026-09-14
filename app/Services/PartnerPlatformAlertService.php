<?php

namespace App\Services;

use App\Jobs\SendPartnerPlatformAlert;
use App\Models\Partner;
use Illuminate\Support\Facades\Log;
use Throwable;

class PartnerPlatformAlertService
{
    public function dispatch(string $event, Partner $partner, string $eventKey, array $context = []): void
    {
        try {
            SendPartnerPlatformAlert::dispatch($event, (int) $partner->id, $eventKey, $context)->afterCommit();
        } catch (Throwable $exception) {
            report($exception);
            Log::error('Alerte e-mail partenaire impossible à planifier', [
                'event' => $event,
                'event_key' => $eventKey,
                'partner_id' => $partner->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
