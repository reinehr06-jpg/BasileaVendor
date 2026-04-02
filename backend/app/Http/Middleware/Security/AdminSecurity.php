<?php

namespace App\Http\Middleware\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // IP Whitelist Check for ADM (if configured and column exists)
        if (Schema::hasColumn('users', 'allowed_ips') && !empty($user->allowed_ips)) {
            try {
                $allowedIps = json_decode($user->allowed_ips, true);
                $clientIp = $request->ip();
                
                if (!empty($allowedIps) && !in_array($clientIp, $allowedIps)) {
                    abort(403, 'Acesso negado. Seu IP não está autorizado para acessar esta área.');
                }
            } catch (\Exception $e) {
                // Ignorar
            }
        }

        // Account lockout check (só se coluna existir)
        if (Schema::hasColumn('users', 'account_locked_until')) {
            try {
                if (!is_null($user->account_locked_until) && $user->account_locked_until > now()) {
                    abort(403, 'Conta temporariamente bloqueada devido a múltiplas tentativas de login falhas.');
                }
            } catch (\Exception $e) {
                // Ignorar
            }
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

        // Update last activity (só se colunas existirem)
        try {
            $updateData = [];
            if (Schema::hasColumn('users', 'last_login_at')) {
                $updateData['last_login_at'] = now();
            }
            if (Schema::hasColumn('users', 'login_ip')) {
                $updateData['login_ip'] = $request->ip();
            }
            if (!empty($updateData)) {
                DB::table('users')->where('id', $user->id)->update($updateData);
            }
        } catch (\Exception $e) {
            // Ignorar erro de coluna inexistente
        }

        return $next($request);
    }
}