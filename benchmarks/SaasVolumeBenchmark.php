<?php

namespace Tests\Performance;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

/**
 * Benchmark volontairement exclu des suites PHPUnit habituelles.
 * Lancer explicitement : php artisan test benchmarks/SaasVolumeBenchmark.php
 */
class SaasVolumeBenchmark extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    private array $metrics = [];

    public function test_high_volume_tenant_routes_remain_bounded(): void
    {
        $database = (string) DB::connection()->getDatabaseName();
        $this->assertStringEndsWith('_testing', $database, 'Benchmark refusé hors d’une base *_testing.');

        $seedStartedAt = microtime(true);
        [$owner, $company] = $this->seedVolume();
        $seedDuration = round(microtime(true) - $seedStartedAt, 3);

        $this->actingAs($owner);

        $this->measure('dashboard_general', fn () => $this->get(route('dashboard'))->assertOk());
        $this->measure('pos_initial', fn () => $this->get(route('sale.index'))->assertOk());
        $this->measure('recherche_produits', fn () => $this->getJson(route('products.search', [
            'q' => 'Produit volume 099',
            'page' => 1,
        ]))->assertOk());
        $this->measure('recherche_clients', fn () => $this->getJson(route('clients.search', [
            'q' => 'Client volume 049',
            'page' => 1,
        ]))->assertOk());
        $this->measure('liste_utilisateurs', fn () => $this->getJson(route('user.index').'?draw=1&start=0&length=10', [
            'X-Requested-With' => 'XMLHttpRequest',
        ])->assertOk());
        $this->measure('liste_commandes', fn () => $this->getJson(route('ecommerce.orders.index').'?draw=1&start=0&length=10', [
            'X-Requested-With' => 'XMLHttpRequest',
        ])->assertOk());
        $this->measure('historique_ventes', fn () => $this->getJson(route('history').'?'.http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'daterange' => now()->format('d-m-Y').' - '.now()->format('d-m-Y'),
        ]), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk());

        foreach ($this->metrics as $metric) {
            $this->assertLessThan(2500, $metric['wall_ms'], $metric['name'].' dépasse 2,5 secondes.');
            $this->assertLessThan(40, $metric['queries'], $metric['name'].' exécute trop de requêtes.');
        }

        fwrite(STDOUT, PHP_EOL.'SAAS_VOLUME_BENCHMARK='.json_encode([
            'database' => $database,
            'company_id' => $company->id,
            'volumes' => [
                'companies' => 5,
                'users_active_company' => 50,
                'products_active_company' => 10000,
                'clients_active_company' => 5000,
                'sales_active_company' => 50000,
                'sale_details_active_company' => 100000,
                'orders_active_company' => 10000,
            ],
            'seed_seconds' => $seedDuration,
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'routes' => $this->metrics,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    private function seedVolume(): array
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'volume');
        $role = Role::where('company_id', $company->id)->where('key', 'owner')->firstOrFail();
        $now = now()->format('Y-m-d H:i:s');

        foreach (range(2, 5) as $number) {
            Company::create([
                'name' => 'Entreprise bruit '.$number,
                'email' => 'volume-company-'.$number.'@test.local',
                'number1' => '000000000',
            ]);
        }

        $users = User::factory()->count(49)->create(['user_type' => 3, 'status' => 1]);
        $this->insertChunks('company_user', $users->map(fn (User $user) => [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        DB::table('categories')->insert([
            'company_id' => $company->id,
            'name' => 'Catégorie volume',
            'created_by' => $owner->id,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $categoryId = (int) DB::table('categories')->where('company_id', $company->id)->value('id');

        $this->insertGenerated('products', 10000, fn (int $number) => [
            'company_id' => $company->id,
            'category_id' => $categoryId,
            'name' => 'Produit volume '.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
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

        $this->insertGenerated('clients', 5000, fn (int $number) => [
            'company_id' => $company->id,
            'name' => 'Client volume '.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
            'created_by' => $owner->id,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insertGenerated('sales', 50000, fn (int $number) => [
            'company_id' => $company->id,
            'code' => 1000000 + $number,
            'received_amount' => 3000,
            'total_amount' => 3000,
            'remaining_amount' => 0,
            'total_profit' => 1000,
            'code_promo' => 0,
            'discount' => 0,
            'amount_init' => 3000,
            'tax_amount' => 0,
            'cashier' => $owner->name,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $firstProductId = (int) DB::table('products')->where('company_id', $company->id)->min('id');
        $firstSaleId = (int) DB::table('sales')->where('company_id', $company->id)->min('id');
        $this->insertGenerated('sale_details', 100000, fn (int $number) => [
            'company_id' => $company->id,
            'sale_id' => $firstSaleId + (($number - 1) % 50000),
            'product_id' => $firstProductId + (($number - 1) % 10000),
            'quantity' => 1,
            'unit_price' => 1500,
            'total_price' => 1500,
            'profit' => 500,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insertGenerated('orders', 10000, fn (int $number) => [
            'company_id' => $company->id,
            'code' => 'VOL-'.$company->id.'-'.$number,
            'customer_name' => 'Acheteur '.$number,
            'customer_phone' => '00000000',
            'subtotal' => 3000,
            'tax' => 0,
            'total' => 3000,
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$owner, $company];
    }

    private function insertGenerated(string $table, int $count, callable $factory): void
    {
        for ($offset = 1; $offset <= $count; $offset += 1000) {
            $rows = [];
            $limit = min($offset + 999, $count);
            for ($number = $offset; $number <= $limit; $number++) {
                $rows[] = $factory($number);
            }
            DB::table($table)->insert($rows);
        }
    }

    private function insertChunks(string $table, iterable $rows): void
    {
        foreach (collect($rows)->chunk(1000) as $chunk) {
            DB::table($table)->insert($chunk->values()->all());
        }
    }

    private function measure(string $name, callable $request): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $startedAt = microtime(true);
        $request();
        $wallMs = round((microtime(true) - $startedAt) * 1000, 2);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->metrics[] = [
            'name' => $name,
            'wall_ms' => $wallMs,
            'queries' => count($queries),
            'sql_ms' => round(array_sum(array_column($queries, 'time')), 2),
            'slowest_queries' => collect($queries)
                ->sortByDesc('time')
                ->take(3)
                ->map(fn (array $query) => [
                    'sql_ms' => round((float) $query['time'], 2),
                    'sql' => preg_replace('/\s+/', ' ', trim($query['query'])),
                ])->values()->all(),
        ];
    }
}
