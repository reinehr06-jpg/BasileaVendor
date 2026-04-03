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

        // Allow access to 2FA setup, verify, enable, disable and logout routes
        if ($request->is('2fa/*') || $request->is('logout') || $request->is('password/*')) {
            return $next($request);
        }

        // If 2FA is not enabled, force user to set it up
        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }

        // If 2FA is enabled but not verified this session, require verification
        if (!Session::get('2fa_verified_' . $user->id)) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
