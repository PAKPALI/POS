<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Partner;
use App\Models\PartnerCommission;
use App\Models\PartnerPromoCode;
use App\Models\PartnerWalletEntry;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\PartnerCommissionService;
use App\Services\PlatformConfigurationService;
use App\Services\SubscriptionAccountService;
use App\Services\SubscriptionCheckoutService;
use App\Services\SubscriptionSettlementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerCommissionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-09 12:00:00');
        app(PlatformConfigurationService::class)->forget(['partners.commission_hold_days']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function account(): array
    {
        $user = User::factory()->create(['email' => fake()->unique()->safeEmail()]);
        $company = Company::create([
            'name' => 'Commission partenaire',
            'email' => $user->email,
            'number1' => '90000000',
            'created_by' => $user->id,
        ]);
        app(SubscriptionAccountService::class)->ensureFor($company, $user->id);

        return [$user, $company->fresh()];
    }

    private function activeCode(): PartnerPromoCode
    {
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);

        return PartnerPromoCode::create([
            'partner_id' => $partner->id,
            'code' => 'COMMISSION2026',
            'normalized_code' => 'COMMISSION2026',
            'status' => 'active',
            'is_primary' => true,
            'activated_at' => now(),
        ]);
    }

    public function test_unattributed_subscription_never_creates_a_commission_or_wallet_entry(): void
    {
        [$user, $company] = $this->account();
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 1);

        $this->assertTrue(app(SubscriptionSettlementService::class)->creditVerified($payment, [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 5000,
        ]));

        $this->assertDatabaseCount('partner_attributions', 0);
        $this->assertDatabaseCount('partner_commissions', 0);
        $this->assertDatabaseCount('partner_wallet_entries', 0);
    }

    public function test_first_confirmed_subscription_creates_one_immediately_available_commission_and_credit(): void
    {
        [$user, $company] = $this->account();
        $code = $this->activeCode();
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 1, $code->code);

        $this->assertTrue(app(SubscriptionSettlementService::class)->creditVerified($payment, [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500,
        ]));

        $commission = PartnerCommission::firstOrFail();
        $this->assertSame('acquisition', $commission->type);
        $this->assertSame('available', $commission->status);
        $this->assertSame(5000, (int) $commission->gross_amount);
        $this->assertSame(500, (int) $commission->discount_amount);
        $this->assertSame(4500, (int) $commission->net_paid_amount);
        $this->assertSame(1000, (int) $commission->commission_rate_bps);
        $this->assertSame(500, (int) $commission->commission_amount);
        $this->assertTrue($commission->available_at->equalTo(now()));
        $this->assertSame($code->partner_id, $commission->partner_id);
        $this->assertSame($payment->id, $commission->subscription_payment_id);
        $this->assertSame('floor(gross_amount * commission_rate_bps / 10000)', $commission->calculation_snapshot['formula']);

        $this->assertDatabaseHas('partner_wallet_entries', [
            'partner_id' => $code->partner_id,
            'entry_type' => 'commission_credit',
            'bucket' => 'available',
            'direction' => 'credit',
            'amount' => 500,
            'source_type' => PartnerCommissionService::SOURCE_TYPE,
            'source_id' => $commission->id,
            'idempotency_key' => 'partner-commission:'.$commission->id.':commission-credit',
        ]);
    }

    public function test_renewal_uses_the_rate_acquired_on_attribution_without_a_second_discount(): void
    {
        [$user, $company] = $this->account();
        $code = $this->activeCode();
        $checkout = app(SubscriptionCheckoutService::class);
        $settlement = app(SubscriptionSettlementService::class);
        $first = $checkout->create($company->id, $user->id, 'bronze', 1, $code->code);
        $settlement->creditVerified($first, ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500]);

        $code->partner->update(['current_rate_bps' => 2500]);
        $renewal = $checkout->create($company->id, $user->id, 'bronze', 1);
        $this->assertSame(5000, (int) $renewal->amount);
        $this->assertSame(0, (int) $renewal->discount_amount);
        $settlement->creditVerified($renewal, ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 5000]);

        $commission = PartnerCommission::where('subscription_payment_id', $renewal->id)->firstOrFail();
        $this->assertSame('renewal', $commission->type);
        $this->assertSame(1000, (int) $commission->commission_rate_bps);
        $this->assertSame(500, (int) $commission->commission_amount);
        $this->assertDatabaseCount('partner_commissions', 2);
        $this->assertDatabaseCount('partner_wallet_entries', 2);
    }

    public function test_replayed_payment_confirmation_cannot_create_a_second_commission_or_wallet_credit(): void
    {
        [$user, $company] = $this->account();
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 1, $this->activeCode()->code);
        $settlement = app(SubscriptionSettlementService::class);
        $verified = ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500];

        $this->assertTrue($settlement->creditVerified($payment, $verified, 'event-commission-1'));
        $this->assertFalse($settlement->creditVerified($payment->fresh(), $verified, 'event-commission-1'));
        $this->assertDatabaseCount('partner_commissions', 1);
        $this->assertDatabaseCount('partner_wallet_entries', 1);
    }

    public function test_maturity_moves_the_same_amount_from_pending_to_available_once(): void
    {
        PlatformSetting::where('key', 'partners.commission_hold_days')->update(['value' => '7']);
        app(PlatformConfigurationService::class)->forget(['partners.commission_hold_days']);
        [$user, $company] = $this->account();
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 1, $this->activeCode()->code);
        app(SubscriptionSettlementService::class)->creditVerified($payment, [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4500,
        ]);
        $commission = PartnerCommission::firstOrFail();
        $commission->update(['available_at' => now()->subSecond()]);

        $service = app(PartnerCommissionService::class);
        $this->assertSame(1, $service->matureDue());
        $this->assertSame(0, $service->matureDue());

        $this->assertSame('available', $commission->fresh()->status);
        $this->assertDatabaseCount('partner_wallet_entries', 3);
        $this->assertDatabaseHas('partner_wallet_entries', [
            'source_id' => $commission->id,
            'entry_type' => 'maturity',
            'bucket' => 'pending',
            'direction' => 'debit',
            'amount' => 500,
        ]);
        $this->assertDatabaseHas('partner_wallet_entries', [
            'source_id' => $commission->id,
            'entry_type' => 'maturity',
            'bucket' => 'available',
            'direction' => 'credit',
            'amount' => 500,
        ]);
        $this->assertSame(500, (int) PartnerWalletEntry::where('bucket', 'available')->sum('amount'));
    }
}
