<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformSetting;
use App\Models\Company;
use App\Services\EntitlementService;
use App\Services\KprimePayService;
use App\Services\PlatformConfigurationService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class PlatformGeneralSettingTest extends TestCase
{
    use RefreshDatabase;
    private function admin(): PlatformAdmin{return PlatformAdmin::create(['name'=>'Configuration','email'=>'config@example.test','password'=>Hash::make('SecurePassword!123'),'role'=>'super_admin','is_active'=>true,'must_change_password'=>false,'two_factor_enabled'=>false]);}
    private function payload(): array{return ['app_name'=>config('app.name'),'support_email'=>'support@example.test','support_phone'=>'+22890000000','support_hours'=>'Lundi au vendredi','currency'=>'XOF','country'=>'TG','invitation_expiry_hours'=>72,'two_factor_expiry_minutes'=>15,'payment_expiry_hours'=>36,'email_enabled'=>1,'sms_enabled'=>0,'whatsapp_enabled'=>1,'kprimepay_enabled'=>0,'maintenance_message'=>'Maintenance planifiée pour amélioration.','reason'=>'Configuration initiale de la plateforme','current_password'=>'SecurePassword!123'];}

    public function test_super_admin_updates_general_settings_and_services_really_obey_switches(): void
    {
        $admin=$this->admin();
        $this->actingAs($admin,'platform')->put(route('platform.settings.general.update'),$this->payload())->assertSessionHas('success');
        $this->assertDatabaseHas('platform_settings',['key'=>'security.two_factor_expiry_minutes','value'=>'15']);
        $this->assertDatabaseHas('platform_audit_logs',['action'=>'platform.general_setting.updated','target_id'=>'services.kprimepay.enabled']);
        $this->assertFalse(app(PlatformConfigurationService::class)->channelEnabled('sms'));
        $this->assertFalse(app(PlatformConfigurationService::class)->channelEnabled('kprimepay'));
        $this->assertSame(false,app(SmsService::class)->sendSms('90000000','test')['status']);
        $this->expectException(RuntimeException::class);
        app(KprimePayService::class)->createCheckout(new \App\Models\QuotaPayment(),'/return');
    }

    public function test_platform_maintenance_blocks_pos_but_keeps_console_available(): void
    {
        $admin=$this->admin(); $payload=$this->payload(); $payload['maintenance_enabled']=1;
        $this->actingAs($admin,'platform')->put(route('platform.settings.general.update'),$payload)->assertSessionHas('success');
        $this->get('/login')->assertStatus(503)->assertSee('Maintenance en cours');
        $this->actingAs($admin,'platform')->get(route('platform.settings.general'))->assertOk();
    }

    public function test_limited_admin_cannot_manage_general_settings(): void
    {
        $admin=$this->admin(); $admin->update(['role'=>'technical']);
        $this->actingAs($admin,'platform')->get(route('platform.settings.general'))->assertForbidden();
    }

    public function test_wrong_password_on_general_settings_returns_an_exploitable_message(): void
    {
        $admin = $this->admin();
        $payload = $this->payload();
        $payload['current_password'] = 'WrongPassword!123';

        $this->actingAs($admin, 'platform')->put(route('platform.settings.general.update'), $payload)
            ->assertSessionHasErrors(['current_password' => 'Votre mot de passe plateforme est incorrect.']);
    }

    public function test_super_admin_can_override_subscription_enforcement_for_one_company(): void
    {
        $admin = $this->admin();
        $company = Company::create(['name' => 'Entreprise ciblée', 'email' => 'ciblee@example.test', 'number1' => '000']);
        PlatformSetting::updateOrCreate(['key' => 'subscriptions.enforcement_enabled'], ['value' => '0', 'type' => 'string']);

        $payload = ['mode' => 'enabled', 'reason' => 'Recette ciblée de cette entreprise', 'current_password' => 'SecurePassword!123'];
        $this->actingAs($admin, 'platform')->put(route('platform.settings.general.companies.subscription-enforcement', $company), $payload)->assertSessionHas('success');
        $company->refresh();
        $this->assertTrue($company->subscription_enforcement_enabled);
        $this->assertTrue(app(EntitlementService::class)->enforcementEnabledFor($company));

        $payload['mode'] = 'disabled';
        $this->actingAs($admin, 'platform')->put(route('platform.settings.general.companies.subscription-enforcement', $company), $payload)->assertSessionHas('success');
        $company->refresh();
        $this->assertFalse($company->subscription_enforcement_enabled);
        $this->assertFalse(app(EntitlementService::class)->enforcementEnabledFor($company));

        $payload['mode'] = 'inherit';
        PlatformSetting::where('key', 'subscriptions.enforcement_enabled')->update(['value' => '1']);
        $this->actingAs($admin, 'platform')->put(route('platform.settings.general.companies.subscription-enforcement', $company), $payload)->assertSessionHas('success');
        $company->refresh();
        $this->assertNull($company->subscription_enforcement_enabled);
        $this->assertTrue(app(EntitlementService::class)->enforcementEnabledFor($company));
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'company.subscription_enforcement.updated', 'target_id' => (string) $company->id]);
    }

    public function test_wrong_password_on_company_enforcement_returns_an_exploitable_message(): void
    {
        $admin = $this->admin();
        $company = Company::create(['name' => 'Entreprise protégée', 'email' => 'protegee@example.test', 'number1' => '001']);

        $this->actingAs($admin, 'platform')->put(route('platform.settings.general.companies.subscription-enforcement', $company), [
            'mode' => 'enabled', 'reason' => 'Test du mot de passe invalide', 'current_password' => 'WrongPassword!123',
        ])->assertSessionHasErrors(['current_password' => 'Votre mot de passe plateforme est incorrect.']);
    }
}
