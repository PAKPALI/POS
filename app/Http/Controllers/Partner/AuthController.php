<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\PartnerAuthenticationService;
use App\Services\PartnerCountryService;
use App\Services\PlatformConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function __construct(
        private PartnerAuthenticationService $authentication,
        private PlatformConfigurationService $configuration,
        private PartnerCountryService $countries,
    ) {}

    public function showLogin()
    {
        if (Auth::guard('partner')->check()) return redirect()->route('partner.dashboard');
        return view('partner.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']], $this->loginValidationMessages());
        if (!$this->configuration->boolean('partners.enabled', (bool) config('partners.enabled', false))) {
            return back()->withErrors(['email' => 'L’espace partenaire est temporairement indisponible.'])->onlyInput('email');
        }

        $email = mb_strtolower(trim($validated['email']));
        $partner = Partner::where('normalized_email', $email)->first();
        if ($partner && $partner->status === 'pending_email' && !$partner->email_verified_at && Hash::check($validated['password'], $partner->password)) {
            return back()->withErrors([
                'email' => 'Votre inscription n’est pas encore validée. Cliquez sur le lien envoyé par e-mail pour activer votre compte, puis revenez vous connecter.',
            ])->onlyInput('email');
        }
        if (!$partner || $partner->status !== 'active' || !$partner->email_verified_at || !Hash::check($validated['password'], $partner->password)) {
            return back()->withErrors(['email' => 'Les identifiants fournis sont incorrects.'])->onlyInput('email');
        }

        if ($partner->two_factor_login_enabled) {
            $this->authentication->issueTwoFactor($partner, $request);
            return redirect()->route('partner.two-factor.challenge');
        }

        return $this->completeLogin($request, $partner);
    }

    public function showRegister()
    {
        return view('partner.auth.register', ['countries' => $this->countries->activeCountries()]);
    }

    public function register(Request $request)
    {
        if (!$this->configuration->boolean('partners.registration_enabled', (bool) config('partners.registration_enabled', false))) {
            return back()->withErrors(['email' => 'Les inscriptions partenaires sont temporairement fermées.'])->withInput();
        }

        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'username' => strtolower(trim((string) $request->input('username'))),
            'country_code' => strtoupper(trim((string) $request->input('country_code'))),
            'phone_number' => trim((string) $request->input('phone_number')),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'regex:/^[a-z0-9_-]{3,30}$/', 'unique:partners,normalized_username'],
            'email' => ['required', 'email', 'max:255', 'unique:partners,normalized_email'],
            'country_code' => ['required', Rule::in($this->countries->activeCodes())],
            'phone_number' => ['required', 'string', 'max:24'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
            'accepted_terms' => ['accepted'],
        ], $this->registrationValidationMessages());
        try {
            $validated['phone_e164'] = $this->countries->normalizePhone($validated['country_code'], $validated['phone_number']);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['phone_number' => $exception->getMessage()]);
        }
        if (Partner::where('phone_e164', $validated['phone_e164'])->exists()) {
            throw ValidationException::withMessages(['phone_number' => 'Ce numéro est déjà associé à un compte partenaire.']);
        }

        $this->authentication->register($validated, $request);
        return redirect()->route('partner.login')->with('status', 'Votre inscription est presque terminée. Un lien de validation vient d’être envoyé à votre adresse e-mail. Cliquez sur ce lien pour activer votre compte, puis revenez vous connecter. Tant que l’adresse n’est pas confirmée, la connexion reste bloquée.');
    }

    public function verifyEmail(Request $request, Partner $partner, string $hash)
    {
        abort_unless(hash_equals(sha1($partner->getEmailForVerification()), $hash), 403);
        $this->authentication->verifyEmail($partner);
        return redirect()->route('partner.login')->with('status', 'Votre adresse est confirmée. Vous pouvez maintenant vous connecter.');
    }

    public function showTwoFactor(Request $request)
    {
        abort_unless($request->session()->has('partner_2fa_partner_id'), 403);
        return view('partner.auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']], $this->twoFactorValidationMessages());
        $partner = Partner::find($request->session()->get('partner_2fa_partner_id'));
        if (!$partner || $partner->status !== 'active' || !$this->authentication->verifyTwoFactor($partner, $validated['code'])) {
            return back()->withErrors(['code' => 'Le code est incorrect, expiré ou déjà utilisé.']);
        }
        $request->session()->forget('partner_2fa_partner_id');
        return $this->completeLogin($request, $partner);
    }

    public function resendTwoFactor(Request $request)
    {
        $partner = Partner::find($request->session()->get('partner_2fa_partner_id'));
        abort_unless($partner && $partner->status === 'active', 403);
        $this->authentication->issueTwoFactor($partner, $request);
        return back()->with('status', 'Un nouveau code vient d’être envoyé.');
    }

    public function showForgotPassword()
    {
        return view('partner.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']], $this->emailValidationMessages());
        $status = Password::broker('partners')->sendResetLink(['email' => mb_strtolower(trim($request->input('email')))]);
        return back()->with('status', 'Si ce compte existe et est actif, un lien sécurisé vient d’être envoyé.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('partner.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'], 'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
        ], $this->resetValidationMessages());
        $status = Password::broker('partners')->reset([
            'email' => mb_strtolower(trim($validated['email'])), 'password' => $validated['password'],
            'password_confirmation' => $request->input('password_confirmation'), 'token' => $validated['token'],
        ], function (Partner $partner, string $password): void {
            $partner->forceFill(['password' => $password, 'auth_version' => $partner->auth_version + 1])->save();
        });
        if ($status !== Password::PASSWORD_RESET) return back()->withErrors(['email' => 'Ce lien est invalide ou a expiré.']);
        return redirect()->route('partner.login')->with('status', 'Votre mot de passe a été réinitialisé.');
    }

    public function logout(Request $request)
    {
        Auth::guard('partner')->logout();
        $request->session()->forget(['partner_auth_version', 'partner_2fa_partner_id']);
        $request->session()->regenerateToken();
        return redirect()->route('partner.login');
    }

    private function completeLogin(Request $request, Partner $partner)
    {
        Auth::guard('partner')->login($partner);
        $request->session()->regenerate();
        $request->session()->put('partner_auth_version', $partner->auth_version);
        $partner->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        return redirect()->intended(route('partner.dashboard'));
    }

    private function passwordValidationMessages(): array
    {
        return [
            'password.required' => 'Saisissez un mot de passe.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 12 caractères.',
            'password.mixed' => 'Le mot de passe doit contenir une majuscule et une minuscule.',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un symbole.',
            'password.uncompromised' => 'Ce mot de passe est trop courant. Choisissez-en un autre.',
        ];
    }

    private function registrationValidationMessages(): array
    {
        return array_merge($this->passwordValidationMessages(), [
            'name.required' => 'Saisissez votre nom complet.',
            'name.string' => 'Le nom complet doit être une chaîne de caractères.',
            'name.max' => 'Le nom complet ne doit pas dépasser 120 caractères.',
            'username.required' => 'Choisissez un pseudonyme partenaire.',
            'username.string' => 'Le pseudonyme doit être une chaîne de caractères.',
            'username.regex' => 'Le pseudonyme doit contenir 3 à 30 caractères : lettres minuscules, chiffres, tiret ou underscore.',
            'username.unique' => 'Ce pseudonyme est déjà utilisé.',
            'email.required' => 'Saisissez votre adresse e-mail.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
            'email.max' => 'L’adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà associée à un compte partenaire.',
            'country_code.required' => 'Sélectionnez votre pays de résidence.',
            'country_code.in' => 'Ce pays n’est pas ouvert aux inscriptions partenaires.',
            'phone_number.required' => 'Saisissez votre numéro.',
            'phone_number.string' => 'Le numéro doit être renseigné sous forme de texte.',
            'phone_number.max' => 'Le numéro ne doit pas dépasser 24 caractères.',
            'accepted_terms.accepted' => 'Vous devez accepter les conditions du programme partenaire.',
        ]);
    }

    private function loginValidationMessages(): array
    {
        return [
            'email.required' => 'Saisissez votre adresse e-mail.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
            'password.required' => 'Saisissez votre mot de passe.',
            'password.string' => 'Le mot de passe doit être renseigné sous forme de texte.',
        ];
    }

    private function emailValidationMessages(): array
    {
        return [
            'email.required' => 'Saisissez votre adresse e-mail.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
        ];
    }

    private function twoFactorValidationMessages(): array
    {
        return [
            'code.required' => 'Saisissez le code de vérification.',
            'code.digits' => 'Le code de vérification doit contenir exactement 6 chiffres.',
        ];
    }

    private function resetValidationMessages(): array
    {
        return array_merge($this->passwordValidationMessages(), [
            'token.required' => 'Le lien de réinitialisation est incomplet.',
            'email.required' => 'Saisissez votre adresse e-mail.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
        ]);
    }
}
