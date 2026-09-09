<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePartner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('partner')->check()) {
            return redirect()->guest(route('partner.login'));
        }

        return $next($request);
    }
}
