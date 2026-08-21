<?php

namespace App\Http\Middleware;

use App\Models\CompanyUser;
use App\Services\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware qui charge la compagnie active depuis la session.
 * Vérifie que l'utilisateur a une adhésion active.
 *
 * @see docs/documentation-saas-pos.html — Section 3.1 et 5.2
 */
class ResolveCompany
{
    public function __construct(
        private CompanyContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $companyId = session('active_company_id');

        if (!$companyId) {
            // Pas de compagnie en session → rediriger vers le sélecteur
            return redirect()->route('companies.select');
        }

        // Vérifier que l'utilisateur a bien une adhésion active
        $membership = CompanyUser::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->with('company', 'role')
            ->first();

        if (!$membership) {
            // Adhésion révoquée ou inexistante → purger la session
            session()->forget('active_company_id');

            return redirect()->route('companies.select')
                ->with('error', 'Votre accès à cette entreprise a été révoqué.');
        }

        // Vérifier que la compagnie est active
        if (!$membership->company || $membership->company->status !== 'active') {
            session()->forget('active_company_id');

            return redirect()->route('companies.select')
                ->with('error', 'Cette entreprise est inactive.');
        }

        // Charger le contexte pour toute la requête
        $this->context->set($membership->company, $membership);

        // Rendre company_id disponible dans toutes les vues Blade
        view()->share('activeCompany', $membership->company);
        view()->share('currentMembership', $membership);

        return $next($request);
    }
}
