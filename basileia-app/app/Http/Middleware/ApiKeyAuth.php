<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');

        if (!$key) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $apiKey = ApiKey::where('key', hash('sha256', $key))
            ->where('active', true)
            ->first();

        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        if ($apiKey->allowed_ips && count($apiKey->allowed_ips) > 0) {
            if (!in_array($request->ip(), $apiKey->allowed_ips)) {
                return response()->json(['error' => 'IP not allowed'], 403);
            }
        }

        $apiKey->markUsed();
        $request->merge(['api_key_service' => $apiKey->service, 'api_key_id' => $apiKey->id]);

        return $next($request);
    }
}
