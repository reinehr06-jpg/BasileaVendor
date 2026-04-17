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

        // Allow access to 2FA setup, verify, enable, disable, logout, password routes and chat routes
        if ($request->is('2fa/*') || $request->is('logout') || $request->is('password/*')
            || $request->is('admin/chat*') || $request->is('chat*') || $request->is('webhook/chat*')) {
            return $next($request);
        }

        // If 2FA is not enabled, force user to set it up
        // Redirect to 2fa.setup which is OUTSIDE the 2fa middleware group
        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup')
                ->with('warning', '⚠️ Acesso bloqueado: Você deve configurar a autenticação em duas etapas (2FA) antes de usar o sistema.');
        }

        // If 2FA is enabled but not verified this session, require verification
        if (!Session::get('2fa_verified_' . $user->id)) {
            return redirect()->route('2fa.verify')
                ->with('warning', '🔒 Acesso bloqueado: Verifique sua identidade com o código do app autenticador.');
        }

        return $next($request);
    }
}
