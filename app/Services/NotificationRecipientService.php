<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRecipientService
{
    public function users(int $companyId, string $category, string $channel): Collection
    {
        abort_unless(in_array($category, ['sale', 'inventory'], true), 500);
        abort_unless(in_array($channel, ['email', 'whatsapp', 'sms'], true), 500);

        return User::where('status', 1)
            ->when($channel === 'email', fn ($query) => $query->whereNotNull('email'))
            ->when($channel !== 'email', fn ($query) => $query->whereNotNull('phone'))
            ->whereHas('memberships', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)->where('status', 'active');
            })
            ->whereHas('notificationPreferences', function ($query) use ($companyId, $category, $channel) {
                $query->where('company_id', $companyId)->where('category', $category)
                    ->where($channel.'_enabled', true);
            })->get();
    }
}
