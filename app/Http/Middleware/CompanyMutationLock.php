<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Services\CompanyContext;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CompanyMutationLock
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethodSafe()) return $next($request);
        $company = app(CompanyContext::class)->getCompanyOrNull() ?: $request->route('company');
        if (!$company instanceof Company) return $next($request);
        try {
            return Cache::lock('company-mutation:'.$company->id, 300)->block(5, function () use ($request, $next, $company) {
                // Une requête déjà en attente ne doit pas contourner une révocation de membres.
                if ($request->user() && app(CompanyContext::class)->getMembershipOrNull()) {
                    abort_unless(CompanyUser::where('company_id', $company->id)
                        ->where('user_id', $request->user()->id)->where('status', 'active')->exists(), 403, 'Votre accès à cette entreprise a été retiré.');
                }
                return $next($request);
            });
        } catch (LockTimeoutException $exception) {
            abort(409, 'Une opération est déjà en cours dans cette entreprise. Réessayez dans quelques instants.');
        }
    }
}
