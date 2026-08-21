<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class ExistingUserMembershipTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_existing_user_can_join_another_company_with_a_different_role(): void
    {
        $ownerB = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $companyB = $this->activateCompanyFor($ownerB, 'company-b');
        $managerB = Role::create([
            'company_id' => $companyB->id, 'name' => 'Manager B', 'key' => 'manager-b', 'is_system' => false,
        ]);

        $userX = User::factory()->create(['email' => 'user-x@test.local', 'user_type' => 3, 'status' => 1]);
        $companyA = Company::create(['name' => 'Compagnie A', 'email' => 'a@test.local', 'number1' => '1']);
        $cashierA = Role::create([
            'company_id' => $companyA->id, 'name' => 'Caissier A', 'key' => 'cashier-a', 'is_system' => false,
        ]);
        CompanyUser::create([
            'company_id' => $companyA->id, 'user_id' => $userX->id, 'role_id' => $cashierA->id,
            'status' => 'active', 'joined_at' => now(),
        ]);

        $this->actingAs($ownerB)->postJson(route('user.attach-existing'), [
            'email' => $userX->email,
            'role_id' => $managerB->id,
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertDatabaseHas('company_user', [
            'company_id' => $companyA->id, 'user_id' => $userX->id, 'role_id' => $cashierA->id,
        ]);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $companyB->id, 'user_id' => $userX->id, 'role_id' => $managerB->id,
        ]);

        $this->actingAs($userX)->withSession(['active_company_id' => $companyA->id])
            ->post(route('companies.switch', $companyB->id))
            ->assertRedirect(route('profil'))
            ->assertSessionHas('active_company_id', $companyB->id);
    }

    public function test_role_from_another_company_cannot_be_assigned(): void
    {
        $ownerB = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $this->activateCompanyFor($ownerB, 'company-b-invalid-role');
        $userX = User::factory()->create(['email' => 'other-user@test.local', 'user_type' => 3, 'status' => 1]);
        $otherCompany = Company::create(['name' => 'Autre compagnie', 'email' => 'other@test.local', 'number1' => '2']);
        $foreignRole = Role::create([
            'company_id' => $otherCompany->id, 'name' => 'Rôle externe', 'key' => 'foreign', 'is_system' => false,
        ]);

        $this->actingAs($ownerB)->postJson(route('user.attach-existing'), [
            'email' => $userX->email,
            'role_id' => $foreignRole->id,
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('company_user', [
            'company_id' => $this->company->id, 'user_id' => $userX->id,
        ]);
    }

    public function test_user_can_be_integrated_from_the_action_list_into_another_managed_company(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $companyA = $this->activateCompanyFor($owner, 'source-company');
        $userX = User::factory()->create(['user_type' => 3, 'status' => 1]);
        $sourceRole = Role::create([
            'company_id' => $companyA->id, 'name' => 'Caissier A', 'key' => 'cashier-a', 'is_system' => false,
        ]);
        CompanyUser::create([
            'company_id' => $companyA->id, 'user_id' => $userX->id, 'role_id' => $sourceRole->id,
            'status' => 'active', 'joined_at' => now(),
        ]);

        $companyB = Company::create(['name' => 'Compagnie B', 'email' => 'b@test.local', 'number1' => '2']);
        $ownerRoleB = Role::create([
            'company_id' => $companyB->id, 'name' => 'Propriétaire', 'key' => 'owner', 'is_system' => true,
        ]);
        $managerRoleB = Role::create([
            'company_id' => $companyB->id, 'name' => 'Manager B', 'key' => 'manager-b', 'is_system' => false,
        ]);
        CompanyUser::create([
            'company_id' => $companyB->id, 'user_id' => $owner->id, 'role_id' => $ownerRoleB->id,
            'status' => 'active', 'joined_at' => now(),
        ]);

        $this->actingAs($owner)->withSession(['active_company_id' => $companyA->id])
            ->getJson(route('user.transfer-options', $userX))
            ->assertOk()
            ->assertJsonPath('companies.0.id', $companyB->id)
            ->assertJsonPath('companies.0.roles.0.id', $managerRoleB->id);

        $this->actingAs($owner)->withSession(['active_company_id' => $companyA->id])
            ->postJson(route('user.transfer-company', $userX), [
                'company_id' => $companyB->id,
                'role_id' => $managerRoleB->id,
            ])->assertOk()->assertJson(['status' => true]);

        $this->assertDatabaseHas('company_user', [
            'company_id' => $companyA->id, 'user_id' => $userX->id, 'role_id' => $sourceRole->id,
        ]);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $companyB->id, 'user_id' => $userX->id, 'role_id' => $managerRoleB->id,
            'status' => 'active',
        ]);
    }
}
