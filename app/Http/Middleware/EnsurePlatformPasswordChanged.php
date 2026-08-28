<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('platform')->user()?->must_change_password) {
            return redirect()->route('platform.password.edit')
                ->with('warning', 'Choisissez un nouveau mot de passe avant d’accéder à la console.');
        }

        return $next($request);
    }
}
