<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_uses_the_new_saas_shell(): void
    {
        $this->authenticatedUsers();

        $this->get(route('profil'))
            ->assertOk()
            ->assertSee('Paramètres du compte')
            ->assertSee('saas-shell', false)
            ->assertSee('navbarAppearanceModal', false)
            ->assertSee('Personnaliser l’interface')
            ->assertDontSee('app-theme-panel', false);
    }

    public function test_forged_user_id_cannot_change_another_users_email(): void
    {
        [$user, $victim, $company] = $this->authenticatedUsers();

        $this->postJson(route('profile.email.update'), [
            'user_id' => $victim->id,
            'NE' => 'nouvelle-adresse@test.local',
            'CE' => 'nouvelle-adresse@test.local',
            'current_password' => 'CurrentPassword123',
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertSame('nouvelle-adresse@test.local', $user->fresh()->email);
        $this->assertSame('victime@test.local', $victim->fresh()->email);
        $this->assertSame($company->id, session('active_company_id'));
    }

    public function test_email_change_requires_current_password_and_an_unused_address(): void
    {
        [$user, $victim] = $this->authenticatedUsers();

        $this->postJson(route('profile.email.update'), [
            'NE' => 'tentative@test.local',
            'CE' => 'tentative@test.local',
            'current_password' => 'MauvaisMotDePasse123',
        ])->assertUnprocessable()->assertJson(['status' => false]);

        $this->postJson(route('profile.email.update'), [
            'NE' => $victim->email,
            'CE' => $victim->email,
            'current_password' => 'CurrentPassword123',
        ])->assertUnprocessable()->assertJson(['status' => false]);

        $this->assertSame('utilisateur@test.local', $user->fresh()->email);
    }

    public function test_forged_user_id_cannot_change_another_users_password(): void
    {
        [$user, $victim] = $this->authenticatedUsers();
        $victimPassword = $victim->password;

        $this->postJson(route('profile.password.update'), [
            'user_id' => $victim->id,
            'AM' => 'CurrentPassword123',
            'NM' => 'NewPassword456',
            'CM' => 'NewPassword456',
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertTrue(Hash::check('NewPassword456', $user->fresh()->password));
        $this->assertSame($victimPassword, $victim->fresh()->password);
    }

    public function test_password_change_rejects_wrong_current_password_and_confirmation(): void
    {
        [$user] = $this->authenticatedUsers();
        $originalPassword = $user->password;

        $this->postJson(route('profile.password.update'), [
            'AM' => 'MauvaisMotDePasse123',
            'NM' => 'NewPassword456',
            'CM' => 'NewPassword456',
        ])->assertUnprocessable()->assertJson(['status' => false]);

        $this->postJson(route('profile.password.update'), [
            'AM' => 'CurrentPassword123',
            'NM' => 'NewPassword456',
            'CM' => 'DifferentPassword789',
        ])->assertUnprocessable()->assertJson(['status' => false]);

        $this->assertSame($originalPassword, $user->fresh()->password);
    }

    public function test_user_can_save_personal_appearance_preferences(): void
    {
        [$user, $victim] = $this->authenticatedUsers();

        $this->putJson(route('profile.appearance.update'), [
            'appearance_mode' => 'light',
            'accent_color' => '#7c5cfc',
            'user_id' => $victim->id,
        ])->assertOk()->assertJson([
            'status' => true,
            'appearance' => ['mode' => 'light', 'accent' => '#7C5CFC'],
        ]);

        $this->assertSame('light', $user->fresh()->appearance_mode);
        $this->assertSame('#7C5CFC', $user->fresh()->accent_color);
        $this->assertSame('system', $victim->fresh()->appearance_mode);
        $this->assertSame('#FF9F43', $victim->fresh()->accent_color);
    }

    public function test_appearance_preferences_reject_invalid_values(): void
    {
        [$user] = $this->authenticatedUsers();

        $this->putJson(route('profile.appearance.update'), [
            'appearance_mode' => 'neon',
            'accent_color' => 'orange',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['appearance_mode', 'accent_color']);

        $this->assertSame('system', $user->fresh()->appearance_mode);
        $this->assertSame('#FF9F43', $user->fresh()->accent_color);
    }

    private function authenticatedUsers(): array
    {
        $user = User::factory()->create([
            'email' => 'utilisateur@test.local',
            'password' => Hash::make('CurrentPassword123'),
            'status' => 1,
            'user_type' => 3,
        ]);
        $victim = User::factory()->create([
            'email' => 'victime@test.local',
            'password' => Hash::make('VictimPassword123'),
            'status' => 1,
            'user_type' => 3,
        ]);
        $company = Company::create([
            'name' => 'Entreprise Profil',
            'email' => 'entreprise-profil@test.local',
            'number1' => '100',
        ]);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Membre',
            'key' => 'member',
            'is_system' => false,
        ]);
        CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($user)->withSession([
            'active_company_id' => $company->id,
            'active_company_name' => $company->name,
        ]);

        return [$user, $victim, $company];
    }
}
