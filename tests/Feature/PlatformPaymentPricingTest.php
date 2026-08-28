<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformSetting;
use App\Models\QuotaPayment;
use App\Models\User;
use App\Services\PlatformPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class PlatformPaymentPricingTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    private function admin(string $role = 'super_admin'): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => 'Administrateur SaaS', 'email' => $role.'@example.test',
            'password' => Hash::make('SecurePassword!123'), 'role' => $role,
            'is_active' => true, 'must_change_password' => false,
        ]);
    }

    public function test_super_admin_can_change_prices_with_password_and_history(): void
    {
        config(['services.kprimepay.sms_unit_price' => 35, 'services.kprimepay.whatsapp_unit_price' => 30]);
        $admin = $this->admin();

        $this->actingAs($admin, 'platform')->put(route('platform.settings.pricing.update'), [
            'sms_unit_price' => 40,
            'whatsapp_unit_price' => 32,
            'sms_unit_cost' => 15,
            'whatsapp_unit_cost' => 15,
            'reason' => 'Ajustement du coût fournisseur',
            'current_password' => 'SecurePassword!123',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('40', PlatformSetting::where('key', PlatformPricingService::SMS_KEY)->value('value'));
        $this->assertSame('32', PlatformSetting::where('key', PlatformPricingService::WHATSAPP_KEY)->value('value'));
        $this->assertDatabaseCount('platform_setting_histories', 2);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'platform.pricing.updated', 'reason' => 'Ajustement du coût fournisseur']);
        $this->assertSame(40, app(PlatformPricingService::class)->smsUnitPrice());
        $this->assertSame(32, app(PlatformPricingService::class)->whatsappUnitPrice());
    }

    public function test_wrong_password_or_non_super_admin_cannot_change_prices(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'platform')->put(route('platform.settings.pricing.update'), [
            'sms_unit_price' => 40, 'whatsapp_unit_price' => 32,
            'sms_unit_cost' => 15, 'whatsapp_unit_cost' => 15,
            'reason' => 'Changement non autorisé', 'current_password' => 'WrongPassword!123',
        ])->assertSessionHasErrors('current_password');
        $this->assertDatabaseCount('platform_settings', 0);

        $support = $this->admin('support');
        $this->actingAs($support, 'platform')->get(route('platform.settings.edit'))->assertForbidden();
    }

    public function test_new_checkout_uses_database_prices_and_keeps_a_snapshot(): void
    {
        config(['services.kprimepay.token' => 'sandbox', 'services.kprimepay.base_url' => 'https://api.test/v2']);
        $admin = $this->admin();
        PlatformSetting::create(['key' => PlatformPricingService::SMS_KEY, 'value' => '40', 'type' => 'integer', 'updated_by' => $admin->id]);
        PlatformSetting::create(['key' => PlatformPricingService::WHATSAPP_KEY, 'value' => '32', 'type' => 'integer', 'updated_by' => $admin->id]);
        app(PlatformPricingService::class)->forget();

        $owner = User::factory()->create(['status' => 1]);
        $this->activateCompanyFor($owner, 'priced-checkout');
        Http::fake(fn (Request $request) => Http::response(['status' => true, 'data' => [
            'kpp_tx_reference' => 'KPP_PRICING', 'checkout_url' => 'https://checkout.test/pay',
        ]], 201));

        $this->actingAs($owner)->postJson(route('sms-quota.checkout'), [
            'sms_quantity' => 2, 'whatsapp_quantity' => 3,
        ])->assertOk();

        $payment = QuotaPayment::firstOrFail();
        $this->assertSame(176, (int) $payment->amount);
        $this->assertSame(40, (int) $payment->sms_unit_price);
        $this->assertSame(32, (int) $payment->whatsapp_unit_price);
        $this->assertSame(15, (int) $payment->sms_unit_cost);
        $this->assertSame(15, (int) $payment->whatsapp_unit_cost);

        PlatformSetting::where('key', PlatformPricingService::SMS_KEY)->update(['value' => '50']);
        $this->assertSame(176, (int) $payment->fresh()->amount);
        $this->assertSame(40, (int) $payment->fresh()->sms_unit_price);
    }

    public function test_paid_payment_profit_is_shown_separately_and_combined(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create();
        $company = $this->activateCompanyFor($owner, 'profit-company');
        QuotaPayment::create([
            'company_id' => $company->id, 'user_id' => $owner->id,
            'transaction_id' => 'QUOTA-PROFIT', 'idempotency_key' => 'profit-test',
            'sms_quantity' => 10, 'sms_unit_price' => 35, 'sms_unit_cost' => 15,
            'whatsapp_quantity' => 10, 'whatsapp_unit_price' => 30, 'whatsapp_unit_cost' => 15,
            'amount' => 650, 'currency' => 'XOF', 'status' => 'paid', 'paid_at' => now(),
        ]);

        $this->actingAs($admin, 'platform')->get(route('platform.payments.index'))
            ->assertOk()
            ->assertSee('Rentabilité des paiements confirmés')
            ->assertSee('350 XOF')
            ->assertSee('300 XOF')
            ->assertSee('200 XOF')
            ->assertSee('150 XOF')
            ->assertSee('Bénéfice total : 350 XOF');
    }

    public function test_platform_can_list_and_reconcile_a_payment_only_once(): void
    {
        config(['services.kprimepay.token' => 'sandbox', 'services.kprimepay.base_url' => 'https://api.test/v2']);
        $admin = $this->admin();
        $owner = User::factory()->create();
        $company = $this->activateCompanyFor($owner, 'reconciliation-company');
        $payment = QuotaPayment::create([
            'company_id' => $company->id, 'user_id' => $owner->id,
            'transaction_id' => 'QUOTA-PLATFORM-TEST', 'idempotency_key' => 'platform-test',
            'sms_quantity' => 2, 'sms_unit_price' => 35,
            'whatsapp_quantity' => 1, 'whatsapp_unit_price' => 30,
            'amount' => 100, 'currency' => 'XOF', 'status' => 'pending',
        ]);
        Http::fake(['*/transactions/debit-status' => Http::response(['status' => true, 'data' => [
            'status' => 'success', 'transaction_currency' => 'XOF', 'transaction_amount' => 100,
            'kpp_tx_reference' => 'KPP_PLATFORM_TEST',
        ]])]);

        $this->actingAs($admin, 'platform')->get(route('platform.payments.index', ['q' => 'reconciliation-company']))
            ->assertOk()->assertSee('QUOTA-PLATFORM-TEST');
        $this->actingAs($admin, 'platform')->postJson(route('platform.payments.reconcile', $payment), [
            'reason' => 'Confirmation demandée par le client',
        ])->assertOk()->assertJson(['status' => 'paid']);

        $this->assertSame(2, (int) $company->fresh()->sms_count);
        $this->assertSame(1, (int) $company->fresh()->whatsapp_count);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'payment.reconciliation.paid', 'target_id' => (string) $payment->id]);

        $this->actingAs($admin, 'platform')->postJson(route('platform.payments.reconcile', $payment), [
            'reason' => 'Deuxième tentative volontaire',
        ])->assertStatus(422);
        $this->assertSame(2, (int) $company->fresh()->sms_count);
    }
}
