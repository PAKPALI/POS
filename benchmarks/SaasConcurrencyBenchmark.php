<?php

namespace Tests\Performance;

use App\Models\AMS\CashAccount;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\CompanyProvisioner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Benchmark explicitement exclu de la suite quotidienne.
 * Lancer : php artisan test benchmarks/SaasConcurrencyBenchmark.php
 */
class SaasConcurrencyBenchmark extends TestCase
{
    public function test_concurrent_cashiers_cannot_oversell_the_same_product(): void
    {
        $database = (string) DB::connection()->getDatabaseName();
        $this->assertStringEndsWith('_testing', $database, 'Benchmark refusé hors d’une base *_testing.');
        Artisan::call('migrate:fresh', ['--force' => true]);

        [$company, $owner, $product] = $this->seedScenario();
        $workerCount = 10;
        $quantityPerWorker = 2;
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pos-concurrency-'.Str::uuid();
        mkdir($barrier, 0777, true);

        $environment = $this->databaseEnvironment();
        $processes = [];
        for ($worker = 1; $worker <= $workerCount; $worker++) {
            $process = new Process([
                PHP_BINARY,
                base_path('tests/Performance/Support/sale_worker.php'),
                (string) $company->id,
                (string) $owner->id,
                (string) $product->id,
                (string) $quantityPerWorker,
                $barrier,
                (string) $worker,
            ], base_path(), $environment, null, 30);
            $process->start();
            $processes[] = $process;
        }

        $readyDeadline = microtime(true) + 15;
        while (count(glob($barrier.DIRECTORY_SEPARATOR.'ready-*')) < $workerCount) {
            if (microtime(true) >= $readyDeadline) {
                $this->fail('Les processus concurrents ne sont pas tous prêts.');
            }
            usleep(20000);
        }

        $startedAt = microtime(true);
        touch($barrier.DIRECTORY_SEPARATOR.'go');
        $results = [];
        foreach ($processes as $process) {
            $process->wait();
            $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
            $results[] = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        }
        $wallMs = round((microtime(true) - $startedAt) * 1000, 2);

        $successes = collect($results)->where('status', 'success');
        $rejections = collect($results)->where('status', 'rejected');
        $product->refresh();
        $mainCash = CashAccount::withoutCompanyScope()
            ->where('company_id', $company->id)->where('is_default', 1)->firstOrFail();

        $this->assertCount(5, $successes);
        $this->assertCount(5, $rejections);
        $this->assertTrue($rejections->every(fn (array $result) => str_contains($result['reason'], 'Stock insuffisant')));
        $this->assertSame(0, (int) $product->qte);
        $this->assertSame(5, Sale::withoutCompanyScope()->where('company_id', $company->id)->count());
        $this->assertSame(5, SaleDetail::withoutCompanyScope()->where('company_id', $company->id)->count());
        $this->assertSame(10000.0, (float) $mainCash->balance);

        fwrite(STDOUT, PHP_EOL.'SAAS_CONCURRENCY_BENCHMARK='.json_encode([
            'database' => $database,
            'workers' => $workerCount,
            'initial_stock' => 10,
            'quantity_per_worker' => $quantityPerWorker,
            'successes' => $successes->count(),
            'stock_rejections' => $rejections->count(),
            'final_stock' => (int) $product->qte,
            'sales_created' => $successes->count(),
            'cash_balance' => (float) $mainCash->balance,
            'wall_ms' => $wallMs,
            'worker_duration_ms' => [
                'min' => $successes->merge($rejections)->min('duration_ms'),
                'max' => $successes->merge($rejections)->max('duration_ms'),
                'average' => round((float) $successes->merge($rejections)->avg('duration_ms'), 2),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        foreach (glob($barrier.DIRECTORY_SEPARATOR.'*') as $file) {
            unlink($file);
        }
        rmdir($barrier);
    }

    public function test_concurrent_workers_convert_an_order_only_once(): void
    {
        $database = (string) DB::connection()->getDatabaseName();
        $this->assertStringEndsWith('_testing', $database, 'Benchmark refusé hors d’une base *_testing.');
        Artisan::call('migrate:fresh', ['--force' => true]);

        [$company, $owner, $product] = $this->seedScenario();
        $order = Order::create([
            'company_id' => $company->id,
            'code' => 'CONCURRENT-ORDER',
            'customer_name' => 'Client concurrence',
            'customer_phone' => '00000000',
            'subtotal' => 3000,
            'tax' => 0,
            'total' => 3000,
            'status' => 'pending',
        ]);
        $order->items()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'unit_price' => 1000,
            'total_price' => 3000,
        ]);

        $workerCount = 8;
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pos-order-concurrency-'.Str::uuid();
        mkdir($barrier, 0777, true);
        $processes = [];
        foreach (range(1, $workerCount) as $worker) {
            $process = new Process([
                PHP_BINARY,
                base_path('tests/Performance/Support/order_worker.php'),
                (string) $company->id,
                (string) $owner->id,
                (string) $order->id,
                $barrier,
                (string) $worker,
            ], base_path(), $this->databaseEnvironment(), null, 30);
            $process->start();
            $processes[] = $process;
        }

        $readyDeadline = microtime(true) + 15;
        while (count(glob($barrier.DIRECTORY_SEPARATOR.'ready-*')) < $workerCount) {
            if (microtime(true) >= $readyDeadline) {
                $this->fail('Les processus de commande ne sont pas tous prêts.');
            }
            usleep(20000);
        }

        $startedAt = microtime(true);
        touch($barrier.DIRECTORY_SEPARATOR.'go');
        $results = [];
        foreach ($processes as $process) {
            $process->wait();
            $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
            $results[] = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        }
        $wallMs = round((microtime(true) - $startedAt) * 1000, 2);

        $successes = collect($results)->where('status', 'success');
        $rejections = collect($results)->where('status', 'rejected');
        $order->refresh();
        $product->refresh();

        $this->assertCount(1, $successes);
        $this->assertCount($workerCount - 1, $rejections);
        $this->assertSame('converted', $order->status);
        $this->assertNotNull($order->sale_id);
        $this->assertSame(1, Sale::withoutCompanyScope()->where('company_id', $company->id)->count());
        $this->assertSame(7, (int) $product->qte);

        fwrite(STDOUT, PHP_EOL.'SAAS_ORDER_CONCURRENCY_BENCHMARK='.json_encode([
            'database' => $database,
            'workers' => $workerCount,
            'successes' => $successes->count(),
            'duplicate_rejections' => $rejections->count(),
            'sales_created' => 1,
            'stock_before' => 10,
            'stock_after' => (int) $product->qte,
            'wall_ms' => $wallMs,
            'worker_duration_ms' => [
                'min' => collect($results)->min('duration_ms'),
                'max' => collect($results)->max('duration_ms'),
                'average' => round((float) collect($results)->avg('duration_ms'), 2),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        foreach (glob($barrier.DIRECTORY_SEPARATOR.'*') as $file) {
            unlink($file);
        }
        rmdir($barrier);
    }

    private function seedScenario(): array
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = Company::create([
            'name' => 'Entreprise concurrence',
            'email' => 'concurrency@test.local',
            'number1' => '000000000',
            'sale_email_enabled' => false,
            'sale_whatsapp_enabled' => false,
            'sale_sms_enabled' => false,
        ]);
        $membership = app(CompanyProvisioner::class)->provision($company, $owner, 0);
        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));

        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $company->id,
            'name' => 'Concurrence',
            'created_by' => $owner->id,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $categoryId,
            'name' => 'Produit concurrent',
            'qte' => 10,
            'margin' => 0,
            'price' => 1000,
            'price_ttc' => 1000,
            'purchase_price' => 500,
            'profit' => 500,
            'status' => 1,
            'email' => 0,
            'type' => 1,
            'created_by' => $owner->id,
        ]);

        return [$company, $owner, $product];
    }

    private function databaseEnvironment(): array
    {
        $connection = config('database.default');
        $config = config('database.connections.'.$connection);

        return [
            'APP_ENV' => 'testing',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
            'DB_CONNECTION' => (string) $connection,
            'DB_HOST' => (string) ($config['host'] ?? '127.0.0.1'),
            'DB_PORT' => (string) ($config['port'] ?? '3306'),
            'DB_DATABASE' => (string) $config['database'],
            'DB_USERNAME' => (string) $config['username'],
            'DB_PASSWORD' => (string) ($config['password'] ?? ''),
        ];
    }
}
