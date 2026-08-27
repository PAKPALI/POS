<?php

namespace Tests\Feature;

use App\Models\Action;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\AMS\Transaction;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\NotificationRecipient;
use App\Jobs\SendSaleEmailJob;
use App\Jobs\SendSaleWhatsappJob;
use App\Jobs\SendMarginEmailJob;
use App\Jobs\SendCustomerInvoiceJob;
use App\Services\NotificationRecipientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Concerns\InteractsWithCompanies;

class SalesFlowTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Product $product;
    protected CashAccount $mainCash;
    protected CashAccount $taxCash;
    protected Setting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'sale-' . Str::random(8) . '@test.com',
            'password' => bcrypt('password'),
            'user_type' => '1',
            'status' => '1',
        ]);
        $this->activateCompanyFor($this->user, 'sales');

        $this->category = Category::create([
            'name' => 'Test Category',
            'created_by' => $this->user->id,
            'status' => '1',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'qte' => 100,
            'price' => 5000,
            'purchase_price' => 3000,
            'profit' => 2000,
            'margin' => 10,
            'type' => 1,
            'status' => '1',
            'created_by' => $this->user->id,
        ]);

        $this->mainCash = CashAccount::create([
            'name' => 'Caisse Principale',
            'code' => 'CP-' . Str::random(4),
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 1,
            'is_tax' => 0,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $this->taxCash = CashAccount::create([
            'name' => 'Caisse Taxe',
            'code' => 'CT-' . Str::random(4),
            'balance' => 0,
            'currency' => 'FCFA',
            'is_default' => 0,
            'is_tax' => 1,
            'status' => 1,
            'created_by' => $this->user->id,
        ]);

        $this->setting = Setting::create([
            'default_cash_id' => $this->mainCash->id,
            'tax_cash_id' => $this->taxCash->id,
            'default_tax' => 18.00,
        ]);
    }

    private function makeSale(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)->postJson('/pos/sale', array_merge([
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => $this->product->price,
                    'total_price' => $this->product->price * 2,
                ],
            ],
            'total_amount' => $this->product->price * 2,
            'received_amount' => $this->product->price * 2,
        ], $overrides));
    }

    public function test_sale_notification_jobs_keep_active_company_context(): void
    {
        Queue::fake();

        $this->makeSale()->assertJson(['status' => true]);

        Queue::assertPushed(SendSaleEmailJob::class, fn ($job) => $job->companyId === $this->company->id);
        Queue::assertPushed(SendSaleWhatsappJob::class, fn ($job) => $job->companyId === $this->company->id);
    }

    public function test_sale_whatsapp_notification_uses_provider_template_endpoint(): void
    {
        Queue::fake();
        Http::fake(fn () => Http::response(['status' => true, 'message_id' => 'wa-sale-123'], 200));
        $this->user->update(['phone' => '90000000', 'country_code' => 'TG']);
        $this->company->update(['sale_whatsapp_enabled' => true, 'sale_sms_enabled' => false, 'whatsapp_count' => 2]);
        NotificationRecipient::updateOrCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'category' => 'sale',
        ], [
            'email_enabled' => false,
            'whatsapp_enabled' => true,
            'sms_enabled' => false,
        ]);

        $this->makeSale()->assertJson(['status' => true]);
        $sale = Sale::latest()->firstOrFail();
        (new SendSaleWhatsappJob($sale->id, $this->company->id))->handle();

        Http::assertSentCount(1);
        [$request] = Http::recorded()->first();
        $this->assertStringEndsWith('whatsapp/template/text-message', $request->url());
        $this->assertSame('TG', $request['country']);
        $this->assertSame('90000000', (string) $request['phone_number']);
        $this->assertStringContainsString($this->company->name, $request['title']);
        $this->assertStringContainsString('['.$this->company->name.']', $request['content']);
        $this->assertSame(config('services.kprimesms.response_url'), $request['response_url']);
        $this->assertSame(1, (int) $this->company->fresh()->whatsapp_count);
        $this->assertDatabaseHas('communication_logs', [
            'company_id' => $this->company->id,
            'channel' => 'whatsapp',
            'function' => 'sale',
            'recipient' => '90000000',
        ]);
        $this->assertDatabaseHas('notification_deliveries', [
            'company_id' => $this->company->id,
            'event_key' => (string) $sale->id,
            'channel' => 'whatsapp',
            'user_id' => $this->user->id,
            'status' => 'sent',
        ]);
    }

    public function test_sale_with_client_queues_each_authorized_invoice_channel_independently(): void
    {
        Queue::fake();
        $client = Client::create(['name' => 'Client facture', 'phone' => '90000000', 'created_by' => $this->user->id]);

        $this->makeSale(['client_id' => $client->id])->assertJson(['status' => true, 'hasClient' => true]);

        Queue::assertPushed(SendCustomerInvoiceJob::class, 2);
        Queue::assertPushed(SendCustomerInvoiceJob::class, fn ($job) => $job->channel === 'whatsapp');
        Queue::assertPushed(SendCustomerInvoiceJob::class, fn ($job) => $job->channel === 'sms');
    }

    public function test_manual_sms_invoice_checks_authorization_and_consumes_quota(): void
    {
        Queue::fake();
        Http::fake(fn () => Http::response(['status' => true, 'message' => 'MESSAGE_SENT_SUCCESSFULLY'], 200));
        $this->company->update(['invoice_sms_enabled' => true, 'sms_count' => 1]);
        $this->makeSale()->assertJson(['status' => true]);
        $sale = Sale::latest()->firstOrFail();

        $this->actingAs($this->user)->postJson(route('sale.send-invoice', $sale), [
            'phone' => '90000000', 'country_code' => 'BJ', 'whatsapp' => false, 'sms' => true,
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertSame(0, (int) $this->company->fresh()->sms_count);
        Http::assertSent(fn ($request) => $request['country'] === 'BJ' && $request['phone_number'] === '90000000');
        $this->assertDatabaseHas('communication_logs', [
            'company_id' => $this->company->id, 'channel' => 'sms', 'function' => 'invoice',
            'recipient' => '90000000', 'country_code' => 'BJ', 'units' => 1,
        ]);
    }

    public function test_whatsapp_invoice_uses_documented_endpoints_and_country_payload(): void
    {
        Queue::fake();
        Http::fakeSequence()
            ->push(['status' => true, 'media_id' => 'media-123'], 200)
            ->push(['status' => true, 'message_id' => 'wa-123'], 200);
        $this->company->update(['invoice_whatsapp_enabled' => true, 'whatsapp_count' => 1]);
        $this->makeSale()->assertJson(['status' => true]);
        $sale = Sale::latest()->firstOrFail();

        $this->actingAs($this->user)->postJson(route('sale.send-invoice', $sale), [
            'phone' => '0700000000', 'country_code' => 'CI', 'whatsapp' => true, 'sms' => false,
        ])->assertJson(['status' => true, 'whatsappQuota' => 0])->assertOk();

        Http::assertSent(fn ($request) => isset($request['media_id']) && $request['media_id'] === 'media-123'
            && $request['country'] === 'CI'
            && $request['phone_number'] === '0700000000'
            && $request['response_url'] === config('services.kprimesms.response_url'));
    }

    public function test_disabled_sale_channels_prevent_whatsapp_and_sms_requests(): void
    {
        Queue::fake();
        Http::fake();
        $this->user->update(['phone' => '90000000']);
        $this->company->update(['sale_whatsapp_enabled' => false, 'sale_sms_enabled' => false]);
        $this->makeSale()->assertJson(['status' => true]);
        $sale = Sale::latest()->firstOrFail();

        (new SendSaleWhatsappJob($sale->id, $this->company->id))->handle();

        Http::assertNothingSent();
    }

    public function test_disabled_email_channels_skip_sale_and_inventory_recipients(): void
    {
        Queue::fake();
        $this->company->update([
            'sale_email_enabled' => false,
            'inventory_email_enabled' => false,
        ]);
        $this->makeSale()->assertJson(['status' => true]);
        $sale = Sale::latest()->firstOrFail();

        $this->mock(NotificationRecipientService::class)
            ->shouldNotReceive('users');

        (new SendSaleEmailJob($sale->id, $this->company->id))->handle();
        (new SendMarginEmailJob('Test Product', 10, 9, $this->company->id))->handle();
    }

    public function test_margin_alert_is_dispatched_only_when_stock_crosses_the_threshold(): void
    {
        Queue::fake();

        $this->makeSale([
            'products' => [[
                'product_id' => $this->product->id,
                'quantity' => 90,
                'unit_price' => 5000,
                'total_price' => 450000,
            ]],
            'total_amount' => 450000,
            'received_amount' => 450000,
        ])->assertJson(['status' => true]);

        $this->makeSale([
            'products' => [[
                'product_id' => $this->product->id,
                'quantity' => 1,
                'unit_price' => 5000,
                'total_price' => 5000,
            ]],
            'total_amount' => 5000,
            'received_amount' => 5000,
        ])->assertJson(['status' => true]);

        Queue::assertPushed(SendMarginEmailJob::class, 1);
    }

    /** Creating a sale decreases product quantity */
    public function test_sale_decreases_product_quantity(): void
    {
        $initialQte = $this->product->qte;

        $this->makeSale([
            'products' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 5000, 'total_price' => 25000],
            ],
            'total_amount' => 25000,
            'received_amount' => 25000,
        ])->assertJson(['status' => true]);

        $this->product->refresh();
        $this->assertEquals($initialQte - 5, $this->product->qte);
    }

    /** Creating a sale creates sale record with correct amounts */
    public function test_sale_creates_correct_record(): void
    {
        $this->makeSale()->assertJson(['status' => true]);

        $sale = Sale::latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals(10000, $sale->total_amount);
        $this->assertEquals($this->user->name, $sale->cashier);
    }

    /** Creating a sale creates sale details */
    public function test_sale_creates_details(): void
    {
        $this->makeSale()->assertJson(['status' => true]);

        $sale = Sale::latest()->first();
        $this->assertEquals(1, $sale->saleDetails->count());

        $detail = $sale->saleDetails->first();
        $this->assertEquals($this->product->id, $detail->product_id);
        $this->assertEquals(2, $detail->quantity);
    }

    /** Sale updates main cash account balance */
    public function test_sale_updates_main_cash_balance(): void
    {
        $this->makeSale([
            'total_amount' => 10000,
            'received_amount' => 10000,
            'products' => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 5000, 'total_price' => 10000],
            ],
        ])->assertJson(['status' => true]);

        $this->mainCash->refresh();
        $expectedNet = round(10000 / (1 + (18 / 100)), 2);
        $this->assertEquals($expectedNet, $this->mainCash->balance);
    }

    /** Sale updates tax cash account when tax > 0 */
    public function test_sale_updates_tax_cash_balance(): void
    {
        $this->makeSale([
            'total_amount' => 10000,
            'received_amount' => 10000,
            'products' => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 5000, 'total_price' => 10000],
            ],
        ])->assertJson(['status' => true]);

        $this->taxCash->refresh();
        $expectedTax = round(10000 - (10000 / (1 + (18 / 100))), 2);
        $this->assertEquals($expectedTax, $this->taxCash->balance);
    }

    /** Sale creates transaction records */
    public function test_sale_creates_transactions(): void
    {
        $this->makeSale()->assertJson(['status' => true]);

        $transactions = Transaction::where('to_cash_id', $this->mainCash->id)->get();
        $this->assertGreaterThanOrEqual(1, $transactions->count());
        $this->assertEquals('IN', $transactions->first()->type);
    }

    /**
     * BUG P0: Stock insuffisant ne bloque PAS la vente.
     * updateProductQuantity() appelle DB::rollBack() puis retourne un Response,
     * mais le retour n'est PAS capturé par processProducts().
     * Le DB::rollBack() interrompt la transaction externe de store(),
     * puis DB::commit() est un no-op. La vente "réussit" côté code (status: true)
     * mais la quantité du produit reste à 2 (pas de mise à jour).
     *
     * Comportement réel : status = true, produit reste à qte=2,
     * mais la transaction DB est corrompue.
     */
    public function test_sale_fails_with_insufficient_stock(): void
    {
        $this->product->update(['qte' => 2]);

        $response = $this->makeSale([
            'products' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 5000, 'total_price' => 25000],
            ],
            'total_amount' => 25000,
            'received_amount' => 25000,
        ]);

        // BUG: Le controller retourne status=true même avec stock insuffisant
        // car DB::rollBack() dans updateProductQuantity corrompt la transaction
        // mais le code continue d'exécuter et retourne un succès.
        $response->assertJson(['status' => false]);

        // La quantité ne change pas car l'update est dans la branche if() qui n'est pas exécutée
        $this->product->refresh();
        $this->assertEquals(2, $this->product->qte);
        $this->assertDatabaseCount('sales', 0);
    }

    /** Sale with discount applies correctly */
    public function test_sale_with_discount(): void
    {
        $response = $this->makeSale([
            'total_amount' => 9000,
            'received_amount' => 9000,
            'discount' => 1000,
        ]);

        $response->assertJson(['status' => true]);
        $sale = Sale::latest()->first();
        $this->assertEquals(1000, $sale->discount);
        $this->assertEquals(9000, $sale->total_amount);
    }

    /** Sale creates action log */
    public function test_sale_creates_action_log(): void
    {
        $this->makeSale()->assertJson(['status' => true]);

        $action = Action::where('function', 'VENTE')->latest()->first();
        $this->assertNotNull($action);
        $this->assertEquals($this->user->id, $action->user_id);
    }

    /** Sale with client links correctly */
    public function test_sale_with_client(): void
    {
        $client = Client::create([
            'name' => 'Test Client',
            'created_by' => $this->user->id,
            'status' => '1',
        ]);

        $this->makeSale(['client_id' => $client->id])->assertJson(['status' => true]);

        $sale = Sale::latest()->first();
        $this->assertEquals($client->id, $sale->client_id);
    }

    /** Sale total profit is calculated correctly */
    public function test_sale_profit_calculation(): void
    {
        $this->makeSale()->assertJson(['status' => true]);

        $sale = Sale::latest()->first();
        $this->assertEquals(2000 * 2, $sale->total_profit);
    }
}
