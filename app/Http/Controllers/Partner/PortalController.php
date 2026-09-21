<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerCodeService;
use App\Services\PartnerAuthenticationService;
use App\Services\PlatformConfigurationService;
use App\Models\Partner;
use App\Models\PartnerAuditLog;
use App\Notifications\PartnerEmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PortalController extends Controller
{
    public function dashboard(PartnerCodeService $codes, PlatformConfigurationService $configuration)
    {
        $partner = Auth::guard('partner')->user();
        return view('partner.dashboard', [
            'partner' => $partner,
            'code' => $codes->ensurePrimaryCode($partner),
            'discountPercent' => intdiv($configuration->integer('partners.first_discount_bps', 1000), 100),
        ]);
    }

    public function profile()
    {
        return view('partner.profile', ['partner' => Auth::guard('partner')->user()]);
    }

    public function guide()
    {
        return view('partner.guide', ['partner' => Auth::guard('partner')->user()]);
    }

    public function updateAppearance(Request $request)
    {
        $validated = $request->validate([
            'appearance_mode' => ['required', 'in:system,light,dark'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'appearance_mode.required' => 'Sélectionnez un mode d’affichage.',
            'appearance_mode.in' => 'Le mode d’affichage sélectionné est invalide.',
            'accent_color.required' => 'Sélectionnez une couleur d’accent.',
            'accent_color.regex' => 'La couleur d’accent sélectionnée est invalide.',
        ]);
        $partner = Auth::guard('partner')->user();
        $partner->update([
            'appearance_mode' => $validated['appearance_mode'],
            'accent_color' => strtoupper($validated['accent_color']),
        ]);
        $this->audit($partner, 'partner.profile_appearance_updated', $request);
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'msg' => 'Vos préférences d’apparence ont été enregistrées.', 'appearance' => ['mode' => $partner->appearance_mode, 'accent' => $partner->accent_color]]);
        }
        return back()->with('success', 'Vos préférences d’apparence ont été enregistrées.');
    }

    public function updateIdentity(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        $request->merge(['username' => strtolower(trim((string) $request->input('username')))]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'regex:/^[a-z0-9_-]{3,30}$/', Rule::unique('partners', 'normalized_username')->ignore($partner->id)],
            'current_password' => ['required', 'current_password:partner'],
        ]);
        $partner->update(['name' => trim($validated['name']), 'username' => $validated['username'], 'normalized_username' => $validated['username']]);
        $this->audit($partner, 'partner.profile_identity_updated', $request);
        return back()->with('success', 'Vos informations de profil ont été enregistrées.');
    }

    public function updateEmail(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $validated = $request->validate(['email' => ['required', 'email', 'max:255', Rule::unique('partners', 'normalized_email')->ignore($partner->id)], 'current_password' => ['required', 'current_password:partner']]);
        $partner->forceFill(['email' => $validated['email'], 'normalized_email' => $validated['email'], 'email_verified_at' => null, 'status' => 'pending_email', 'auth_version' => $partner->auth_version + 1])->save();
        $this->audit($partner, 'partner.profile_email_change_requested', $request);
        $partner->notify(new PartnerEmailVerificationNotification($partner));
        Auth::guard('partner')->logout();
        $request->session()->forget('partner_auth_version');
        $request->session()->regenerate();
        return redirect()->route('partner.login')->with('status', 'Confirmez votre nouvelle adresse e-mail pour réactiver votre compte partenaire.');
    }

    public function updatePassword(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        $validated = $request->validate(['current_password' => ['required', 'current_password:partner'], 'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()]]);
        $partner->forceFill(['password' => $validated['password'], 'auth_version' => $partner->auth_version + 1])->save();
        $this->audit($partner, 'partner.profile_password_updated', $request);
        $request->session()->put('partner_auth_version', $partner->auth_version);
        return back()->with('success', 'Votre mot de passe a été mis à jour.');
    }

    public function updateTwoFactor(Request $request, PartnerAuthenticationService $authentication)
    {
        $partner = Auth::guard('partner')->user();
        $enabled = $request->boolean('enabled');

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:partner'],
            'code' => ['nullable', 'digits:6'],
        ], [
            'current_password.required' => 'Saisissez votre mot de passe actuel.',
            'current_password.current_password' => 'Votre mot de passe actuel est incorrect.',
            'code.digits' => 'Le code de sécurité doit comporter 6 chiffres.',
        ]);

        if (!$enabled) {
            $partner->forceFill([
                'two_factor_login_enabled' => false,
                'auth_version' => $partner->auth_version + 1,
            ])->save();
            $request->session()->put('partner_auth_version', $partner->auth_version);
            $request->session()->forget('partner_2fa_setup_partner_id');
            $this->audit($partner, 'partner.two_factor_disabled', $request);

            return back()->with('success', 'La double authentification est désactivée pour votre compte.');
        }

        if ($partner->two_factor_login_enabled) {
            return back()->with('success', 'La double authentification est déjà activée.');
        }

        if (!$request->filled('code')) {
            $authentication->issueTwoFactorSetup($partner, $request);

            return back()
                ->with('two_factor_setup_pending', true)
                ->with('status', 'Un code de confirmation vient d’être envoyé à votre adresse e-mail.');
        }

        if ((int) $request->session()->get('partner_2fa_setup_partner_id') !== (int) $partner->id
            || !$authentication->verifyTwoFactorSetup($partner, (string) $validated['code'])) {
            return back()->withErrors(['code' => 'Le code est incorrect, expiré ou déjà utilisé.']);
        }

        $partner->forceFill([
            'two_factor_login_enabled' => true,
            'auth_version' => $partner->auth_version + 1,
        ])->save();
        $request->session()->put('partner_auth_version', $partner->auth_version);
        $request->session()->forget('partner_2fa_setup_partner_id');
        $this->audit($partner, 'partner.two_factor_enabled', $request);

        return back()->with('success', 'La double authentification est maintenant activée.');
    }

    private function audit(Partner $partner, string $action, Request $request): void
    {
        PartnerAuditLog::create(['partner_id' => $partner->id, 'action' => $action, 'target_type' => Partner::class, 'target_id' => (string) $partner->id, 'ip_address' => $request->ip(), 'user_agent_hash' => hash('sha256', (string) $request->userAgent())]);
    }
}
