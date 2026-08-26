<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class MenuCatalogOptimizationTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_menu_product_search_is_paginated_and_cross_company_composition_is_rejected(): void
    {
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Menus',
            'status' => 1,
            'created_by' => $owner->id,
        ]);

        foreach (range(1, 25) as $number) {
            $this->createProduct($company->id, $category->id, $owner->id, 'Composant menu '.str_pad((string) $number, 2, '0', STR_PAD_LEFT));
        }

        $results = $this->actingAs($owner)->getJson(route('menu.products.search', [
            'q' => 'Composant menu',
            'page' => 1,
        ]))->assertOk();
        $this->assertCount(20, $results->json('results'));
        $this->assertTrue($results->json('pagination.more'));

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertDontSee('Composant menu 01');

        $foreignCompany = Company::create([
            'name' => 'Autre restaurant',
            'email' => 'other-menu@test.local',
            'number1' => '400',
        ]);
        $foreignCategory = Category::withoutGlobalScopes()->create([
            'company_id' => $foreignCompany->id,
            'name' => 'Étrangère',
            'status' => 1,
            'created_by' => $owner->id,
        ]);
        $foreignProduct = $this->createProduct($foreignCompany->id, $foreignCategory->id, $owner->id, 'Produit étranger', true);

        $this->postJson(route('menu.store'), [
            'type' => 2,
            'category' => $category->id,
            'name' => 'Menu invalide',
            'qte' => 1,
            'price' => 1000,
            'margin' => 0,
            'products' => [['product_id' => $foreignProduct->id, 'quantity' => 1]],
        ])->assertOk()->assertJson(['status' => false]);

        $this->assertDatabaseMissing('products', [
            'company_id' => $company->id,
            'name' => 'Menu invalide',
            'type' => 2,
        ]);
    }

    private function createProduct(int $companyId, int $categoryId, int $userId, string $name, bool $withoutScopes = false): Product
    {
        $query = $withoutScopes ? Product::withoutGlobalScopes() : new Product();

        return $query->create([
            'company_id' => $companyId,
            'category_id' => $categoryId,
            'name' => $name,
            'qte' => 10,
            'price' => 100,
            'price_ttc' => 100,
            'purchase_price' => 50,
            'profit' => 50,
            'margin' => 1,
            'type' => 1,
            'status' => 1,
            'created_by' => $userId,
        ]);
    }
}
