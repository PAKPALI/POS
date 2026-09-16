<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('code_promos', function (Blueprint $table) {
            $table->string('normalized_code', 64)->nullable()->after('code');
            $table->timestamp('expires_at')->nullable()->after('status')->index();
        });

        $seen = [];
        DB::table('code_promos')->orderBy('id')->get()->each(function ($promo) use (&$seen): void {
            $normalized = strtoupper(trim((string) $promo->code));
            $key = $promo->company_id.'|'.$normalized;
            if (isset($seen[$key])) {
                $normalized = substr($normalized, 0, 52).'-'.$promo->id;
                DB::table('code_promos')->where('id', $promo->id)->update(['code' => $normalized]);
            }
            $seen[$promo->company_id.'|'.$normalized] = true;
            DB::table('code_promos')->where('id', $promo->id)->update(['normalized_code' => $normalized]);
        });

        Schema::table('code_promos', function (Blueprint $table) {
            $table->unique(['company_id', 'normalized_code'], 'code_promos_company_normalized_unique');
            $table->index(['company_id', 'status', 'expires_at'], 'code_promos_company_status_expiry_index');
        });

        $now = now();
        DB::table('subscription_plans')->select('id', 'rank')->orderBy('id')->get()->each(function ($plan) use ($now): void {
            DB::table('plan_features')->updateOrInsert(
                ['subscription_plan_id' => $plan->id, 'feature_key' => 'promo_codes'],
                ['enabled' => (int) $plan->rank >= 3, 'created_at' => $now, 'updated_at' => $now]
            );
        });
    }

    public function down(): void
    {
        DB::table('plan_features')->where('feature_key', 'promo_codes')->delete();
        Schema::table('code_promos', function (Blueprint $table) {
            $table->dropUnique('code_promos_company_normalized_unique');
            $table->dropIndex('code_promos_company_status_expiry_index');
            $table->dropColumn(['normalized_code', 'expires_at']);
        });
    }
};
