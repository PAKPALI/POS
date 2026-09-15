<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformSetting;
use App\Services\PlatformConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MarketingSocialCommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_configured_social_networks_are_public_and_whatsapp_requires_rules(): void
    {
        PlatformSetting::create(['key' => 'social.whatsapp_url', 'value' => 'https://chat.whatsapp.com/maxanou', 'type' => 'url']);
        PlatformSetting::create(['key' => 'social.instagram_url', 'value' => 'https://instagram.com/maxanou', 'type' => 'url']);

        $this->get('/aide')->assertOk()
            ->assertSee('La communauté Maxanou')
            ->assertSee('Communauté WhatsApp')
            ->assertSee('Instagram')
            ->assertSee('https://chat.whatsapp.com/maxanou', false)
            ->assertSee('Les insultes, images ou sujets inappropriés sont interdits.')
            ->assertDontSee('TikTok');
        $this->get('/')->assertOk()
            ->assertSee('data-social-invite-modal', false)
            ->assertSee('Rejoindre plus tard');
    }

    public function test_super_admin_can_configure_social_network_links(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Configuration sociale',
            'email' => 'social@example.test',
            'password' => Hash::make('SecurePassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => false,
            'two_factor_enabled' => false,
        ]);

        $payload = [
            'whatsapp_url' => 'https://chat.whatsapp.com/maxanou',
            'tiktok_url' => '',
            'facebook_url' => 'https://facebook.com/maxanou',
            'instagram_url' => 'https://instagram.com/maxanou',
            'reason' => 'Publication des canaux officiels',
            'current_password' => 'SecurePassword!123',
        ];

        $this->actingAs($admin, 'platform')->get(route('platform.settings.social-networks.edit'))
            ->assertOk()
            ->assertSee('Réseaux sociaux')
            ->assertSee('Communauté WhatsApp');
        $this->actingAs($admin, 'platform')->put(route('platform.settings.social-networks.update'), $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('platform_settings', ['key' => 'social.whatsapp_url', 'value' => 'https://chat.whatsapp.com/maxanou']);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'platform.social_network_setting.updated', 'target_id' => 'social.instagram_url']);
        $this->assertArrayNotHasKey('tiktok', app(PlatformConfigurationService::class)->socialNetworks());
        $this->assertSame('https://facebook.com/maxanou', app(PlatformConfigurationService::class)->socialNetworks()['facebook']['url']);
    }
}
