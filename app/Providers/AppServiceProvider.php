<?php

namespace App\Providers;
use App\Services\CompanyContext;
use Illuminate\Routing\UrlGenerator;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(UrlGenerator $url)
    {
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
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
