<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Utilisateur test', 'email' => $email,
            'password' => bcrypt('password'), 'user_type' => '2', 'status' => '1',
        ]);
    }

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name, 'email' => str($name)->slug() . '@test.local', 'number1' => '000000000',
        ]);
    }

    private function membership(User $user, Company $company, string $roleKey = 'owner'): CompanyUser
    {
        $role = Role::create([
            'company_id' => $company->id, 'name' => ucfirst($roleKey),
            'key' => $roleKey, 'is_system' => true,
        ]);

        return CompanyUser::create([
            'company_id' => $company->id, 'user_id' => $user->id,
            'role_id' => $role->id, 'status' => 'active', 'joined_at' => now(),
        ]);
    }

    public function test_user_can_switch_only_to_a_company_where_membership_is_active(): void
    {
        $user = $this->user('switch@test.local');
        $allowed = $this->company('Allowed');
        $forbidden = $this->company('Forbidden');
        $this->membership($user, $allowed);

        $this->actingAs($user)->post(route('companies.switch', $allowed->id))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('active_company_id', $allowed->id);

        $this->actingAs($user)->post(route('companies.switch', $forbidden->id))
            ->assertRedirect(route('companies.select'));
    }

    public function test_business_queries_and_creations_are_isolated_by_active_company(): void
    {
        $user = $this->user('isolation@test.local');
        $companyA = $this->company('Company A');
        $companyB = $this->company('Company B');
        $membershipA = $this->membership($user, $companyA);
        $membershipB = $this->membership($user, $companyB);

        $context = app(CompanyContext::class);
        $context->set($companyA, $membershipA->load('role.permissions'));
        $productA = Product::create($this->productData('Produit A'));

        $context->set($companyB, $membershipB->load('role.permissions'));
        $productB = Product::create($this->productData('Produit B'));

        $this->assertSame($companyA->id, $productA->company_id);
        $this->assertSame($companyB->id, $productB->company_id);
        $this->assertSame(['Produit B'], Product::pluck('name')->all());

        $context->set($companyA, $membershipA);
        $this->assertSame(['Produit A'], Product::pluck('name')->all());
        $this->assertNull(Product::find($productB->id));
    }

    public function test_permission_is_evaluated_in_selected_company(): void
    {
        $user = $this->user('permission@test.local');
        $company = $this->company('Read only');
        $this->membership($user, $company, 'viewer');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id])
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    private function productData(string $name): array
    {
        return [
            'name' => $name, 'qte' => 10, 'price' => 1000, 'purchase_price' => 500,
            'profit' => 500, 'margin' => 10, 'type' => 1, 'status' => '1',
            'created_by' => 1,
        ];
    }
}
