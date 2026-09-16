<?php

namespace Tests\Feature;

use App\Jobs\ExecutePlatformWithdrawal;
use App\Models\PlatformAdmin;
use App\Models\PlatformWithdrawal;
use App\Models\PlatformWithdrawalAccount;
use App\Services\KprimePayPayoutService;
use App\Services\PlatformTreasuryPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
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

    public function test_admin_can_reauthorize_unknown_withdrawal_after_provider_confirms_transaction_not_found(): void
    {
        $admin = $this->admin();
        $account = PlatformWithdrawalAccount::create([
            'platform_admin_id' => $admin->id, 'country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG',
            'phone_e164' => '+22890010203', 'phone_fingerprint' => hash('sha256', '+22890010203'),
            'beneficiary_name' => 'Ama Doe', 'status' => 'verified', 'verified_at' => now(), 'is_primary' => true,
        ]);
        $withdrawal = PlatformWithdrawal::create([
            'platform_admin_id' => $admin->id, 'platform_withdrawal_account_id' => $account->id,
            'transaction_id' => (string) \Illuminate\Support\Str::uuid(), 'idempotency_key' => 'test:'.\Illuminate\Support\Str::uuid(),
            'amount' => 1941, 'estimated_fees' => 59, 'fees' => 0, 'currency' => 'XOF', 'with_fees' => false,
            'status' => 'unknown', 'provider_status' => 'unknown', 'failure_reason' => 'Réponse indisponible.',
            'account_snapshot' => ['beneficiary_name' => 'Ama Doe', 'gateway' => 'MOOV-MONEY-TG', 'phone' => '+228••••03'],
            'funding_snapshot' => [], 'requested_at' => now()->subMinutes(20),
            'processing_at' => now()->subMinutes(15), 'unknown_at' => now()->subMinutes(10),
        ]);
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        Http::fake(['https://kprime.test/v2/transactions/credit-status' => Http::response([
            'status' => false, 'code' => 'TRANSACTION_NOT_FOUND', 'message' => 'Transaction inconnue.',
        ], 404)]);
        Queue::fake();

        $this->actingAs($admin, 'platform')->post(route('platform.treasury.withdrawals.retry', $withdrawal), [
            'reason' => 'KPrimePay confirme l’absence de cette transaction.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('platform_withdrawals', [
            'id' => $withdrawal->id, 'status' => 'otp_verified', 'provider_status' => 'retry_authorized',
        ]);
        $this->assertDatabaseHas('platform_audit_logs', [
            'action' => 'platform.treasury.withdrawal.retry_authorized', 'target_id' => (string) $withdrawal->id,
        ]);
        Queue::assertPushed(ExecutePlatformWithdrawal::class, fn (ExecutePlatformWithdrawal $job): bool => $job->withdrawalId === $withdrawal->id);
        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), '/payouts/from-collection-balance'));
    }

    public function test_unknown_withdrawal_cannot_be_reauthorized_while_provider_is_pending(): void
    {
        $admin = $this->admin();
        $account = PlatformWithdrawalAccount::create([
            'platform_admin_id' => $admin->id, 'country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG',
            'phone_e164' => '+22890010203', 'phone_fingerprint' => hash('sha256', '+22890010203'),
            'beneficiary_name' => 'Ama Doe', 'status' => 'verified', 'verified_at' => now(), 'is_primary' => true,
        ]);
        $withdrawal = PlatformWithdrawal::create([
            'platform_admin_id' => $admin->id, 'platform_withdrawal_account_id' => $account->id,
            'transaction_id' => (string) \Illuminate\Support\Str::uuid(), 'idempotency_key' => 'test:'.\Illuminate\Support\Str::uuid(),
            'amount' => 1000, 'estimated_fees' => 10, 'fees' => 0, 'currency' => 'XOF', 'with_fees' => false,
            'status' => 'unknown', 'provider_status' => 'unknown', 'failure_reason' => 'Réponse indisponible.',
            'account_snapshot' => [], 'funding_snapshot' => [], 'requested_at' => now()->subMinutes(10),
            'processing_at' => now()->subMinutes(5), 'unknown_at' => now()->subMinutes(4),
        ]);
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        Http::fake(['https://kprime.test/v2/transactions/credit-status' => Http::response([
            'status' => true, 'data' => ['status' => 'pending'],
        ], 200)]);
        Queue::fake();
        Log::spy();

        $this->actingAs($admin, 'platform')->post(route('platform.treasury.withdrawals.retry', $withdrawal), [
            'reason' => 'Je vérifie le statut avant toute nouvelle action.',
        ])->assertRedirect()->assertSessionHasErrors('withdrawal_retry');

        $this->assertDatabaseHas('platform_withdrawals', ['id' => $withdrawal->id, 'status' => 'unknown']);
        Queue::assertNothingPushed();
    }

    public function test_transaction_not_found_reconciliation_is_recorded_without_failing_the_queue_job(): void
    {
        $admin = $this->admin();
        $account = PlatformWithdrawalAccount::create([
            'platform_admin_id' => $admin->id, 'country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG',
            'phone_e164' => '+22890010203', 'phone_fingerprint' => hash('sha256', '+22890010203'),
            'beneficiary_name' => 'Ama Doe', 'status' => 'verified', 'verified_at' => now(), 'is_primary' => true,
        ]);
        $withdrawal = PlatformWithdrawal::create([
            'platform_admin_id' => $admin->id, 'platform_withdrawal_account_id' => $account->id,
            'transaction_id' => (string) \Illuminate\Support\Str::uuid(), 'idempotency_key' => 'test:'.\Illuminate\Support\Str::uuid(),
            'amount' => 1000, 'estimated_fees' => 10, 'fees' => 0, 'currency' => 'XOF', 'with_fees' => false,
            'status' => 'unknown', 'provider_status' => 'unknown', 'failure_reason' => 'Réponse indisponible.',
            'account_snapshot' => [], 'funding_snapshot' => [], 'requested_at' => now()->subMinutes(10),
            'processing_at' => now()->subMinutes(5), 'unknown_at' => now()->subMinutes(4),
        ]);
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        Http::fake(['https://kprime.test/v2/transactions/credit-status' => Http::response([
            'status' => false, 'message' => 'TRANSACTION_NOT_FOUND',
        ], 404)]);

        $result = app(PlatformTreasuryPayoutService::class)->reconcile($withdrawal);

        $this->assertSame('transaction_not_found', $result);
        $this->assertDatabaseHas('platform_withdrawals', [
            'id' => $withdrawal->id, 'status' => 'unknown', 'provider_status' => 'transaction_not_found',
        ]);
    }
}
