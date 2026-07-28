<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     * Registra ações destrutivas ou de criação no banco.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apenas logar métodos que causam mutação
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $response->isSuccessful()) {
            
            // Opcional: Ignorar rotas de login/autenticação para não salvar senhas no log
            if ($request->routeIs('login.*') || $request->is('api/login')) {
                return $response;
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'http_' . strtolower($request->method()),
                'model_type' => 'Route:' . $request->path(),
                'model_id' => 0, // 0 indica que é um log de rota
                'old_values' => null,
                'new_values' => $request->except(['password', 'password_confirmation', 'token']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
