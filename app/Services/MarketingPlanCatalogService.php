<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MarketingPlanCatalogService
{
    public function plans(): array
    {
        $fallback = config('marketing.plans', []);

        try {
            if (! Schema::hasTable('subscription_plans') || ! Schema::hasTable('plan_features')) {
                return $fallback;
            }

            $plans = SubscriptionPlan::with('features')
                ->where('is_active', true)
                ->orderBy('rank')
                ->get();

            if ($plans->isEmpty()) {
                return $fallback;
            }

            $fallbackByKey = collect($fallback)->keyBy('key');

            return $plans->map(function (SubscriptionPlan $plan) use ($fallbackByKey): array {
                $key = preg_replace('/-v\d+$/', '', $plan->key) ?: $plan->key;
                $copy = $fallbackByKey->get($key, []);
                $features = $plan->features->pluck('enabled', 'feature_key');

                return [
                    'key' => $key,
                    'name' => $plan->name,
                    'price' => (int) $plan->monthly_price,
                    'annual' => (int) $plan->annual_price,
                    'currency' => $plan->currency ?: 'XOF',
                    'period' => $plan->trial_days > 0 ? $plan->trial_days.' jours' : 'mois',
                    'description' => $copy['description'] ?? 'Une offre adaptée à votre niveau d’activité.',
                    'company_limit' => (int) $plan->company_limit,
                    'user_limit' => (int) $plan->user_limit,
                    'product_limit' => (int) $plan->product_limit,
                    'sms_quota' => (int) $plan->sms_quota,
                    'whatsapp_quota' => (int) $plan->whatsapp_quota,
                    'limits' => $this->limits((int) $plan->company_limit, (int) $plan->user_limit, (int) $plan->product_limit),
                    'quota' => $this->quota((int) $plan->sms_quota, (int) $plan->whatsapp_quota),
                    'suppliers' => (bool) ($features->get('suppliers') ?? false),
                    'ecommerce' => (bool) ($features->get('ecommerce') ?? false),
                    'promo_codes' => (bool) ($features->get('promo_codes') ?? ($plan->rank >= 3)),
                    'featured' => $key === 'bronze',
                ];
            })->all();
        } catch (Throwable) {
            return $fallback;
        }
    }

    private function limits(int $companies, int $users, int $products): string
    {
        return $companies.' entreprise'.($companies > 1 ? 's' : '').' · '
            .$users.' utilisateur'.($users > 1 ? 's' : '').' · '
            .number_format($products, 0, ',', ' ').' produits';
    }

    private function quota(int $sms, int $whatsapp): string
    {
        return number_format($sms, 0, ',', ' ').' SMS · '.number_format($whatsapp, 0, ',', ' ').' WhatsApp / mois';
    }
}
