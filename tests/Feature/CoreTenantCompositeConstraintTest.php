<?php

namespace Tests\Feature;

use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\EcommerceManager;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CoreTenantCompositeConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_rejects_cross_company_business_relations(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Les contraintes composites sont déployées sur MySQL.');
        }

        $user = User::factory()->create(['status' => 1, 'user_type' => 1]);
        [$companyA, $membershipA] = $this->company($user, 'Composite A');
        [$companyB, $membershipB] = $this->company($user, 'Composite B');

        app(CompanyContext::class)->set($companyA, $membershipA);
        $categoryA = Category::create(['name' => 'Catégorie A', 'status' => 1, 'created_by' => $user->id]);
        $productA = $this->product($categoryA, $user, 'Produit A');
        $clientA = Client::create(['name' => 'Client A', 'status' => 1, 'created_by' => $user->id]);
        $cashA = $this->cash($user, 'A');

        app(CompanyContext::class)->set($companyB, $membershipB);
        $categoryB = Category::create(['name' => 'Catégorie B', 'status' => 1, 'created_by' => $user->id]);
        $productB = $this->product($categoryB, $user, 'Produit B');
        $clientB = Client::create(['name' => 'Client B', 'status' => 1, 'created_by' => $user->id]);
        $cashB = $this->cash($user, 'B');
        $saleB = Sale::create([
            'code' => (string) random_int(10000000, 99999999), 'received_amount' => 1000, 'total_amount' => 1000,
            'remaining_amount' => 0, 'total_profit' => 100, 'cashier' => $user->name, 'client_id' => $clientB->id,
        ]);
        $detailB = $saleB->saleDetails()->create([
            'product_id' => $productB->id, 'quantity' => 1, 'unit_price' => 1000,
            'total_price' => 1000, 'profit' => 100,
        ]);
        $settingB = Setting::create(['default_cash_id' => $cashB->id, 'tax_cash_id' => null, 'default_tax' => 0]);

        $this->assertForeignKeyRejects(fn () => DB::table('products')->where('id', $productB->id)->update(['category_id' => $categoryA->id]));
        $this->assertForeignKeyRejects(fn () => DB::table('sales')->where('id', $saleB->id)->update(['client_id' => $clientA->id]));
        $this->assertForeignKeyRejects(fn () => DB::table('sale_details')->where('id', $detailB->id)->update(['product_id' => $productA->id]));
        $this->assertForeignKeyRejects(fn () => DB::table('settings')->where('id', $settingB->id)->update(['default_cash_id' => $cashA->id]));

        $this->assertSame($categoryB->id, $productB->fresh()->category_id);
        $this->assertSame($clientB->id, $saleB->fresh()->client_id);
        $this->assertSame($productB->id, $detailB->fresh()->product_id);
        $this->assertSame($cashB->id, $settingB->fresh()->default_cash_id);
    }

    public function test_expected_composite_foreign_keys_exist(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Le contrôle INFORMATION_SCHEMA cible MySQL.');
        }

        $constraints = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->whereIn('CONSTRAINT_NAME', [
                'products_category_company_fk', 'sales_client_company_fk',
                'sale_details_sale_company_fk', 'sale_details_product_company_fk',
                'inventories_product_company_fk', 'settings_default_cash_company_fk',
                'order_items_order_company_fk', 'orders_sale_company_fk',
            ])->count();

        $this->assertSame(8, $constraints);
    }

    public function test_database_rejects_foreign_roles_and_recipients_without_membership(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Les contraintes composites sont déployées sur MySQL.');
        }

        $member = User::factory()->create(['status' => 1, 'user_type' => 1]);
        $outsider = User::factory()->create(['status' => 1, 'user_type' => 3]);
        [$companyA, $membershipA] = $this->company($member, 'Adhésion A');
        [$companyB] = $this->company($member, 'Adhésion B');
        $roleB = Role::where('company_id', $companyB->id)->firstOrFail();

        $this->assertForeignKeyRejects(fn () => DB::table('company_user')
            ->where('id', $membershipA->id)->update(['role_id' => $roleB->id]));
        $this->assertForeignKeyRejects(fn () => DB::table('notification_recipients')->insert([
            'company_id' => $companyA->id, 'user_id' => $outsider->id,
            'category' => 'sale', 'email_enabled' => 1, 'whatsapp_enabled' => 0,
            'sms_enabled' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]));
        $this->assertForeignKeyRejects(fn () => EcommerceManager::withoutEvents(fn () => DB::table('ecommerce_managers')->insert([
            'company_id' => $companyA->id, 'user_id' => $outsider->id,
            'created_at' => now(), 'updated_at' => now(),
        ])));

        $this->assertSame($companyA->id, $membershipA->fresh()->company_id);
        $this->assertNotSame($roleB->id, $membershipA->fresh()->role_id);
    }

    private function assertForeignKeyRejects(callable $operation): void
    {
        try {
            $operation();
            $this->fail('MySQL aurait dû refuser la relation inter-compagnies.');
        } catch (QueryException $exception) {
            $this->assertSame('23000', $exception->getCode());
        }
    }

    private function company(User $user, string $name): array
    {
        $company = Company::create(['name' => $name, 'email' => Str::slug($name).'-'.uniqid().'@test.local', 'number1' => '000']);
        $role = Role::create(['company_id' => $company->id, 'name' => 'Propriétaire', 'key' => 'owner', 'is_system' => true]);
        $membership = CompanyUser::create(['company_id' => $company->id, 'user_id' => $user->id, 'role_id' => $role->id, 'status' => 'active', 'joined_at' => now()]);

        return [$company, $membership->load('role.permissions')];
    }

    private function product(Category $category, User $user, string $name): Product
    {
        return Product::create([
            'category_id' => $category->id, 'name' => $name, 'qte' => 5, 'price' => 1000,
            'purchase_price' => 500, 'profit' => 500, 'margin' => 1, 'type' => 1,
            'status' => 1, 'created_by' => $user->id,
        ]);
    }

    private function cash(User $user, string $suffix): CashAccount
    {
        return CashAccount::create([
            'name' => 'Caisse '.$suffix, 'code' => 'C-'.$suffix.'-'.Str::random(6),
            'balance' => 0, 'currency' => 'FCFA', 'status' => 1, 'created_by' => $user->id,
        ]);
    }
}
