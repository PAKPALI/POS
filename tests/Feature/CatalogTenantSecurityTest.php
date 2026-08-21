<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CatalogTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_resources_from_another_active_company_are_not_accessible(): void
    {
        [$user, $companyA, $membershipA, $companyB, $membershipB] = $this->twoCompanyOwner();
        [$categoryA, , $productA] = $this->catalogFor($companyA, $membershipA, 'A');
        [$categoryB, , $productB] = $this->catalogFor($companyB, $membershipB, 'B');

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));

        $this->actingAs($user)->withSession(['active_company_id' => $companyA->id]);

        $this->get(route('product.show', $productB->id))->assertNotFound();
        $this->get(route('product.edit', $productB->id))->assertNotFound();
        $this->putJson(route('product.update', $productB->id), $this->productPayload($categoryA))
            ->assertNotFound();
        $this->deleteJson(route('product.destroy', $productB->id))->assertNotFound();

        $this->get(route('category.show', $categoryB->id))->assertNotFound();
        $this->get(route('category.edit', $categoryB->id))->assertNotFound();
        $this->putJson(route('category.update', $categoryB->id), ['name' => 'Intrusion'])
            ->assertNotFound();
        $this->deleteJson(route('category.destroy', $categoryB->id))->assertNotFound();

        $this->assertDatabaseHas('products', ['id' => $productA->id, 'company_id' => $companyA->id]);
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'company_id' => $companyB->id]);
        $this->assertDatabaseHas('categories', ['id' => $categoryB->id, 'company_id' => $companyB->id]);
    }

    public function test_product_cannot_receive_a_category_or_supplier_from_another_company(): void
    {
        [$user, $companyA, $membershipA, $companyB, $membershipB] = $this->twoCompanyOwner();
        [$categoryA, $supplierA, $productA] = $this->catalogFor($companyA, $membershipA, 'A');
        [$categoryB, $supplierB] = $this->catalogFor($companyB, $membershipB, 'B');

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));
        $this->actingAs($user)->withSession(['active_company_id' => $companyA->id]);

        $this->postJson(route('product.store'), $this->productPayload($categoryB, $supplierA, 'Catégorie externe'))
            ->assertOk()
            ->assertJson(['status' => false]);

        $this->postJson(route('product.store'), $this->productPayload($categoryA, $supplierB, 'Fournisseur externe'))
            ->assertOk()
            ->assertJson(['status' => false]);

        $this->putJson(
            route('product.update', $productA->id),
            $this->productPayload($categoryB, $supplierA, 'Modification catégorie externe')
        )->assertOk()->assertJson(['status' => false]);

        $this->putJson(
            route('product.update', $productA->id),
            $this->productPayload($categoryA, $supplierB, 'Modification fournisseur externe')
        )->assertOk()->assertJson(['status' => false]);

        $productA->refresh();

        $this->assertSame($categoryA->id, $productA->category_id);
        $this->assertSame($supplierA->id, $productA->supplier_id);
        $this->assertDatabaseMissing('products', ['name' => 'Catégorie externe']);
        $this->assertDatabaseMissing('products', ['name' => 'Fournisseur externe']);
    }

    public function test_policies_deny_a_resource_outside_the_active_company_even_without_the_global_scope(): void
    {
        [$user, $companyA, $membershipA, $companyB, $membershipB] = $this->twoCompanyOwner();
        [$categoryA, , $productA] = $this->catalogFor($companyA, $membershipA, 'A');
        [$categoryB, , $productB] = $this->catalogFor($companyB, $membershipB, 'B');

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));

        $this->assertTrue(Gate::forUser($user)->allows('update', $productA));
        $this->assertTrue(Gate::forUser($user)->allows('update', $categoryA));
        $this->assertFalse(Gate::forUser($user)->allows('update', $productB));
        $this->assertFalse(Gate::forUser($user)->allows('update', $categoryB));
    }

    public function test_catalog_policies_require_the_catalog_permission(): void
    {
        $user = User::create([
            'name' => 'Lecteur test',
            'email' => 'catalog-viewer@test.local',
            'password' => 'password',
            'user_type' => '2',
            'status' => '1',
        ]);
        $company = $this->company('Catalogue lecture seule');
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Lecteur',
            'key' => 'viewer',
            'is_system' => false,
        ]);
        $membership = CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));

        $this->assertFalse(Gate::forUser($user)->allows('viewAny', Product::class));
        $this->assertFalse(Gate::forUser($user)->allows('viewAny', Category::class));
    }

    public function test_foreign_category_filter_is_rejected_for_listing_and_export(): void
    {
        [$user, $companyA, $membershipA, $companyB, $membershipB] = $this->twoCompanyOwner();
        $this->catalogFor($companyA, $membershipA, 'A');
        [$categoryB] = $this->catalogFor($companyB, $membershipB, 'B');

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));

        $this->actingAs($user)
            ->withSession(['active_company_id' => $companyA->id])
            ->get(route('product.index', ['category_id' => $categoryB->id]))
            ->assertNotFound();

        $this->actingAs($user)
            ->withSession(['active_company_id' => $companyA->id])
            ->get(route('product.export.pdf', ['category_id' => $categoryB->id]))
            ->assertNotFound();
    }

    private function twoCompanyOwner(): array
    {
        $user = User::create([
            'name' => 'Propriétaire test',
            'email' => 'catalog-security@test.local',
            'password' => 'password',
            'user_type' => '2',
            'status' => '1',
        ]);

        $companyA = $this->company('Catalogue A');
        $companyB = $this->company('Catalogue B');

        return [
            $user,
            $companyA,
            $this->ownerMembership($user, $companyA),
            $companyB,
            $this->ownerMembership($user, $companyB),
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

    private function ownerMembership(User $user, Company $company): CompanyUser
    {
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Propriétaire',
            'key' => 'owner',
            'is_system' => true,
        ]);

        return CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    private function catalogFor(Company $company, CompanyUser $membership, string $suffix): array
    {
        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));

        $category = Category::create([
            'name' => "Catégorie {$suffix}",
            'created_by' => $membership->user_id,
        ]);
        $supplier = Supplier::create([
            'name' => "Fournisseur {$suffix}",
            'created_by' => $membership->user_id,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => "Produit {$suffix}",
            'qte' => 10,
            'price' => 1000,
            'purchase_price' => 600,
            'profit' => 400,
            'margin' => 2,
            'type' => 1,
            'status' => '1',
            'created_by' => $membership->user_id,
        ]);

        return [$category, $supplier, $product];
    }

    private function productPayload(
        Category $category,
        ?Supplier $supplier = null,
        string $name = 'Produit test'
    ): array {
        return [
            'type' => 1,
            'category' => $category->id,
            'supplier_id' => $supplier?->id,
            'name' => $name,
            'price' => 1200,
            'purchase_price' => 700,
            'margin' => 2,
            'profit' => 500,
        ];
    }
}
