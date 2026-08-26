<?php

use App\Http\Controllers\Ecommerce\OrderController;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Auth;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$app = require dirname(__DIR__, 3).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[$script, $companyId, $userId, $orderId, $barrierDirectory, $workerId] = $argv;
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
        ->where('user_id', (int) $userId)->where('status', 'active')
        ->with('role.permissions')->firstOrFail();
    app(CompanyContext::class)->set($company, $membership);
    $user = User::findOrFail((int) $userId);
    Auth::login($user);
    request()->setUserResolver(fn () => $user);

    $response = app(OrderController::class)->execute((int) $orderId);
    $payload = $response->getData(true);
    echo json_encode([
        'status' => ($payload['status'] ?? false) ? 'success' : 'rejected',
        'http_status' => $response->getStatusCode(),
        'reason' => $payload['msg'] ?? null,
        'sale_id' => $payload['sale_id'] ?? null,
        'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
    ]);
} catch (Throwable $exception) {
    echo json_encode([
        'status' => 'error',
        'reason' => $exception->getMessage(),
        'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
    ]);
}
