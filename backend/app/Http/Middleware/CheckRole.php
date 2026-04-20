<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }

        $user = Auth::user();
        $allowedRoles = explode(',', $roles);

        if (!in_array($user->perfil, $allowedRoles)) {
            abort(403, 'Forbidden. You do not have access to this resource.');
        }

        return $next($request);
    }
}