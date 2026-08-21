<?php

namespace App\Http\Middleware;

use App\Services\AuthorizedLandingPage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function __construct(private AuthorizedLandingPage $landingPage) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->landingPage->forUser(
                    Auth::guard($guard)->user(),
                    $request->session()->get('active_company_id')
                ));
            }
        }

        return $next($request);
    }
}
