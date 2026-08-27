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
            ->assertSee(asset('hub/assets/css/app.min.css'));

        $this->get(route('user_login'))
            ->assertOk()
            ->assertSeeText("Vous n'avez pas encore de compte ?", false)
            ->assertSee(route('register'))
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSee(asset('hub/assets/css/app.min.css'));

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="company_name"', false)
            ->assertDontSee('name="default_tax"', false)
            ->assertSee('name="country_code"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSee(asset('hub/assets/css/app.min.css'))
            ->assertSee(route('admin_register'))
            ->assertSee(route('user_login'));

        $this->get(route('password.request'))->assertOk()
            ->assertSee(route('password.email'))
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSeeText('MOT DE PASSE OUBLIÉ');
        $this->get(route('password.reset', ['token' => 'test-token']))->assertOk()
            ->assertSee(asset('hub/assets/css/app.min.css'))
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

    public function test_pwa_starts_on_the_authenticated_home_route(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('/home', $manifest['start_url']);
        $this->assertStringContainsString('pro-seller-pwa-v4', file_get_contents(public_path('sw.js')));
        $this->get('/home')->assertRedirect(route('user_login'));
    }
}
