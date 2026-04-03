<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckGestor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !in_array(Auth::user()->perfil, ['gestor'])) {
            abort(403, 'Acesso não autorizado. Área exclusiva para Gestores.');
        }
        return $next($request);
    }
}
