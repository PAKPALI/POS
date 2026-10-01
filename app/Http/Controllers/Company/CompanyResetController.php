<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Jobs\SendCompanyResetReceipt;
use App\Mail\CompanyResetMail;
use App\Models\Company;
use App\Models\CompanyResetRequest;
use App\Services\CompanyContext;
use App\Services\CompanyResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompanyResetController extends Controller
{
    public function __construct(private CompanyContext $context, private CompanyResetService $service) {}

    private function company(Request $request): Company
    {
        $company = $this->context->getCompany()->fresh();
        abort_unless($this->service->isOwner($company, $request->user()), 403, 'Seul le propriétaire de cette entreprise peut la réinitialiser.');
        return $company;
    }

    public function index(Request $request)
    {
        $company = $this->company($request);
        return view('company.reset', ['company' => $company, 'catalog' => $this->service->catalog($company, $request->user())]);
    }

    public function challenge(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'selection' => ['required', 'array', 'min:1', 'max:20'], 'selection.*' => ['required', 'string', 'distinct'],
            'terms' => ['required', 'accepted'], 'current_password' => ['required', 'string', 'current_password:web'],
        ], ['terms.accepted' => 'Acceptez les conditions de suppression.', 'current_password.current_password' => 'Le mot de passe actuel est incorrect.']);
        $plan = $this->service->plan($company, $request->user(), $data['selection']);
        $code = (string) random_int(100000, 999999);
        $request->session()->put('company_reset_binding', Str::random(64));
        $reset = DB::transaction(function () use ($request, $company, $code, $plan) {
            CompanyResetRequest::where('company_id', $company->id)->where('user_id', $request->user()->id)
                ->whereNull('completed_at')->update(['expires_at' => now()]);
            return CompanyResetRequest::create([
                'id' => (string) Str::uuid(), 'company_id' => $company->id, 'company_name' => $company->name,
                'user_id' => $request->user()->id, 'email' => $request->user()->email,
                'session_hash' => hash('sha256', $request->session()->get('company_reset_binding')), 'code_hash' => Hash::make($code),
                'selection' => $plan['selection'], 'summary' => $plan['summary'], 'fingerprint' => $plan['fingerprint'],
                'expires_at' => now()->addMinutes(10), 'terms_accepted_at' => now(), 'request_ip' => $request->ip(),
            ]);
        });
        try {
            Mail::to($reset->email)->send(new CompanyResetMail($reset, $code));
        } catch (\Throwable $e) {
            $reset->update(['expires_at' => now()]);
            report($e);
            return response()->json(['message' => 'Le code de confirmation n’a pas pu être envoyé. Aucune donnée n’a été supprimée. Réessayez.'], 503);
        }
        return response()->json(['id' => $reset->id, 'summary' => $reset->summary, 'message' => 'Un code a été envoyé à votre adresse e-mail. Il est valable 10 minutes.']);
    }

    public function execute(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate(['id' => ['required', 'uuid'], 'code' => ['required', 'digits:6'], 'terms' => ['required', 'accepted']]);
        $result = DB::transaction(function () use ($request, $company, $data) {
            Company::whereKey($company->id)->lockForUpdate()->firstOrFail();
            $reset = CompanyResetRequest::whereKey($data['id'])->where('company_id', $company->id)
                ->where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            if (!hash_equals($reset->session_hash, hash('sha256', (string) $request->session()->get('company_reset_binding'))) || $reset->email !== $request->user()->email) abort(403);
            if ($reset->completed_at) return ['reset' => $reset]; // Une reprise réseau ne supprime jamais les nouveaux articles.
            if ($reset->expires_at->isPast() || $reset->attempts >= 5) return ['error' => 'Le code a expiré ou le nombre d’essais est atteint. Recommencez la confirmation.'];
            if (!Hash::check($data['code'], $reset->code_hash)) {
                $reset->increment('attempts');
                return ['error' => 'Code incorrect. '.max(0, 5 - $reset->attempts).' essai(s) restant(s).'];
            }
            $plan = $this->service->plan($company, $request->user(), $reset->selection);
            if (!hash_equals($reset->fingerprint, $plan['fingerprint']) || $plan['selection'] !== $reset->selection) {
                $reset->update(['expires_at' => now()]);
                return ['error' => 'Les données de l’entreprise ont changé depuis votre sélection. Actualisez la liste et confirmez à nouveau.'];
            }
            $this->service->execute($company, $request->user(), $reset->selection);
            if (array_intersect($reset->selection, ['products', 'packs', 'sales', 'inventories', 'orders'])) {
                DB::table('company_settings')->where('id', $company->id)->update(['data_reset_at' => now()]);
            }
            $reset->update(['completed_at' => now()]);
            return ['reset' => $reset];
        }, 3);
        if (isset($result['error'])) throw ValidationException::withMessages(['code' => $result['error']]);
        try { SendCompanyResetReceipt::dispatch($result['reset']->id)->afterCommit(); }
        catch (\Throwable $e) { report($e); } // Le scheduler reprend l'email ; les données sont déjà validées.
        return response()->json(['message' => 'Les données sélectionnées ont été réinitialisées. Votre abonnement et vos quotas restants sont conservés.', 'redirect' => route('profil')]);
    }
}
