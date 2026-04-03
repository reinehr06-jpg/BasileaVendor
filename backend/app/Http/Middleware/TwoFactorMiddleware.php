<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip if 2FA not enabled
        if (!$user->two_factor_enabled) {
            return $next($request);
        }

        // Skip if already verified this session
        if (Session::get('2fa_verified_' . $user->id)) {
            return $next($request);
        }

        // Skip 2FA routes to prevent infinite redirect
        if ($request->is('2fa/*') || $request->is('logout')) {
            return $next($request);
        }

        // Redirect to 2FA verification
        return redirect()->route('2fa.verify');
    }
}
