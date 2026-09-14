<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $partner = Auth::guard('partner')->user();
        $sessionVersion = (int) $request->session()->get('partner_auth_version', 0);

        if (!$partner || $partner->status !== 'active' || $sessionVersion !== (int) $partner->auth_version) {
            Auth::guard('partner')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('partner.login')->withErrors(['email' => 'Cette session partenaire n’est plus active.']);
        }

        return $next($request);
    }
}
