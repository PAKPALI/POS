<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
}
