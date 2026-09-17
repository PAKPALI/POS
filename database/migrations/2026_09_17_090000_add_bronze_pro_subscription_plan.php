<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $bronzePro = DB::table('subscription_plans')->where('key', 'bronze-pro')->first();

            if (! $bronzePro) {
                DB::table('subscription_plans')
                    ->where('rank', '>=', 3)
                    ->orderByDesc('rank')
                    ->orderByDesc('version')
                    ->get()
                    ->each(fn (object $plan) => DB::table('subscription_plans')->where('id', $plan->id)->update([
                        'rank' => $plan->rank + 1,
                        'updated_at' => $now,
                    ]));

                $bronzeProId = DB::table('subscription_plans')->insertGetId([
                    'key' => 'bronze-pro',
                    'name' => 'Bronze Pro',
                    'rank' => 3,
                    'is_active' => true,
                    'monthly_price' => 7500,
                    'annual_price' => 82500,
                    'currency' => 'XOF',
                    'company_limit' => 1,
                    'user_limit' => 4,
                    'product_limit' => 250,
                    'sms_quota' => 30,
                    'whatsapp_quota' => 30,
                    'trial_days' => 0,
                    'version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $bronzeProId = $bronzePro->id;
                DB::table('subscription_plans')->where('id', $bronzeProId)->update([
                    'name' => 'Bronze Pro',
                    'rank' => 3,
                    'monthly_price' => 7500,
                    'annual_price' => 82500,
                    'company_limit' => 1,
                    'user_limit' => 4,
                    'product_limit' => 250,
                    'sms_quota' => 30,
                    'whatsapp_quota' => 30,
                    'updated_at' => $now,
                ]);
            }

            foreach (['suppliers', 'ecommerce', 'promo_codes'] as $feature) {
                DB::table('plan_features')->updateOrInsert(
                    ['subscription_plan_id' => $bronzeProId, 'feature_key' => $feature],
                    ['enabled' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            DB::table('subscription_plans')
                ->where(function ($query): void {
                    $query->where('key', 'bronze')->orWhere('key', 'like', 'bronze-v%');
                })
                ->pluck('id')
                ->each(function (int $planId) use ($now): void {
                    foreach (['suppliers' => true, 'ecommerce' => false, 'promo_codes' => false] as $feature => $enabled) {
                        DB::table('plan_features')->updateOrInsert(
                            ['subscription_plan_id' => $planId, 'feature_key' => $feature],
                            ['enabled' => $enabled, 'created_at' => $now, 'updated_at' => $now]
                        );
                    }
                });
        });
    }

    public function down(): void
    {
        // Le plan peut être référencé par des abonnements ou des paiements : ne pas le supprimer.
    }
};
