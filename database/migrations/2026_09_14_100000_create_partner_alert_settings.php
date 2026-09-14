<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'partners.alerts.enabled' => ['value' => 'true', 'type' => 'boolean'],
            'partners.alerts.recipient_admin_ids' => ['value' => '[]', 'type' => 'json'],
            'partners.alerts.partner_registered' => ['value' => 'true', 'type' => 'boolean'],
            'partners.alerts.email_verified' => ['value' => 'true', 'type' => 'boolean'],
            'partners.alerts.code_changed' => ['value' => 'false', 'type' => 'boolean'],
            'partners.alerts.commission_created' => ['value' => 'false', 'type' => 'boolean'],
            'partners.alerts.withdrawal_requested' => ['value' => 'true', 'type' => 'boolean'],
            'partners.alerts.withdrawal_succeeded' => ['value' => 'true', 'type' => 'boolean'],
            'partners.alerts.withdrawal_failed' => ['value' => 'true', 'type' => 'boolean'],
            'partners.alerts.withdrawal_unknown' => ['value' => 'true', 'type' => 'boolean'],
        ] as $key => $setting) {
            DB::table('platform_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('platform_settings')->whereIn('key', [
            'partners.alerts.enabled',
            'partners.alerts.recipient_admin_ids',
            'partners.alerts.partner_registered',
            'partners.alerts.email_verified',
            'partners.alerts.code_changed',
            'partners.alerts.commission_created',
            'partners.alerts.withdrawal_requested',
            'partners.alerts.withdrawal_succeeded',
            'partners.alerts.withdrawal_failed',
            'partners.alerts.withdrawal_unknown',
        ])->delete();
    }
};
