<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckChatPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $chatEnabled = \App\Models\Setting::get('chat_enabled', false);
        if (!$chatEnabled && $user->perfil !== 'master') {
            return response()->json(['error' => 'Chat desabilitado'], 403);
        }

        return $next($request);
    }
}