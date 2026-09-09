<?php

namespace App\Services;

use App\Models\{Partner, PartnerAttribution, PartnerCheckoutIntent, PartnerPromoCode, SubscriptionAccount, SubscriptionPayment, SubscriptionPlan};
use InvalidArgumentException;

class PartnerPromotionService
{
    public const RULE_VERSION = 'partner-promotion-v1';

    public function __construct(
        private PartnerCodeService $codes,
        private PlatformConfigurationService $configuration,
    ) {}

    public function quote(SubscriptionAccount $account, SubscriptionPlan $plan, int $months, ?string $promoCode = null): array
    {
        if ($months < 1 || $months > 12) {
            throw new InvalidArgumentException('La durée doit être comprise entre 1 et 12 mois.');
        }

        $gross = $months === 12 ? (int) $plan->annual_price : (int) $plan->monthly_price * $months;
        if ($gross < 1) {
            throw new InvalidArgumentException('Ce plan ne peut pas être payé.');
        }

        $quote = [
            'gross_amount' => $gross,
            'discount_amount' => 0,
            'net_amount' => $gross,
            'currency' => (string) $plan->currency,
            'discount_bps' => 0,
            'candidate_commission_bps' => 0,
            'eligible' => false,
            'code' => null,
            'partner_id' => null,
            'partner_promo_code_id' => null,
            'message' => $promoCode ? 'Ce code ne peut pas être appliqué à ce compte.' : null,
        ];

        $normalized = trim((string) $promoCode);
        if ($normalized === '') {
            return $quote;
        }

        $code = $this->codes->findPublic($normalized);
        if (!$code) {
            throw new InvalidArgumentException('Ce code partenaire est invalide ou indisponible.');
        }

        $alreadyPaid = SubscriptionPayment::query()
            ->where('subscription_account_id', $account->id)
            ->where('status', 'paid')
            ->exists();
        $alreadyAttributed = PartnerAttribution::query()
            ->where('subscription_account_id', $account->id)
            ->exists();

        $quote['code'] = $code->normalized_code;
        $quote['partner_id'] = $code->partner_id;
        $quote['partner_promo_code_id'] = $code->id;
        $quote['candidate_commission_bps'] = (int) ($code->partner?->current_rate_bps ?: 1000);

        if ($alreadyPaid || $alreadyAttributed) {
            $quote['message'] = 'La remise partenaire est réservée au premier abonnement payé de ce compte.';
            return $quote;
        }

        $discountBps = max(0, min(10000, $this->configuration->integer('partners.first_discount_bps', 1000)));
        $discount = intdiv($gross * $discountBps, 10000);
        $quote['discount_bps'] = $discountBps;
        $quote['discount_amount'] = $discount;
        $quote['net_amount'] = $gross - $discount;
        $quote['eligible'] = true;
        $quote['message'] = 'Remise partenaire appliquée sur votre premier abonnement payé.';

        return $quote;
    }

    public function createIntent(SubscriptionPayment $payment, array $quote): ?PartnerCheckoutIntent
    {
        if (!$quote['eligible'] || !$quote['partner_id'] || !$quote['partner_promo_code_id']) {
            return null;
        }

        return PartnerCheckoutIntent::create([
            'subscription_payment_id' => $payment->id,
            'subscription_account_id' => $payment->subscription_account_id,
            'partner_id' => $quote['partner_id'],
            'partner_promo_code_id' => $quote['partner_promo_code_id'],
            'code' => $quote['code'],
            'rule_version' => self::RULE_VERSION,
            'discount_bps' => $quote['discount_bps'],
            'candidate_commission_bps' => $quote['candidate_commission_bps'],
            'gross_amount' => $quote['gross_amount'],
            'discount_amount' => $quote['discount_amount'],
            'net_amount' => $quote['net_amount'],
            'currency' => $quote['currency'],
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
        ]);
    }

    public function settleIntent(PartnerCheckoutIntent $intent, SubscriptionPayment $payment, \DateTimeInterface $settledAt): ?PartnerAttribution
    {
        if ($intent->status !== 'pending') {
            return null;
        }

        if (PartnerAttribution::query()->where('subscription_account_id', $payment->subscription_account_id)->exists()
            || SubscriptionPayment::query()->where('subscription_account_id', $payment->subscription_account_id)->where('status', 'paid')->where('id', '<>', $payment->id)->exists()) {
            $intent->update(['status' => 'rejected', 'failure_reason' => 'Compte déjà attribué ou abonnement déjà payé.', 'settled_at' => $settledAt]);
            return null;
        }

        $partner = Partner::query()->whereKey($intent->partner_id)->lockForUpdate()->firstOrFail();
        $rank = (int) $partner->qualified_clients_count + 1;
        $rate = self::rateForRank($rank);
        $attribution = PartnerAttribution::create([
            'subscription_account_id' => $payment->subscription_account_id,
            'partner_id' => $partner->id,
            'partner_promo_code_id' => $intent->partner_promo_code_id,
            'first_subscription_payment_id' => $payment->id,
            'acquisition_rank' => $rank,
            'commission_rate_bps' => $rate,
            'rule_version' => $intent->rule_version,
            'attributed_at' => $settledAt,
            'status' => 'active',
        ]);
        $partner->increment('qualified_clients_count');
        $partner->update(['current_rate_bps' => $rate]);
        PartnerPromoCode::query()->whereKey($intent->partner_promo_code_id)->update(['last_used_at' => $settledAt]);
        $intent->update(['status' => 'settled', 'settled_at' => $settledAt]);

        return $attribution;
    }

    public static function rateForRank(int $rank): int
    {
        return match (true) {
            $rank <= 5 => 1000,
            $rank <= 10 => 1100,
            $rank <= 15 => 1200,
            $rank <= 20 => 1300,
            $rank <= 25 => 1400,
            $rank <= 35 => 1500,
            $rank <= 45 => 1600,
            $rank <= 55 => 1700,
            $rank <= 65 => 1800,
            $rank <= 75 => 1900,
            $rank <= 95 => 2000,
            $rank <= 115 => 2100,
            $rank <= 135 => 2200,
            $rank <= 155 => 2300,
            $rank <= 175 => 2400,
            default => 2500,
        };
    }
}
