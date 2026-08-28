<?php

namespace App\Services;

use App\Models\NotificationDelivery;
use App\Models\CompanyUser;
use Closure;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class NotificationDeliveryService
{
    public function deliver(
        int $companyId,
        string $eventType,
        string|int $eventKey,
        string $category,
        string $channel,
        int $userId,
        Closure $sender,
    ): bool {
        if (!app(PlatformConfigurationService::class)->channelEnabled($channel)) {
            return false;
        }
        $activeMembershipExists = CompanyUser::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
        if (!$activeMembershipExists) {
            throw new RuntimeException('Le destinataire n’est plus membre actif de cette compagnie.');
        }

        $delivery = NotificationDelivery::firstOrCreate([
            'company_id' => $companyId,
            'event_type' => $eventType,
            'event_key' => (string) $eventKey,
            'channel' => $channel,
            'user_id' => $userId,
        ], [
            'category' => $category,
            'status' => 'pending',
        ]);

        $claimed = DB::transaction(function () use ($delivery, $category): bool {
            $lockedDelivery = NotificationDelivery::whereKey($delivery->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDelivery->status === 'sent') {
                return false;
            }

            // Un autre worker vient de prendre cette livraison. Un état ancien
            // reste récupérable après dix minutes en cas d'arrêt brutal du worker.
            if ($lockedDelivery->status === 'processing'
                && $lockedDelivery->updated_at?->isAfter(now()->subMinutes(10))) {
                return false;
            }

            $lockedDelivery->update([
                'category' => $category,
                'status' => 'processing',
                'attempts' => $lockedDelivery->attempts + 1,
                'last_error' => null,
            ]);

            return true;
        }, 3);

        if (! $claimed) {
            return false;
        }

        try {
            $sender();
            $delivery->update([
                'status' => 'sent',
                'sent_at' => now(),
                'last_error' => null,
            ]);

            return true;
        } catch (Throwable $exception) {
            $delivery->update([
                'status' => 'failed',
                'last_error' => class_basename($exception),
            ]);

            throw $exception;
        }
    }
}
