<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerWalletEntry;
use App\Services\PartnerWithdrawalService;
use App\Services\PlatformConfigurationService;
use App\Models\PlatformSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PartnerWithdrawalPreparationTest extends TestCase
{
    use RefreshDatabase;

    private function request(): Request
    {
        return Request::create('/partner/withdrawals', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_USER_AGENT' => 'phpunit']);
    }

    public function test_account_is_encrypted_masked_and_pending_by_default(): void
    {
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $account = app(PartnerWithdrawalService::class)->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MIXX-YAS-TG', 'phone_number' => '90 00 00 00', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $this->assertSame('pending_verification', $account->status);
        $this->assertSame('+228••••00', $account->maskedPhone());
        $this->assertNotSame('+2289000000', (string) $account->getRawOriginal('phone_e164'));
        $this->assertDatabaseHas('partner_audit_logs', ['action' => 'partner.withdrawal_account.registered']);
    }

    public function test_withdrawal_page_exposes_preparation_state_without_transfer_action(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $this->actingAs($partner, 'partner')->get(route('partner.withdrawals'))
            ->assertOk()
            ->assertSee('Retraits Mobile Money')
            ->assertSee('Les retraits ne sont pas encore ouverts')
            ->assertSee('Aucun appel KPrimePay')
            ->assertDontSee('payouts/transfers');
    }

    public function test_request_is_blocked_while_payout_feature_is_disabled(): void
    {
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 3]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 8000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 1, 'idempotency_key' => 'test-withdrawal-disabled', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '91 00 00 00', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());
        $this->expectExceptionMessage('PAYOUTS_DISABLED');
        $service->requestWithdrawal($partner, $account, 5000, $this->request(), true);
    }

    public function test_reservation_moves_balance_and_failure_releases_it_atomically(): void
    {
        $setting = \App\Models\PlatformSetting::where('key', 'partners.payouts_enabled')->firstOrFail();
        $setting->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.payouts_enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 3]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 8000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 2, 'idempotency_key' => 'test-withdrawal-enabled', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MIXX-YAS-TG', 'phone_number' => '92 00 00 00', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());
        $withdrawal = $service->requestWithdrawal($partner, $account, 5000, $this->request(), true);
        $this->assertSame('otp_verified', $withdrawal->status);
        $this->assertSame(3000, $service->balances($partner)['available']);
        $this->assertSame(5000, $service->balances($partner)['reserved']);
        $service->failAndRelease($withdrawal, 'Échec simulé de recette', $this->request());
        $this->assertSame(8000, $service->balances($partner)['available']);
        $this->assertSame(0, $service->balances($partner)['reserved']);
        $this->assertSame('failed', $withdrawal->fresh()->status);
    }

    public function test_unknown_provider_result_keeps_the_reservation(): void
    {
        $setting = \App\Models\PlatformSetting::where('key', 'partners.payouts_enabled')->firstOrFail();
        $setting->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.payouts_enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 3]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 8000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 3, 'idempotency_key' => 'test-withdrawal-unknown', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MIXX-YAS-TG', 'phone_number' => '93 00 00 00', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());
        $withdrawal = $service->requestWithdrawal($partner, $account, 5000, $this->request(), true);
        $withdrawal->update(['status' => 'unknown']);
        $service->failAndRelease($withdrawal, 'Réponse fournisseur inconnue', $this->request());
        $this->assertSame('unknown', $withdrawal->fresh()->status);
        $this->assertSame(3000, $service->balances($partner)['available']);
        $this->assertSame(5000, $service->balances($partner)['reserved']);
    }
}
