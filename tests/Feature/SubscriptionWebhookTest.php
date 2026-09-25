<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function payment(string $transactionId = 'SUB-WEBHOOK-1', string $status = 'pending'): array
    {
        $user = User::factory()->create();
        $company = Company::create([
            'name' => 'Abonnement webhook',
            'email' => strtolower($transactionId).'@example.test',
            'number1' => '90000000',
            'created_by' => $user->id,
        ]);
        $account = app(SubscriptionAccountService::class)->ensureFor($company, $user->id);
        $plan = SubscriptionPlan::where('key', 'bronze')->with('features')->firstOrFail();
        $payment = SubscriptionPayment::create([
            'subscription_account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'user_id' => $user->id,
            'transaction_id' => $transactionId,
            'idempotency_key' => strtolower($transactionId),
            'operation' => 'upgrade',
            'billing_period' => 'monthly',
            'amount_ht' => 5000,
            'tax_amount' => 0,
            'amount' => 5000,
            'currency' => 'XOF',
            'snapshot' => app(SubscriptionAccountService::class)->snapshot($plan),
            'status' => $status,
            'expires_at' => now()->subMinute(),
        ]);

        return [$user, $company, $payment];
    }

    private function v2Payload(SubscriptionPayment $payment, string $event = 'collection.succeeded', string $eventId = 'evt-subscription-1'): array
    {
        return [
            'api_version' => '2.0',
            'event' => $event,
            'event_id' => $eventId,
            'data' => [
                'transaction_id' => $payment->transaction_id,
                'kpp_reference' => 'KPP-SUB-TEST',
                'transaction_details' => ['currency' => 'XOF', 'amount' => 5000],
                'failure_reason' => 'Solde insuffisant',
            ],
        ];
    }

    private function v2Headers(string $event, string $eventId): array
    {
        return [
            'X-API-VERSION' => '2.0',
            'X-API-BY' => 'KPRIMESOFT',
            'X-KPP-EVENT' => $event,
            'X-KPP-EVENT-ID' => $eventId,
        ];
    }

    public function test_v2_success_is_verified_then_settled_once(): void
    {
        config(['services.kprimepay.base_url' => 'https://api.kprimepay.test/v2', 'services.kprimepay.token' => 'sandbox-secret']);
        [, $company, $payment] = $this->payment();
        Http::fake(['*/transactions/debit-status' => Http::response(['status' => true, 'data' => [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 5000,
        ]])]);

        $payload = $this->v2Payload($payment);
        $headers = $this->v2Headers('collection.succeeded', 'evt-subscription-1');
        $this->withHeaders($headers)->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()->assertJson(['message' => 'SETTLED']);
        $this->withHeaders($headers)->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()->assertJson(['message' => 'SETTLED']);

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(23, (int) $company->fresh()->sms_count);
        $this->assertSame(23, (int) $company->fresh()->whatsapp_count);
        $this->assertDatabaseCount('subscriptions', 2);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/transactions/debit-status')
            && $request->hasHeader('Authorization', 'Bearer sandbox-secret'));
    }

    public function test_v1_success_is_normalized_and_settled(): void
    {
        config(['services.kprimepay.base_url' => 'https://api.kprimepay.test/v2', 'services.kprimepay.token' => 'sandbox-secret']);
        [, $company, $payment] = $this->payment('SUB-WEBHOOK-V1');
        Http::fake(['*/transactions/debit-status' => Http::response(['status' => true, 'data' => [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 5000,
        ]])]);

        $payload = [
            'object' => 'payment',
            'status' => 'success',
            'type' => 'payment.web.checkout',
            'data' => [
                'transaction_id' => $payment->transaction_id,
                'kpp_tx_reference' => 'KPP-SUB-V1',
                'transaction_currency' => 'XOF',
                'transaction_amount' => '5000',
                'payment_date' => '2026-09-03 12:00:00',
                'payment_status' => 'TRANSACTION-COMPLETED',
            ],
        ];

        $this->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()->assertJson(['message' => 'SETTLED']);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertStringStartsWith('v1_', (string) $payment->fresh()->event_id);
        $this->assertSame('KPP-SUB-V1', $payment->fresh()->kpp_reference);
        $this->assertSame(23, (int) $company->fresh()->sms_count);
    }

    public function test_failed_webhook_marks_subscription_payment_failed_without_crediting(): void
    {
        [, $company, $payment] = $this->payment('SUB-WEBHOOK-FAILED');
        $payload = $this->v2Payload($payment, 'collection.failed', 'evt-subscription-failed');

        $this->withHeaders($this->v2Headers('collection.failed', 'evt-subscription-failed'))
            ->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()->assertJson(['message' => 'FAILED']);

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('Solde insuffisant', $payment->fresh()->failure_reason);
        $this->assertSame(3, (int) $company->fresh()->sms_count);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_invalid_v2_signature_or_amount_is_rejected_without_settlement(): void
    {
        config(['services.kprimepay.base_url' => 'https://api.kprimepay.test/v2', 'services.kprimepay.token' => 'sandbox-secret']);
        [, $company, $payment] = $this->payment('SUB-WEBHOOK-MISMATCH');
        $payload = $this->v2Payload($payment, 'collection.succeeded', 'evt-subscription-mismatch');

        $this->withHeaders(['X-API-BY' => 'wrong'])
            ->postJson('/api/kprimepay/webhook', $payload)
            ->assertStatus(400)->assertJson(['message' => 'INVALID_WEBHOOK']);

        Http::fake(['*/transactions/debit-status' => Http::response(['status' => true, 'data' => [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 4999,
        ]])]);
        $payload['data']['transaction_details']['amount'] = 4999;
        $this->withHeaders($this->v2Headers('collection.succeeded', 'evt-subscription-mismatch'))
            ->postJson('/api/kprimepay/webhook', $payload)
            ->assertStatus(422)->assertJson(['message' => 'PAYMENT_MISMATCH']);

        $payload['data']['transaction_details']['amount'] = 5000;
        $this->withHeaders($this->v2Headers('collection.succeeded', 'evt-subscription-mismatch'))
            ->postJson('/api/kprimepay/webhook', $payload)
            ->assertStatus(422)->assertJson(['message' => 'PAYMENT_MISMATCH']);

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame(3, (int) $company->fresh()->sms_count);
    }

    public function test_reconciliation_settles_subscription_payment_or_marks_it_expired(): void
    {
        config(['services.kprimepay.base_url' => 'https://api.kprimepay.test/v2', 'services.kprimepay.token' => 'sandbox-secret']);
        [, $company, $paid] = $this->payment('SUB-RECON-PAID');
        [, , $expired] = $this->payment('SUB-RECON-EXPIRED');

        Http::fake(function (Request $request) {
            return Http::response(['status' => true, 'data' => str_ends_with((string) $request['transaction_id'], 'PAID')
                ? ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 5000, 'kpp_tx_reference' => 'KPP-RECON-PAID']
                : ['status' => 'pending'],
            ]);
        });

        $this->artisan('payments:reconcile-kprimepay')->assertSuccessful();
        $this->assertSame('paid', $paid->fresh()->status);
        $this->assertSame('expired', $expired->fresh()->status);
        $this->assertSame(23, (int) $company->fresh()->sms_count);
    }

    public function test_unknown_collection_is_relayed_to_staging_only_from_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'services.kprimepay.webhook_bridge.enabled' => true,
            'services.kprimepay.webhook_bridge.url' => 'https://pos.lacasaweb.com/api/kprimepay/webhook/relay',
            'services.kprimepay.webhook_bridge.secret' => 'bridge-test-secret',
        ]);
        Http::fake([
            'https://pos.lacasaweb.com/api/kprimepay/webhook/relay' => Http::response(['status' => true, 'message' => 'SETTLED']),
        ]);

        $payload = [
            'api_version' => '2.0',
            'event' => 'collection.succeeded',
            'event_id' => 'evt-staging-only-1',
            'data' => [
                'transaction_id' => 'STAGING-ONLY-PAYMENT',
                'kpp_reference' => 'KPP-STAGING-ONLY',
                'transaction_details' => ['currency' => 'XOF', 'amount' => 7500],
            ],
        ];

        $this->withHeaders($this->v2Headers('collection.succeeded', 'evt-staging-only-1'))
            ->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()
            ->assertJson(['message' => 'RELAYED']);

        Http::assertSent(function (Request $request) use ($payload): bool {
            return $request->url() === 'https://pos.lacasaweb.com/api/kprimepay/webhook/relay'
                && $request->hasHeader('X-Maxanou-Relay-Timestamp')
                && $request->hasHeader('X-Maxanou-Relay-Signature')
                && $request['payload'] === $payload
                && $request['provider_headers']['X-KPP-EVENT-ID'] === 'evt-staging-only-1';
        });
    }

    public function test_relay_rejects_invalid_signature(): void
    {
        config(['services.kprimepay.webhook_bridge.secret' => 'bridge-test-secret']);
        $body = json_encode([
            'payload' => ['api_version' => '2.0'],
            'provider_headers' => [],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', '/api/kprimepay/webhook/relay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_MAXANOU_RELAY_TIMESTAMP' => (string) time(),
            'HTTP_X_MAXANOU_RELAY_SIGNATURE' => 'sha256=invalid',
        ], $body)->assertUnauthorized();
    }

    public function test_staging_does_not_relay_unknown_collection_back_to_another_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'staging');
        config([
            'services.kprimepay.webhook_bridge.enabled' => true,
            'services.kprimepay.webhook_bridge.url' => 'https://production.example/api/kprimepay/webhook/relay',
            'services.kprimepay.webhook_bridge.secret' => 'bridge-test-secret',
        ]);
        Http::fake();

        $payload = [
            'api_version' => '2.0',
            'event' => 'collection.succeeded',
            'event_id' => 'evt-staging-no-loop',
            'data' => [
                'transaction_id' => 'UNKNOWN-STAGING-PAYMENT',
                'transaction_details' => ['currency' => 'XOF', 'amount' => 7500],
            ],
        ];

        $this->withHeaders($this->v2Headers('collection.succeeded', 'evt-staging-no-loop'))
            ->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()
            ->assertJson(['message' => 'IGNORED']);

        Http::assertNothingSent();
    }
}
