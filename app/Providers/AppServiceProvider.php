<?php

namespace App\Providers;
use App\Services\CompanyContext;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(UrlGenerator $url)
    {
        if ($this->app->environment('production')) {
            $url->forceScheme('https');
        }

        if (config('performance.slow_queries.enabled')) {
            DB::listen(function (QueryExecuted $query): void {
                $threshold = (float) config('performance.slow_queries.threshold_ms', 300);
                if ($query->time < $threshold) {
                    return;
                }

                $request = $this->app->bound('request') ? $this->app->make('request') : null;
                $company = $this->app->make(CompanyContext::class)->getCompanyOrNull();
                $context = [
                    'duration_ms' => round($query->time, 2),
                    'connection' => $query->connectionName,
                    'method' => $request?->method(),
                    'route' => $request?->route()?->getName(),
                    'path' => $request?->path(),
                    'company_id' => $company?->id,
                    'user_id' => $request?->user()?->id,
                ];

                if (config('performance.slow_queries.include_sql', true)) {
                    $context['sql'] = Str::limit(
                        preg_replace('/\s+/', ' ', trim($query->sql)),
                        1200
                    );
                }

                Log::channel(config('performance.slow_queries.channel', 'performance'))
                    ->warning('Requête SQL lente détectée.', $context);
            });
        }
    }
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Le contexte ne doit jamais survivre à une requête (Octane/queues/tests).
        $this->app->scoped(CompanyContext::class, fn () => new CompanyContext());
    }
}
