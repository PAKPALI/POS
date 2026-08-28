<?php

namespace App\Http\Middleware;

use App\Services\PlatformConfigurationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('platform*', 'admin-saas', 'api/*')) return $next($request);
        $configuration = app(PlatformConfigurationService::class);
        if (!$configuration->maintenanceEnabled()) return $next($request);
        return response()->view('maintenance.platform', [
            'message' => $configuration->get('maintenance.message', 'Une maintenance est en cours. Veuillez réessayer dans quelques instants.'),
            'supportEmail' => $configuration->get('support.email'),
        ], 503, ['Retry-After' => '300']);
    }
}
