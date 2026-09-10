<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('platform_settings')->updateOrInsert(['key' => 'partners.payout_gateways'], [
            'value' => json_encode(['TG' => ['MOOV-MONEY-TG']]),
            'type' => 'json',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('platform_settings')->where('key', 'partners.payout_gateways')->delete();
    }
};
