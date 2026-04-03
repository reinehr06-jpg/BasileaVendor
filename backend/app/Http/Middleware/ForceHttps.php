<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        URL::forceScheme('https');
        $request->server->set('HTTPS', 'on');
        $request->headers->set('X-Forwarded-Proto', 'https');
        return $next($request);
    }
}
