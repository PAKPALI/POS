<?php

namespace App\Http\Controllers\Partner;

use App\Exceptions\PartnerCodeChangeTooSoon;
use App\Http\Controllers\Controller;
use App\Services\PartnerCodeService;
use App\Services\PlatformConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class CodeController extends Controller
{
    public function __construct(
        private PartnerCodeService $codes,
        private PlatformConfigurationService $configuration,
    ) {}

    public function show()
    {
        $partner = Auth::guard('partner')->user();
        $code = $this->codes->ensurePrimaryCode($partner);

        return view('partner.code', [
            'partner' => $partner,
            'code' => $code,
            'history' => $this->codes->history($partner),
            'discountPercent' => intdiv($this->configuration->integer('partners.first_discount_bps', 1000), 100),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{4,24}$/'],
        ], [
            'code.required' => 'Saisissez votre code partenaire.',
            'code.string' => 'Le code partenaire doit être renseigné sous forme de texte.',
            'code.regex' => 'Le code doit contenir 4 à 24 lettres ou chiffres, sans espace ni symbole.',
        ]);

        try {
            $code = $this->codes->customize(
                Auth::guard('partner')->user(),
                $validated['code'],
                (string) $request->ip(),
                (string) $request->userAgent(),
            );
        } catch (PartnerCodeChangeTooSoon $exception) {
            return back()->withErrors(['code' => $exception->getMessage()])->withInput();
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['code' => $exception->getMessage()])->withInput();
        }

        return back()->with('success', 'Votre code partenaire « '.$code->code.' » est maintenant actif.');
    }

    public function availability(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        return response()->json($this->codes->availability($partner, (string) $request->input('code', '')));
    }

    public function validatePublic(Request $request)
    {
        $keys = array_unique([
            'partner-code-ip|'.$request->ip(),
            'partner-code-session|'.$request->session()->getId(),
            'partner-code-account|'.(string) (Auth::guard('partner')->id() ?: 'guest'),
        ]);
        foreach ($keys as $key) {
            if (RateLimiter::tooManyAttempts($key, 10)) {
                return response()->json([
                    'valid' => false,
                    'discount_percent' => 0,
                    'message' => 'La vérification est temporairement limitée. Réessayez dans quelques instants.',
                ], 429);
            }
        }
        foreach ($keys as $key) {
            RateLimiter::hit($key, 60);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:24'],
        ], [
            'code.required' => 'Saisissez un code partenaire.',
            'code.string' => 'Le code partenaire doit être renseigné sous forme de texte.',
            'code.max' => 'Le code partenaire ne doit pas dépasser 24 caractères.',
        ]);

        if (!$this->configuration->boolean('partners.enabled', false)) {
            return response()->json([
                'valid' => false,
                'discount_percent' => 0,
                'message' => 'Ce code partenaire n’est pas disponible.',
            ]);
        }

        $code = $this->codes->findPublic($validated['code']);
        $discountPercent = intdiv($this->configuration->integer('partners.first_discount_bps', 1000), 100);

        return response()->json($code ? [
            'valid' => true,
            'discount_percent' => $discountPercent,
            'message' => 'Code partenaire valide. La remise s’appliquera au premier abonnement éligible.',
        ] : [
            'valid' => false,
            'discount_percent' => 0,
            'message' => 'Ce code partenaire n’est pas disponible.',
        ]);
    }
}
