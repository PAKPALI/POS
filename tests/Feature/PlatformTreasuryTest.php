<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformWithdrawal;
use App\Models\PlatformWithdrawalAccount;
use App\Services\KprimePayPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformTreasuryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin'): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => 'Trésorier plateforme', 'email' => $role.'@treasury.test',
            'password' => Hash::make('SecurePassword!123'), 'role' => $role,
            'is_active' => true, 'must_change_password' => false,
        ]);
    }

    public function test_super_admin_can_view_the_protected_treasury_dashboard(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'platform')->get(route('platform.treasury.index'))
            ->assertOk()
            ->assertSee('Capacité de retrait protégée')
            ->assertSee('Encaissements confirmés')
            ->assertSee('Retraits admin exécutés')
            ->assertSee('Compte Mobile Money administrateur');
    }

    public function test_finance_role_can_consult_but_cannot_create_a_treasury_withdrawal(): void
    {
        $admin = $this->admin('finance');
        $this->actingAs($admin, 'platform')->get(route('platform.treasury.index'))
            ->assertOk()
            ->assertSee('lecture seule');
        $this->actingAs($admin, 'platform')->post(route('platform.treasury.withdrawals.start'), [
            'platform_withdrawal_account_id' => 1, 'amount' => 100, 'current_password' => 'SecurePassword!123',
        ])->assertForbidden();
    }

    public function test_admin_treasury_history_includes_actual_and_reserved_costs(): void
    {
        $admin = $this->admin();
        $account = PlatformWithdrawalAccount::create([
            'platform_admin_id' => $admin->id, 'country_code' => 'TG', 'gateway' => 'FLOOZ-TG',
            'phone_e164' => '+22890010203', 'phone_fingerprint' => hash('sha256', '+22890010203'),
            'beneficiary_name' => 'Ama Doe', 'status' => 'verified', 'verified_at' => now(), 'is_primary' => true,
        ]);
        PlatformWithdrawal::create([
            'platform_admin_id' => $admin->id, 'platform_withdrawal_account_id' => $account->id,
            'transaction_id' => (string) \Illuminate\Support\Str::uuid(), 'idempotency_key' => 'test:'.\Illuminate\Support\Str::uuid(),
            'amount' => 1000, 'estimated_fees' => 10, 'fees' => 8, 'status' => 'succeeded', 'currency' => 'XOF',
            'account_snapshot' => ['beneficiary_name' => 'Ama Doe', 'gateway' => 'FLOOZ-TG', 'phone' => '+228••••03'],
            'funding_snapshot' => [], 'requested_at' => now(), 'succeeded_at' => now(),
        ]);
        $this->actingAs($admin, 'platform')->get(route('platform.treasury.index'))
            ->assertOk()->assertSee('Ama Doe')->assertSee('Succès')->assertSee('1 000 XOF');
    }

    public function test_platform_payout_always_sends_with_fees_zero(): void
    {
        $admin = $this->admin();
        $account = PlatformWithdrawalAccount::create([
            'platform_admin_id' => $admin->id, 'country_code' => 'TG', 'gateway' => 'FLOOZ-TG',
            'phone_e164' => '+22890010203', 'phone_fingerprint' => hash('sha256', '+22890010203'),
            'beneficiary_name' => 'Ama Doe', 'status' => 'verified', 'verified_at' => now(), 'is_primary' => true,
        ]);
        $withdrawal = PlatformWithdrawal::create([
            'platform_admin_id' => $admin->id, 'platform_withdrawal_account_id' => $account->id,
            'transaction_id' => (string) \Illuminate\Support\Str::uuid(), 'idempotency_key' => 'test:'.\Illuminate\Support\Str::uuid(),
            'amount' => 1000, 'estimated_fees' => 10, 'currency' => 'XOF', 'status' => 'otp_verified',
            'account_snapshot' => [], 'funding_snapshot' => [], 'requested_at' => now(),
        ]);
        config()->set('services.kprimepay_payout.token', 'kpp_test');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        Http::fake(['https://kprime.test/v2/payouts/from-collection-balance' => Http::response(['status' => true, 'data' => ['transfer_reference' => 'KPP-1']], 201)]);

        app(KprimePayPayoutService::class)->transferPlatformWithdrawal($withdrawal);

        Http::assertSent(fn ($request) => $request->url() === 'https://kprime.test/v2/payouts/from-collection-balance'
            && $request['amount'] === 1000 && $request['with_fees'] === 0
            && $request->hasHeader('Idempotency-Key', $withdrawal->idempotency_key));
    }
}
