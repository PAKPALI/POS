<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerPromoCode;
use App\Models\PartnerTwoFactorChallenge;
use App\Models\PlatformSetting;
use App\Notifications\PartnerTwoFactorSetupNotification;
use App\Services\PlatformConfigurationService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PartnerFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_guard_password_broker_and_feature_flags_are_isolated_and_disabled(): void
    {
        $this->assertSame('partners', config('auth.guards.partner.provider'));
        $this->assertSame(Partner::class, config('auth.providers.partners.model'));
        $this->assertSame('partner_password_reset_tokens', config('auth.passwords.partners.table'));

        $configuration = app(PlatformConfigurationService::class);
        $this->assertFalse($configuration->boolean('partners.enabled'));
        $this->assertFalse($configuration->boolean('partners.registration_enabled'));
        $this->assertFalse($configuration->boolean('partners.payouts_enabled'));
        $this->assertSame(1000, $configuration->integer('partners.first_discount_bps', 0));
    }

    public function test_partner_identity_code_and_two_factor_constraints_are_enforced(): void
    {
        $partner = Partner::factory()->create();
        PartnerPromoCode::create([
            'partner_id' => $partner->id, 'code' => 'MAX2026', 'normalized_code' => 'MAX2026',
            'status' => 'active', 'is_primary' => true,
        ]);
        PartnerTwoFactorChallenge::create([
            'partner_id' => $partner->id, 'purpose' => 'login', 'code_hash' => bcrypt('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->assertDatabaseHas('partner_promo_codes', ['normalized_code' => 'MAX2026']);
        $this->assertDatabaseHas('partner_two_factor_challenges', ['partner_id' => $partner->id, 'purpose' => 'login']);

        $this->expectException(QueryException::class);
        PartnerPromoCode::create([
            'partner_id' => $partner->id, 'code' => 'max2026', 'normalized_code' => 'MAX2026',
            'status' => 'active',
        ]);
    }

    public function test_partner_profile_appearance_is_persisted_with_the_dynamic_controls(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);

        $this->actingAs($partner, 'partner')->get(route('partner.profile'))
            ->assertOk()
            ->assertSee('partnerAppearanceForm', false)
            ->assertSee('Selon l’appareil')
            ->assertSee('Aperçu du thème');

        $this->actingAs($partner, 'partner')->put(route('partner.profile.appearance.update'), [
            'appearance_mode' => 'light',
            'accent_color' => '#7c5cfc',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'appearance_mode' => 'light',
            'accent_color' => '#7C5CFC',
        ]);
    }

    public function test_partner_can_securely_update_identity_password_and_email(): void
    {
        $this->enablePartners();
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $this->actingAs($partner, 'partner')->put(route('partner.profile.identity.update'), [
            'name' => 'Nouveau nom', 'username' => 'nouveau-partenaire', 'current_password' => 'Password!123456',
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'normalized_username' => 'nouveau-partenaire']);

        // `actingAs` authenticates the guard but does not populate the
        // versioned session key used by EnsurePartnerActive. Re-seed it with
        // the current value so this test models an already authenticated
        // browser session after the identity update.
        $this->withSession(['partner_auth_version' => $partner->fresh()->auth_version])
            ->actingAs($partner->fresh(), 'partner')->put(route('partner.profile.password.update'), [
            'current_password' => 'Password!123456', 'password' => 'NewPartnerPassword!123', 'password_confirmation' => 'NewPartnerPassword!123',
        ])->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewPartnerPassword!123', $partner->fresh()->password));

        Notification::fake();
        $this->withSession(['partner_auth_version' => $partner->fresh()->auth_version])
            ->actingAs($partner->fresh(), 'partner')->put(route('partner.profile.email.update'), [
            'email' => 'new-partner@example.test', 'current_password' => 'NewPartnerPassword!123',
        ])->assertRedirect(route('partner.login'));
        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'normalized_email' => 'new-partner@example.test', 'status' => 'pending_email']);
        Notification::assertSentTo($partner->fresh(), \App\Notifications\PartnerEmailVerificationNotification::class);
    }

    public function test_partner_can_optionally_enable_two_factor_from_profile_with_email_code(): void
    {
        $this->enablePartners();
        Notification::fake();
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $session = ['partner_auth_version' => $partner->auth_version];

        $this->withSession($session)->actingAs($partner, 'partner')
            ->put(route('partner.profile.two-factor.update'), [
                'enabled' => 1,
                'current_password' => 'Password!123456',
            ])->assertSessionHas('two_factor_setup_pending');

        $this->assertFalse($partner->fresh()->two_factor_login_enabled);
        $this->assertDatabaseHas('partner_two_factor_challenges', [
            'partner_id' => $partner->id,
            'purpose' => 'settings_2fa',
        ]);
        Notification::assertSentTo($partner, PartnerTwoFactorSetupNotification::class);

        $notification = null;
        Notification::assertSentTo($partner, function (PartnerTwoFactorSetupNotification $sent) use (&$notification) {
            $notification = $sent;
            return true;
        });
        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('code');
        $property->setAccessible(true);
        $code = $property->getValue($notification);
        $renderedMail = $notification->toMail($partner)->render();
        $this->assertStringContainsString('Sécurité du compte partenaire', $renderedMail);
        $this->assertStringContainsString('Votre code d’activation', $renderedMail);
        $this->assertStringContainsString('Copyright', $renderedMail);
        $this->assertStringContainsString($code, $renderedMail);

        $this->actingAs($partner->fresh(), 'partner')
            ->withSession([
                'partner_auth_version' => $partner->fresh()->auth_version,
                'partner_2fa_setup_partner_id' => $partner->id,
            ])
            ->put(route('partner.profile.two-factor.update'), [
                'enabled' => 1,
                'current_password' => 'Password!123456',
                'code' => $code,
            ])->assertSessionHas('success');

        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'two_factor_login_enabled' => 1]);
        $this->assertDatabaseHas('partner_audit_logs', ['partner_id' => $partner->id, 'action' => 'partner.two_factor_enabled']);
        $this->withSession(['partner_auth_version' => $partner->fresh()->auth_version])
            ->actingAs($partner->fresh(), 'partner')
            ->get(route('partner.profile'))->assertOk();

        $this->actingAs($partner->fresh(), 'partner')
            ->withSession(['partner_auth_version' => $partner->fresh()->auth_version])
            ->put(route('partner.profile.two-factor.update'), [
                'enabled' => 0,
                'current_password' => 'Password!123456',
            ])->assertSessionHas('success');

        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'two_factor_login_enabled' => 0]);
    }

    private function enablePartners(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
    }
}
