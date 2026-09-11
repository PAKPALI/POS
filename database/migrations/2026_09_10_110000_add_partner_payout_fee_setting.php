<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('platform_settings')->updateOrInsert(['key' => 'partners.payout_fee_bps'], [
            'value' => '100',
            'type' => 'integer',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('platform_settings')->where('key', 'partners.payout_fee_bps')->delete();
    }
};
