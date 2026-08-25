<?php

namespace App\Services;

use App\Models\NotificationDelivery;
use App\Models\CompanyUser;
use Closure;
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

        if ($delivery->status === 'sent') {
            return false;
        }

        $delivery->update([
            'category' => $category,
            'status' => 'processing',
            'attempts' => $delivery->attempts + 1,
            'last_error' => null,
        ]);

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
