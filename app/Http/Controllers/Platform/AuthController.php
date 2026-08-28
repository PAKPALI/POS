<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Mail\PlatformSecurityMail;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::guard('platform')->check()) {
            return redirect()->route('platform.dashboard');
        }

        return response()->view('platform.auth.login')->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $key = 'platform-login|'.$email.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).' secondes.',
            ])->onlyInput('email');
        }

        $admin = PlatformAdmin::where('email', $email)->first();

        if (!$admin || !$admin->is_active || !Hash::check($validated['password'], $admin->password)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Les identifiants fournis sont incorrects.'])->onlyInput('email');
        }

        RateLimiter::clear($key);

        if ($admin->two_factor_enabled) {
            $this->issueTwoFactorCode($request, $admin);
            return redirect()->route('platform.two-factor.challenge');
        }

        return $this->completeLogin($request, $admin);
    }

    public function showTwoFactor(Request $request)
    {
        abort_unless($request->session()->has('platform_2fa_admin_id'), 403);
        return response()->view('platform.auth.two-factor')->withHeaders(['Cache-Control' => 'no-store']);
    }

    public function verifyTwoFactor(Request $request)
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $admin = PlatformAdmin::find($request->session()->get('platform_2fa_admin_id'));

        if (!$admin || !$admin->is_active || !$admin->two_factor_code || !$admin->two_factor_expires_at || $admin->two_factor_expires_at->isPast()) {
            $request->session()->forget('platform_2fa_admin_id');
            return redirect()->route('platform.login')->withErrors(['email' => 'Le code a expiré. Reconnectez-vous pour en recevoir un nouveau.']);
        }

        if ($admin->two_factor_attempts >= 5 || !Hash::check($validated['code'], $admin->two_factor_code)) {
            $admin->increment('two_factor_attempts');
            return back()->withErrors(['code' => 'Le code saisi est incorrect.']);
        }

        $admin->forceFill(['two_factor_code' => null, 'two_factor_expires_at' => null, 'two_factor_attempts' => 0])->save();
        $request->session()->forget('platform_2fa_admin_id');
        return $this->completeLogin($request, $admin);
    }

    public function resendTwoFactor(Request $request)
    {
        $admin = PlatformAdmin::find($request->session()->get('platform_2fa_admin_id'));
        abort_unless($admin && $admin->is_active, 403);
        $this->issueTwoFactorCode($request, $admin);
        return back()->with('success', 'Un nouveau code vient de vous être envoyé.');
    }

    public function showForgotPassword()
    {
        return view('platform.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $email = mb_strtolower(trim($validated['email']));
        $admin = PlatformAdmin::where('email', $email)->where('is_active', true)->first();

        if ($admin) {
            $token = Str::random(64);
            DB::table('platform_password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
            Mail::to($admin->email)->send(new PlatformSecurityMail(
                'Réinitialisation du mot de passe',
                'Une demande de nouveau mot de passe a été effectuée pour votre compte administrateur.',
                actionUrl: route('platform.password.reset', ['token' => $token, 'email' => $email]),
                actionLabel: 'Choisir un nouveau mot de passe',
                expiry: '60 minutes',
            ));
            $this->audit($request, $admin, 'platform.password.reset.requested');
        }

        return back()->with('success', 'Si ce compte existe et est actif, un lien sécurisé vient d’être envoyé.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('platform.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'], 'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $email = mb_strtolower(trim($validated['email']));
        $reset = DB::table('platform_password_reset_tokens')->where('email', $email)->first();
        $admin = PlatformAdmin::where('email', $email)->where('is_active', true)->first();

        if (!$reset || !$admin || now()->diffInMinutes($reset->created_at) > 60 || !Hash::check($validated['token'], $reset->token)) {
            return back()->withErrors(['email' => 'Ce lien est invalide ou a expiré. Demandez un nouveau lien.']);
        }

        DB::transaction(function () use ($admin, $email, $validated) {
            $admin->update(['password' => $validated['password'], 'must_change_password' => false,
                'remember_token' => Str::random(60), 'auth_version' => $admin->auth_version + 1]);
            DB::table('platform_password_reset_tokens')->where('email', $email)->delete();
        });
        $this->audit($request, $admin, 'platform.password.reset.completed');
        return redirect()->route('platform.login')->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
    }

    public function editPassword()
    {
        return view('platform.auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:platform'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.required' => 'Saisissez votre mot de passe actuel.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required' => 'Saisissez un nouveau mot de passe.',
            'password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 12 caractères.',
            'password.mixed' => 'Le nouveau mot de passe doit contenir au moins une lettre majuscule et une lettre minuscule.',
            'password.numbers' => 'Le nouveau mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le nouveau mot de passe doit contenir au moins un symbole, par exemple !, @, # ou $.',
        ]);

        $admin = Auth::guard('platform')->user();
        $admin->update([
            'password' => $validated['password'],
            'must_change_password' => false,
            'remember_token' => Str::random(60),
            'auth_version' => $admin->auth_version + 1,
        ]);
        $request->session()->put('platform_auth_version', $admin->auth_version);

        PlatformAuditLog::create([
            'platform_admin_id' => $admin->id,
            'action' => 'platform.password.changed',
            'target_type' => PlatformAdmin::class,
            'target_id' => (string) $admin->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
        ]);

        return redirect()->route('platform.dashboard')->with('success', 'Votre mot de passe a été sécurisé.');
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('platform')->user();
        if ($admin) {
            PlatformAuditLog::create([
                'platform_admin_id' => $admin->id,
                'action' => 'platform.logout',
                'target_type' => PlatformAdmin::class,
                'target_id' => (string) $admin->id,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            ]);
        }

        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }

    private function issueTwoFactorCode(Request $request, PlatformAdmin $admin): void
    {
        $code = (string) random_int(100000, 999999);
        $expiry = app(\App\Services\PlatformConfigurationService::class)->integer('security.two_factor_expiry_minutes', 10);
        $admin->forceFill(['two_factor_code' => Hash::make($code), 'two_factor_expires_at' => now()->addMinutes($expiry), 'two_factor_attempts' => 0])->save();
        $request->session()->put('platform_2fa_admin_id', $admin->id);
        Mail::to($admin->email)->send(new PlatformSecurityMail('Code de connexion', 'Saisissez ce code pour terminer votre connexion à la console SaaS.', $code));
        $this->audit($request, $admin, 'platform.two_factor.sent');
    }

    private function completeLogin(Request $request, PlatformAdmin $admin)
    {
        Auth::guard('platform')->login($admin);
        $request->session()->regenerate();
        $request->session()->put('platform_auth_version', $admin->auth_version);
        $admin->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        $this->audit($request, $admin, 'platform.login');
        return redirect()->intended($admin->must_change_password ? route('platform.password.edit') : route('platform.dashboard'));
    }

    private function audit(Request $request, PlatformAdmin $admin, string $action): void
    {
        PlatformAuditLog::create(['platform_admin_id' => $admin->id, 'action' => $action,
            'target_type' => PlatformAdmin::class, 'target_id' => (string) $admin->id,
            'ip_address' => $request->ip(), 'user_agent' => Str::limit((string) $request->userAgent(), 1000, '')]);
    }
}
