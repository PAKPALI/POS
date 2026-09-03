<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_link_to_each_other(): void
    {
        $this->get(route('signup'))
            ->assertOk()
            ->assertSeeText('Vous avez déjà un compte ?')
            ->assertSee(route('user_login'))
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSee(asset('hub/assets/css/public-auth.css'));

        $this->get(route('user_login'))
            ->assertOk()
            ->assertSeeText('Vous débutez ?', false)
            ->assertSee(route('register'))
            ->assertDontSeeText('Administration SaaS')
            ->assertDontSee('auth-platform-link', false)
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSee(asset('hub/assets/css/public-auth.css'));

        $this->get(route('platform.entry'))
            ->assertRedirect('/platform/login');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="company_name"', false)
            ->assertDontSee('name="default_tax"', false)
            ->assertSee('name="country_code"', false)
            ->assertSee('name="appearance_mode"', false)
            ->assertSee('name="accent_color"', false)
            ->assertSee('data-public-accent', false)
            ->assertSee('publicCustomAccent', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSee(asset('hub/assets/css/public-auth.css'))
            ->assertSee(route('admin_register'))
            ->assertSee(route('user_login'));

        $this->get(route('password.request'))->assertOk()
            ->assertSee(route('password.email'))
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSeeText('MOT DE PASSE OUBLIÉ');
        $this->get(route('password.reset', ['token' => 'test-token']))->assertOk()
            ->assertSee(asset('hub/assets/css/public-auth.css'))
            ->assertSeeText('NOUVEAU MOT DE PASSE');
        $this->get(route('user_login'))->assertSee(route('password.request'));

        $this->get(route('login'))
            ->assertRedirect(route('user_login'));

        $user = User::create([
            'name' => 'Utilisateur connecté',
            'email' => 'authenticated-register@test.local',
            'password' => 'password',
            'user_type' => '2',
            'status' => '1',
        ]);

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('companies.select'));

        $this->get(route('home'))
            ->assertRedirect(route('companies.select'));
    }

    public function test_pwa_starts_on_the_authentication_route(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('/user_login', $manifest['start_url']);
        $this->assertStringContainsString('pro-seller-pwa-v7', file_get_contents(public_path('sw.js')));
        $this->assertStringContainsString('beforeinstallprompt', file_get_contents(public_path('pwa-register.js')));
        $this->assertStringContainsString('android-pwa-install-prompt', file_get_contents(public_path('pwa-register.js')));
        $this->assertStringContainsString('mobile-pwa-install-fallback', file_get_contents(public_path('pwa-register.js')));
        $this->get('/user_login')
            ->assertOk()
            ->assertSee('Bon retour.');
    }
}
