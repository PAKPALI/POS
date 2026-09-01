<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\EcommerceManager;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceManagerTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_operations_are_restricted_to_the_active_company(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $memberA = User::factory()->create([
            'name' => 'Membre Alpha', 'email' => 'member-alpha@test.local',
            'user_type' => 3, 'status' => 1,
        ]);
        $memberB = User::factory()->create([
            'name' => 'Membre Beta', 'email' => 'member-beta@test.local',
            'user_type' => 3, 'status' => 1,
        ]);
        $companyA = Company::create([
            'name' => 'Compagnie Alpha', 'email' => 'alpha@test.local', 'number1' => '100',
        ]);
        $companyB = Company::create([
            'name' => 'Compagnie Beta', 'email' => 'beta@test.local', 'number1' => '200',
        ]);
        $ownerRoleA = $this->role($companyA, 'owner-a', 'owner');
        $ownerRoleB = $this->role($companyB, 'owner-b', 'owner');
        $memberRoleA = $this->role($companyA, 'staff-a', 'staff-a');
        $memberRoleB = $this->role($companyB, 'staff-b', 'staff-b');

        $this->membership($owner, $companyA, $ownerRoleA);
        $this->membership($owner, $companyB, $ownerRoleB);
        $this->membership($memberA, $companyA, $memberRoleA);
        $this->membership($memberB, $companyB, $memberRoleB);

        $foreignManager = EcommerceManager::create([
            'company_id' => $companyB->id,
            'user_id' => $memberB->id,
        ]);

        $this->actingAs($owner)->withSession(['active_company_id' => $companyA->id]);

        $this->get(route('ecommerce.settings'))
            ->assertOk();

        $this->getJson(route('ecommerce.users.search', ['q' => 'member']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('member-alpha@test.local')
            ->assertDontSee('member-beta@test.local');

        $this->postJson(route('ecommerce.managers.add'), [
            'user_id' => $memberA->id,
            'company_id' => $companyB->id,
        ])->assertOk()->assertJson(['status' => true]);

        $localManager = EcommerceManager::where('company_id', $companyA->id)
            ->where('user_id', $memberA->id)->firstOrFail();
        $this->assertDatabaseMissing('ecommerce_managers', [
            'company_id' => $companyB->id,
            'user_id' => $memberA->id,
        ]);

        $this->postJson(route('ecommerce.managers.add'), [
            'user_id' => $memberB->id,
            'company_id' => $companyA->id,
        ])->assertUnprocessable()->assertJson(['status' => false]);

        $this->getJson(route('ecommerce.managers.list'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('member-alpha@test.local')
            ->assertDontSee('member-beta@test.local');

        $this->deleteJson(route('ecommerce.managers.remove', $foreignManager->id))->assertNotFound();
        $this->assertDatabaseHas('ecommerce_managers', ['id' => $foreignManager->id]);

        $this->deleteJson(route('ecommerce.managers.remove', $localManager->id))
            ->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseMissing('ecommerce_managers', ['id' => $localManager->id]);
    }

    private function role(Company $company, string $name, string $key): Role
    {
        return Role::create([
            'company_id' => $company->id,
            'name' => $name,
            'key' => $key,
            'is_system' => $key === 'owner',
        ]);
    }

    private function membership(User $user, Company $company, Role $role): CompanyUser
    {
        return CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }
}
