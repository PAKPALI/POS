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
            ->assertSee('name="default_tax"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee(asset('hub/assets/css/vendor.min.css'))
            ->assertSee(asset('hub/assets/css/app.min.css'))
            ->assertSee(route('admin_register'))
            ->assertSee(route('user_login'));

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
}
