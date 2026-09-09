<?php

namespace App\Http\Middleware;

use App\Services\PlatformConfigurationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnersEnabled
{
    public function __construct(private PlatformConfigurationService $configuration) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->configuration->boolean('partners.enabled', (bool) config('partners.enabled', false))) {
            return response()->view('partner.unavailable', [], 503);
        }

        return $next($request);
    }
}
