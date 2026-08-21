<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\AuthorizedLandingPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_owner_can_create_a_company_role_with_permissions(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $permission = Permission::firstOrCreate(['key' => 'inventory.manage'], [
            'module' => 'inventory', 'description' => 'Inventaire',
        ]);

        $this->actingAs($owner)->post(route('roles.store'), [
            'name' => 'Magasinier',
            'permissions' => [$permission->id],
        ])->assertRedirect(route('roles.index'));

        $role = Role::where('company_id', $company->id)->where('name', 'Magasinier')->firstOrFail();
        $this->assertTrue($role->permissions()->whereKey($permission->id)->exists());
    }

    public function test_role_from_another_company_cannot_be_modified(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $this->activateCompanyFor($owner);
        $otherCompany = Company::create(['name' => 'Autre', 'email' => 'other@test.local', 'number1' => '1']);
        $foreignRole = Role::create([
            'company_id' => $otherCompany->id, 'name' => 'Externe', 'key' => 'external', 'is_system' => false,
        ]);

        $this->actingAs($owner)->put(route('roles.update', $foreignRole), [
            'name' => 'Intrus', 'permissions' => [],
        ])->assertNotFound();
    }

    public function test_role_is_assigned_only_in_the_active_company_membership(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $employee = User::factory()->create(['user_type' => 3, 'status' => 1]);
        $cashier = Role::create([
            'company_id' => $company->id, 'name' => 'Caissier', 'key' => 'cashier', 'is_system' => true,
        ]);
        $manager = Role::create([
            'company_id' => $company->id, 'name' => 'Manager', 'key' => 'manager', 'is_system' => false,
        ]);
        $membership = CompanyUser::create([
            'company_id' => $company->id, 'user_id' => $employee->id, 'role_id' => $cashier->id,
            'status' => 'active', 'joined_at' => now(),
        ]);

        $this->actingAs($owner)->putJson(route('user.update', $employee), [
            'name' => $employee->name, 'phone' => $employee->phone, 'role_id' => $manager->id,
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertSame($manager->id, $membership->fresh()->role_id);
    }

    public function test_client_only_role_cannot_see_or_access_other_modules(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $employee = User::factory()->create(['user_type' => 3, 'status' => 1]);
        $clientPermission = Permission::firstOrCreate(['key' => 'clients.manage'], [
            'module' => 'clients', 'description' => 'Clients',
        ]);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Gestionnaire clients',
            'key' => 'client-manager',
            'is_system' => false,
        ]);
        $role->permissions()->attach($clientPermission->id);
        $membership = CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $employee->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->assertSame(
            route('client.index'),
            app(AuthorizedLandingPage::class)->forMembership($membership)
        );

        $this->actingAs($employee)->withSession(['active_company_id' => $company->id]);

        $this->get(route('client.index'))
            ->assertOk()
            ->assertSee('Clients')
            ->assertDontSee('Point de ventes')
            ->assertDontSee('Tableau de bord')
            ->assertDontSee('Inventaires');
        $this->get(route('dashboard'))
            ->assertForbidden()
            ->assertSee('Cette fonctionnalité n’est pas disponible pour votre rôle')
            ->assertSee('Votre rôle ne vous donne pas accès au tableau de bord dans cette entreprise.')
            ->assertSee('Clients')
            ->assertDontSee('dashboard.view');
        $this->get(route('sale.index'))->assertForbidden();
        $this->get(route('inventory.index'))->assertForbidden();
        $this->post(route('statistics.topProducts'))->assertForbidden();
    }

    public function test_inventory_permission_is_independent_from_catalog_permission(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $employee = User::factory()->create(['user_type' => 3, 'status' => 1]);
        $inventoryPermission = Permission::firstOrCreate(['key' => 'inventory.manage'], [
            'module' => 'inventory', 'description' => 'Inventaire',
        ]);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Gestionnaire inventaire',
            'key' => 'inventory-manager',
            'is_system' => false,
        ]);
        $role->permissions()->attach($inventoryPermission->id);
        CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $employee->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($employee)->withSession(['active_company_id' => $company->id]);

        $this->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Inventaires')
            ->assertDontSee('Liste Produits');
        $this->get(route('product.index'))->assertForbidden();
        $this->get(route('supplier.index'))->assertForbidden();
    }

    public function test_sales_responses_do_not_expose_profit_without_financial_permission(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $employee = User::factory()->create(['user_type' => 3, 'status' => 1]);
        $salesPermission = Permission::firstOrCreate(['key' => 'sales.manage'], [
            'module' => 'sales', 'description' => 'Ventes',
        ]);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Caissier sans bénéfices',
            'key' => 'sales-without-profit',
            'is_system' => false,
        ]);
        $role->permissions()->attach($salesPermission->id);
        CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $employee->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
        Sale::create([
            'company_id' => $company->id,
            'code' => 654321,
            'received_amount' => 15000,
            'total_amount' => 12000,
            'remaining_amount' => 3000,
            'total_profit' => 987654,
            'cashier' => $employee->name,
            'discount' => 0,
        ]);

        $this->actingAs($employee)->withSession(['active_company_id' => $company->id]);

        $this->getJson(route('sale.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonMissing(['total_profit' => 987654])
            ->assertDontSee('987654');
        $this->getJson(route('history'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonMissing(['total_profit' => 987654])
            ->assertDontSee('987654');

        $this->actingAs($owner)->withSession(['active_company_id' => $company->id]);
        $this->getJson(route('sale.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('987654');
    }
}
