<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PlatformSetting;
use App\Notifications\PartnerEmailVerificationNotification;
use App\Notifications\PartnerTwoFactorNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PartnerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function enable(string $key): void
    {
        PlatformSetting::where('key', $key)->update(['value' => 'true']);
        app(\App\Services\PlatformConfigurationService::class)->forget([$key]);
    }

    private function activePartner(array $attributes = []): Partner
    {
        return Partner::factory()->create(array_merge(['status' => 'active', 'email_verified_at' => now()], $attributes));
    }

    public function test_registration_is_closed_by_default_and_enabled_registration_requires_email_verification(): void
    {
        Notification::fake();
        $this->post(route('partner.register.submit'), [])->assertSessionHasErrors('email');
        $this->enable('partners.registration_enabled');
        $response = $this->post(route('partner.register.submit'), [
            'name' => 'Partenaire Test', 'username' => 'partenaire-test', 'email' => 'partner@example.test',
            'country_code' => 'TG', 'phone_number' => '90 00 00 00',
            'password' => 'PartnerPassword!123', 'password_confirmation' => 'PartnerPassword!123', 'accepted_terms' => '1',
        ]);
        $response->assertRedirect(route('partner.login'))->assertSessionHas('status');
        $this->assertStringContainsString('Cliquez sur ce lien pour activer votre compte', session('status'));
        $this->assertDatabaseHas('partners', ['normalized_email' => 'partner@example.test', 'phone_e164' => '+22890000000', 'status' => 'pending_email']);
        Notification::assertSentTo(Partner::where('normalized_email', 'partner@example.test')->first(), PartnerEmailVerificationNotification::class);
    }

    public function test_pending_partner_is_told_to_validate_email_before_login(): void
    {
        $this->enable('partners.enabled');
        $partner = Partner::factory()->create([
            'email' => 'pending-login@example.test',
            'normalized_email' => 'pending-login@example.test',
            'status' => 'pending_email',
            'email_verified_at' => null,
        ]);

        $response = $this->from(route('partner.login'))->post(route('partner.login.submit'), [
            'email' => $partner->email,
            'password' => 'Password!123456',
        ]);

        $response->assertRedirect(route('partner.login'));
        $this->assertSame(
            'Les identifiants fournis sont incorrects.',
            session('errors')->first('email')
        );
        $this->assertGuest('partner');
    }

    public function test_verified_partner_can_login_and_reach_protected_dashboard_when_enabled(): void
    {
        $this->enable('partners.enabled');
        $partner = $this->activePartner();
        $this->post(route('partner.login.submit'), ['email' => $partner->email, 'password' => 'Password!123456'])
            ->assertRedirect(route('partner.dashboard'));
        $this->assertAuthenticatedAs($partner, 'partner');
        $this->get(route('partner.dashboard'))->assertOk()->assertSee('Votre activité partenaire');
    }

    public function test_two_factor_login_uses_one_time_challenge(): void
    {
        Notification::fake();
        $this->enable('partners.enabled');
        $partner = $this->activePartner(['two_factor_login_enabled' => true]);
        $this->post(route('partner.login.submit'), ['email' => $partner->email, 'password' => 'Password!123456'])
            ->assertRedirect(route('partner.two-factor.challenge'));
        $this->assertGuest('partner');
        Notification::assertSentTo($partner, PartnerTwoFactorNotification::class);
        $notification = null;
        Notification::assertSentTo($partner, function (PartnerTwoFactorNotification $sent) use (&$notification) { $notification = $sent; return true; });
        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('code'); $property->setAccessible(true);
        $code = $property->getValue($notification);
        $this->post(route('partner.two-factor.verify'), ['code' => $code])->assertRedirect(route('partner.dashboard'));
        $this->assertAuthenticatedAs($partner, 'partner');
        $this->post(route('partner.two-factor.verify'), ['code' => $code])->assertSessionHasErrors('code');
    }

    public function test_disabled_portal_does_not_allow_login_even_with_valid_credentials(): void
    {
        $partner = $this->activePartner();
        $this->post(route('partner.login.submit'), ['email' => $partner->email, 'password' => 'Password!123456'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('partner');
    }

    public function test_partner_password_validation_messages_are_in_french(): void
    {
        $this->enable('partners.registration_enabled');
        $response = $this->from(route('partner.register'))->post(route('partner.register.submit'), [
            'name' => 'Partenaire Test', 'username' => 'partenaire-faible', 'email' => 'weak-password@example.test',
            'country_code' => 'TG', 'phone_number' => '90 00 00 01',
            'password' => 'password', 'password_confirmation' => 'password', 'accepted_terms' => '1',
        ]);

        $response->assertRedirect(route('partner.register'));
        $this->assertStringContainsString('Le mot de passe doit contenir au moins 12 caractères.', session('errors')->first('password'));
    }

    public function test_partner_phone_validation_messages_are_in_french(): void
    {
        $this->enable('partners.registration_enabled');
        $response = $this->from(route('partner.register'))->post(route('partner.register.submit'), [
            'name' => 'Partenaire Test', 'username' => 'partenaire-numero', 'email' => 'phone-validation@example.test',
            'country_code' => 'TG', 'phone_number' => str_repeat('1', 25),
            'password' => 'PartnerPassword!123', 'password_confirmation' => 'PartnerPassword!123', 'accepted_terms' => '1',
        ]);

        $response->assertRedirect(route('partner.register'));
        $this->assertSame('Le numéro ne doit pas dépasser 24 caractères.', session('errors')->first('phone_number'));
    }

    public function test_registration_rejects_an_email_already_used_by_a_partner(): void
    {
        $this->enable('partners.registration_enabled');
        Partner::factory()->create([
            'email' => 'existing-partner@example.test',
            'normalized_email' => 'existing-partner@example.test',
        ]);

        $this->from(route('partner.register'))->post(route('partner.register.submit'), [
            'name' => 'Partenaire Doublon', 'username' => 'partenaire-email-doublon',
            'email' => 'EXISTING-PARTNER@example.test', 'country_code' => 'TG',
            'phone_number' => '90 00 00 20', 'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123', 'accepted_terms' => '1',
        ])->assertRedirect(route('partner.register'))
            ->assertSessionHasErrors(['email' => 'Cette adresse e-mail est déjà associée à un compte partenaire.']);
    }

    public function test_registration_rejects_a_phone_already_used_by_a_partner(): void
    {
        $this->enable('partners.registration_enabled');
        Partner::factory()->create([
            'email' => 'existing-phone-partner@example.test',
            'normalized_email' => 'existing-phone-partner@example.test',
            'phone_country_code' => 'TG', 'country_code' => 'TG',
            'phone_e164' => '+22890000021',
        ]);

        $this->from(route('partner.register'))->post(route('partner.register.submit'), [
            'name' => 'Partenaire Doublon', 'username' => 'partenaire-telephone-doublon',
            'email' => 'new-partner@example.test', 'country_code' => 'TG',
            'phone_number' => '+228 90 00 00 21', 'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123', 'accepted_terms' => '1',
        ])->assertRedirect(route('partner.register'))
            ->assertSessionHasErrors(['phone_number' => 'Ce numéro est déjà associé à un compte partenaire.']);
    }

    public function test_signed_email_verification_activates_pending_partner(): void
    {
        $partner = Partner::factory()->create(['status' => 'pending_email', 'email_verified_at' => null]);
        $url = URL::temporarySignedRoute('partner.email.verify', now()->addMinutes(10), [
            'partner' => $partner->id,
            'hash' => sha1($partner->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect(route('partner.login'));
        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'status' => 'active']);
    }

    public function test_password_reset_uses_partner_broker_and_invalidates_auth_version(): void
    {
        Notification::fake();
        $partner = $this->activePartner();
        $this->post(route('partner.password.email'), ['email' => $partner->email])
            ->assertSessionHas('status');
        Notification::assertSentTo($partner, \App\Notifications\PartnerResetPasswordNotification::class);
        $notification = null;
        Notification::assertSentTo($partner, function (\App\Notifications\PartnerResetPasswordNotification $sent) use (&$notification) {
            $notification = $sent;
            return true;
        });
        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('token');
        $property->setAccessible(true);
        $token = $property->getValue($notification);
        $oldVersion = (int) $partner->fresh()->auth_version;

        $this->post(route('partner.password.reset.update'), [
            'token' => $token,
            'email' => $partner->email,
            'password' => 'NewPartnerPassword!123',
            'password_confirmation' => 'NewPartnerPassword!123',
        ])->assertRedirect(route('partner.login'));

        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'auth_version' => $oldVersion + 1]);
    }

    public function test_partner_logout_invalidates_the_entire_session(): void
    {
        $partner = $this->activePartner();

        $this->actingAs($partner, 'partner')
            ->withSession([
                'partner_auth_version' => $partner->auth_version,
                'sensitive_marker' => 'must-disappear',
            ])
            ->post(route('partner.logout'))
            ->assertRedirect(route('partner.login'))
            ->assertSessionMissing('partner_auth_version')
            ->assertSessionMissing('sensitive_marker');

        $this->assertGuest('partner');
    }
}
