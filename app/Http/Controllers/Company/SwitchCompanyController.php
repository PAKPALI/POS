<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\CompanyUser;
use App\Services\CompanyContext;
use App\Services\AuthorizedLandingPage;
use App\Services\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SwitchCompanyController extends Controller
{
    public function __construct(
        private CompanyContext $context,
        private AuthorizedLandingPage $landingPage,
        private EntitlementService $entitlements,
    ) {}

    /**
     * Affiche la page de sélection de compagnie.
     */
    public function select()
    {
        $user = Auth::user();
        $memberships = CompanyUser::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('company', fn ($query) => $query->where('status', 'active'))
            ->with('company', 'role')
            ->orderByDesc('last_accessed_at')
            ->get();

        $activeCompanyId = session('active_company_id');
        $activeMembership = $memberships->firstWhere('company_id', (int) $activeCompanyId);
        $returnUrl = $activeMembership
            ? $this->landingPage->forMembership($activeMembership)
            : null;

        $canCreateCompany = !$activeMembership || $this->entitlements->canAdd($activeMembership->company, 'company');
        $canManageSubscription = $activeMembership?->hasPermission('subscription.manage') ?? false;
        return view('company.select', compact('memberships', 'activeCompanyId', 'returnUrl', 'canCreateCompany', 'canManageSubscription'));
    }

    /**
     * Change la compagnie active en session.
     *
     * POST /companies/{company}/switch
     */
    public function switch(Request $request, int $companyId)
    {
        $user = Auth::user();
        $previousCompanyId = session('active_company_id');

        // Rechercher l'adhésion active (jamais accepter un ID sans vérification)
        $membership = CompanyUser::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->with('company')
            ->first();

        if (!$membership) {
            return redirect()->route('companies.select')
                ->with('error', 'Vous n\'êtes pas membre de cette entreprise ou votre accès a été révoqué.');
        }

        if ($membership->company->status !== 'active') {
            return redirect()->route('companies.select')
                ->with('error', 'Cette entreprise est inactive.');
        }

        // Mettre à jour la session
        session(['active_company_id' => $companyId]);
        session(['active_company_name' => $membership->company->name]);

        // Régénérer l'ID de session pour la sécurité (previent les attaques de fixation)
        $request->session()->regenerate();

        // Mettre à jour last_accessed_at
        $membership->update(['last_accessed_at' => now()]);

        // Log du switch
        Log::info('Company switch', [
            'user_id' => $user->id,
            'from_company' => $previousCompanyId,
            'to_company' => $companyId,
        ]);

        Action::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'function' => $previousCompanyId ? 'CHANGEMENT ENTREPRISE' : 'CONNEXION',
            'text' => $previousCompanyId
                ? $user->name.' a ouvert l’entreprise '.$membership->company->name.'.'
                : $user->name.' s’est connecté à l’entreprise '.$membership->company->name.'.',
        ]);

        return redirect($this->landingPage->forMembership($membership))
            ->with('success', 'Entreprise « ' . $membership->company->name . ' » sélectionnée.');
    }

    /**
     * Quitte la compagnie active (retour au sélecteur).
     */
    public function leave()
    {
        session()->forget('active_company_id');
        session()->forget('active_company_name');
        $this->context->clear();

        return redirect()->route('companies.select');
    }
}
