<?php

namespace App\Services;

use App\Models\{Company, Subscription, SubscriptionPayment, SubscriptionPlan};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SubscriptionCheckoutService
{
    public function __construct(
        private SubscriptionAccountService $accounts,
        private KprimePayService $kprime,
        private PartnerPromotionService $promotion,
    ) {}

    public function preview(int $companyId, string $planKey, int|string $duration, ?string $promoCode = null): array
    {
        $months = $this->months($duration);
        $company = Company::withoutGlobalScopes()->findOrFail($companyId);
        $account = $company->subscriptionAccount()->with('owner')->firstOrFail();
        $plan = SubscriptionPlan::where('key', $planKey)->where('is_active', true)->with('features')->firstOrFail();
        $quote = $this->promotion->quote($account, $plan, $months, $promoCode);

        return $quote + ['plan_key' => $plan->key, 'plan_name' => $plan->name, 'duration_months' => $months];
    }

    public function create(int $companyId, int $userId, string $planKey, int|string $duration, ?string $promoCode = null, ?string $paymentTermsVersion = null): SubscriptionPayment
    {
        $months = $this->months($duration);

        return DB::transaction(function () use ($companyId, $userId, $planKey, $months, $promoCode, $paymentTermsVersion): SubscriptionPayment {
            $company = Company::withoutGlobalScopes()->lockForUpdate()->findOrFail($companyId);
            $account = $company->subscriptionAccount()->with('owner')->lockForUpdate()->firstOrFail();
            $plan = SubscriptionPlan::where('key', $planKey)->where('is_active', true)->with('features')->firstOrFail();
            $current = Subscription::where('subscription_account_id', $account->id)
                ->whereIn('status', ['trial', 'active'])->orderByDesc('ends_at')->lockForUpdate()->first();
            $currentRank = (int) ($current?->snapshot['rank'] ?? -1);
            if ($current && (int) $plan->rank < $currentRank) {
                throw new RuntimeException('Le passage à un plan inférieur est interdit.');
            }

            $quote = $this->promotion->quote($account, $plan, $months, $promoCode);
            $snapshot = array_merge($this->accounts->snapshot($plan), [
                'duration_months' => $months,
                'discount_applied' => $months === 12,
                'annual_discount_applied' => $months === 12,
                'partner_discount_applied' => (bool) $quote['eligible'],
                'partner_code' => $quote['code'],
                'partner_discount_bps' => (int) $quote['discount_bps'],
                'gross_amount' => (int) $quote['gross_amount'],
                'discount_amount' => (int) $quote['discount_amount'],
                'net_amount' => (int) $quote['net_amount'],
                'promotion_rule_version' => $quote['eligible'] ? PartnerPromotionService::RULE_VERSION : null,
                'payment_terms_version' => $paymentTermsVersion,
                'payment_terms_accepted_at' => $paymentTermsVersion ? now()->toIso8601String() : null,
            ]);
            $reference = 'SUB-'.$account->id.'-'.strtoupper(Str::random(16));
            $payment = SubscriptionPayment::create([
                'subscription_account_id' => $account->id, 'subscription_id' => $current?->id,
                'subscription_plan_id' => $plan->id, 'user_id' => $userId, 'transaction_id' => $reference,
                'idempotency_key' => 'subscription-'.strtolower($reference),
                'operation' => $current && $plan->rank > $currentRank ? 'upgrade' : 'renewal',
                'billing_period' => $months === 12 ? 'annual' : 'monthly', 'duration_months' => $months,
                'amount_ht' => $quote['net_amount'], 'tax_amount' => 0, 'amount' => $quote['net_amount'],
                'gross_amount' => $quote['gross_amount'], 'discount_amount' => $quote['discount_amount'],
                'currency' => $quote['currency'], 'snapshot' => $snapshot,
                'promotion_rule_version' => $quote['eligible'] ? PartnerPromotionService::RULE_VERSION : null,
                'status' => 'created',
            ]);
            $intent = $this->promotion->createIntent($payment, $quote);
            if ($intent) $payment->update(['partner_checkout_intent_id' => $intent->id]);
            return $payment->fresh();
        });
    }

    public function checkout(SubscriptionPayment $payment): array
    {
        $data = $this->kprime->createSubscriptionCheckout($payment, route('subscriptions.return', ['transaction_id' => $payment->transaction_id]));
        $expiresAt = $data['expires_at'] ?? now()->addHours(24);
        $payment->update(['status' => 'pending', 'kpp_reference' => $data['kpp_tx_reference'], 'checkout_url' => $data['checkout_url'], 'expires_at' => $expiresAt]);
        if ($payment->partner_checkout_intent_id) $payment->partnerCheckoutIntent()->update(['expires_at' => $expiresAt]);
        return $data;
    }

    private function months(int|string $duration): int
    {
        $months = is_string($duration) ? match ($duration) { 'annual' => 12, 'monthly' => 1, default => 0 } : (int) $duration;
        if ($months < 1 || $months > 12) throw new RuntimeException('La durée doit être comprise entre 1 et 12 mois.');
        return $months;
    }
}
