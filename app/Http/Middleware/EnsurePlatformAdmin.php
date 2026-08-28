<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('platform')->user();

        if (!$admin) {
            return redirect()->guest(route('platform.login'));
        }

        $securityChanged = (int) $request->session()->get('platform_auth_version', 0) !== (int) $admin->auth_version;

        if (!$admin->is_active || $securityChanged) {
            Auth::guard('platform')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('platform.login')
                ->withErrors(['email' => 'Ce compte d’administration est désactivé.']);
        }

        return $next($request);
    }
}
