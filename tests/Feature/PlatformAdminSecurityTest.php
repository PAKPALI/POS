<?php

namespace Tests\Feature;

use App\Mail\PlatformSecurityMail;
use App\Models\PlatformAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformAdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $attributes = []): PlatformAdmin
    {
        return PlatformAdmin::create(array_merge([
            'name' => 'Administrateur sécurisé', 'email' => 'secure@example.test',
            'password' => Hash::make('SecurePassword!123'), 'role' => 'super_admin',
            'is_active' => true, 'must_change_password' => false, 'two_factor_enabled' => true,
        ], $attributes));
    }

    public function test_two_factor_code_is_required_and_completes_login(): void
    {
        Mail::fake();
        $admin = $this->admin();

        $this->post(route('platform.login.submit'), ['email' => $admin->email, 'password' => 'SecurePassword!123'])
            ->assertRedirect(route('platform.two-factor.challenge'));
        $this->assertGuest('platform');

        $code = null;
        Mail::assertSent(PlatformSecurityMail::class, function ($mail) use (&$code) {
            $code = $mail->code;
            return $mail->hasTo('secure@example.test');
        });
        $this->post(route('platform.two-factor.verify'), ['code' => $code])->assertRedirect(route('platform.dashboard'));
        $this->assertAuthenticatedAs($admin, 'platform');
        $this->assertDatabaseHas('platform_audit_logs', ['platform_admin_id' => $admin->id, 'action' => 'platform.login']);
    }

    public function test_password_recovery_is_single_use_and_changes_security_version(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $this->post(route('platform.password.email'), ['email' => $admin->email])->assertSessionHas('success');

        $url = null;
        Mail::assertSent(PlatformSecurityMail::class, function ($mail) use (&$url) {
            $url = $mail->actionUrl;
            return $mail->hasTo('secure@example.test');
        });
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $token = basename((string) parse_url($url, PHP_URL_PATH));

        $payload = ['email' => $query['email'], 'token' => $token,
            'password' => 'NewSecurePassword!456', 'password_confirmation' => 'NewSecurePassword!456'];
        $this->post(route('platform.password.reset.update'), $payload)->assertRedirect(route('platform.login'));
        $this->assertTrue(Hash::check('NewSecurePassword!456', $admin->fresh()->password));
        $this->assertSame(1, $admin->fresh()->auth_version);
        $this->assertNull(DB::table('platform_password_reset_tokens')->where('email', $admin->email)->first());
        $this->post(route('platform.password.reset.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_reset_two_factor_with_password_reason_and_audit(): void
    {
        $actor = $this->admin(['email' => 'actor@example.test']);
        $target = $this->admin(['email' => 'target@example.test', 'two_factor_attempts' => 5]);

        $this->actingAs($actor, 'platform')->post(route('platform.admins.two-factor.reset', $target), [
            'reason' => 'Compte bloqué après plusieurs essais',
            'current_password' => 'SecurePassword!123',
        ])->assertSessionHas('success');

        $this->assertSame(0, $target->fresh()->two_factor_attempts);
        $this->assertSame(1, $target->fresh()->auth_version);
        $this->assertDatabaseHas('platform_audit_logs', [
            'platform_admin_id' => $actor->id, 'target_id' => (string) $target->id,
            'action' => 'platform.admin.two_factor.reset',
        ]);
    }

    public function test_super_admin_can_disable_and_enable_two_factor(): void
    {
        Mail::fake();
        $actor = $this->admin(['email' => 'toggle-actor@example.test']);
        $target = $this->admin(['email' => 'toggle-target@example.test']);

        $this->actingAs($actor, 'platform')->patch(route('platform.admins.two-factor.update', $target), [
            'enabled' => false,
            'reason' => 'Désactivation demandée par le responsable',
            'current_password' => 'SecurePassword!123',
        ])->assertSessionHas('success');

        $this->assertFalse($target->fresh()->two_factor_enabled);
        $this->assertDatabaseHas('platform_audit_logs', [
            'action' => 'platform.admin.two_factor.disabled', 'target_id' => (string) $target->id,
        ]);

        $this->post(route('platform.logout'));
        $this->post(route('platform.login.submit'), [
            'email' => $target->email, 'password' => 'SecurePassword!123',
        ])->assertRedirect(route('platform.dashboard'));
        $this->assertAuthenticatedAs($target, 'platform');
        Mail::assertNothingSent();
    }
}
