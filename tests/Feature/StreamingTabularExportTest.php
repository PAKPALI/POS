<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\CompanyUser;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class StreamingTabularExportTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_product_inventory_and_sale_exports_stream_csv_and_excel_with_active_filters(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'streaming-exports');
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Exports',
            'created_by' => $owner->id,
            'status' => 1,
        ]);
        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => '=PRODUIT À PROTÉGER',
            'qte' => 10,
            'margin' => 1,
            'price' => 1000,
            'price_ttc' => 1000,
            'purchase_price' => 500,
            'profit' => 500,
            'status' => 1,
            'type' => 1,
            'created_by' => $owner->id,
        ]);
        Inventory::create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'type' => 1,
            'qte_before' => 0,
            'qte_added' => 10,
            'qte_after' => 10,
            'created_by' => $owner->id,
        ]);
        Sale::create([
            'company_id' => $company->id,
            'code' => 9800001,
            'received_amount' => 1000,
            'total_amount' => 1000,
            'remaining_amount' => 0,
            'total_profit' => 500,
            'discount' => 0,
            'amount_init' => 1000,
            'tax_amount' => 0,
            'cashier' => $owner->name,
        ]);

        $this->actingAs($owner)->withSession(['active_company_id' => $company->id]);
        $today = now()->format('Y-m-d');
        $dateRange = now()->format('d-m-Y').' - '.now()->format('d-m-Y');

        $productCsv = $this->get(route('product.export.tabular', [
            'format' => 'csv', 'category_id' => $category->id,
        ]))->assertOk();
        $this->assertStringContainsString("'=PRODUIT À PROTÉGER", $productCsv->streamedContent());

        $productExcel = $this->get(route('product.export.tabular', [
            'format' => 'excel', 'category_id' => $category->id,
        ]))->assertOk();
        $productExcel->assertDownload();
        $this->assertStringContainsString('.xlsx', $productExcel->headers->get('content-disposition'));
        $this->assertSame('PK', substr($productExcel->baseResponse->getFile()->getContent(), 0, 2));

        $inventoryCsv = $this->get(route('inventory.export.tabular', [
            'format' => 'csv', 'start_date' => $today, 'end_date' => $today,
        ]))->assertOk();
        $this->assertStringContainsString('Entrée', $inventoryCsv->streamedContent());

        $inventoryExcel = $this->get(route('inventory.export.tabular', [
            'format' => 'excel', 'start_date' => $today, 'end_date' => $today,
        ]))->assertOk();
        $inventoryExcel->assertDownload();
        $this->assertSame('PK', substr($inventoryExcel->baseResponse->getFile()->getContent(), 0, 2));

        $saleCsv = $this->get(route('history.export.tabular', [
            'format' => 'csv', 'daterange' => $dateRange,
        ]))->assertOk();
        $this->assertStringContainsString('9800001', $saleCsv->streamedContent());
        $this->assertStringContainsString('Bénéfice', $saleCsv->streamedContent());

        $saleExcel = $this->get(route('history.export.tabular', [
            'format' => 'excel', 'daterange' => $dateRange,
        ]))->assertOk();
        $saleExcel->assertDownload();
        $this->assertSame('PK', substr($saleExcel->baseResponse->getFile()->getContent(), 0, 2));

        $salesPermission = Permission::firstOrCreate([
            'key' => 'sales.manage',
        ], ['module' => 'sales', 'description' => 'Ventes']);
        $cashierRole = Role::create([
            'company_id' => $company->id,
            'name' => 'Ventes sans bénéfice',
            'key' => 'sales-without-margin',
            'is_system' => false,
        ]);
        $cashierRole->permissions()->attach($salesPermission);
        $cashier = User::factory()->create(['user_type' => 3, 'status' => 1]);
        CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $cashier->id,
            'role_id' => $cashierRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $restrictedCsv = $this->actingAs($cashier)
            ->withSession(['active_company_id' => $company->id])
            ->get(route('history.export.tabular', [
                'format' => 'csv', 'daterange' => $dateRange,
            ]))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('Bénéfice', $restrictedCsv);

        $this->get('/component/product/export/pdfx')->assertNotFound();
    }
}
