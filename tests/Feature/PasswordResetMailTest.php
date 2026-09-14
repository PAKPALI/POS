<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_uses_the_project_email_template(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'Client Test', 'email' => 'reset@test.local',
            'password' => 'Password123', 'status' => 1,
        ]);

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            return $mail->view === 'emails.user.resetPassword'
                && str_contains($mail->subject, config('app.name'));
        });

        $html = view('emails.user.resetPassword', [
            'user' => $user, 'resetUrl' => 'https://example.test/reset',
            'expiresInMinutes' => 60, 'company' => null,
        ])->render();
        $this->assertStringContainsString('Copyright', $html);
        $this->assertStringContainsString(config('app.name'), $html);
    }

    public function test_forgot_password_does_not_reveal_whether_an_account_exists(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'known-reset@test.local']);

        $known = $this->post(route('password.email'), ['email' => ' KNOWN-RESET@TEST.LOCAL ']);
        $missing = $this->post(route('password.email'), ['email' => 'missing-reset@test.local']);

        $known->assertSessionHas('status');
        $missing->assertSessionHas('status');
        $this->assertSame($known->getSession()->get('status'), $missing->getSession()->get('status'));
    }

    public function test_password_reset_requires_a_strong_password_and_does_not_auto_login(): void
    {
        $user = User::factory()->create([
            'email' => 'secure-reset@test.local',
            'password' => Hash::make('PreviousPassword!123'),
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'LongPassword123',
            'password_confirmation' => 'LongPassword123',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('PreviousPassword!123', $user->fresh()->password));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword!123',
            'password_confirmation' => 'NewSecurePassword!123',
        ])->assertRedirect('/user_login');

        $this->assertGuest();
        $this->assertTrue(Hash::check('NewSecurePassword!123', $user->fresh()->password));
    }
}
