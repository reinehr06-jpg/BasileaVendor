<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
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
        if (Auth::check()) {
            $user = Auth::user();

            // Redirect to password change if required (só se coluna existir)
            try {
                if (Schema::hasColumn('users', 'require_password_change') &&
                    $user->require_password_change && 
                    !$request->is('password/*') && 
                    !$request->is('logout') &&
                    !$request->is('api/*')) {
                    
                    return redirect()->route('password.change')
                        ->with('warning', 'Você deve alterar sua senha provisória antes de continuar.');
                }
            } catch (\Exception $e) {
                // Ignorar erro de coluna inexistente
            }
        }

        return $next($request);
    }
}
