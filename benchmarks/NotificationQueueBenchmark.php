<?php

namespace Tests\Performance;

use App\Jobs\SendSaleEmailJob;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\NotificationDelivery;
use App\Models\NotificationRecipient;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\CompanyProvisioner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class NotificationQueueBenchmark extends TestCase
{
    public function test_database_queue_handles_duplicate_notification_load_with_four_workers(): void
    {
        $database = (string) DB::connection()->getDatabaseName();
        $this->assertStringEndsWith('_testing', $database, 'Benchmark refusé hors d’une base *_testing.');
        Artisan::call('migrate:fresh', ['--force' => true]);
        [$company, $owner, $sales] = $this->seedScenario();

        config(['queue.default' => 'database']);
        $enqueueStartedAt = microtime(true);
        foreach ($sales as $sale) {
            SendSaleEmailJob::dispatch($sale->id, $company->id)->onQueue('notifications');
            SendSaleEmailJob::dispatch($sale->id, $company->id)->onQueue('notifications');
        }
        $enqueueMs = round((microtime(true) - $enqueueStartedAt) * 1000, 2);
        $this->assertSame(100, DB::table('jobs')->count());

        DB::disconnect();
        $processes = [];
        $processingStartedAt = microtime(true);
        foreach (range(1, 4) as $worker) {
            $process = new Process([
                PHP_BINARY, 'artisan', 'queue:work', 'database',
                '--queue=notifications', '--stop-when-empty', '--tries=1', '--timeout=60',
            ], base_path(), $this->databaseEnvironment(), null, 120);
            $process->start();
            $processes[] = $process;
        }
        foreach ($processes as $process) {
            $process->wait();
            $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        }
        $processingMs = round((microtime(true) - $processingStartedAt) * 1000, 2);
        DB::reconnect();

        $deliveries = NotificationDelivery::query()->where('company_id', $company->id);
        $expectedDeliveries = 50 * 20;
        $this->assertSame(0, DB::table('jobs')->count());
        $this->assertSame(0, DB::table('failed_jobs')->count());
        $this->assertSame($expectedDeliveries, (clone $deliveries)->count());
        $this->assertSame($expectedDeliveries, (clone $deliveries)->where('status', 'sent')->count());
        $this->assertSame(0, (clone $deliveries)->where('attempts', '>', 1)->count());

        fwrite(STDOUT, PHP_EOL.'SAAS_NOTIFICATION_QUEUE_BENCHMARK='.json_encode([
            'database' => $database,
            'workers' => 4,
            'sales' => 50,
            'recipients_per_sale' => 20,
            'jobs_enqueued_with_duplicates' => 100,
            'unique_deliveries' => $expectedDeliveries,
            'duplicate_deliveries' => 0,
            'failed_jobs' => 0,
            'enqueue_ms' => $enqueueMs,
            'processing_ms' => $processingMs,
            'deliveries_per_second' => round($expectedDeliveries / ($processingMs / 1000), 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    private function seedScenario(): array
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = Company::create([
            'name' => 'Entreprise notifications volume',
            'email' => 'notification-volume@test.local',
            'number1' => '000000000',
            'sale_email_enabled' => true,
            'sale_whatsapp_enabled' => false,
            'sale_sms_enabled' => false,
        ]);
        $membership = app(CompanyProvisioner::class)->provision($company, $owner, 0);
        app(CompanyContext::class)->set($company, $membership->load('role.permissions'));
        $role = Role::where('company_id', $company->id)->where('key', 'admin')->firstOrFail();

        $additionalUsers = User::factory()->count(19)->create(['user_type' => 3, 'status' => 1]);
        foreach ($additionalUsers as $user) {
            CompanyUser::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);
            NotificationRecipient::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'category' => 'sale',
                'email_enabled' => true,
                'whatsapp_enabled' => false,
                'sms_enabled' => false,
            ]);
        }
        NotificationRecipient::where('company_id', $company->id)
            ->where('user_id', $owner->id)->where('category', 'sale')
            ->update(['email_enabled' => true, 'whatsapp_enabled' => false, 'sms_enabled' => false]);

        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $company->id,
            'name' => 'Notifications volume',
            'created_by' => $owner->id,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $categoryId,
            'name' => 'Produit notification',
            'qte' => 1000,
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

        $sales = collect();
        foreach (range(1, 50) as $number) {
            $sale = Sale::create([
                'company_id' => $company->id,
                'code' => 8000000 + $number,
                'received_amount' => 1000,
                'total_amount' => 1000,
                'remaining_amount' => 0,
                'total_profit' => 500,
                'discount' => 0,
                'amount_init' => 1000,
                'tax_amount' => 0,
                'cashier' => $owner->name,
            ]);
            $sale->saleDetails()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 1000,
                'total_price' => 1000,
                'profit' => 500,
            ]);
            $sales->push($sale);
        }

        return [$company, $owner, $sales];
    }

    private function databaseEnvironment(): array
    {
        $connection = config('database.default');
        $config = config('database.connections.'.$connection);

        return [
            'APP_ENV' => 'testing',
            'APP_DEBUG' => 'false',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'database',
            'MAIL_MAILER' => 'array',
            'LOG_CHANNEL' => 'null',
            'DB_CONNECTION' => (string) $connection,
            'DB_HOST' => (string) ($config['host'] ?? '127.0.0.1'),
            'DB_PORT' => (string) ($config['port'] ?? '3306'),
            'DB_DATABASE' => (string) $config['database'],
            'DB_USERNAME' => (string) $config['username'],
            'DB_PASSWORD' => (string) ($config['password'] ?? ''),
        ];
    }
}
