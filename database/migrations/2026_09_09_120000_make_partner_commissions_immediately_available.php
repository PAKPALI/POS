<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $updated = DB::table('platform_settings')
            ->where('key', 'partners.commission_hold_days')
            ->where('value', '7')
            ->update([
                'value' => '0',
                'type' => 'integer',
                'updated_at' => now(),
            ]);

        if ($updated === 0 && ! DB::table('platform_settings')->where('key', 'partners.commission_hold_days')->exists()) {
            DB::table('platform_settings')->insert([
                'key' => 'partners.commission_hold_days',
                'value' => '0',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Ne pas réactiver silencieusement une réserve financière lors d'un rollback.
    }
};
