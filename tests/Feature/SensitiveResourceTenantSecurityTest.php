<?php

namespace Tests\Feature;

use App\Models\AMS\CashAccount;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Role;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SensitiveResourceTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_foreign_sales_inventories_cashes_and_orders_return_not_found(): void
    {
        [$user, $companyA, $companyB, $membershipA] = $this->tenantPair();
        app(CompanyContext::class)->set($companyB, CompanyUser::where('company_id', $companyB->id)->firstOrFail()->load('role.permissions'));

        $sale = Sale::create($this->saleData());
        $product = $this->product($user);
        $inventory = Inventory::create([
            'type' => 1, 'product_id' => $product->id, 'qte_before' => 0, 'qte_added' => 1, 'qte_after' => 1,
            'created_by' => $user->id,
        ]);
        $cash = CashAccount::create([
            'name' => 'Caisse étrangère', 'code' => 'FOREIGN-'.Str::random(8),
            'balance' => 0, 'currency' => 'FCFA', 'status' => 1, 'created_by' => $user->id,
        ]);
        $order = Order::create([
            'code' => 'CMD-'.Str::upper(Str::random(10)), 'customer_name' => 'Client',
            'customer_phone' => '90000000', 'subtotal' => 1000, 'tax' => 0,
            'total' => 1000, 'status' => 'pending',
        ]);

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));
        $session = ['active_company_id' => $companyA->id];

        $this->actingAs($user)->withSession($session)->get(route('sale.show', $sale->id))->assertNotFound();
        $this->actingAs($user)->withSession($session)->get(route('inventory.show', $inventory->id))->assertNotFound();
        $this->actingAs($user)->withSession($session)->get(route('cash-account.show', $cash->id))->assertNotFound();
        $this->actingAs($user)->withSession($session)->get(route('ecommerce.orders.show', $order->id))->assertNotFound();
        $this->actingAs($user)->withSession($session)->putJson(route('cash-account.update', $cash->id), ['name' => 'Intrusion'])->assertNotFound();
        $this->actingAs($user)->withSession($session)->deleteJson(route('cash-account.destroy', $cash->id))->assertNotFound();
        $this->actingAs($user)->withSession($session)->postJson(route('ecommerce.orders.cancel', $order->id), ['reason' => 'Intrusion'])->assertNotFound();
        $this->actingAs($user)->withSession($session)->postJson(route('ecommerce.orders.execute', $order->id))->assertNotFound();

        $this->assertSame('Caisse étrangère', $cash->fresh()->name);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->sale_id);
    }

    public function test_policies_reject_foreign_resources_even_when_loaded_without_scope(): void
    {
        [$user, $companyA, $companyB, $membershipA] = $this->tenantPair();
        app(CompanyContext::class)->set($companyB, CompanyUser::where('company_id', $companyB->id)->firstOrFail()->load('role.permissions'));
        $sale = Sale::create($this->saleData());
        $product = $this->product($user);
        $inventory = Inventory::create(['type' => 1, 'product_id' => $product->id, 'qte_before' => 0, 'qte_added' => 1, 'qte_after' => 1, 'created_by' => $user->id]);
        $cash = CashAccount::create(['name' => 'Caisse B', 'code' => 'B-'.Str::random(8), 'balance' => 0, 'currency' => 'FCFA', 'status' => 1, 'created_by' => $user->id]);
        $order = Order::create(['code' => 'CMD-'.Str::upper(Str::random(10)), 'customer_name' => 'Client', 'customer_phone' => '90000000', 'subtotal' => 1000, 'tax' => 0, 'total' => 1000, 'status' => 'pending']);

        app(CompanyContext::class)->set($companyA, $membershipA->load('role.permissions'));

        $this->assertFalse($user->can('view', $sale));
        $this->assertFalse($user->can('view', $inventory));
        $this->assertFalse($user->can('update', $cash));
        $this->assertFalse($user->can('view', $order));
        $this->assertFalse($user->can('cancel', $order));
        $this->assertFalse($user->can('convert', $order));
    }

    private function tenantPair(): array
    {
        $user = User::factory()->create(['user_type' => 1, 'status' => 1]);
        $companyA = $this->company('Entreprise A');
        $companyB = $this->company('Entreprise B');
        $membershipA = $this->membership($user, $companyA);
        $this->membership($user, $companyB);

        return [$user, $companyA, $companyB, $membershipA];
    }

    private function company(string $name): Company
    {
        return Company::create(['name' => $name, 'email' => Str::slug($name).'-'.uniqid().'@test.local', 'number1' => '000']);
    }

    private function membership(User $user, Company $company): CompanyUser
    {
        $role = Role::create(['company_id' => $company->id, 'name' => 'Propriétaire', 'key' => 'owner', 'is_system' => true]);

        return CompanyUser::create(['company_id' => $company->id, 'user_id' => $user->id, 'role_id' => $role->id, 'status' => 'active', 'joined_at' => now()]);
    }

    private function saleData(): array
    {
        return ['code' => (string) random_int(10000000, 99999999), 'received_amount' => 1000, 'total_amount' => 1000, 'remaining_amount' => 0, 'total_profit' => 100, 'cashier' => 'Test'];
    }

    private function product(User $user): Product
    {
        $category = Category::create(['name' => 'Sécurité', 'status' => 1, 'created_by' => $user->id]);

        return Product::create([
            'category_id' => $category->id, 'name' => 'Produit sécurité', 'qte' => 5,
            'price' => 1000, 'purchase_price' => 500, 'profit' => 500, 'margin' => 1,
            'type' => 1, 'status' => 1, 'created_by' => $user->id,
        ]);
    }
}
