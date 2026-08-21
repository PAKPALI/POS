<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Inventory;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PartnerTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_and_suppliers_from_another_active_company_are_not_accessible(): void
    {
        [$user, $companyA, $membershipA, $companyB, $membershipB] = $this->twoCompanyOwner();
        [$clientA, $supplierA] = $this->partnersFor($companyA, $membershipA, 'A');
        [$clientB, $supplierB] = $this->partnersFor($companyB, $membershipB, 'B');

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));
        $this->actingAs($user)->withSession(['active_company_id' => $companyA->id]);

        $this->get(route('client.show', $clientB->id))->assertNotFound();
        $this->get(route('client.edit', $clientB->id))->assertNotFound();
        $this->putJson(route('client.update', $clientB->id), ['name' => 'Intrusion client'])
            ->assertNotFound();
        $this->deleteJson(route('client.destroy', $clientB->id))->assertNotFound();

        $this->get(route('supplier.show', $supplierB->id))->assertNotFound();
        $this->get(route('supplier.edit', $supplierB->id))->assertNotFound();
        $this->putJson(route('supplier.update', $supplierB->id), ['name' => 'Intrusion fournisseur'])
            ->assertNotFound();
        $this->deleteJson(route('supplier.destroy', $supplierB->id))->assertNotFound();

        $this->assertDatabaseHas('clients', [
            'id' => $clientA->id,
            'company_id' => $companyA->id,
            'status' => '1',
        ]);
        $this->assertDatabaseHas('clients', [
            'id' => $clientB->id,
            'company_id' => $companyB->id,
            'name' => 'Client B',
            'status' => '1',
        ]);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierA->id,
            'company_id' => $companyA->id,
            'status' => '1',
        ]);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierB->id,
            'company_id' => $companyB->id,
            'name' => 'Fournisseur B',
            'status' => '1',
        ]);
    }

    public function test_partner_policies_enforce_active_company_and_dedicated_permissions(): void
    {
        [$user, $companyA, $membershipA, $companyB, $membershipB] = $this->twoCompanyOwner();
        [$clientA, $supplierA] = $this->partnersFor($companyA, $membershipA, 'A');
        [$clientB, $supplierB] = $this->partnersFor($companyB, $membershipB, 'B');

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));

        $this->assertTrue(Gate::forUser($user)->allows('update', $clientA));
        $this->assertTrue(Gate::forUser($user)->allows('update', $supplierA));
        $this->assertFalse(Gate::forUser($user)->allows('update', $clientB));
        $this->assertFalse(Gate::forUser($user)->allows('update', $supplierB));

        $viewer = User::create([
            'name' => 'Lecteur partenaires',
            'email' => 'partner-viewer@test.local',
            'password' => 'password',
            'user_type' => '2',
            'status' => '1',
        ]);
        $viewerMembership = $this->membership($viewer, $companyA, 'viewer');
        app(CompanyContext::class)->set($companyA, $viewerMembership->load('role.permissions'));

        $this->assertFalse(Gate::forUser($viewer)->allows('viewAny', Client::class));
        $this->assertFalse(Gate::forUser($viewer)->allows('viewAny', Supplier::class));

        $clientsPermission = Permission::firstOrCreate(
            ['key' => 'clients.manage'],
            ['module' => 'clients', 'description' => 'Clients']
        );
        $viewerMembership->role->permissions()->attach($clientsPermission->id);
        app(CompanyContext::class)->set(
            $companyA,
            $viewerMembership->load('role.permissions')
        );

        $this->assertTrue(Gate::forUser($viewer)->allows('viewAny', Client::class));
        $this->assertFalse(Gate::forUser($viewer)->allows('viewAny', Supplier::class));

        $this->actingAs($viewer)
            ->withSession(['active_company_id' => $companyA->id])
            ->get(route('client.index'))
            ->assertOk();
        $this->get(route('supplier.index'))->assertForbidden();
    }

    public function test_active_company_owner_can_create_clients_and_suppliers(): void
    {
        [$user, $companyA, $membershipA] = $this->twoCompanyOwner();
        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));
        $this->actingAs($user)->withSession(['active_company_id' => $companyA->id]);

        $this->get(route('client.index'))->assertOk();
        $this->get(route('supplier.index'))->assertOk();

        $this->postJson(route('client.store'), ['name' => 'Client local'])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->postJson(route('supplier.store'), [
            'name' => 'Fournisseur local',
            'contact' => 'Contact test',
            'phone' => '90000000',
            'whatsapp' => '90000000',
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertDatabaseHas('clients', [
            'company_id' => $companyA->id,
            'name' => 'Client local',
        ]);
        $this->assertDatabaseHas('suppliers', [
            'company_id' => $companyA->id,
            'name' => 'Fournisseur local',
        ]);
    }

    public function test_sale_and_inventory_reject_partners_from_another_company(): void
    {
        [$user, $companyA, $membershipA, $companyB, $membershipB] = $this->twoCompanyOwner();
        [, $supplierA] = $this->partnersFor($companyA, $membershipA, 'A');
        [$clientB, $supplierB] = $this->partnersFor($companyB, $membershipB, 'B');
        $productA = $this->productFor($companyA, $membershipA);

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));
        $archivedClientA = Client::create([
            'name' => 'Client archivé A',
            'created_by' => $user->id,
            'status' => '0',
        ]);
        $archivedSupplierA = Supplier::create([
            'name' => 'Fournisseur archivé A',
            'created_by' => $user->id,
            'status' => '0',
        ]);
        $this->actingAs($user)->withSession(['active_company_id' => $companyA->id]);

        $this->postJson(route('sale.store'), [
            'products' => [[
                'product_id' => $productA->id,
                'quantity' => 1,
                'unit_price' => $productA->price,
                'total_price' => $productA->price,
            ]],
            'received_amount' => $productA->price,
            'total_amount' => $productA->price,
            'discount' => 0,
            'client_id' => $clientB->id,
        ])->assertOk()->assertJson([
            'status' => false,
            'msg' => "Le client sélectionné n'est pas disponible dans la compagnie active.",
        ]);

        $this->postJson(route('sale.store'), [
            'products' => [[
                'product_id' => $productA->id,
                'quantity' => 1,
                'unit_price' => $productA->price,
                'total_price' => $productA->price,
            ]],
            'received_amount' => $productA->price,
            'total_amount' => $productA->price,
            'discount' => 0,
            'client_id' => $archivedClientA->id,
        ])->assertOk()->assertJson(['status' => false]);

        $this->postJson(route('inventory.store'), [
            'product_id' => $productA->id,
            'supplier_id' => $supplierB->id,
            'qte_added' => 5,
        ])->assertOk()->assertJson([
            'status' => false,
            'msg' => "Le fournisseur sélectionné n'est pas disponible dans la compagnie active!",
        ]);

        $this->postJson(route('inventory.store'), [
            'product_id' => $productA->id,
            'supplier_id' => $archivedSupplierA->id,
            'qte_added' => 5,
        ])->assertOk()->assertJson(['status' => false]);

        $this->get(route('inventory.index', ['supplier_id' => $supplierB->id]))
            ->assertNotFound();
        $this->get(route('inventory.export.pdf', ['supplier_id' => $supplierB->id]))
            ->assertNotFound();

        $this->assertDatabaseMissing('sales', ['client_id' => $clientB->id]);
        $this->assertDatabaseMissing('inventories', ['supplier_id' => $supplierB->id]);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierA->id,
            'company_id' => $companyA->id,
        ]);
        $this->assertSame(10, $productA->fresh()->qte);
        $this->assertSame(0, Sale::withoutCompanyScope()->count());
        $this->assertSame(0, Inventory::withoutCompanyScope()->count());
    }

    private function twoCompanyOwner(): array
    {
        $user = User::create([
            'name' => 'Propriétaire partenaires',
            'email' => 'partner-security@test.local',
            'password' => 'password',
            'user_type' => '2',
            'status' => '1',
        ]);
        $companyA = $this->company('Partenaires A');
        $companyB = $this->company('Partenaires B');

        return [
            $user,
            $companyA,
            $this->membership($user, $companyA),
            $companyB,
            $this->membership($user, $companyB),
        ];
    }

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'email' => str($name)->slug() . '@test.local',
            'number1' => '000000000',
        ]);
    }

    private function membership(User $user, Company $company, string $roleKey = 'owner'): CompanyUser
    {
        $role = Role::create([
            'company_id' => $company->id,
            'name' => ucfirst($roleKey),
            'key' => $roleKey,
            'is_system' => $roleKey === 'owner',
        ]);

        return CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    private function partnersFor(Company $company, CompanyUser $membership, string $suffix): array
    {
        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));

        return [
            Client::create([
                'name' => "Client {$suffix}",
                'created_by' => $membership->user_id,
                'status' => '1',
            ]),
            Supplier::create([
                'name' => "Fournisseur {$suffix}",
                'created_by' => $membership->user_id,
                'status' => '1',
            ]),
        ];
    }

    private function productFor(Company $company, CompanyUser $membership): Product
    {
        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));
        $category = Category::create([
            'name' => 'Catégorie stock A',
            'created_by' => $membership->user_id,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Produit stock A',
            'qte' => 10,
            'price' => 1000,
            'purchase_price' => 600,
            'profit' => 400,
            'margin' => 2,
            'type' => 1,
            'status' => '1',
            'created_by' => $membership->user_id,
        ]);
    }
}
