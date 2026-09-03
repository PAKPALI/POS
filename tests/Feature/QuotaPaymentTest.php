<?php

namespace Tests\Feature;

use App\Models\CompanyUser;
use App\Models\PlatformAdmin;
use App\Models\QuotaPayment;
use App\Models\Role;
use App\Models\User;
use App\Notifications\QuotaPaymentConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class QuotaPaymentTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_verified_quota_payment_notifies_active_platform_admin_once_with_details(): void
    {
        Notification::fake();
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner, 'quota-admin-notify');
        $admin = PlatformAdmin::create([
            'name' => 'Finance quotas', 'email' => 'finance-quotas@example.test', 'password' => 'secret',
            'role' => 'finance', 'is_active' => true, 'must_change_password' => false,
        ]);
        $payment = QuotaPayment::create([
            'company_id' => $company->id, 'user_id' => $owner->id, 'transaction_id' => 'QUOTA-ADMIN-NOTIFY',
            'idempotency_key' => 'quota-admin-notify', 'sms_quantity' => 4, 'whatsapp_quantity' => 7,
            'amount' => 350, 'currency' => 'XOF', 'status' => 'pending',
        ]);

        $service = app(\App\Services\QuotaPaymentSettlementService::class);
        $verified = ['status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 350];
        $this->assertTrue($service->creditVerified($payment, $verified, 'event-quota-admin', 'KPP-QUOTA-ADMIN'));
        $this->assertFalse($service->creditVerified($payment->fresh(), $verified, 'event-quota-admin', 'KPP-QUOTA-ADMIN'));

        Notification::assertSentTo($admin, QuotaPaymentConfirmedNotification::class, function ($notification) use ($admin, $company, $payment) {
            $rendered = $notification->toMail($admin)->render();
            return str_contains($rendered, $company->name)
                && str_contains($rendered, '4 SMS')
                && str_contains($rendered, '7 WhatsApp')
                && str_contains($rendered, '350 XOF')
                && str_contains($rendered, $payment->transaction_id);
        });
        Notification::assertCount(1);
        $this->assertSame('sent', $payment->fresh()->administration_email_status[(string) $admin->id]['status']);
    }

    public function test_checkout_and_verified_webhook_credit_quotas_only_once(): void
    {
        config([
            'services.kprimepay.base_url' => 'https://api.kprimepay.test/v2',
            'services.kprimepay.token' => 'sandbox-secret',
            'services.kprimepay.mode' => 1,
            'services.kprimepay.sms_unit_price' => 35,
            'services.kprimepay.whatsapp_unit_price' => 30,
        ]);
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner, 'quota-payment');

        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/checkout')) {
                return Http::response([
                    'status' => true,
                    'data' => [
                        'kpp_tx_reference' => 'KPP_TXN_TEST',
                        'checkout_url' => 'https://payments.kprimepay.test/checkout/test',
                        'expires_at' => now()->addHour()->format('Y-m-d H:i:s'),
                    ],
                ], 201);
            }

            return Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'transaction_currency' => 'XOF',
                    'transaction_amount' => 160,
                ],
            ]);
        });

        $checkout = $this->actingAs($owner)->postJson(route('sms-quota.checkout'), [
            'sms_quantity' => 2,
            'whatsapp_quantity' => 3,
        ])->assertOk()->assertJson([
            'status' => true,
            'checkout_url' => 'https://payments.kprimepay.test/checkout/test',
        ]);

        $payment = QuotaPayment::firstOrFail();
        $this->assertSame(160, (int) $payment->amount);
        $this->assertSame(35, (int) $payment->sms_unit_price);
        $this->assertSame(30, (int) $payment->whatsapp_unit_price);
        $this->assertSame(15, (int) $payment->sms_unit_cost);
        $this->assertSame(15, (int) $payment->whatsapp_unit_cost);
        $this->assertSame('pending', $payment->status);
        $checkout->assertJson(['transaction_id' => $payment->transaction_id]);
        $this->getJson(route('sms-quota.status', $payment->transaction_id))
            ->assertOk()->assertJson(['payment_status' => 'pending']);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/checkout')
            && $request->hasHeader('Authorization', 'Bearer sandbox-secret')
            && $request->hasHeader('Idempotency-Key', $payment->idempotency_key));

        $payload = [
            'api_version' => '2.0',
            'event' => 'collection.succeeded',
            'event_id' => 'evt_quota_test_1',
            'data' => [
                'transaction_id' => $payment->transaction_id,
                'kpp_reference' => 'KPP_TXN_TEST',
                'status' => 'succeeded',
                'transaction_details' => ['currency' => 'XOF', 'amount' => 160],
            ],
        ];
        $headers = [
            'X-API-VERSION' => '2.0',
            'X-API-BY' => 'KPRIMESOFT',
            'X-KPP-EVENT' => 'collection.succeeded',
            'X-KPP-EVENT-ID' => 'evt_quota_test_1',
        ];

        $this->withHeaders($headers)->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()->assertJson(['message' => 'CREDITED']);
        $this->assertSame(2, (int) $company->fresh()->sms_count);
        $this->assertSame(3, (int) $company->fresh()->whatsapp_count);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->getJson(route('sms-quota.status', $payment->transaction_id))
            ->assertOk()->assertJson(['payment_status' => 'paid']);

        $this->withHeaders($headers)->postJson('/api/kprimepay/webhook', $payload)->assertOk();
        $this->assertSame(2, (int) $company->fresh()->sms_count);
        $this->assertSame(3, (int) $company->fresh()->whatsapp_count);
    }

    public function test_quota_page_requires_dedicated_company_permission(): void
    {
        $user = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($user, 'quota-permission');
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Sans quota',
            'key' => 'without-quota',
            'is_system' => false,
        ]);
        CompanyUser::where('company_id', $company->id)->where('user_id', $user->id)->update(['role_id' => $role->id]);

        $this->actingAs($user)->get(route('sms-quota.index'))->assertForbidden();
    }

    public function test_v1_webhook_is_verified_and_credits_quotas_only_once(): void
    {
        config([
            'services.kprimepay.base_url' => 'https://api.kprimepay.test/v2',
            'services.kprimepay.token' => 'sandbox-secret',
        ]);
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner, 'quota-v1');
        $payment = QuotaPayment::create([
            'company_id' => $company->id,
            'user_id' => $owner->id,
            'transaction_id' => 'QUOTA-1-V1TEST',
            'idempotency_key' => 'quota-v1-test',
            'sms_quantity' => 0,
            'whatsapp_quantity' => 2,
            'amount' => 60,
            'currency' => 'XOF',
            'status' => 'pending',
        ]);

        Http::fake(["*/transactions/debit-status" => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'transaction_currency' => 'XOF',
                'transaction_amount' => 60,
            ],
        ])]);

        $payload = [
            'object' => 'payment',
            'status' => 'success',
            'type' => 'payment.web.checkout',
            'data' => [
                'transaction_id' => $payment->transaction_id,
                'kpp_tx_reference' => 'KPP_TXN_V1_TEST',
                'transaction_currency' => 'XOF',
                'transaction_amount' => '60',
                'payment_date' => '2026-08-26 16:52:07',
                'payment_status' => 'TRANSACTION-COMPLETED',
            ],
        ];

        $this->postJson('/api/kprimepay/webhook', $payload)
            ->assertOk()->assertJson(['message' => 'CREDITED']);
        $this->assertSame(0, (int) $company->fresh()->sms_count);
        $this->assertSame(2, (int) $company->fresh()->whatsapp_count);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertStringStartsWith('v1_', (string) $payment->fresh()->event_id);
        $this->assertSame('KPP_TXN_V1_TEST', $payment->fresh()->kpp_reference);

        $this->postJson('/api/kprimepay/webhook', $payload)->assertOk();
        $this->assertSame(2, (int) $company->fresh()->whatsapp_count);
    }

    public function test_checkout_explains_when_kprimepay_key_is_missing(): void
    {
        config(['services.kprimepay.token' => null]);
        $owner = User::factory()->create(['status' => 1]);
        $this->activateCompanyFor($owner, 'quota-missing-key');

        $this->actingAs($owner)->postJson(route('sms-quota.checkout'), [
            'sms_quantity' => 1,
            'whatsapp_quantity' => 0,
        ])->assertStatus(503)->assertJson([
            'status' => false,
            'title' => 'KPrimePay non configuré',
        ]);

        $this->assertDatabaseCount('quota_payments', 0);
    }

    public function test_reconciliation_settles_failed_and_expired_payments_without_double_credit(): void
    {
        config([
            'services.kprimepay.base_url' => 'https://api.kprimepay.test/v2',
            'services.kprimepay.token' => 'sandbox-secret',
        ]);
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner, 'quota-reconciliation');
        $makePayment = function (string $suffix, int $sms, int $whatsapp, int $amount) use ($owner, $company) {
            return QuotaPayment::create([
                'company_id' => $company->id,
                'user_id' => $owner->id,
                'transaction_id' => 'QUOTA-RECON-'.$suffix,
                'idempotency_key' => 'quota-recon-'.strtolower($suffix),
                'sms_quantity' => $sms,
                'whatsapp_quantity' => $whatsapp,
                'amount' => $amount,
                'currency' => 'XOF',
                'status' => 'pending',
                'expires_at' => now()->subMinute(),
            ]);
        };
        $paid = $makePayment('PAID', 2, 1, 100);
        $failed = $makePayment('FAILED', 5, 0, 175);
        $expired = $makePayment('EXPIRED', 0, 4, 120);

        Http::fake(function (Request $request) {
            $transactionId = (string) $request['transaction_id'];
            $data = match (true) {
                str_ends_with($transactionId, 'PAID') => [
                    'status' => 'success', 'transaction_currency' => 'XOF',
                    'transaction_amount' => 100, 'kpp_tx_reference' => 'KPP_RECON_PAID',
                ],
                str_ends_with($transactionId, 'FAILED') => [
                    'status' => 'failed', 'failure_reason' => 'Solde insuffisant',
                    'transaction_currency' => 'XOF', 'transaction_amount' => 175,
                ],
                default => ['status' => 'pending'],
            };
            return Http::response(['status' => true, 'data' => $data]);
        });

        $this->artisan('payments:reconcile-kprimepay')->assertSuccessful();
        $this->assertSame('paid', $paid->fresh()->status);
        $this->assertSame('KPP_RECON_PAID', $paid->fresh()->kpp_reference);
        $this->assertSame('failed', $failed->fresh()->status);
        $this->assertSame('Solde insuffisant', $failed->fresh()->failure_reason);
        $this->assertSame('expired', $expired->fresh()->status);
        $this->assertSame(2, (int) $company->fresh()->sms_count);
        $this->assertSame(1, (int) $company->fresh()->whatsapp_count);

        $this->artisan('payments:reconcile-kprimepay')->assertSuccessful();
        $this->assertSame(2, (int) $company->fresh()->sms_count);
        $this->assertSame(1, (int) $company->fresh()->whatsapp_count);
    }
}
