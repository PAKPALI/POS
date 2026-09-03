<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\PlatformAdmin;
use App\Models\SubscriptionAccount;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class PlatformSubscriptionPreflightTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    private function admin(string $role = 'super_admin'): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => 'Administrateur SaaS',
            'email' => $role.'@example.test',
            'password' => Hash::make('SecurePassword!123'),
            'role' => $role,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    public function test_super_admin_can_review_subscription_preflight_without_exposing_secrets(): void
    {
        config(['services.kprimepay.token' => 'sensitive-kprimepay-token']);
        $owner = User::factory()->create();
        $company = $this->activateCompanyFor($owner, 'preflight');
        $account = SubscriptionAccount::create(['owner_id' => $owner->id, 'billing_company_id' => $company->id]);
        $company->update(['subscription_account_id' => $account->id]);
        $plan = SubscriptionPlan::where('key', 'bronze')->firstOrFail();
        SubscriptionPayment::create([
            'subscription_account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'user_id' => $owner->id,
            'transaction_id' => 'SUB-PREFLIGHT-001',
            'idempotency_key' => 'subscription-preflight-001',
            'operation' => 'upgrade',
            'billing_period' => 'monthly',
            'amount_ht' => 5000,
            'tax_amount' => 0,
            'amount' => 5000,
            'currency' => 'XOF',
            'snapshot' => ['name' => 'Bronze'],
            'status' => 'pending',
            'expires_at' => now()->addHour(),
        ]);

        $this->actingAs($this->admin(), 'platform')
            ->get(route('platform.subscriptions.preflight'))
            ->assertOk()
            ->assertSee('Vérifier avant d’activer les abonnements')
            ->assertSee('SUB-PREFLIGHT-001')
            ->assertSee('Bronze')
            ->assertDontSee('sensitive-kprimepay-token');
    }

    public function test_non_super_admin_cannot_access_subscription_preflight(): void
    {
        $this->actingAs($this->admin('finance'), 'platform')
            ->get(route('platform.subscriptions.preflight'))
            ->assertForbidden();
    }
}
