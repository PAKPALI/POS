<?php

namespace App\Services;

use App\Models\PlanFeature;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanCatalogService
{
    public function createVersion(SubscriptionPlan $source, array $values): SubscriptionPlan
    {
        if ($source->rank < 1) {
            throw ValidationException::withMessages(['plan' => 'Le plan d’essai ne peut pas être versionné depuis la console commerciale.']);
        }

        if ((int) $values['annual_price'] !== (int) $values['monthly_price'] * 11) {
            throw ValidationException::withMessages(['annual_price' => 'Le tarif annuel doit correspondre à 11 mensualités.']);
        }

        return DB::transaction(function () use ($source, $values) {
            $lockedSource = SubscriptionPlan::with('features')->lockForUpdate()->findOrFail($source->id);
            $family = $this->familyKey($lockedSource->key);
            $versions = SubscriptionPlan::lockForUpdate()->get()->filter(fn (SubscriptionPlan $plan) => $this->familyKey($plan->key) === $family);
            $nextVersion = ((int) $versions->max('version')) + 1;

            $plan = SubscriptionPlan::create([
                'key' => $family.'-v'.$nextVersion,
                'name' => $values['name'],
                'rank' => $lockedSource->rank,
                'is_active' => false,
                'monthly_price' => $values['monthly_price'],
                'annual_price' => $values['annual_price'],
                'currency' => 'XOF',
                'company_limit' => $values['company_limit'],
                'user_limit' => $values['user_limit'],
                'product_limit' => $values['product_limit'],
                'sms_quota' => $values['sms_quota'],
                'whatsapp_quota' => $values['whatsapp_quota'],
                'trial_days' => 0,
                'version' => $nextVersion,
            ]);

            foreach (['suppliers', 'ecommerce'] as $feature) {
                PlanFeature::create([
                    'subscription_plan_id' => $plan->id,
                    'feature_key' => $feature,
                    'enabled' => (bool) ($values['features'][$feature] ?? false),
                ]);
            }

            return $plan->load('features');
        }, 3);
    }

    public function publish(SubscriptionPlan $draft): SubscriptionPlan
    {
        return DB::transaction(function () use ($draft) {
            $lockedDraft = SubscriptionPlan::lockForUpdate()->findOrFail($draft->id);
            if ($lockedDraft->rank < 1 || $lockedDraft->is_active) {
                throw ValidationException::withMessages(['plan' => 'Seule une version payante en brouillon peut être publiée.']);
            }

            $family = $this->familyKey($lockedDraft->key);
            SubscriptionPlan::lockForUpdate()->get()
                ->filter(fn (SubscriptionPlan $plan) => $this->familyKey($plan->key) === $family && $plan->id !== $lockedDraft->id)
                ->each(fn (SubscriptionPlan $plan) => $plan->update(['is_active' => false]));

            $lockedDraft->update(['is_active' => true]);
            return $lockedDraft->fresh('features');
        }, 3);
    }

    public function familyKey(string $key): string
    {
        return preg_replace('/-v\\d+$/', '', $key) ?: $key;
    }
}
