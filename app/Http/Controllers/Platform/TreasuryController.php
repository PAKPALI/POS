<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformWithdrawal;
use App\Models\PlatformWithdrawalAccount;
use App\Services\PartnerCountryService;
use App\Services\PartnerWithdrawalService;
use App\Services\PlatformTreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

class TreasuryController extends Controller
{
    public function index(PlatformTreasuryService $treasury, PartnerCountryService $countries, PartnerWithdrawalService $partnerWithdrawals)
    {
        $admin = Auth::guard('platform')->user();
        $accounts = PlatformWithdrawalAccount::query()->where('platform_admin_id', $admin->id)->latest('is_primary')->latest()->get();
        $withdrawals = PlatformWithdrawal::query()->with('account')->latest('id')->paginate(10);
        $activeGateways = collect($countries->activeCountries())->mapWithKeys(fn ($country) => [$country['code'] => $partnerWithdrawals->gatewaysFor($country['code'])])->all();
        $adminName = trim((string) $admin->name);
        $adminNameParts = preg_split('/\s+/', $adminName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $defaultBeneficiaryName = count($adminNameParts) >= 2 ? $adminName : '';
        // Un compte vérifié est conservé pour l’audit, mais n’est jamais proposé
        // si son opérateur a été désactivé dans les paramètres de la plateforme.
        $withdrawalAccounts = $accounts->filter(fn (PlatformWithdrawalAccount $account) => $account->status === 'verified'
            && in_array($account->gateway, $activeGateways[$account->country_code] ?? [], true));
        return view('platform.treasury.index', [
            'overview' => $treasury->overview(), 'accounts' => $accounts, 'withdrawals' => $withdrawals,
            'countries' => $countries->activeCountries(), 'gatewayCatalog' => config('partners.payout_gateway_catalog', []),
            'defaultBeneficiaryName' => $defaultBeneficiaryName,
            
            'activeGateways' => $activeGateways, 'withdrawalAccounts' => $withdrawalAccounts,
        ]);
    }

    public function registerAccount(Request $request, PlatformTreasuryService $treasury)
    {
        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:2'], 'gateway' => ['required', 'string', 'max:40'],
            'phone_number' => ['required', 'string', 'max:24'], 'beneficiary_name' => ['required', 'string', 'max:120'],
        ], $this->messages());
        $admin = Auth::guard('platform')->user();
        try {
            $account = $treasury->registerAccount($admin, $validated, $request);
            $treasury->sendChallenge($admin, 'account', ['account_id' => $account->id], $request);
            return back()->with('success', 'Compte enregistré. Un code de vérification vient d’être envoyé à votre e-mail administrateur.');
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['account' => $this->message($exception)]);
        }
    }

    public function verifyAccount(Request $request, PlatformTreasuryService $treasury)
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']], ['code.required' => 'Saisissez le code reçu par e-mail.', 'code.digits' => 'Le code doit contenir exactement 6 chiffres.']);
        try {
            $treasury->verifyAccount(Auth::guard('platform')->user(), $validated['code'], $request);
            return back()->with('success', 'Le compte Mobile Money est vérifié et utilisable pour les retraits de trésorerie.');
        } catch (Throwable $exception) {
            return back()->withErrors(['account_code' => $this->message($exception)]);
        }
    }

    public function startWithdrawal(Request $request, PlatformTreasuryService $treasury)
    {
        $validated = $request->validate([
            'platform_withdrawal_account_id' => ['required', 'integer'], 'amount' => ['required', 'integer', 'min:1'],
            'current_password' => ['required', 'current_password:platform'],
        ], ['platform_withdrawal_account_id.required' => 'Sélectionnez un compte Mobile Money vérifié.', 'amount.required' => 'Saisissez le montant à retirer.', 'amount.integer' => 'Le montant doit être un nombre entier en XOF.', 'amount.min' => 'Le montant doit être supérieur à zéro.', 'current_password.required' => 'Saisissez votre mot de passe plateforme.', 'current_password.current_password' => 'Le mot de passe plateforme est incorrect.']);
        $admin = Auth::guard('platform')->user();
        $overview = $treasury->overview();
        $estimated = (int) ceil($validated['amount'] * $overview['fee_bps'] / 10000);
        if ($validated['amount'] + $estimated > $overview['admin_withdrawable']) return back()->withErrors(['amount' => 'Le montant demandé et son plafond de frais dépassent la capacité retirable du coffre.']);
        if (!PlatformWithdrawalAccount::query()->whereKey($validated['platform_withdrawal_account_id'])->where('platform_admin_id', $admin->id)->where('status', 'verified')->exists()) return back()->withErrors(['platform_withdrawal_account_id' => 'Ce compte n’est pas vérifié ou ne vous appartient pas.']);
        try {
            $treasury->sendChallenge($admin, 'withdrawal', ['account_id' => (int) $validated['platform_withdrawal_account_id'], 'amount' => (int) $validated['amount']], $request);
            return back()->with('success', 'Code envoyé. Saisissez-le ci-dessous pour lancer le retrait.');
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['withdrawal' => 'Impossible d’envoyer le code de confirmation. Aucun retrait n’a été créé.']);
        }
    }

    public function confirmWithdrawal(Request $request, PlatformTreasuryService $treasury)
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']], ['code.required' => 'Saisissez le code reçu par e-mail.', 'code.digits' => 'Le code doit contenir exactement 6 chiffres.']);
        try {
            $withdrawal = $treasury->createWithdrawal(Auth::guard('platform')->user(), $validated['code'], $request);
            return back()->with('success', 'Retrait #'.$withdrawal->id.' créé et envoyé à la file sécurisée. Le résultat sera confirmé par KPrimePay.');
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['withdrawal_code' => $this->message($exception)]);
        }
    }

    private function messages(): array
    {
        return ['country_code.required' => 'Sélectionnez le pays du compte.', 'country_code.size' => 'Le pays sélectionné est invalide.', 'gateway.required' => 'Sélectionnez un opérateur.', 'phone_number.required' => 'Saisissez le numéro Mobile Money.', 'phone_number.max' => 'Le numéro est trop long.', 'beneficiary_name.required' => 'Saisissez le prénom et le nom du bénéficiaire.'];
    }

    private function message(Throwable $exception): string
    {
        return match ($exception instanceof RuntimeException ? $exception->getMessage() : '') {
            'PAYOUT_COUNTRY_INACTIVE' => 'Ce pays n’est pas actif dans les paramètres partenaires.',
            'PAYOUT_GATEWAY_INVALID', 'PAYOUT_GATEWAY_DISABLED' => 'Cet opérateur de retrait n’est pas activé dans les paramètres.',
            'PAYOUT_PHONE_LENGTH_INVALID' => 'Le numéro Mobile Money doit contenir exactement 8 chiffres.',
            'PAYOUT_PHONE_PREFIX_INVALID' => 'Le préfixe ne correspond pas à l’opérateur sélectionné.',
            'PAYOUT_PHONE_DUPLICATE' => 'Ce numéro Mobile Money est déjà enregistré. Utilisez le compte existant ou choisissez un autre numéro.',
            'PAYOUT_BENEFICIARY_NAME_INVALID' => 'Indiquez le prénom et le nom du bénéficiaire.',
            'PAYOUT_CODE_EXPIRED' => 'Le code a expiré. Demandez-en un nouveau.',
            'PAYOUT_CODE_LOCKED' => 'Trop de tentatives. Demandez un nouveau code.',
            'PAYOUT_CODE_INVALID' => 'Le code saisi est incorrect.',
            'PAYOUT_ACCOUNT_NOT_VERIFIED' => 'Le compte Mobile Money doit être vérifié avant un retrait.',
            'PAYOUT_CHALLENGE_INVALID' => 'La demande de retrait est invalide. Recommencez la confirmation.',
            'PLATFORM_PAYOUT_ALREADY_OPEN' => 'Un retrait de trésorerie est déjà en cours de traitement ou de vérification.',
            'PLATFORM_PAYOUT_AMOUNT_OUT_OF_RANGE' => 'Le montant dépasse la capacité de trésorerie disponible.',
            default => 'Cette opération n’a pas pu être finalisée. Aucun retrait n’a été envoyé.',
        };
    }
}
