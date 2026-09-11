<?php

namespace Tests\Feature;

use App\Jobs\ExecutePartnerWithdrawal;
use App\Models\Partner;
use App\Models\PartnerPayoutEvent;
use App\Models\PartnerWalletEntry;
use App\Models\PlatformSetting;
use App\Services\PartnerPayoutService;
use App\Services\PartnerWithdrawalService;
use App\Services\PlatformConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PartnerPayoutIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function request(): Request
    {
        return Request::create('/partner/withdrawals', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
    }

    private function withdrawal(): array
    {
        PlatformSetting::where('key', 'partners.payouts_enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.payouts_enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 3, 'email' => 'partner@example.test']);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 8000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => $partner->id, 'idempotency_key' => 'payout-credit-'.$partner->id, 'occurred_at' => now()]);
        $withdrawals = app(PartnerWithdrawalService::class);
        $account = $withdrawals->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '96000008', 'beneficiary_name' => 'Afi Test'], $this->request());
        $withdrawals->verifyAccount($account, $this->request());
        return [$partner, $withdrawals->requestWithdrawal($partner, $account, 5000, $this->request(), true)];
    }

    public function test_automatic_execution_uses_a_dedicated_key_and_keeps_funds_reserved_until_verification(): void
    {
        [$partner, $withdrawal] = $this->withdrawal();
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        config()->set('services.kprimepay_payout.with_fees', 0);
        $withdrawal->update(['status' => 'approved', 'approved_at' => now()]);
        Http::fake(['https://kprime.test/v2/payouts/from-collection-balance' => Http::response(['status' => true, 'message' => 'COLLECTION_BALANCE_TRANSFER_COMPLETED', 'data' => ['transfer_reference' => 'TM-9981299', 'transaction_fees' => 50]], 201)]);

        app(PartnerPayoutService::class)->execute($withdrawal->id);

        Http::assertSent(function ($request) use ($withdrawal): bool {
            return $request->url() === 'https://kprime.test/v2/payouts/from-collection-balance'
                && $request->hasHeader('Authorization', 'Bearer payout-test-token')
                && $request->hasHeader('Idempotency-Key', $withdrawal->idempotency_key)
                && $request['transaction_id'] === $withdrawal->transaction_id
                && $request['phone_number'] === '96000008'
                && $request['gateway'] === 'MOOV-MONEY-TG'
                && $request['with_fees'] === 0;
        });
        $this->assertSame('processing', $withdrawal->fresh()->status);
        $this->assertSame(5050, app(PartnerWithdrawalService::class)->balances($partner)['reserved']);
        $this->assertSame(0, app(PartnerWithdrawalService::class)->balances($partner)['paid']);
    }

    public function test_gateway_rejection_releases_the_reserved_balance_without_retrying_the_transfer(): void
    {
        [$partner, $withdrawal] = $this->withdrawal();
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        $withdrawal->update(['status' => 'approved', 'approved_at' => now()]);
        Http::fake(['https://kprime.test/v2/payouts/from-collection-balance' => Http::response(['status' => false, 'message' => 'GATEWAY_REJECTED', 'data' => ['balance_restored' => true]], 422)]);

        app(PartnerPayoutService::class)->execute($withdrawal->id);

        $this->assertSame('failed', $withdrawal->fresh()->status);
        $this->assertSame(8000, app(PartnerWithdrawalService::class)->balances($partner)['available']);
        $this->assertSame(0, app(PartnerWithdrawalService::class)->balances($partner)['reserved']);
        Http::assertSentCount(1);
    }

    public function test_verified_transfer_webhook_settles_once_and_records_a_redacted_event(): void
    {
        [$partner, $withdrawal] = $this->withdrawal();
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        $withdrawal->update(['status' => 'processing', 'processing_at' => now()]);
        Http::fake(['https://kprime.test/v2/transactions/credit-status' => Http::response(['status' => true, 'data' => ['status' => 'success', 'transfer_reference' => 'TM-9981299', 'transaction_fees' => 50]], 200)]);
        $payload = ['api_version' => '2.0', 'event' => 'transfer.succeeded', 'event_id' => 'transfer-event-1', 'data' => ['transaction_id' => $withdrawal->transaction_id, 'status' => 'success', 'receiver_email' => 'do-not-store@example.test', 'receiver_phone' => '96000008']];

        $this->postJson('/api/kprimepay/webhook', $payload, ['X-API-BY' => 'KPRIMESOFT', 'X-KPP-EVENT' => 'transfer.succeeded', 'X-KPP-EVENT-ID' => 'transfer-event-1'])
            ->assertOk()->assertJsonPath('message', 'SUCCESS');

        $this->assertSame('succeeded', $withdrawal->fresh()->status);
        $this->assertSame(0, app(PartnerWithdrawalService::class)->balances($partner)['reserved']);
        $this->assertSame(5050, app(PartnerWithdrawalService::class)->balances($partner)['paid']);
        $event = PartnerPayoutEvent::firstOrFail();
        $this->assertArrayNotHasKey('receiver_email', $event->payload['data']);
        $this->assertArrayNotHasKey('receiver_phone', $event->payload['data']);

        $this->postJson('/api/kprimepay/webhook', $payload, ['X-API-BY' => 'KPRIMESOFT', 'X-KPP-EVENT' => 'transfer.succeeded', 'X-KPP-EVENT-ID' => 'transfer-event-1'])
            ->assertOk()->assertJsonPath('message', 'DUPLICATE');
        $this->assertSame(1, PartnerPayoutEvent::count());
    }

    public function test_verified_transfer_charges_only_actual_provider_fees_and_refunds_the_unused_reserve(): void
    {
        [$partner, $withdrawal] = $this->withdrawal();
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        $withdrawal->update(['status' => 'processing', 'processing_at' => now()]);

        // 50 XOF ont été réservés (1 %), mais KPrimePay confirme finalement 0 XOF de frais.
        Http::fake(['https://kprime.test/v2/transactions/credit-status' => Http::response(['status' => true, 'data' => [
            'status' => 'success',
            'transaction_amount' => 5000,
            'total_amount_debited' => 5000,
            'transfer_reference' => 'TM-9981300',
        ]], 200)]);
        $payload = ['api_version' => '2.0', 'event' => 'transfer.succeeded', 'event_id' => 'transfer-event-fee-refund', 'data' => ['transaction_id' => $withdrawal->transaction_id, 'status' => 'success']];

        $this->postJson('/api/kprimepay/webhook', $payload, ['X-API-BY' => 'KPRIMESOFT', 'X-KPP-EVENT' => 'transfer.succeeded', 'X-KPP-EVENT-ID' => 'transfer-event-fee-refund'])
            ->assertOk()->assertJsonPath('message', 'SUCCESS');

        $withdrawal->refresh();
        $balances = app(PartnerWithdrawalService::class)->balances($partner);
        $this->assertSame('succeeded', $withdrawal->status);
        $this->assertSame(50, $withdrawal->estimated_fees);
        $this->assertSame(0, $withdrawal->fees);
        $this->assertSame(3000, $balances['available']);
        $this->assertSame(0, $balances['reserved']);
        $this->assertSame(5000, $balances['paid']);
        $this->assertDatabaseHas('partner_wallet_entries', ['partner_id' => $partner->id, 'entry_type' => 'withdrawal_fee_refund', 'amount' => 50]);
    }

    public function test_verified_transfer_is_held_when_actual_provider_fees_exceed_the_reserved_cap(): void
    {
        [$unused, $withdrawal] = $this->withdrawal();
        config()->set('services.kprimepay_payout.token', 'payout-test-token');
        config()->set('services.kprimepay_payout.base_url', 'https://kprime.test/v2');
        $withdrawal->update(['status' => 'processing', 'processing_at' => now()]);
        Http::fake(['https://kprime.test/v2/transactions/credit-status' => Http::response(['status' => true, 'data' => [
            'status' => 'success', 'transaction_amount' => 5000, 'total_amount_debited' => 5100,
        ]], 200)]);
        $payload = ['api_version' => '2.0', 'event' => 'transfer.succeeded', 'event_id' => 'transfer-event-fee-cap', 'data' => ['transaction_id' => $withdrawal->transaction_id, 'status' => 'success']];

        $this->postJson('/api/kprimepay/webhook', $payload, ['X-API-BY' => 'KPRIMESOFT', 'X-KPP-EVENT' => 'transfer.succeeded', 'X-KPP-EVENT-ID' => 'transfer-event-fee-cap'])
            ->assertOk()->assertJsonPath('message', 'FEE_CAP_EXCEEDED');

        $this->assertSame('unknown', $withdrawal->fresh()->status);
        $this->assertStringContainsString('dépassent le plafond réservé', (string) $withdrawal->fresh()->failure_reason);
    }

    public function test_eligible_withdrawal_is_automatically_approved_without_a_risk_review_toggle(): void
    {
        [$unused, $withdrawal] = $this->withdrawal();
        Bus::fake();
        PlatformSetting::where('key', 'partners.auto_approval_max_xof')->update(['value' => '5050']);
        app(PlatformConfigurationService::class)->forget(['partners.auto_approval_max_xof']);
        $this->assertTrue(app(PartnerPayoutService::class)->approveForAutomaticExecution($withdrawal));
        $this->assertSame('approved', $withdrawal->fresh()->status);
        Bus::assertDispatched(ExecutePartnerWithdrawal::class);
    }
}
