<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PlatformAdmin;
use App\Models\User;
use App\Notifications\NewUserRegisteredNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class PlatformAdminNotificationService
{
    public function newUserRegistered(User $user, Company $company, string $roleName = 'Utilisateur'): void
    {
        if (!app(PlatformConfigurationService::class)->channelEnabled('email')) {
            return;
        }

        $admins = PlatformAdmin::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->get();

        foreach ($admins as $admin) {
            try {
                Notification::send($admin, new NewUserRegisteredNotification($user, $company, $roleName));
            } catch (Throwable $exception) {
                report($exception);
                Log::error('Notification de nouveau compte administrateur impossible', [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'admin_id' => $admin->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
