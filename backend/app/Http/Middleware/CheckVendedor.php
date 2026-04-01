<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class CheckVendedor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !in_array(Auth::user()->perfil, ['vendedor', 'gestor'])) {
            abort(403, 'Acesso não autorizado. Área exclusiva para Vendedores e Gestores.');
        }
        return $next($request);
    }
}
