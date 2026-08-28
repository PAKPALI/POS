<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = Auth::guard('platform')->user();
        abort_unless($admin && $admin->hasPlatformPermission($permission), 403, 'Votre rôle plateforme ne permet pas d’accéder à cette fonctionnalité.');
        return $next($request);
    }
}
