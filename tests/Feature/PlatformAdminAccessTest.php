<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_platform_login(): void
    {
        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.login'));
    }

    public function test_regular_pos_user_cannot_access_platform_console(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.login'));
    }

    public function test_active_platform_admin_can_login_and_view_dashboard(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Administrateur plateforme',
            'email' => 'platform@example.test',
            'password' => Hash::make('SecurePassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => false,
            'two_factor_enabled' => false,
        ]);

        $this->post(route('platform.login.submit'), [
            'email' => ' PLATFORM@EXAMPLE.TEST ',
            'password' => 'SecurePassword!123',
        ])->assertRedirect(route('platform.dashboard'));

        $this->assertAuthenticatedAs($admin, 'platform');
        $this->get(route('platform.dashboard'))
            ->assertOk()
            ->assertSee('Vue générale du SaaS');

        $this->assertDatabaseHas('platform_audit_logs', [
            'platform_admin_id' => $admin->id,
            'action' => 'platform.login',
            'result' => 'success',
        ]);
    }

    public function test_inactive_platform_admin_cannot_login(): void
    {
        PlatformAdmin::create([
            'name' => 'Compte désactivé',
            'email' => 'inactive@example.test',
            'password' => Hash::make('SecurePassword!123'),
            'role' => 'super_admin',
            'is_active' => false,
        ]);

        $this->from(route('platform.login'))->post(route('platform.login.submit'), [
            'email' => 'inactive@example.test',
            'password' => 'SecurePassword!123',
        ])->assertRedirect(route('platform.login'));

        $this->assertGuest('platform');
    }

    public function test_logout_is_audited(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Administrateur plateforme',
            'email' => 'logout@example.test',
            'password' => Hash::make('SecurePassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->actingAs($admin, 'platform')
            ->post(route('platform.logout'))
            ->assertRedirect(route('platform.login'));

        $this->assertGuest('platform');
        $this->assertTrue(PlatformAuditLog::where('platform_admin_id', $admin->id)
            ->where('action', 'platform.logout')->exists());
    }

    public function test_initial_password_must_be_changed_before_dashboard_access(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Premier administrateur',
            'email' => 'first@example.test',
            'password' => Hash::make('InitialPassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $this->actingAs($admin, 'platform')
            ->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.password.edit'));

        $this->actingAs($admin, 'platform')
            ->put(route('platform.password.update'), [
                'current_password' => 'InitialPassword!123',
                'password' => 'PermanentPassword!456',
                'password_confirmation' => 'PermanentPassword!456',
            ])->assertRedirect(route('platform.dashboard'));

        $this->assertFalse($admin->fresh()->must_change_password);
        $this->assertTrue(Hash::check('PermanentPassword!456', $admin->fresh()->password));
        $this->assertDatabaseHas('platform_audit_logs', [
            'platform_admin_id' => $admin->id,
            'action' => 'platform.password.changed',
        ]);
    }

    public function test_password_change_page_offers_visibility_controls_for_all_password_fields(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Administrateur plateforme',
            'email' => 'visibility@example.test',
            'password' => Hash::make('InitialPassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $this->actingAs($admin, 'platform')
            ->get(route('platform.password.edit'))
            ->assertOk()
            ->assertSee('data-target="current_password"', false)
            ->assertSee('data-target="password"', false)
            ->assertSee('data-target="password_confirmation"', false)
            ->assertSee('bi-eye');
    }

    public function test_password_validation_messages_are_clear_and_in_french(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Administrateur plateforme',
            'email' => 'validation@example.test',
            'password' => Hash::make('CurrentPassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $this->actingAs($admin, 'platform')
            ->from(route('platform.password.edit'))
            ->put(route('platform.password.update'), [
                'current_password' => 'CurrentPassword!123',
                'password' => 'Didier230595',
                'password_confirmation' => 'Different230595',
            ])
            ->assertRedirect(route('platform.password.edit'))
            ->assertSessionHasErrors(['password'])
            ->assertSessionHasErrorsIn('default', [
                'password' => 'La confirmation du nouveau mot de passe ne correspond pas.',
            ]);
    }
}
