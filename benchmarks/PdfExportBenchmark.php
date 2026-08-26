<?php

namespace Tests\Performance;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\CompanyProvisioner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PdfExportBenchmark extends TestCase
{
    private array $metrics = [];

    public function test_large_pdf_exports_complete_with_bounded_time_and_memory(): void
    {
        $database = (string) DB::connection()->getDatabaseName();
        $this->assertStringEndsWith('_testing', $database, 'Benchmark refusé hors d’une base *_testing.');
        Artisan::call('migrate:fresh', ['--force' => true]);
        [$owner, $company] = $this->seedScenario();
        $this->actingAs($owner)->withSession(['active_company_id' => $company->id]);

        $this->get(route('product.export.pdf'))->assertRedirect()->assertSessionHas('error');
        $this->get(route('inventory.export.pdf'))->assertRedirect()->assertSessionHas('error');
        $this->get(route('history.export.pdf', [
            'daterange' => now()->subDay()->format('d-m-Y').' - '.now()->format('d-m-Y'),
        ]))->assertRedirect()->assertSessionHas('error');

        $categoryId = (int) DB::table('categories')->where('name', 'Exports volume 1')->value('id');

        $this->measure('produits_300', fn () => $this->get(route('product.export.pdf', ['category_id' => $categoryId])));
        $this->measure('inventaires_500', fn () => $this->get(route('inventory.export.pdf', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ])));
        $this->measure('ventes_100_details_200', fn () => $this->get(route('history.export.pdf', [
            'daterange' => now()->format('d-m-Y').' - '.now()->format('d-m-Y'),
        ])));

        foreach ($this->metrics as $metric) {
            $this->assertLessThan(60000, $metric['wall_ms'], $metric['name'].' dépasse 60 secondes.');
            $this->assertGreaterThan(1000, $metric['pdf_bytes'], $metric['name'].' ne produit pas un PDF valide.');
        }
        $this->assertLessThan(450, memory_get_peak_usage(true) / 1024 / 1024, 'Le pic mémoire dépasse 450 Mo.');

        fwrite(STDOUT, PHP_EOL.'SAAS_PDF_EXPORT_BENCHMARK='.json_encode([
            'database' => $database,
            'volumes' => [
                'products' => 3000,
                'inventories' => 5000,
                'sales' => 1000,
                'sale_details' => 2000,
            ],
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'exports' => $this->metrics,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    private function seedScenario(): array
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = Company::create([
            'name' => 'Entreprise exports volume',
            'email' => 'pdf-volume@test.local',
            'number1' => '000000000',
            'sale_email_enabled' => false,
            'sale_whatsapp_enabled' => false,
            'sale_sms_enabled' => false,
        ]);
        $membership = app(CompanyProvisioner::class)->provision($company, $owner, 0);
        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));
        $now = now()->format('Y-m-d H:i:s');

        $categoryIds = [];
        foreach (range(1, 10) as $number) {
            $categoryIds[] = DB::table('categories')->insertGetId([
                'company_id' => $company->id,
                'name' => 'Exports volume '.$number,
                'created_by' => $owner->id,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->insertGenerated('products', 3000, fn (int $number) => [
            'company_id' => $company->id,
            'category_id' => $categoryIds[($number - 1) % 10],
            'name' => 'Produit export '.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
            'qte' => 100,
            'margin' => 10,
            'price' => 1500,
            'price_ttc' => 1500,
            'purchase_price' => 1000,
            'profit' => 500,
            'status' => 1,
            'email' => 0,
            'type' => 1,
            'created_by' => $owner->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $firstProductId = (int) DB::table('products')->where('company_id', $company->id)->min('id');

        $this->insertGenerated('inventories', 5000, fn (int $number) => [
            'company_id' => $company->id,
            'product_id' => $firstProductId + (($number - 1) % 3000),
            'type' => $number % 2 === 0 ? 1 : 2,
            'qte_before' => 90,
            'qte_added' => 10,
            'qte_after' => 100,
            'note' => null,
            'created_by' => $owner->id,
            'created_at' => $number <= 500 ? $now : now()->subDay()->format('Y-m-d H:i:s'),
            'updated_at' => $number <= 500 ? $now : now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $this->insertGenerated('sales', 1000, fn (int $number) => [
            'company_id' => $company->id,
            'code' => 9000000 + $number,
            'received_amount' => 3000,
            'total_amount' => 3000,
            'remaining_amount' => 0,
            'total_profit' => 1000,
            'discount' => 0,
            'amount_init' => 3000,
            'tax_amount' => 0,
            'cashier' => $owner->name,
            'created_at' => $number <= 100 ? $now : now()->subDay()->format('Y-m-d H:i:s'),
            'updated_at' => $number <= 100 ? $now : now()->subDay()->format('Y-m-d H:i:s'),
        ]);
        $firstSaleId = (int) DB::table('sales')->where('company_id', $company->id)->min('id');
        $this->insertGenerated('sale_details', 2000, fn (int $number) => [
            'company_id' => $company->id,
            'sale_id' => $firstSaleId + (($number - 1) % 1000),
            'product_id' => $firstProductId + (($number - 1) % 3000),
            'quantity' => 1,
            'unit_price' => 1500,
            'total_price' => 1500,
            'profit' => 500,
            'created_at' => ((($number - 1) % 1000) + 1) <= 100 ? $now : now()->subDay()->format('Y-m-d H:i:s'),
            'updated_at' => ((($number - 1) % 1000) + 1) <= 100 ? $now : now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        return [$owner, $company];
    }

    private function insertGenerated(string $table, int $count, callable $factory): void
    {
        for ($offset = 1; $offset <= $count; $offset += 500) {
            $rows = [];
            foreach (range($offset, min($offset + 499, $count)) as $number) {
                $rows[] = $factory($number);
            }
            DB::table($table)->insert($rows);
        }
    }

    private function measure(string $name, callable $request): void
    {
        gc_collect_cycles();
        $memoryBefore = memory_get_usage(true);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $startedAt = microtime(true);
        $response = $request();
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $wallMs = round((microtime(true) - $startedAt) * 1000, 2);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $content = $response->getContent();

        $this->metrics[] = [
            'name' => $name,
            'wall_ms' => $wallMs,
            'queries' => count($queries),
            'sql_ms' => round(array_sum(array_column($queries, 'time')), 2),
            'memory_delta_mb' => round((memory_get_usage(true) - $memoryBefore) / 1024 / 1024, 2),
            'pdf_bytes' => strlen((string) $content),
        ];
        unset($content, $response);
        gc_collect_cycles();
    }
}
