<?php

namespace Tests\Feature;

use App\Jobs\SendEcommerceOrderEmailJob;
use App\Mail\EcommerceOrderNotification;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\EcommerceManager;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EcommerceOrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_uses_server_prices_without_decreasing_stock_and_commits_notification_together(): void
    {
        Queue::fake();
        $company = $this->company('alpha', 'Repère Alpha');
        $first = $this->product($company, 'Produit A', 1500, 10);
        $second = $this->product($company, 'Produit B', 2750, 8);

        $response = $this->postJson(route('storefront.order.place', $company), $this->payload([
            ['product_id' => $first->id, 'quantity' => 2, 'price' => 1, 'name' => 'Prix falsifié'],
            ['product_id' => $second->id, 'quantity' => 1, 'price' => 1],
        ]))->assertOk()->assertJson(['status' => true]);

        $order = Order::withoutCompanyScope()->with('items')->where('code', $response->json('code'))->firstOrFail();
        $this->assertSame($company->id, $order->company_id);
        $this->assertEquals(5750, $order->subtotal);
        $this->assertEquals(5750, $order->total);
        $this->assertCount(2, $order->items);
        $this->assertSame($company->id, $order->items->first()->company_id);
        $this->assertEquals(1500, $order->items->firstWhere('product_id', $first->id)->unit_price);
        $this->assertSame('Produit A', $order->items->firstWhere('product_id', $first->id)->product_name);
        $this->assertSame(10, (int) $first->fresh()->qte);
        $this->assertSame(8, (int) $second->fresh()->qte);
        Queue::assertPushed(SendEcommerceOrderEmailJob::class, fn ($job) =>
            $job->orderId === $order->id && $job->companyId === $company->id
        );
    }

    public function test_foreign_product_and_ambiguous_legacy_shop_order_are_rejected(): void
    {
        Queue::fake();
        $companyA = $this->company('company-a', 'Compagnie A');
        $companyB = $this->company('company-b', 'Compagnie B');
        $foreignProduct = $this->product($companyB, 'Produit étranger', 1000, 5);

        $this->postJson(route('storefront.order.place', $companyA), $this->payload([
            ['product_id' => $foreignProduct->id, 'quantity' => 1],
        ]))->assertUnprocessable()->assertJson(['status' => false]);

        $this->postJson(route('shop.order.place'), $this->payload([
            ['product_id' => $foreignProduct->id, 'quantity' => 1],
        ]))->assertNotFound()->assertJson(['status' => false]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, (int) $foreignProduct->fresh()->qte);
        Queue::assertNothingPushed();
    }

    public function test_customer_can_share_a_safe_google_maps_location(): void
    {
        Queue::fake();
        $company = $this->company('delivery-location', 'Livraison localisée');
        $product = $this->product($company, 'Produit livré', 2000, 4);

        $this->postJson(route('storefront.order.place', $company), array_merge($this->payload([
            ['product_id' => $product->id, 'quantity' => 1],
        ]), [
            'delivery_latitude' => 6.1319444,
            'delivery_longitude' => 1.2227778,
            'delivery_location_url' => 'https://www.google.com/maps/search/?api=1&query=0,0',
        ]))->assertOk()->assertJson(['status' => true]);

        $order = Order::withoutCompanyScope()->firstOrFail();
        $this->assertSame(6.1319444, $order->delivery_latitude);
        $this->assertSame(1.2227778, $order->delivery_longitude);
        $this->assertSame(
            'https://www.google.com/maps/search/?api=1&query=6.1319444,1.2227778',
            $order->delivery_location_url
        );
        $this->assertSame(4, (int) $product->fresh()->qte);
    }

    public function test_non_google_or_non_https_delivery_location_is_rejected(): void
    {
        Queue::fake();
        $company = $this->company('unsafe-location', 'Lien sécurisé');
        $product = $this->product($company, 'Produit test', 2000, 4);

        foreach (['javascript:alert(1)', 'http://maps.google.com/test', 'https://google.com.attacker.test/maps'] as $url) {
            $this->postJson(route('storefront.order.place', $company), array_merge($this->payload([
                ['product_id' => $product->id, 'quantity' => 1],
            ]), ['delivery_location_url' => $url]))
                ->assertUnprocessable()
                ->assertJson(['status' => false]);
        }

        $this->assertDatabaseCount('orders', 0);
        Queue::assertNothingPushed();
    }

    public function test_insufficient_stock_rolls_back_the_entire_order(): void
    {
        Queue::fake();
        $company = $this->company('rollback', 'Compagnie transactionnelle');
        $available = $this->product($company, 'Produit disponible', 1000, 5);
        $insufficient = $this->product($company, 'Produit insuffisant', 2000, 1);

        $this->postJson(route('storefront.order.place', $company), $this->payload([
            ['product_id' => $available->id, 'quantity' => 2],
            ['product_id' => $insufficient->id, 'quantity' => 2],
        ]))->assertUnprocessable()->assertJson(['status' => false]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(5, (int) $available->fresh()->qte);
        $this->assertSame(1, (int) $insufficient->fresh()->qte);
        Queue::assertNothingPushed();
    }

    public function test_styled_email_is_sent_only_to_active_managers_of_the_order_company(): void
    {
        Mail::fake();
        $companyA = $this->company('mail-a', 'Entreprise Mail A');
        $companyB = $this->company('mail-b', 'Entreprise Mail B');
        $managerA = $this->manager($companyA, 'manager-a@test.local', true);
        $this->manager($companyA, 'inactive-a@test.local', false);
        $this->manager($companyB, 'manager-b@test.local', true);
        $product = $this->product($companyA, 'Produit e-mail', 3500, 5);
        $order = Order::withoutCompanyScope()->create([
            'company_id' => $companyA->id,
            'code' => 'CMD-MAIL-001',
            'customer_name' => 'Client Test',
            'customer_phone' => '90000000',
            'delivery_location_url' => 'https://maps.app.goo.gl/AbCdEf123456',
            'subtotal' => 3500,
            'tax' => 0,
            'total' => 3500,
            'status' => 'pending',
        ]);
        $order->items()->create([
            'company_id' => $companyA->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 3500,
            'total_price' => 3500,
        ]);

        (new SendEcommerceOrderEmailJob($order->id, $companyA->id))->handle();

        Mail::assertSent(EcommerceOrderNotification::class, function ($mail) use ($managerA, $companyA) {
            $mail->build();

            return $mail->hasTo($managerA->email)
                && $mail->hasFrom(config('mail.from.address'), $companyA->name);
        });
        Mail::assertSentCount(1);

        $html = (new EcommerceOrderNotification($order->load('items'), $companyA))->render();
        $this->assertStringContainsString('Entreprise Mail A', $html);
        $this->assertStringContainsString('CMD-MAIL-001', $html);
        $this->assertStringContainsString('Produit e-mail', $html);
        $this->assertStringContainsString('Cet e-mail vous est envoyé par', $html);
        $this->assertStringContainsString(config('app.name'), $html);
        $this->assertStringContainsString((string) now()->year, $html);
        $this->assertStringContainsString('Consulter la commande', $html);
        $this->assertStringContainsString('Ouvrir dans Google Maps', $html);
        $this->assertStringContainsString('https://maps.app.goo.gl/AbCdEf123456', $html);
    }

    private function company(string $slug, string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => $slug,
            'email' => $slug.'@test.local',
            'number1' => '100',
            'ecommerce_active' => true,
        ]);
    }

    private function product(Company $company, string $name, float $price, int $quantity): Product
    {
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Catégorie '.$name,
            'status' => 1,
            'created_by' => 1,
        ]);

        return Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => $name,
            'qte' => $quantity,
            'price' => $price,
            'purchase_price' => 500,
            'profit' => max($price - 500, 0),
            'margin' => 10,
            'type' => 1,
            'status' => 1,
            'created_by' => 1,
        ]);
    }

    private function manager(Company $company, string $email, bool $active): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'user_type' => 3,
            'status' => 1,
        ]);
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Manager '.uniqid(),
            'key' => 'manager-'.uniqid(),
            'is_system' => false,
        ]);
        CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => $active ? 'active' : 'inactive',
            'joined_at' => now(),
        ]);
        EcommerceManager::create(['company_id' => $company->id, 'user_id' => $user->id]);

        return $user;
    }

    private function payload(array $cart): array
    {
        return [
            'customer_name' => 'Client Test',
            'customer_phone' => '90000000',
            'customer_email' => 'client@test.local',
            'customer_address' => 'Adresse client',
            'notes' => 'Livraison rapide',
            'cart' => json_encode($cart),
        ];
    }
}
