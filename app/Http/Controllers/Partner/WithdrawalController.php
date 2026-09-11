<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerCountryService;
use App\Services\PartnerAuthenticationService;
use App\Services\PartnerWithdrawalService;
use App\Services\PartnerPayoutService;
use App\Models\PartnerWithdrawalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

class WithdrawalController extends Controller
{
    public function __construct(private PartnerWithdrawalService $withdrawals, private PartnerCountryService $countries, private \App\Services\PartnerAuthenticationService $authentication, private PartnerPayoutService $payouts) {}

    public function index(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        $gateways = [];
        foreach ($this->countries->activeCountries() as $country) $gateways[$country['code']] = $this->withdrawals->gatewaysFor($country['code']);
        $withdrawalCountries = array_values(array_filter($this->countries->activeCountries(), fn (array $country) => !empty($gateways[$country['code']] ?? [])));
        $accounts = $partner->withdrawalAccounts()->latest('is_primary')->latest('id')->get();
        $pendingAccounts = $accounts->filter(fn (PartnerWithdrawalAccount $account) => $account->status !== 'verified')->values();
        $verifiedAccounts = $accounts->where('status', 'verified')->values();
        $availableVerifiedAccounts = $verifiedAccounts->filter(fn (PartnerWithdrawalAccount $account) => $this->withdrawals->gatewayIsEnabled($account->country_code, $account->gateway))->values();
        $disabledVerifiedAccounts = $verifiedAccounts->reject(fn (PartnerWithdrawalAccount $account) => $this->withdrawals->gatewayIsEnabled($account->country_code, $account->gateway))->values();
        $pendingAccountId = (int) $request->session()->get('partner_withdrawal_account_pending_id');
        $pendingAccount = $pendingAccounts->first(fn (PartnerWithdrawalAccount $account) => $account->id === $pendingAccountId) ?? $pendingAccounts->first();
        return view('partner.withdrawals', [
            'partner' => $partner,
            'eligibility' => $this->withdrawals->eligibility($partner),
            'payoutFeeBps' => $this->withdrawals->payoutFeeBps(),
            'accounts' => $accounts,
            'availableVerifiedAccounts' => $availableVerifiedAccounts,
            'disabledVerifiedAccounts' => $disabledVerifiedAccounts,
            'withdrawals' => $partner->withdrawals()->with('account')->latest('requested_at')->limit(10)->get(),
            'pendingAccounts' => $pendingAccounts,
            'pendingAccount' => $pendingAccount,
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
            $account = $this->withdrawals->registerAccount(Auth::guard('partner')->user(), $data, $request);
        } catch (RuntimeException $exception) {
            $message = match ($exception->getMessage()) {
                'PAYOUT_COUNTRY_INACTIVE' => 'Le pays sélectionné n’est plus actif.',
                'PAYOUT_GATEWAY_INVALID' => 'Cet opérateur Mobile Money n’est pas disponible pour ce pays.',
                'PAYOUT_PHONE_LENGTH_INVALID' => 'Le numéro Mobile Money doit contenir exactement 8 chiffres.',
                'PAYOUT_PHONE_PREFIX_INVALID' => $this->phonePrefixErrorMessage($data),
                'PAYOUT_BENEFICIARY_NAME_INVALID' => 'Saisissez au moins un prénom et un nom pour le bénéficiaire.',
                default => 'Le compte Mobile Money n’a pas pu être enregistré.',
            };
            return back()->withErrors(['phone_number' => $message])->withInput();
        }
        $request->session()->put('partner_withdrawal_account_pending_id', $account->id);
        $this->authentication->issueWithdrawalAccountConfirmation(Auth::guard('partner')->user(), $request);
        return redirect()->route('partner.withdrawals.accounts.confirm')->with('status', 'Un code de confirmation vient d’être envoyé à votre adresse e-mail.');
    }

    public function showAccountConfirmation(Request $request)
    {
        abort_unless($request->session()->has('partner_withdrawal_account_pending_id'), 403);
        return view('partner.withdrawal-account-confirm');
    }

    public function resendAccountConfirmation(Request $request)
    {
        $data = $request->validate(['account_id' => ['nullable', 'integer']]);
        $accountId = (int) ($data['account_id'] ?? $request->session()->get('partner_withdrawal_account_pending_id'));
        abort_unless($accountId, 403);
        $partner = Auth::guard('partner')->user();
        $account = PartnerWithdrawalAccount::whereKey($accountId)->where('partner_id', $partner->id)->firstOrFail();
        abort_if($account->status === 'verified', 422, 'Ce compte est déjà vérifié.');
        $request->session()->put('partner_withdrawal_account_pending_id', $account->id);
        $this->authentication->issueWithdrawalAccountConfirmation($partner, $request);

        return redirect()->route('partner.withdrawals')->with('status', 'Un nouveau code de confirmation vient d’être envoyé à votre adresse e-mail.')->with('open_partner_account_confirmation', true);
    }

    public function confirmAccount(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
            'account_id' => ['nullable', 'integer'],
        ], [
            'code.required' => 'Saisissez le code de confirmation.',
            'code.digits' => 'Le code doit contenir exactement 6 chiffres.',
        ]);
        $accountId = (int) ($data['account_id'] ?? $request->session()->get('partner_withdrawal_account_pending_id'));
        abort_unless($accountId, 403);
        $partner = Auth::guard('partner')->user();
        $account = PartnerWithdrawalAccount::whereKey($accountId)->where('partner_id', $partner->id)->firstOrFail();
        if (!$this->authentication->verifyWithdrawalAccountConfirmation($partner, $data['code'])) return back()->withErrors(['code' => 'Le code est incorrect, expiré ou déjà utilisé.']);
        $this->withdrawals->verifyAccount($account, $request);
        $request->session()->forget('partner_withdrawal_account_pending_id');
        return redirect()->route('partner.withdrawals')->with('success', 'Votre compte Mobile Money est maintenant vérifié et disponible pour un retrait.');
    }

    private function phonePrefixErrorMessage(array $data): string
    {
        $digits = preg_replace('/\D+/', '', (string) ($data['phone_number'] ?? ''));
        $prefix = substr($digits, 0, 2);
        $accepted = $this->withdrawals->gatewayPrefixes((string) ($data['country_code'] ?? ''), (string) ($data['gateway'] ?? ''));

        return sprintf(
            'Les deux premiers chiffres (%s) ne correspondent pas aux préfixes acceptés%s.',
            $prefix ?: '—',
            $accepted !== [] ? ' : '.implode(', ', $accepted) : '',
        );
    }

    public function beginWithdrawal(Request $request, PartnerAuthenticationService $authentication)
    {
        $partner = Auth::guard('partner')->user();
        $data = $request->validate(['account_id' => ['required', 'integer'], 'amount' => ['required', 'integer', 'min:1'], 'current_password' => ['required', 'current_password:partner']], ['current_password.required' => 'Saisissez votre mot de passe pour confirmer le retrait.', 'current_password.current_password' => 'Votre mot de passe est incorrect.']);
        $account = PartnerWithdrawalAccount::whereKey($data['account_id'])->where('partner_id', $partner->id)->firstOrFail();
        if (!$this->withdrawals->gatewayIsEnabled($account->country_code, $account->gateway)) {
            return back()->withErrors(['account_id' => 'Ce moyen de retrait est temporairement indisponible. Sélectionnez un opérateur activé par la plateforme.'])->withInput();
        }
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
            $message = $exception->getMessage() === 'PAYOUT_GATEWAY_DISABLED'
                ? 'Ce moyen de retrait a été désactivé par la plateforme. Aucun versement n’a été lancé.'
                : 'La demande ne peut plus être créée. Vérifiez votre solde et les conditions de retrait.';
            return redirect()->route('partner.withdrawals')->withErrors(['amount' => $message]);
        }
        $request->session()->forget('partner_withdrawal_pending');
        $automatic = $this->payouts->approveForAutomaticExecution($withdrawal);
        $message = $automatic
            ? 'Votre demande de retrait #'.$withdrawal->id.' est confirmée et transmise de façon sécurisée au prestataire.'
            : 'Votre demande de retrait #'.$withdrawal->id.' est enregistrée et réservée pour contrôle.';
        return redirect()->route('partner.withdrawals')->with('success', $message);
    }

}
