<?php

namespace App\Http\Middleware\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByRole
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
        // Skip rate limiting for certain routes (webhooks, APIs that need high throughput)
        if ($this->shouldSkipRateLimiting($request)) {
            return $next($request);
        }

        $user = Auth::user();
        $ip = $request->ip();
        $route = $request->route()->getName() ?? $request->path();
        
        // Different rate limits based on user role
        if ($user) {
            switch ($user->perfil) {
                case 'master':
                    // ADM: Higher limits for legitimate admin work
                    $limitPerMinute = 120;
                    $key = 'admin:' . $ip . ':' . $route;
                    break;
                    
                case 'gestor':
                    // Gestor: Medium limits
                    $limitPerMinute = 100;
                    $key = 'gestor:' . $ip . ':' . $route;
                    break;
                    
                default:
                    // Vendedor: Standard limits
                    $limitPerMinute = 80;
                    $key = 'vendedor:' . $ip . ':' . $route;
                    break;
            }
        } else {
            // Guest/unauthenticated: Strict limits
            $limitPerMinute = 30;
            $key = 'guest:' . $ip . ':' . $route;
        }

        // Attempt to do the rate limiting
        if (!$this->attempt($request, $key, $limitPerMinute)) {
            // Rate limit exceeded
            $seconds = RateLimiter::availableIn($key);
            
            $headers = [
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $limitPerMinute,
                'X-RateLimit-Remaining' => '0',
                'X-RateLimit-Reset' => time() + $seconds,
            ];

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Rate limit exceeded. Please try again later.'
                ], 429, $headers);
            }

            return redirect()->back()
                ->with('error', 'Muitas solicitações. Por favor, aguarde alguns minutos antes de tentar novamente.')
                ->withHeaders($headers);
        }

        // Add rate limit headers to response
        $response = $next($request);
        
        $remaining = RateLimiter::remaining($key, $limitPerMinute);
        $retryAfter = RateLimiter::availableIn($key);
        
        $response->headers->set('X-RateLimit-Limit', $limitPerMinute);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', time() + $retryAfter);
        $response->headers->set('Retry-After', $retryAfter);
        
        return $response;
    }

    /**
     * Determine if rate limiting should be skipped for this request.
     */
    protected function shouldSkipRateLimiting(Request $request): bool
    {
        // Skip for webhooks and health checks
        $skipPaths = [
            'api/asaas/webhook',
            'webhook/saque',
            'webhook/basileia-church/sync',
            '/up',
            '/health'
        ];

        foreach ($skipPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attempt to perform the rate limiting action.
     */
    protected function attempt(Request $request, string $key, int $limitPerMinute): bool
    {
        return RateLimiter::attempt(
            $key,
            $limitPerMinute,
            function () {
                return true;
            }
        );
    }
}