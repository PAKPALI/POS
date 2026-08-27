<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityRemediationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_logo_rejects_an_executable_upload(): void
    {
        $user = User::factory()->create(['status' => 1, 'user_type' => 3]);
        $file = UploadedFile::fake()->createWithContent('backdoor.php', '<?php echo "unsafe";');

        $this->actingAs($user)->postJson(route('companies.store'), [
            'name' => 'Secure Company',
            'email' => 'secure@test.local',
            'adress' => 'Test',
            'number1' => '90000000',
            'logo' => $file,
        ])->assertOk()->assertJson(['status' => false]);

        $this->assertDatabaseMissing('company_settings', ['name' => 'Secure Company']);
    }

    public function test_sms_callback_requires_a_valid_signature(): void
    {
        config(['services.kprimesms.callback_secret' => 'test-callback-secret']);
        $payload = json_encode(['status' => 'delivered', 'response_token' => 'provider-reference']);

        $this->call('POST', '/api/sms/callback', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_KPRIME_SIGNATURE' => 'invalid',
        ], $payload)->assertUnauthorized();

        $signature = hash_hmac('sha256', $payload, 'test-callback-secret');
        $this->call('POST', '/api/sms/callback', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_KPRIME_SIGNATURE' => $signature,
        ], $payload)->assertOk()->assertJson(['status' => 'accepted']);
    }

    public function test_login_is_rate_limited_and_does_not_enumerate_accounts(): void
    {
        RateLimiter::clear('known@test.local|127.0.0.1');
        RateLimiter::clear('missing@test.local|127.0.0.1');
        User::factory()->create(['email' => 'known@test.local', 'password' => 'CorrectPassword123', 'user_type' => 3, 'status' => 1]);

        $known = $this->postJson('/login', ['email' => 'known@test.local', 'password' => 'wrong']);
        $missing = $this->postJson('/login', ['email' => 'missing@test.local', 'password' => 'wrong']);
        $known->assertOk();
        $missing->assertOk();
        $this->assertSame($known->json('msg'), $missing->json('msg'));

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->postJson('/login', ['email' => 'known@test.local', 'password' => 'wrong']);
        }

        $this->postJson('/login', ['email' => 'known@test.local', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    public function test_login_does_not_depend_on_the_legacy_global_user_type(): void
    {
        RateLimiter::clear('role-based@test.local|127.0.0.1');
        User::factory()->create([
            'email' => 'role-based@test.local',
            'password' => 'CorrectPassword123',
            'user_type' => 99,
            'status' => 1,
        ]);

        $this->postJson('/login', [
            'email' => '  ROLE-BASED@TEST.LOCAL  ',
            'password' => 'CorrectPassword123',
        ])->assertOk()->assertJson([
            'status' => true,
            'redirect_to' => route('companies.select'),
        ]);

        $this->assertAuthenticated();
    }

    public function test_disabled_account_is_rejected_regardless_of_legacy_user_type(): void
    {
        RateLimiter::clear('disabled-owner@test.local|127.0.0.1');
        User::factory()->create([
            'email' => 'disabled-owner@test.local',
            'password' => 'CorrectPassword123',
            'user_type' => 2,
            'status' => 0,
        ]);

        $this->postJson('/login', [
            'email' => 'disabled-owner@test.local',
            'password' => 'CorrectPassword123',
        ])->assertOk()->assertJson([
            'status' => false,
            'msg' => 'Votre compte est désactivé. Contactez un administrateur.',
        ]);

        $this->assertGuest();
    }
}
