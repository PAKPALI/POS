<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionAccountService;
use App\Services\SubscriptionCheckoutService;
use App\Services\SubscriptionSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionDurationTest extends TestCase
{
    use RefreshDatabase;

    private function account(): array
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Durée abonnement', 'email' => 'duration@example.test', 'number1' => '90000000', 'created_by' => $user->id]);
        $account = app(SubscriptionAccountService::class)->ensureFor($company, $user->id);
        return [$user, $company->fresh(), $account];
    }

    public function test_one_to_eleven_months_use_the_monthly_price_without_discount(): void
    {
        [$user, $company] = $this->account();
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 3);

        $this->assertSame(15000, (int) $payment->amount);
        $this->assertSame(3, (int) $payment->duration_months);
        $this->assertSame('monthly', $payment->billing_period);
        $this->assertFalse((bool) $payment->snapshot['discount_applied']);
    }

    public function test_twelve_months_use_the_annual_reduced_price_and_expire_after_twelve_months(): void
    {
        [$user, $company] = $this->account();
        $payment = app(SubscriptionCheckoutService::class)->create($company->id, $user->id, 'bronze', 12);
        $this->assertSame(55000, (int) $payment->amount);
        $this->assertSame('annual', $payment->billing_period);
        $this->assertTrue((bool) $payment->snapshot['discount_applied']);

        $before = now();
        $settlement = app(SubscriptionSettlementService::class);
        $this->assertTrue($settlement->creditVerified($payment, ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 55000]));
        $subscription = $payment->fresh()->subscription_id;
        $endsAt = \App\Models\Subscription::findOrFail($subscription)->ends_at;
        $this->assertTrue($endsAt->between($before->copy()->addMonths(12)->subSeconds(2), $before->copy()->addMonths(12)->addSeconds(2)));
    }
}
