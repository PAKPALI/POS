<?php

namespace Tests\Feature;

use App\Models\CompanyUser;
use App\Models\Company;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Transaction;
use App\Models\Category;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class QueryOptimizationTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_dashboard_aggregates_sales_without_loading_each_sale(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);

        foreach (range(1, 25) as $number) {
            Sale::create([
                'company_id' => $company->id,
                'code' => 700000 + $number,
                'received_amount' => 1000,
                'total_amount' => 1000,
                'remaining_amount' => 0,
                'total_profit' => 200,
                'cashier' => $owner->id,
                'discount' => 0,
                'tax_amount' => 0,
                'amount_init' => 1000,
            ]);
        }

        $salesQueries = [];
        DB::listen(function (QueryExecuted $query) use (&$salesQueries) {
            if (str_contains(strtolower($query->sql), 'from "sales"') ||
                str_contains(strtolower($query->sql), 'from `sales`')) {
                $salesQueries[] = $query->sql;
            }
        });

        $this->actingAs($owner)->get(route('dashboard'))->assertOk();

        $this->assertCount(1, $salesQueries);
        foreach ($salesQueries as $query) {
            $this->assertStringNotContainsString('select *', strtolower($query));
        }
    }

    public function test_user_datatable_paginates_in_sql_and_returns_the_company_role(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Équipe rapide',
            'key' => 'fast-team',
            'is_system' => false,
        ]);

        foreach (range(1, 15) as $number) {
            $user = User::factory()->create([
                'name' => 'Employé '.$number,
                'user_type' => 3,
                'status' => 1,
            ]);
            CompanyUser::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $response = $this->actingAs($owner)->getJson(route('user.index').'?draw=1&start=0&length=10', [
            'X-Requested-With' => 'XMLHttpRequest',
        ])->assertOk();

        $this->assertSame(16, $response->json('recordsTotal'));
        $this->assertCount(10, $response->json('data'));
        $this->assertContains('Équipe rapide', array_column($response->json('data'), 'role_name'));
    }

    public function test_order_datatable_paginates_in_sql(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);

        foreach (range(1, 15) as $number) {
            Order::create([
                'company_id' => $company->id,
                'code' => 'WEB-PERF-'.$number,
                'customer_name' => 'Client '.$number,
                'customer_phone' => '00000000',
                'subtotal' => 1000,
                'tax' => 0,
                'total' => 1000,
                'status' => 'pending',
            ]);
        }

        $columns = [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'customer_name', 'name' => 'customer_name', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'customer_phone', 'name' => 'customer_phone', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'total', 'name' => 'total', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'status', 'name' => 'status', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'created_at', 'name' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
        ];
        $parameters = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'WEB-PERF-12', 'regex' => 'false'],
            'columns' => $columns,
            'order' => [['column' => 6, 'dir' => 'desc']],
        ]);

        $response = $this->actingAs($owner)->getJson(route('ecommerce.orders.index').'?'.$parameters, [
            'X-Requested-With' => 'XMLHttpRequest',
        ])->assertOk();

        $this->assertSame(15, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('WEB-PERF-12', $response->json('data.0.code'));
    }

    public function test_sale_tables_search_the_client_without_using_a_virtual_sql_column(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $client = Client::create([
            'company_id' => $company->id,
            'name' => 'Client Recherche Rapide',
            'created_by' => $owner->id,
            'status' => 1,
        ]);
        Sale::create([
            'company_id' => $company->id,
            'code' => 881122,
            'received_amount' => 5000,
            'total_amount' => 5000,
            'remaining_amount' => 0,
            'total_profit' => 1000,
            'cashier' => $owner->name,
            'discount' => 0,
            'tax_amount' => 0,
            'amount_init' => 5000,
            'client_id' => $client->id,
        ]);

        $todayColumns = [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'received_amount', 'name' => 'received_amount', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'total_amount', 'name' => 'total_amount', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'remaining_amount', 'name' => 'remaining_amount', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'total_profit', 'name' => 'total_profit', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'code_promo', 'name' => 'code_promo', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'discount', 'name' => 'discount', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'client', 'name' => 'client', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'cashier', 'name' => 'cashier', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
        ];
        $todayResponse = $this->actingAs($owner)->getJson($this->dataTableUrl(
            route('sale.index'), $todayColumns, 'Client Recherche Rapide'
        ), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $this->assertSame(1, $todayResponse->json('recordsFiltered'));
        $this->assertSame('Client Recherche Rapide', $todayResponse->json('data.0.client'));

        $historyColumns = [
            ['data' => 'id', 'name' => 'id', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'total_amount', 'name' => 'total_amount', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'discount', 'name' => 'discount', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'total_profit', 'name' => 'total_profit', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'client', 'name' => 'client', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'created_at', 'name' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'cashier', 'name' => 'cashier', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
        ];
        $range = now()->format('d-m-Y').' - '.now()->format('d-m-Y');
        $historyResponse = $this->getJson($this->dataTableUrl(
            route('history'), $historyColumns, 'Client Recherche Rapide', ['daterange' => $range]
        ), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $this->assertSame(1, $historyResponse->json('recordsFiltered'));
    }

    public function test_product_table_searches_related_category_name(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $category = Category::create([
            'company_id' => $company->id, 'name' => 'Catégorie Optimisée',
            'created_by' => $owner->id, 'status' => 1,
        ]);
        $supplier = Supplier::create([
            'company_id' => $company->id, 'name' => 'Fournisseur Optimisé',
            'created_by' => $owner->id, 'status' => 1,
        ]);
        $product = Product::create([
            'company_id' => $company->id, 'category_id' => $category->id,
            'supplier_id' => $supplier->id, 'name' => 'Produit Optimisé',
            'qte' => 10, 'margin' => 2, 'price' => 1500, 'purchase_price' => 1000,
            'status' => 1, 'type' => 1, 'created_by' => $owner->id,
        ]);
        Inventory::create([
            'company_id' => $company->id, 'type' => 1, 'supplier_id' => $supplier->id,
            'product_id' => $product->id, 'qte_before' => 0, 'qte_added' => 10,
            'qte_after' => 10, 'created_by' => $owner->id,
        ]);

        $productColumns = [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => 'margin', 'name' => 'margin', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'category_id', 'name' => 'category_id', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'supplier_id', 'name' => 'supplier_id', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'qte', 'name' => 'qte', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'price', 'name' => 'price', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'price_ttc', 'name' => 'price_ttc', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'status', 'name' => 'status', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
        ];
        $productRoute = route('product.index');

        $productResponse = $this->actingAs($owner)->getJson($this->dataTableUrl(
            $productRoute, $productColumns, 'Catégorie Optimisée'
        ), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertSame(1, $productResponse->json('recordsFiltered'));
    }

    public function test_inventory_table_searches_related_product_name(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $category = Category::create([
            'company_id' => $company->id, 'name' => 'Catégorie Stock',
            'created_by' => $owner->id, 'status' => 1,
        ]);
        $supplier = Supplier::create([
            'company_id' => $company->id, 'name' => 'Fournisseur Stock',
            'created_by' => $owner->id, 'status' => 1,
        ]);
        $product = Product::create([
            'company_id' => $company->id, 'category_id' => $category->id,
            'supplier_id' => $supplier->id, 'name' => 'Produit Optimisé',
            'qte' => 10, 'margin' => 2, 'price' => 1500, 'purchase_price' => 1000,
            'status' => 1, 'type' => 1, 'created_by' => $owner->id,
        ]);
        Inventory::create([
            'company_id' => $company->id, 'type' => 1, 'supplier_id' => $supplier->id,
            'product_id' => $product->id, 'qte_before' => 0, 'qte_added' => 10,
            'qte_after' => 10, 'created_by' => $owner->id,
        ]);

        $inventoryColumns = [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => 'type', 'name' => 'type', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'product_id', 'name' => 'product_id', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'supplier_id', 'name' => 'supplier_id', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'qte_before', 'name' => 'qte_before', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'qte_added', 'name' => 'qte_added', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'qte_after', 'name' => 'qte_after', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'created_by', 'name' => 'created_by', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'created_at', 'name' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false'],
        ];

        $inventoryResponse = $this->actingAs($owner)->getJson($this->dataTableUrl(
            route('inventory.index'), $inventoryColumns, 'Produit Optimisé'
        ), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertSame(1, $inventoryResponse->json('recordsFiltered'));
    }

    public function test_pos_catalog_is_tenant_scoped_searchable_and_paginated(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $category = Category::create([
            'company_id' => $company->id, 'name' => 'Boissons POS',
            'created_by' => $owner->id, 'status' => 1,
        ]);

        foreach (range(1, 30) as $number) {
            Product::create([
                'company_id' => $company->id,
                'category_id' => $category->id,
                'name' => 'Produit POS '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'qte' => 10,
                'price' => 1000 + $number,
                'purchase_price' => 500,
                'status' => 1,
                'type' => 1,
                'created_by' => $owner->id,
            ]);
        }

        $firstPageUrl = route('products.search', [
            'q' => 'Produit POS',
            'category_id' => $category->id,
            'page' => 1,
        ]);
        $secondPageUrl = route('products.search', [
            'q' => 'Produit POS',
            'category_id' => $category->id,
            'page' => 2,
        ]);

        $firstPage = $this->actingAs($owner)->getJson($firstPageUrl)->assertOk();

        $this->assertSame(30, $firstPage->json('total'));
        $this->assertSame(1, $firstPage->json('current_page'));
        $this->assertSame(2, $firstPage->json('last_page'));
        $this->assertCount(24, $firstPage->json('data'));
        $this->assertArrayNotHasKey('company_id', $firstPage->json('data.0'));
        $this->assertSame(1001, $firstPage->json('data.0.sale_price'));

        $secondPage = $this->getJson($secondPageUrl)->assertOk();
        $this->assertCount(6, $secondPage->json('data'));
    }

    public function test_pos_client_search_is_tenant_scoped_searchable_and_paginated(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);

        foreach (range(1, 25) as $number) {
            Client::create([
                'company_id' => $company->id,
                'name' => 'Client POS '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'created_by' => $owner->id,
                'status' => 1,
            ]);
        }

        $foreignCompany = Company::create([
            'name' => 'Entreprise étrangère',
            'email' => 'foreign-company@test.local',
            'number1' => '111111111',
        ]);
        $foreignClient = Client::withoutGlobalScopes()->create([
            'company_id' => $foreignCompany->id,
            'name' => 'Client POS étranger',
            'created_by' => $owner->id,
            'status' => 1,
        ]);

        $firstPage = $this->actingAs($owner)->getJson(route('clients.search', [
            'q' => 'Client POS',
            'page' => 1,
        ]))->assertOk();
        $secondPageUrl = route('clients.search', ['q' => 'Client POS', 'page' => 2]);

        $this->assertCount(20, $firstPage->json('results'));
        $this->assertTrue($firstPage->json('pagination.more'));
        $this->assertSame('Client POS 01', $firstPage->json('results.0.text'));

        $secondPage = $this->getJson($secondPageUrl)->assertOk();
        $this->assertCount(5, $secondPage->json('results'));
        $this->assertFalse($secondPage->json('pagination.more'));

        $this->getJson(route('clients.search', ['client_id' => $foreignClient->id]))
            ->assertOk()
            ->assertJsonCount(0, 'results');
    }

    public function test_pos_page_does_not_query_the_complete_client_list(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner);

        foreach (range(1, 60) as $number) {
            Client::create([
                'company_id' => $company->id,
                'name' => 'Client initial '.$number,
                'created_by' => $owner->id,
                'status' => 1,
            ]);
        }

        $clientQueries = [];
        DB::listen(function (QueryExecuted $query) use (&$clientQueries) {
            if (preg_match('/from ["`]clients["`]/i', $query->sql)) {
                $clientQueries[] = $query->sql;
            }
        });

        $this->actingAs($owner)
            ->get(route('sale.index'))
            ->assertOk()
            ->assertSee('clientSelect')
            ->assertDontSee('Client initial 1');

        $this->assertSame([], $clientQueries);
    }

    public function test_inventory_selectors_are_tenant_scoped_and_paginated(): void
    {
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Sélecteurs inventaire',
            'created_by' => $owner->id,
            'status' => 1,
        ]);

        foreach (range(1, 25) as $number) {
            Product::create([
                'company_id' => $company->id,
                'category_id' => $category->id,
                'name' => 'Produit inventaire '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'qte' => $number === 25 ? 0 : $number,
                'price' => 100,
                'price_ttc' => 100,
                'purchase_price' => 50,
                'profit' => 50,
                'margin' => 1,
                'type' => 1,
                'status' => 1,
                'created_by' => $owner->id,
            ]);
            Supplier::create([
                'company_id' => $company->id,
                'name' => 'Fournisseur inventaire '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'status' => 1,
                'created_by' => $owner->id,
            ]);
        }

        $products = $this->actingAs($owner)->getJson(route('inventory.products.search', [
            'q' => 'Produit inventaire',
            'page' => 1,
        ]))->assertOk();
        $this->assertCount(20, $products->json('results'));
        $this->assertTrue($products->json('pagination.more'));

        $inStock = $this->getJson(route('inventory.products.search', [
            'q' => 'Produit inventaire 25',
            'in_stock' => 1,
        ]))->assertOk();
        $this->assertCount(0, $inStock->json('results'));

        $suppliers = $this->getJson(route('inventory.suppliers.search', [
            'q' => 'Fournisseur inventaire',
            'page' => 2,
        ]))->assertOk();
        $this->assertCount(5, $suppliers->json('results'));
        $this->assertFalse($suppliers->json('pagination.more'));

        $this->get(route('inventory.index'))
            ->assertOk()
            ->assertDontSee('Produit inventaire 01')
            ->assertDontSee('Fournisseur inventaire 01');
    }

    public function test_transaction_dashboard_uses_one_summary_query(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'transaction-volume');
        $cash = CashAccount::create([
            'company_id' => $company->id,
            'name' => 'Caisse volume',
            'code' => 'VOL-'.$company->id,
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => true,
            'is_tax' => false,
            'status' => 1,
            'created_by' => $owner->id,
        ]);

        foreach (range(1, 80) as $number) {
            Transaction::create([
                'company_id' => $company->id,
                'to_cash_id' => $cash->id,
                'type' => $number % 2 === 0 ? 'IN' : 'OUT',
                'amount' => 100 + $number,
                'description' => 'Volume '.$number,
                'created_by' => $owner->id,
            ]);
        }

        $transactionQueries = [];
        DB::listen(function (QueryExecuted $query) use (&$transactionQueries) {
            if (preg_match('/from ["`]transactions["`]/i', $query->sql)) {
                $transactionQueries[] = $query->sql;
            }
        });

        $this->actingAs($owner)->get(route('transaction.index'))->assertOk();

        $this->assertCount(1, $transactionQueries);
        $this->assertStringContainsString('case when', strtolower($transactionQueries[0]));
        $this->assertStringNotContainsString('select *', strtolower($transactionQueries[0]));
    }

    public function test_client_datatable_eager_loads_creators_without_n_plus_one_queries(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'client-query-volume');

        foreach (range(1, 15) as $number) {
            Client::create([
                'company_id' => $company->id,
                'name' => 'Client requête '.$number,
                'created_by' => $owner->id,
                'status' => 1,
            ]);
        }

        $queries = [];
        DB::listen(function (QueryExecuted $query) use (&$queries) {
            if (preg_match('/from ["`](clients|users)["`]/i', $query->sql)) {
                $queries[] = $query->sql;
            }
        });

        $response = $this->actingAs($owner)->getJson(route('client.index').'?'.http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $this->assertCount(10, $response->json('data'));
        $this->assertLessThanOrEqual(4, count($queries));
    }

    public function test_cash_dashboard_uses_two_queries_for_all_summaries(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'cash-query-volume');

        foreach (range(1, 12) as $number) {
            CashAccount::create([
                'company_id' => $company->id,
                'name' => 'Caisse performance '.$number,
                'code' => 'PERF-'.$company->id.'-'.$number,
                'balance' => 1000,
                'currency' => 'FCFA',
                'is_default' => $number === 1,
                'is_tax' => $number === 2,
                'status' => $number % 3 === 0 ? 0 : 1,
                'created_by' => $owner->id,
            ]);
        }

        $cashQueries = [];
        DB::listen(function (QueryExecuted $query) use (&$cashQueries) {
            if (preg_match('/from ["`]cash_accounts["`]/i', $query->sql)) {
                $cashQueries[] = $query->sql;
            }
        });

        $this->actingAs($owner)->get(route('cash-account.index'))->assertOk();

        $this->assertCount(2, $cashQueries);
        $this->assertStringContainsString('case when', strtolower($cashQueries[0]));
    }

    private function dataTableUrl(string $url, array $columns, string $search, array $extra = []): string
    {
        return $url.'?'.http_build_query(array_merge($extra, [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => $search, 'regex' => 'false'],
            'columns' => $columns,
        ]));
    }
}
