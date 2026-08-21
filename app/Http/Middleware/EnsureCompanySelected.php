<?php

namespace App\Http\Middleware;

use App\Services\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige vers le sélecteur si l'utilisateur n'a pas encore de compagnie active.
 * À appliquer après ResolveCompany sur les routes protégées.
 */
class EnsureCompanySelected
{
    public function __construct(
        private CompanyContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->context->isResolved()) {
            return redirect()->route('companies.select')
                ->with('error', 'Veuillez sélectionner une entreprise.');
        }

        return $next($request);
    }
}
