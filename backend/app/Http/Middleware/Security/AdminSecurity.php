<?php

namespace App\Http\Middleware\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        $user = Auth::user();
        
        // Check if user is ADM
        if ($user->perfil !== 'master') {
            Log::warning('Unauthorized access attempt to admin area', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            abort(403, 'Acesso negado. Esta área é restrita apenas para administradores.');
        }

        // IP Whitelist Check for ADM (if configured)
        if (!empty($user->allowed_ips)) {
            $allowedIps = json_decode($user->allowed_ips, true);
            $clientIp = $request->ip();
            
            if (!empty($allowedIps) && !in_array($clientIp, $allowedIps)) {
                Log::warning('Admin login from unauthorized IP', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $clientIp,
                    'url' => $request->fullUrl()
                ]);
                
                abort(403, 'Acesso negado. Seu IP não está autorizado para acessar esta área.');
            }
        }

        // Account lockout check
        if (!is_null($user->account_locked_until) && $user->account_locked_until > now()) {
            Log::warning('Login attempt on locked account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            abort(403, 'Conta temporariamente bloqueada devido a múltiplas tentativas de login falhas.');
        }


        // Log admin access for audit
        Log::info('Admin area accessed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent()
        ]);

        // Update last activity
        $user->last_login_at = now();
        $user->login_ip = $request->ip();
        $user->save();

        return $next($request);
    }
}