<?php

namespace Tests\Feature;

use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class EcommerceOrderLifecycleTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    private User $owner;
    private Product $product;
    private CashAccount $mainCash;
    private CashAccount $taxCash;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->owner = User::factory()->create(['user_type' => 1, 'status' => 1]);
        $this->activateCompanyFor($this->owner, 'order-lifecycle');
        $this->company->update(['slug' => 'order-lifecycle', 'ecommerce_active' => true]);

        $category = Category::create([
            'name' => 'Commandes', 'status' => 1, 'created_by' => $this->owner->id,
        ]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produit commandé',
            'qte' => 10,
            'price' => 5000,
            'purchase_price' => 3000,
            'profit' => 2000,
            'margin' => 1,
            'type' => 1,
            'status' => 1,
            'created_by' => $this->owner->id,
        ]);
        $this->mainCash = CashAccount::create([
            'name' => 'Caisse principale', 'code' => 'CP-'.Str::random(8),
            'balance' => 0, 'currency' => 'FCFA', 'is_default' => 1,
            'is_tax' => 0, 'status' => 1, 'created_by' => $this->owner->id,
        ]);
        $this->taxCash = CashAccount::create([
            'name' => 'Caisse taxe', 'code' => 'CT-'.Str::random(8),
            'balance' => 0, 'currency' => 'FCFA', 'is_default' => 0,
            'is_tax' => 1, 'status' => 1, 'created_by' => $this->owner->id,
        ]);
        Setting::create([
            'default_cash_id' => $this->mainCash->id,
            'tax_cash_id' => $this->taxCash->id,
            'default_tax' => 18,
        ]);
    }

    public function test_executing_an_order_creates_one_sale_and_only_then_decreases_stock(): void
    {
        $order = $this->order(2);
        $this->assertSame(10, (int) $this->product->fresh()->qte);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_company_id' => $this->company->id])
            ->postJson(route('ecommerce.orders.execute', $order->id));

        $response->assertOk()->assertJson(['status' => true]);
        $order->refresh();
        $this->assertSame('converted', $order->status);
        $this->assertNotNull($order->sale_id);
        $this->assertSame($this->owner->id, $order->converted_by);
        $this->assertSame(8, (int) $this->product->fresh()->qte);
        $this->assertDatabaseHas('sales', ['id' => $order->sale_id, 'total_amount' => 10000]);
        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $order->sale_id, 'product_id' => $this->product->id, 'quantity' => 2,
        ]);
        $this->assertGreaterThan(0, (float) $this->mainCash->fresh()->balance);
        $this->assertGreaterThan(0, (float) $this->taxCash->fresh()->balance);

        $this->postJson(route('ecommerce.orders.execute', $order->id))
            ->assertUnprocessable()->assertJson(['status' => false]);
        $this->assertSame(1, Sale::count());
        $this->assertSame(8, (int) $this->product->fresh()->qte);
    }

    public function test_conversion_is_atomic_when_stock_is_no_longer_sufficient(): void
    {
        $order = $this->order(3);
        $this->product->update(['qte' => 1]);

        $this->actingAs($this->owner)
            ->withSession(['active_company_id' => $this->company->id])
            ->postJson(route('ecommerce.orders.execute', $order->id))
            ->assertUnprocessable()->assertJson(['status' => false]);

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->sale_id);
        $this->assertSame(1, (int) $this->product->fresh()->qte);
        $this->assertDatabaseCount('sales', 0);
        $this->assertEquals(0, (float) $this->mainCash->fresh()->balance);
    }

    public function test_cancelling_an_order_records_the_reason_without_changing_stock(): void
    {
        $order = $this->order(2);

        $this->actingAs($this->owner)
            ->withSession(['active_company_id' => $this->company->id])
            ->postJson(route('ecommerce.orders.cancel', $order->id), [
                'reason' => 'Le client n’a pas confirmé après notre appel.',
            ])->assertOk()->assertJson(['status' => true]);

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame($this->owner->id, $order->cancelled_by);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame('Le client n’a pas confirmé après notre appel.', $order->cancellation_reason);
        $this->assertSame(10, (int) $this->product->fresh()->qte);
        $this->assertDatabaseCount('sales', 0);

        $this->postJson(route('ecommerce.orders.execute', $order->id))
            ->assertUnprocessable()->assertJson(['status' => false]);
    }

    private function order(int $quantity): Order
    {
        $order = Order::create([
            'code' => 'CMD-'.Str::upper(Str::random(10)),
            'customer_name' => 'Client test',
            'customer_phone' => '90000000',
            'subtotal' => $this->product->price * $quantity,
            'tax' => 0,
            'total' => $this->product->price * $quantity,
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => $quantity,
            'unit_price' => $this->product->price,
            'total_price' => $this->product->price * $quantity,
        ]);

        return $order;
    }
}
