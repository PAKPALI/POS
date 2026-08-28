<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformSetting;
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
}
