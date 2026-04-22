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

        // Allow only auth-management routes before full 2FA gate
        if ($request->is('2fa/*') || $request->is('logout') || $request->is('password/*') || $request->is('password-change')) {
            return $next($request);
        }

        // 1. PRIORITY: If user must change password, let them do it first
        if ($user->require_password_change) {
            return $next($request);
        }

        // 2. If 2FA is not enabled, force setup
        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup')
                ->with('warning', '⚠️ Acesso bloqueado: Você deve configurar autenticação em duas etapas (2FA).');
        }

        // 3. If 2FA is enabled but not verified this session, require verification
        $verified = Session::get('2fa_verified_' . $user->id) === true;
        if (!$verified) {
            return redirect()->route('2fa.verify')
                ->with('warning', '🔒 Acesso bloqueado: Verifique sua identidade com o código do app autenticador.');
        }

        return $next($request);
    }
}
