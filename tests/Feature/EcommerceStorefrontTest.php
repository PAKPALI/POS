<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Category;
use App\Models\Product;
use App\Services\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_storefront_uses_its_own_public_url(): void
    {
        $company = Company::create([
            'name' => 'Boutique Démonstration',
            'email' => 'boutique@test.local',
            'number1' => '90000000',
            'ecommerce_active' => true,
        ]);
        app(CompanyContext::class)->setPublicCompany($company);
        $category = Category::create(['name' => 'Boissons', 'status' => 1, 'created_by' => 1]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Jus naturel', 'qte' => 10,
            'price' => 1000, 'purchase_price' => 500, 'profit' => 500, 'margin' => 50,
            'type' => 1, 'status' => 1, 'created_by' => 1,
        ]);

        $url = route('storefront.home', $company);

        $this->assertStringContainsString('/boutique/'.$company->slug, $url);
        $this->get($url)->assertOk()
            ->assertSee('Boutique Démonstration')
            ->assertSee('globalStorefrontSearch')
            ->assertSee(route('storefront.products', $company), false);
        $this->get(route('storefront.products', $company))->assertOk();
        $this->get(route('storefront.checkout', $company))->assertOk();
        $this->get(route('storefront.category', [$company, $category->id]))
            ->assertOk()->assertSee('Jus naturel');
        $this->get(route('storefront.product', [$company, $product->id]))
            ->assertOk()->assertSee('Jus naturel');
    }

    public function test_companies_with_the_same_name_receive_distinct_stable_storefront_urls(): void
    {
        $first = Company::create([
            'name' => 'Matrix Boutique',
            'email' => 'matrix-one@test.local',
            'number1' => '100',
            'description' => 'Repère unique de la première boutique',
            'ecommerce_active' => true,
        ]);
        $second = Company::create([
            'name' => 'Matrix Boutique',
            'email' => 'matrix-two@test.local',
            'number1' => '200',
            'description' => 'Repère unique de la deuxième boutique',
            'ecommerce_active' => true,
        ]);

        $this->assertSame('matrix-boutique', $first->slug);
        $this->assertSame('matrix-boutique-2', $second->slug);
        $this->assertNotSame($first->public_id, $second->public_id);
        $this->assertNotSame(route('storefront.home', $first), route('storefront.home', $second));

        $this->get(route('storefront.home', $first))
            ->assertOk()
            ->assertSee('Repère unique de la première boutique')
            ->assertDontSee('Repère unique de la deuxième boutique');
        $this->get(route('storefront.home', $second))
            ->assertOk()
            ->assertSee('Repère unique de la deuxième boutique')
            ->assertDontSee('Repère unique de la première boutique');

        $originalSlug = $first->slug;
        $first->update(['name' => 'Matrix Boutique renommée']);
        $this->assertSame($originalSlug, $first->fresh()->slug);
    }

    public function test_public_catalog_is_paginated_searchable_and_checkout_does_not_embed_every_product(): void
    {
        $company = Company::create([
            'name' => 'Boutique volumineuse',
            'email' => 'volume@test.local',
            'number1' => '300',
            'ecommerce_active' => true,
        ]);
        app(CompanyContext::class)->setPublicCompany($company);
        $category = Category::create(['name' => 'Catalogue', 'status' => 1, 'created_by' => 1]);

        foreach (range(1, 14) as $number) {
            Product::create([
                'category_id' => $category->id,
                'name' => 'Article vitrine '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'qte' => 10,
                'price' => 1000,
                'purchase_price' => 500,
                'profit' => 500,
                'margin' => 50,
                'type' => 1,
                'status' => 1,
                'created_by' => 1,
            ]);
        }

        $firstCategoryPage = $this->get(route('storefront.category', [$company, $category->id]))
            ->assertOk()
            ->assertSee('14 produit(s)')
            ->assertSee('Article vitrine 14')
            ->assertDontSee('Article vitrine 01');
        $this->assertStringContainsString('page=2', $firstCategoryPage->getContent());

        $this->get(route('storefront.products', [$company, 'q' => 'Article vitrine 01']))
            ->assertOk()
            ->assertSee('Article vitrine 01')
            ->assertDontSee('Article vitrine 02');

        $this->get(route('storefront.checkout', $company))
            ->assertOk()
            ->assertDontSee('Article vitrine 14')
            ->assertSee('var CHECKOUT_PRODUCT_IMAGES = {};', false);
    }
}
