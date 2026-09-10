<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Services\PartnerCountryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformPartnerSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => 'Configuration partenaire',
            'email' => 'partner-settings@example.test',
            'password' => Hash::make('SecurePassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => false,
            'two_factor_enabled' => false,
        ]);
    }

    public function test_togo_is_the_only_active_country_by_default(): void
    {
        $this->assertSame(['TG'], app(PartnerCountryService::class)->activeCodes());
        $this->get(route('partner.register'))->assertOk()->assertSee('Togo (+228)')->assertDontSee('Côte d’Ivoire (+225)');
    }

    public function test_super_admin_can_enable_countries_and_partner_registration_with_audited_settings(): void
    {
        $admin = $this->admin();
        $response = $this->actingAs($admin, 'platform')->put(route('platform.settings.partners.update'), [
            'partners_enabled' => '1',
            'registration_enabled' => '1',
            'countries' => ['TG', 'CI'],
            'code_cooldown_days' => 45,
            'reason' => 'Ouverture de la recette Togo et Côte d’Ivoire',
            'current_password' => 'SecurePassword!123',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('platform_settings', ['key' => 'partners.enabled', 'value' => 'true']);
        $this->assertDatabaseHas('platform_settings', ['key' => 'partners.registration_enabled', 'value' => 'true']);
        $this->assertDatabaseHas('platform_settings', ['key' => 'partners.active_countries', 'value' => '["TG","CI"]']);
        $this->assertDatabaseHas('platform_settings', ['key' => 'partners.code_change_cooldown_days', 'value' => '45']);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'platform.partner_setting.updated', 'target_id' => 'partners.active_countries']);
        $this->assertSame(['TG', 'CI'], app(PartnerCountryService::class)->activeCodes());
        $this->assertSame('+2250102030405', app(PartnerCountryService::class)->normalizePhone('CI', '01 02 03 04 05'));
    }

    public function test_registration_cannot_be_opened_when_partner_portal_is_disabled(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'platform')->put(route('platform.settings.partners.update'), [
            'registration_enabled' => '1',
            'countries' => ['TG'],
            'reason' => 'Tentative de recette isolée',
            'current_password' => 'SecurePassword!123',
        ])->assertSessionHasErrors('registration_enabled');
    }

    public function test_super_admin_can_enable_mixx_for_partner_withdrawals_without_opening_payouts(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'platform')->put(route('platform.settings.partners.update'), [
            'partners_enabled' => '1',
            'countries' => ['TG'],
            'payout_gateways' => ['TG' => ['MOOV-MONEY-TG', 'MIXX-YAS-TG']],
            'reason' => 'Activation contrôlée de Mixx pour la recette retrait',
            'current_password' => 'SecurePassword!123',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('platform_settings', ['key' => 'partners.payout_gateways', 'value' => '{"TG":["MOOV-MONEY-TG","MIXX-YAS-TG"]}']);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'platform.partner_setting.updated', 'target_id' => 'partners.payout_gateways']);
        $this->assertDatabaseHas('platform_settings', ['key' => 'partners.payouts_enabled', 'value' => 'false']);
    }
}
