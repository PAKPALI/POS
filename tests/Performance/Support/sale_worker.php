<?php

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Product;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\SaleCreationService;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$app = require dirname(__DIR__, 3).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[$script, $companyId, $userId, $productId, $quantity, $barrierDirectory, $workerId] = $argv;
$database = (string) config('database.connections.'.config('database.default').'.database');

if (! str_ends_with($database, '_testing')) {
    fwrite(STDERR, 'Concurrency worker refused outside a *_testing database.'.PHP_EOL);
    exit(2);
}

touch($barrierDirectory.DIRECTORY_SEPARATOR.'ready-'.$workerId);
$deadline = microtime(true) + 15;
while (! is_file($barrierDirectory.DIRECTORY_SEPARATOR.'go')) {
    if (microtime(true) >= $deadline) {
        fwrite(STDERR, 'Concurrency barrier timeout.'.PHP_EOL);
        exit(3);
    }
    usleep(10000);
}

$startedAt = microtime(true);
try {
    $company = Company::findOrFail((int) $companyId);
    $membership = CompanyUser::where('company_id', $company->id)
        ->where('user_id', (int) $userId)
        ->where('status', 'active')
        ->with('role.permissions')
        ->firstOrFail();
    app(CompanyContext::class)->set($company, $membership);
    $user = User::findOrFail((int) $userId);
    $product = Product::findOrFail((int) $productId);

    $sale = app(SaleCreationService::class)->create([
        'products' => [[
            'product_id' => $product->id,
            'quantity' => (int) $quantity,
            'unit_price' => $product->price,
            'total_price' => $product->price * (int) $quantity,
        ]],
        'received_amount' => $product->price * (int) $quantity,
        'total_amount' => $product->price * (int) $quantity,
        'discount' => 0,
    ], $user);

    echo json_encode([
        'status' => 'success',
        'sale_id' => $sale->id,
        'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
    ]);
} catch (Throwable $exception) {
    echo json_encode([
        'status' => 'rejected',
        'reason' => $exception->getMessage(),
        'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
    ]);
}
