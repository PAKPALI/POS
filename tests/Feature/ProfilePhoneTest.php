<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class ProfilePhoneTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_authenticated_user_can_add_a_phone_number_from_profile(): void
    {
        $user = User::factory()->create(['phone' => null, 'country_code' => 'TG']);
        $company = $this->activateCompanyFor($user, 'profile-phone');

        $this->actingAs($user)->withSession(['active_company_id' => $company->id])
            ->putJson(route('profile.phone.update'), ['phone' => '90 85 94 88', 'country_code' => 'TG'])
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone' => '90859488', 'country_code' => 'TG']);
    }

    public function test_profile_phone_number_rejects_invalid_country_or_number(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $company = $this->activateCompanyFor($user, 'profile-phone-validation');

        $this->actingAs($user)->withSession(['active_company_id' => $company->id])
            ->putJson(route('profile.phone.update'), ['phone' => '123', 'country_code' => 'XX'])
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_profile_phone_rejects_a_togolese_number_with_its_country_calling_code(): void
    {
        $user = User::factory()->create(['phone' => null, 'country_code' => 'TG']);
        $company = $this->activateCompanyFor($user, 'profile-phone-country-rule');

        $this->actingAs($user)->withSession(['active_company_id' => $company->id])
            ->putJson(route('profile.phone.update'), ['phone' => '22890859488', 'country_code' => 'TG'])
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('msg', 'Le numéro local pour Togo doit contenir 8 chiffres. Ne saisissez pas l’indicatif +228.');
    }
}
