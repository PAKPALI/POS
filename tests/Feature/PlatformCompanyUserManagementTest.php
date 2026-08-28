<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\PlatformAdmin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformCompanyUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function platformAdmin(): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'super@example.test',
            'password' => Hash::make('SecurePassword!123'),
            'role' => 'super_admin',
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    private function company(string $name, string $email): Company
    {
        return Company::create([
            'name' => $name,
            'email' => $email,
            'number1' => '90000000',
            'status' => 'active',
        ]);
    }

    public function test_platform_company_list_is_global_searchable_and_paginated(): void
    {
        $admin = $this->platformAdmin();
        $matrix = $this->company('Matrix SARL', 'matrix@example.test');
        $this->company('Fenix SARL', 'fenix@example.test');

        $this->actingAs($admin, 'platform')
            ->get(route('platform.companies.index', ['q' => 'Matrix']))
            ->assertOk()
            ->assertSee('Matrix SARL')
            ->assertDontSee('Fenix SARL')
            ->assertSee(route('platform.companies.show', $matrix));
    }

    public function test_platform_user_list_and_detail_show_all_company_memberships(): void
    {
        $admin = $this->platformAdmin();
        $user = User::factory()->create(['name' => 'Utilisateur partagé', 'email' => 'shared@example.test']);
        $matrix = $this->company('Matrix', 'matrix@example.test');
        $fenix = $this->company('Fenix', 'fenix@example.test');
        $matrixRole = Role::create(['company_id' => $matrix->id, 'name' => 'Manager', 'key' => 'manager']);
        $fenixRole = Role::create(['company_id' => $fenix->id, 'name' => 'Vendeur', 'key' => 'seller']);
        CompanyUser::create(['company_id' => $matrix->id, 'user_id' => $user->id, 'role_id' => $matrixRole->id, 'status' => 'active']);
        CompanyUser::create(['company_id' => $fenix->id, 'user_id' => $user->id, 'role_id' => $fenixRole->id, 'status' => 'active']);

        $this->actingAs($admin, 'platform')
            ->get(route('platform.users.index', ['q' => 'shared@example.test']))
            ->assertOk()
            ->assertSee('Utilisateur partagé')
            ->assertSee('Matrix')
            ->assertSee('Fenix')
            ->assertSee('2 active(s) sur 2')
            ->assertSee(route('platform.companies.show', $matrix))
            ->assertSee(route('platform.companies.show', $fenix));

        $this->actingAs($admin, 'platform')
            ->get(route('platform.users.show', $user))
            ->assertOk()
            ->assertSee('Matrix')
            ->assertSee('Fenix')
            ->assertSee('Manager')
            ->assertSee('Vendeur');
    }

    public function test_company_suspension_and_reactivation_are_audited(): void
    {
        $admin = $this->platformAdmin();
        $company = $this->company('Entreprise sensible', 'sensitive@example.test');

        $this->actingAs($admin, 'platform')
            ->patch(route('platform.companies.status', $company), [
                'status' => 'suspended',
                'reason' => 'Contrôle administratif en cours',
            ])->assertRedirect();

        $this->assertSame('suspended', $company->fresh()->status);
        $this->assertDatabaseHas('platform_audit_logs', [
            'platform_admin_id' => $admin->id,
            'action' => 'company.suspended',
            'target_id' => (string) $company->id,
            'reason' => 'Contrôle administratif en cours',
        ]);

        $this->actingAs($admin, 'platform')
            ->patch(route('platform.companies.status', $company), [
                'status' => 'active',
                'reason' => 'Contrôle terminé avec succès',
            ])->assertRedirect();

        $this->assertSame('active', $company->fresh()->status);
        $this->assertDatabaseHas('platform_audit_logs', [
            'action' => 'company.reactivated',
            'target_id' => (string) $company->id,
        ]);
    }

    public function test_status_change_requires_a_meaningful_reason(): void
    {
        $admin = $this->platformAdmin();
        $company = $this->company('Entreprise', 'company@example.test');

        $this->actingAs($admin, 'platform')
            ->patch(route('platform.companies.status', $company), [
                'status' => 'suspended',
                'reason' => 'non',
            ])->assertSessionHasErrors('reason');

        $this->assertSame('active', $company->fresh()->status);
    }

    public function test_pos_user_cannot_access_global_company_or_user_pages(): void
    {
        $user = User::factory()->create();
        $company = $this->company('Privée', 'private@example.test');

        $this->actingAs($user)->get(route('platform.companies.index'))->assertRedirect(route('platform.login'));
        $this->actingAs($user)->get(route('platform.companies.show', $company))->assertRedirect(route('platform.login'));
        $this->actingAs($user)->get(route('platform.users.index'))->assertRedirect(route('platform.login'));
    }
}
