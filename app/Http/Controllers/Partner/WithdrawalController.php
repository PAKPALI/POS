<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerCountryService;
use App\Services\PartnerAuthenticationService;
use App\Services\PartnerWithdrawalService;
use App\Models\PartnerWithdrawalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

class WithdrawalController extends Controller
{
    public function __construct(private PartnerWithdrawalService $withdrawals, private PartnerCountryService $countries) {}

    public function index()
    {
        $partner = Auth::guard('partner')->user();
        $gateways = [];
        foreach ($this->countries->activeCountries() as $country) $gateways[$country['code']] = $this->withdrawals->gatewaysFor($country['code']);
        $withdrawalCountries = array_values(array_filter($this->countries->activeCountries(), fn (array $country) => !empty($gateways[$country['code']] ?? [])));
        return view('partner.withdrawals', [
            'partner' => $partner,
            'eligibility' => $this->withdrawals->eligibility($partner),
            'accounts' => $partner->withdrawalAccounts()->latest('is_primary')->latest('id')->get(),
            'countries' => $withdrawalCountries,
            'gateways' => $gateways,
            'gatewayCatalog' => config('partners.payout_gateway_catalog', []),
        ]);
    }

    public function storeAccount(Request $request, PartnerCountryService $countries)
    {
        $data = $request->validate([
            'country_code' => ['required', Rule::in($countries->activeCodes())],
            'gateway' => ['required', 'string', 'max:40'],
            'phone_number' => ['required', 'string', 'max:24'],
            'beneficiary_name' => ['required', 'string', 'max:120'],
        ], [
            'country_code.required' => 'Sélectionnez le pays du compte Mobile Money.',
            'country_code.in' => 'Ce pays n’est pas actif pour les partenaires.',
            'gateway.required' => 'Sélectionnez l’opérateur Mobile Money.',
            'phone_number.required' => 'Saisissez le numéro Mobile Money.',
            'phone_number.max' => 'Le numéro ne doit pas dépasser 24 caractères.',
            'beneficiary_name.required' => 'Saisissez le nom du bénéficiaire.',
        ]);
        try {
            $this->withdrawals->registerAccount(Auth::guard('partner')->user(), $data, $request);
        } catch (RuntimeException $exception) {
            $message = match ($exception->getMessage()) {
                'PAYOUT_COUNTRY_INACTIVE' => 'Le pays sélectionné n’est plus actif.',
                'PAYOUT_GATEWAY_INVALID' => 'Cet opérateur Mobile Money n’est pas disponible pour ce pays.',
                'PAYOUT_PHONE_LENGTH_INVALID' => 'Le numéro Mobile Money doit contenir exactement 8 chiffres.',
                'PAYOUT_PHONE_PREFIX_INVALID' => 'Le préfixe du numéro ne correspond pas à l’opérateur sélectionné.',
                default => 'Le compte Mobile Money n’a pas pu être enregistré.',
            };
            return back()->withErrors(['phone_number' => $message])->withInput();
        }
        return back()->with('success', 'Compte Mobile Money enregistré. Sa vérification sera requise avant tout retrait.');
    }

    public function beginWithdrawal(Request $request, PartnerAuthenticationService $authentication)
    {
        $partner = Auth::guard('partner')->user();
        $data = $request->validate(['account_id' => ['required', 'integer'], 'amount' => ['required', 'integer', 'min:1'], 'current_password' => ['required', 'current_password:partner']], ['current_password.required' => 'Saisissez votre mot de passe pour confirmer le retrait.', 'current_password.current_password' => 'Votre mot de passe est incorrect.']);
        $account = PartnerWithdrawalAccount::whereKey($data['account_id'])->where('partner_id', $partner->id)->firstOrFail();
        if (!$this->withdrawals->eligibility($partner)['eligible'] || $account->status !== 'verified') return back()->withErrors(['amount' => 'Votre demande de retrait ne remplit pas encore les conditions requises.']);
        $request->session()->put('partner_withdrawal_pending', ['account_id' => $account->id, 'amount' => (int) $data['amount']]);
        $authentication->issueWithdrawalConfirmation($partner, $request);
        return redirect()->route('partner.withdrawals.confirm')->with('status', 'Un code de confirmation vient d’être envoyé à votre adresse e-mail.');
    }

    public function showConfirm(Request $request)
    {
        abort_unless($request->session()->has('partner_withdrawal_pending'), 403);
        return view('partner.withdrawal-confirm');
    }

    public function confirmWithdrawal(Request $request, PartnerAuthenticationService $authentication)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']], ['code.required' => 'Saisissez le code de confirmation.', 'code.digits' => 'Le code doit contenir exactement 6 chiffres.']);
        $pending = $request->session()->get('partner_withdrawal_pending');
        abort_unless(is_array($pending), 403);
        $partner = Auth::guard('partner')->user();
        if (!$authentication->verifyWithdrawalConfirmation($partner, $data['code'])) return back()->withErrors(['code' => 'Le code est incorrect, expiré ou déjà utilisé.']);
        $account = PartnerWithdrawalAccount::whereKey($pending['account_id'])->where('partner_id', $partner->id)->firstOrFail();
        try {
            $withdrawal = $this->withdrawals->requestWithdrawal($partner, $account, (int) $pending['amount'], $request, true);
        } catch (RuntimeException $exception) {
            return redirect()->route('partner.withdrawals')->withErrors(['amount' => 'La demande ne peut plus être créée. Vérifiez votre solde et les conditions de retrait.']);
        }
        $request->session()->forget('partner_withdrawal_pending');
        return redirect()->route('partner.withdrawals')->with('success', 'Votre demande de retrait #'.$withdrawal->id.' est enregistrée et réservée pour contrôle.');
    }

}
