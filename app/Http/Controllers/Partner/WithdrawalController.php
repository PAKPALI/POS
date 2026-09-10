<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerCountryService;
use App\Services\PartnerWithdrawalService;
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
        $gateways = config('partners.payout_gateways', []);
        $withdrawalCountries = array_values(array_filter($this->countries->activeCountries(), fn (array $country) => !empty($gateways[$country['code']] ?? [])));
        return view('partner.withdrawals', [
            'partner' => $partner,
            'eligibility' => $this->withdrawals->eligibility($partner),
            'accounts' => $partner->withdrawalAccounts()->latest('is_primary')->latest('id')->get(),
            'countries' => $withdrawalCountries,
            'gateways' => $gateways,
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
                default => 'Le compte Mobile Money n’a pas pu être enregistré.',
            };
            return back()->withErrors(['phone_number' => $message])->withInput();
        }
        return back()->with('success', 'Compte Mobile Money enregistré. Sa vérification sera requise avant tout retrait.');
    }

}
