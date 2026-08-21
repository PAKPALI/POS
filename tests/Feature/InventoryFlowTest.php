<?php

namespace Tests\Feature;

use App\Models\Action;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Jobs\SendInventoryWhatsappJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Concerns\InteractsWithCompanies;

class InventoryFlowTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Product $product;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'inventory-' . Str::random(8) . '@test.com',
            'password' => bcrypt('password'),
            'user_type' => '1',
            'status' => '1',
        ]);
        $this->activateCompanyFor($this->user, 'inventory');

        $this->category = Category::create([
            'name' => 'Test Category',
            'created_by' => $this->user->id,
            'status' => '1',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'qte' => 50,
            'price' => 5000,
            'purchase_price' => 3000,
            'profit' => 2000,
            'margin' => 10,
            'type' => 1,
            'status' => '1',
            'created_by' => $this->user->id,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'created_by' => $this->user->id,
            'status' => '1',
        ]);
    }

    public function test_inventory_notification_job_keeps_active_company_context(): void
    {
        Queue::fake();

        $this->actingAs($this->user)->postJson('/component/inventory', [
            'product_id' => $this->product->id,
            'qte_added' => 5,
            'supplier_id' => $this->supplier->id,
            'note' => 'Vérification contexte tenant',
        ])->assertJson(['status' => true]);

        Queue::assertPushed(
            SendInventoryWhatsappJob::class,
            fn ($job) => $job->companyId === $this->company->id
        );
    }

    public function test_disabled_inventory_channels_prevent_whatsapp_and_sms_requests(): void
    {
        Http::fake();
        $this->user->update(['phone' => '90000000']);
        $this->company->update(['inventory_whatsapp_enabled' => false, 'inventory_sms_enabled' => false]);
        $inventory = Inventory::create([
            'product_id' => $this->product->id, 'supplier_id' => $this->supplier->id,
            'type' => 1, 'qte_before' => 50, 'qte_added' => 1, 'qte_after' => 51,
            'note' => 'Notification désactivée', 'created_by' => $this->user->id,
        ]);

        (new SendInventoryWhatsappJob($inventory->id, $this->company->id))->handle();

        Http::assertNothingSent();
    }

    /** Test: Stock entry increases product quantity */
    public function test_stock_entry_increases_quantity(): void
    {
        $initialQte = $this->product->qte;

        $response = $this->actingAs($this->user)->postJson('/component/inventory', [
            'product_id' => $this->product->id,
            'qte_added' => 20,
            'supplier_id' => $this->supplier->id,
            'note' => 'Test entry',
        ]);

        $response->assertJson(['status' => true]);
        $this->product->refresh();
        $this->assertEquals($initialQte + 20, $this->product->qte);
    }

    /** Test: Stock entry creates inventory record */
    public function test_stock_entry_creates_record(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory', [
            'product_id' => $this->product->id,
            'qte_added' => 15,
            'supplier_id' => $this->supplier->id,
            'note' => 'Test inventory record',
        ])->assertJson(['status' => true]);

        $inventory = Inventory::latest()->first();
        $this->assertNotNull($inventory);
        $this->assertEquals(1, $inventory->type);
        $this->assertEquals(50, $inventory->qte_before);
        $this->assertEquals(15, $inventory->qte_added);
        $this->assertEquals(65, $inventory->qte_after);
    }

    /** Test: Stock removal decreases product quantity */
    public function test_stock_removal_decreases_quantity(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory-remove', [
            'product_id' => $this->product->id,
            'qte_removed' => 10,
            'note' => 'Test removal',
        ])->assertJson(['status' => true]);

        $this->product->refresh();
        $this->assertEquals(40, $this->product->qte);
    }

    /** Test: Stock removal creates inventory record */
    public function test_stock_removal_creates_record(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory-remove', [
            'product_id' => $this->product->id,
            'qte_removed' => 5,
            'note' => 'Test removal record',
        ])->assertJson(['status' => true]);

        $inventory = Inventory::latest()->first();
        $this->assertNotNull($inventory);
        $this->assertEquals(2, $inventory->type);
        $this->assertEquals(50, $inventory->qte_before);
        $this->assertEquals(5, $inventory->qte_added);
        $this->assertEquals(45, $inventory->qte_after);
    }

    /** Test: Stock removal creates action log */
    public function test_stock_removal_creates_action(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory-remove', [
            'product_id' => $this->product->id,
            'qte_removed' => 3,
            'note' => 'Test action log',
        ])->assertJson(['status' => true]);

        $action = Action::where('function', 'SORTIE STOCK')->latest()->first();
        $this->assertNotNull($action);
        $this->assertEquals($this->user->id, $action->user_id);
    }

    /** Test: Stock entry creates action log */
    public function test_stock_entry_creates_action(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory', [
            'product_id' => $this->product->id,
            'qte_added' => 10,
            'supplier_id' => $this->supplier->id,
            'note' => 'Test entry action',
        ])->assertJson(['status' => true]);

        $action = Action::where('function', 'ENTREE STOCK')->latest()->first();
        $this->assertNotNull($action);
        $this->assertEquals($this->user->id, $action->user_id);
    }

    /**
     * DOCUMENTED BUG: Stock removal with insufficient quantity allows negative stock.
     * The controller does not check if qte_removed <= product->qte.
     */
    public function test_stock_removal_rejects_insufficient_quantity(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory-remove', [
            'product_id' => $this->product->id,
            'qte_removed' => 100,
            'note' => 'Test insufficient',
        ])->assertStatus(422)->assertJson(['status' => false]);

        $this->product->refresh();
        $this->assertEquals(50, $this->product->qte);
    }

    /** Test: Inventory list shows entries and exits */
    public function test_inventory_list(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory', [
            'product_id' => $this->product->id,
            'qte_added' => 10,
            'note' => 'Entry test',
        ]);

        $this->actingAs($this->user)->postJson('/component/inventory-remove', [
            'product_id' => $this->product->id,
            'qte_removed' => 5,
            'note' => 'Exit test',
        ]);

        $inventories = Inventory::with('product', 'user')->get();
        $this->assertEquals(2, $inventories->count());
        $this->assertEquals(1, $inventories->where('type', 1)->count());
        $this->assertEquals(1, $inventories->where('type', 2)->count());
    }

    /** Test: Missing product_id fails validation */
    public function test_stock_entry_validation_missing_product(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory', [
            'qte_added' => 10,
        ])->assertJson(['status' => false]);
    }

    /** Test: Missing qte_added fails validation */
    public function test_stock_entry_validation_missing_quantity(): void
    {
        $this->actingAs($this->user)->postJson('/component/inventory', [
            'product_id' => $this->product->id,
        ])->assertJson(['status' => false]);
    }
}
