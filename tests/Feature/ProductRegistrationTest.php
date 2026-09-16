<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_registration_allows_optional_values_and_calculates_profit_on_the_server(): void
    {
        [$user, $company, $category] = $this->companyContext();

        $this->postJson(route('product.store'), [
            'type' => 1,
            'category' => $category->id,
            'name' => 'Produit sans prix achat',
            'qte' => 10,
            'margin' => 2,
            'price' => 1000,
            'profit' => 9999,
        ])->assertOk()->assertJson(['status' => true]);

        $product = Product::where('name', 'Produit sans prix achat')->firstOrFail();
        $this->assertSame(10, (int) $product->qte);
        $this->assertNull($product->purchase_price);
        $this->assertNull($product->profit);
        $this->assertDatabaseHas('inventories', [
            'company_id' => $company->id,
            'product_id' => $product->id,
            'type' => 1,
            'qte_before' => 0,
            'qte_added' => 10,
            'qte_after' => 10,
        ]);

        $this->postJson(route('product.store'), [
            'type' => 1,
            'category' => $category->id,
            'name' => 'Marge trop haute',
            'qte' => 10,
            'margin' => 10,
            'price' => 1000,
        ])->assertOk()
            ->assertJson(['status' => false])
            ->assertJsonPath('msg', 'La marge de sécurité doit être strictement inférieure à la quantité disponible.');

        $this->postJson(route('product.store'), [
            'type' => 1,
            'category' => $category->id,
            'name' => 'Produit avec prix achat',
            'qte' => 8,
            'margin' => 2,
            'price' => 1000,
            'purchase_price' => 600,
            'profit' => 1,
        ])->assertOk()->assertJson(['status' => true]);

        $pricedProduct = Product::where('name', 'Produit avec prix achat')->firstOrFail();
        $this->assertSame(600, (int) $pricedProduct->purchase_price);
        $this->assertSame(400, (int) $pricedProduct->profit);
    }

    public function test_product_update_allows_removing_purchase_price_and_recalculates_profit(): void
    {
        [$user, , $category] = $this->companyContext();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produit à modifier',
            'qte' => 10,
            'margin' => 2,
            'price' => 1000,
            'purchase_price' => 600,
            'profit' => 400,
            'type' => 1,
            'status' => '1',
            'created_by' => $user->id,
        ]);

        $basePayload = [
            'category' => $category->id,
            'name' => $product->name,
            'price' => 1200,
        ];

        $this->putJson(route('product.update', $product->id), $basePayload + [
            'margin' => 10,
        ])->assertOk()->assertJson(['status' => false]);

        $this->putJson(route('product.update', $product->id), $basePayload + [
            'margin' => 2,
            'profit' => 9999,
        ])->assertOk()->assertJson(['status' => true]);

        $product->refresh();
        $this->assertNull($product->purchase_price);
        $this->assertNull($product->profit);

        $this->putJson(route('product.update', $product->id), $basePayload + [
            'margin' => 2,
            'purchase_price' => 700,
            'profit' => 0,
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertSame(500, (int) $product->fresh()->profit);
    }

    public function test_product_registration_notice_preference_is_persisted_per_user(): void
    {
        [$user] = $this->companyContext();

        $this->get(route('product.index'))
            ->assertOk()
            ->assertSee('productRegistrationNotice', false)
            ->assertSee('Ne plus me montrer');

        $this->postJson(route('profile.product-registration-notice.dismiss'))
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->assertTrue((bool) $user->fresh()->product_registration_notice_dismissed);

        $this->get(route('product.index'))
            ->assertOk()
            ->assertDontSee('Ne plus me montrer');
    }

    public function test_social_network_prompt_preferences_support_a_seven_day_snooze_and_permanent_hide(): void
    {
        [$user] = $this->companyContext();

        $this->postJson(route('profile.social-network-prompt.preference'), ['action' => 'snooze'])
            ->assertOk()->assertJson(['status' => true]);
        $this->assertNotNull($user->fresh()->social_network_prompt_snoozed_until);
        $this->assertTrue($user->fresh()->social_network_prompt_snoozed_until->between(now()->addDays(6), now()->addDays(8)));

        $this->postJson(route('profile.social-network-prompt.preference'), ['action' => 'hide'])
            ->assertOk()->assertJson(['status' => true]);
        $user->refresh();
        $this->assertTrue((bool) $user->social_network_prompt_hidden);
        $this->assertNull($user->social_network_prompt_snoozed_until);
    }

    private function companyContext(): array
    {
        $user = User::create([
            'name' => 'Propriétaire produit',
            'email' => 'product-'.uniqid().'@test.local',
            'password' => 'password',
            'user_type' => '2',
            'status' => '1',
        ]);
        $company = Company::create([
            'name' => 'Entreprise produit',
            'email' => 'company-'.uniqid().'@test.local',
            'number1' => '000000000',
        ]);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Propriétaire',
            'key' => 'owner',
            'is_system' => true,
        ]);
        $membership = CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));
        $this->actingAs($user)->withSession(['active_company_id' => $company->id]);

        $category = Category::create([
            'name' => 'Catégorie produit',
            'created_by' => $user->id,
        ]);

        return [$user, $company, $category];
    }
}
