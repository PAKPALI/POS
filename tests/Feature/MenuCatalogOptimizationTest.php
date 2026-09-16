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

    public function test_pack_creation_persists_composition_pricing_and_initial_inventory(): void
    {
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $category = Category::create(['company_id' => $company->id, 'name' => 'Packs', 'status' => 1, 'created_by' => $owner->id]);
        $component = $this->createProduct($company->id, $category->id, $owner->id, 'Composant');

        $this->actingAs($owner)->postJson(route('menu.store'), [
            'type' => 2, 'category' => $category->id, 'name' => 'Pack famille', 'qte' => 4,
            'price' => 1500, 'purchase_price' => 900, 'margin' => 1,
            'products' => [['product_id' => $component->id, 'quantity' => 3]],
        ])->assertOk()->assertJson(['status' => true]);

        $pack = Product::where('name', 'Pack famille')->firstOrFail();
        $this->assertSame(2, (int) $pack->type);
        $this->assertSame(600, (int) $pack->profit);
        $this->assertDatabaseHas('menu_products', ['menu_id' => $pack->id, 'product_id' => $component->id, 'quantity' => 3]);
        $this->assertDatabaseHas('inventories', ['product_id' => $pack->id, 'type' => 1, 'qte_before' => 0, 'qte_added' => 4, 'qte_after' => 4]);
    }

    public function test_pack_component_quantity_cannot_exceed_current_product_stock(): void
    {
        $owner = User::factory()->create(['status' => 1]);
        $company = $this->activateCompanyFor($owner);
        $category = Category::create(['company_id' => $company->id, 'name' => 'Packs', 'status' => 1, 'created_by' => $owner->id]);
        $component = $this->createProduct($company->id, $category->id, $owner->id, 'Stock limité');
        $component->update(['qte' => 3]);

        $this->actingAs($owner)->postJson(route('menu.store'), [
            'type' => 2, 'category' => $category->id, 'name' => 'Pack trop grand', 'qte' => 1,
            'price' => 1500, 'products' => [['product_id' => $component->id, 'quantity' => 4]],
        ])->assertOk()->assertJson(['status' => false])->assertJsonPath('msg', 'La quantité du produit sélectionné ne peut pas dépasser son stock actuel (3).');

        $this->assertDatabaseMissing('products', ['company_id' => $company->id, 'name' => 'Pack trop grand', 'type' => 2]);
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
