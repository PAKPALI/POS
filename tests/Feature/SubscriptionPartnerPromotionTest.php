<?php

namespace Tests\Feature;

use App\Models\{Company, Partner, PartnerAttribution, PartnerPromoCode, SubscriptionPayment, SubscriptionPlan, User};
use App\Services\{PartnerPromotionService, SubscriptionAccountService, SubscriptionCheckoutService, SubscriptionSettlementService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPartnerPromotionTest extends TestCase
{
    use RefreshDatabase;

    private function account(): array
    {
        $user = User::factory()->create(['email' => fake()->unique()->safeEmail()]);
        $company = Company::create(['name' => 'Compte promotion', 'email' => $user->email, 'number1' => '90000000', 'created_by' => $user->id]);
        $account = app(SubscriptionAccountService::class)->ensureFor($company, $user->id);
        return [$user, $company->fresh(), $account];
    }

    private function partnerCode(string $suffix): PartnerPromoCode
    {
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        return PartnerPromoCode::create([
            'partner_id' => $partner->id, 'code' => 'PART'.$suffix, 'normalized_code' => 'PART'.$suffix,
            'status' => 'active', 'is_primary' => true, 'activated_at' => now(),
        ]);
    }

    public function test_preview_recalculates_first_subscription_discount_in_integer_xof(): void
    {
        [, $company] = $this->account();
        $service = app(SubscriptionCheckoutService::class);

        $monthly = $service->preview($company->id, 'bronze', 3, $this->partnerCode('ONE')->code);
        $this->assertSame(15000, $monthly['gross_amount']);
        $this->assertSame(1500, $monthly['discount_amount']);
        $this->assertSame(13500, $monthly['net_amount']);
        $this->assertTrue($monthly['eligible']);

        [, $annualCompany] = $this->account();
        $annual = $service->preview($annualCompany->id, 'bronze', 12, $this->partnerCode('TWO')->code);
        $this->assertSame(55000, $annual['gross_amount']);
        $this->assertSame(5500, $annual['discount_amount']);
        $this->assertSame(49500, $annual['net_amount']);
    }

    public function test_checkout_persists_immutable_partner_intent_and_price_snapshot(): void
    {
        [$user, $company] = $this->account();
        $code = $this->partnerCode('THREE');
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 1, $code->code);

        $this->assertSame(5000, (int) $payment->gross_amount);
        $this->assertSame(500, (int) $payment->discount_amount);
        $this->assertSame(4500, (int) $payment->amount);
        $this->assertSame('PARTTHREE', $payment->snapshot['partner_code']);
        $this->assertDatabaseHas('partner_checkout_intents', [
            'subscription_payment_id' => $payment->id, 'partner_id' => $code->partner_id,
            'gross_amount' => 5000, 'discount_amount' => 500, 'net_amount' => 4500, 'status' => 'pending',
        ]);
    }

    public function test_first_confirmed_payment_creates_one_permanent_attribution_and_competing_payment_does_not_replace_it(): void
    {
        [$user, $company] = $this->account();
        $firstCode = $this->partnerCode('FOUR');
        $secondCode = $this->partnerCode('FIVE');
        $checkout = app(SubscriptionCheckoutService::class);
        $first = $checkout->create($company->id, $user->id, 'bronze', 1, $firstCode->code);
        $second = $checkout->create($company->id, $user->id, 'bronze', 1, $secondCode->code);

        $settlement = app(SubscriptionSettlementService::class);
        $this->assertTrue($settlement->creditVerified($first, ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500]));
        $this->assertTrue($settlement->creditVerified($second, ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500]));

        $this->assertDatabaseCount('partner_attributions', 1);
        $this->assertDatabaseHas('partner_attributions', ['subscription_account_id' => $company->fresh()->subscription_account_id, 'partner_id' => $firstCode->partner_id, 'acquisition_rank' => 1, 'commission_rate_bps' => 1000]);
        $this->assertDatabaseHas('partner_checkout_intents', ['subscription_payment_id' => $second->id, 'status' => 'rejected']);
        $this->assertSame(1, (int) $firstCode->partner->fresh()->qualified_clients_count);
        $this->assertSame(0, (int) $secondCode->partner->fresh()->qualified_clients_count);
        $this->assertSame(1, PartnerAttribution::where('subscription_account_id', $company->fresh()->subscription_account_id)->count());
    }

    public function test_existing_paid_account_gets_no_second_discount_or_intent(): void
    {
        [$user, $company] = $this->account();
        $code = $this->partnerCode('SIX');
        $checkout = app(SubscriptionCheckoutService::class);
        $first = $checkout->create($company->id, $user->id, 'bronze', 1, $code->code);
        app(SubscriptionSettlementService::class)->creditVerified($first, ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500]);

        $second = $checkout->create($company->id, $user->id, 'bronze', 1, $code->code);
        $this->assertSame(5000, (int) $second->amount);
        $this->assertNull($second->partner_checkout_intent_id);
        $this->assertFalse(app(PartnerPromotionService::class)->quote($company->fresh()->subscriptionAccount()->with('owner')->first(), SubscriptionPlan::where('key', 'bronze')->firstOrFail(), 1, $code->code)['eligible']);
    }

    public function test_partner_can_use_own_code_for_any_company_account(): void
    {
        [$user, $company] = $this->account();
        $partner = Partner::factory()->create([
            'status' => 'active',
            'email' => $user->email,
            'email_verified_at' => now(),
        ]);
        $code = PartnerPromoCode::create([
            'partner_id' => $partner->id,
            'code' => 'PARTSELF',
            'normalized_code' => 'PARTSELF',
            'status' => 'active',
            'is_primary' => true,
            'activated_at' => now(),
        ]);

        $quote = app(SubscriptionCheckoutService::class)->preview($company->id, 'bronze', 1, $code->code);

        $this->assertTrue($quote['eligible']);
        $this->assertSame(500, $quote['discount_amount']);
        $this->assertSame(4500, $quote['net_amount']);
    }
}
