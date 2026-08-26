<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class PdfExportSafetyTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_oversized_pdf_exports_are_rejected_before_dompdf_runs(): void
    {
        config([
            'performance.pdf_exports.products_max_rows' => 1,
            'performance.pdf_exports.inventories_max_rows' => 1,
            'performance.pdf_exports.sales_max_rows' => 1,
        ]);
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'pdf-safety');
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'PDF sécurisé',
            'created_by' => $owner->id,
            'status' => 1,
        ]);

        foreach (range(1, 2) as $number) {
            $product = Product::create([
                'company_id' => $company->id,
                'category_id' => $category->id,
                'name' => 'Produit PDF '.$number,
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
                'code' => 9700000 + $number,
                'received_amount' => 1000,
                'total_amount' => 1000,
                'remaining_amount' => 0,
                'total_profit' => 500,
                'discount' => 0,
                'amount_init' => 1000,
                'tax_amount' => 0,
                'cashier' => $owner->name,
            ]);
        }

        $this->actingAs($owner)
            ->withSession(['active_company_id' => $company->id])
            ->from(route('product.index'))
            ->get(route('product.export.pdf'))
            ->assertRedirect(route('product.index'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, '1 produits'));

        $this->from(route('inventory.index'))
            ->get(route('inventory.export.pdf'))
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, '1 mouvements'));

        $this->from(route('history'))
            ->get(route('history.export.pdf', [
                'daterange' => now()->format('d-m-Y').' - '.now()->format('d-m-Y'),
            ]))
            ->assertRedirect(route('history'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, '1 ventes'));
    }
}
