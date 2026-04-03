<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\URL;

// Force HTTPS scheme BEFORE any middleware runs (fixes session cookie + CSRF)
if (isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_ENV['APP_URL']) && str_starts_with($_ENV['APP_URL'], 'https://'))) {
    URL::forceScheme('https');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Force HTTPS BEFORE session middleware
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        
        $middleware->validateCsrfTokens(except: [
            'api/asaas/webhook',
            'api/checkout/*',
            'webhook/saque',
            'webhook/basileia-church/*',
            'webhook/checkout',
            'webhooks/asaas',
            'webhooks/asaas/*',
        ]);
        $middleware->append(\App\Http\Middleware\ClearStaleCache::class);
        
        // Trust all proxies behind load balancer / reverse proxy (HTTPS)
        $middleware->trustProxies(at: '*');
        
        // Register security middleware groups
        $middleware->group('admin.security', [
            \App\Http\Middleware\Security\AdminSecurity::class,
            \App\Http\Middleware\Security\RateLimitByRole::class,
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);
        
        // Apply admin security to all master routes
        $middleware->alias([
            'admin.security' => \App\Http\Middleware\Security\AdminSecurity::class,
            'role.rate.limit' => \App\Http\Middleware\Security\RateLimitByRole::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'api.key' => \App\Http\Middleware\ApiKeyAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
