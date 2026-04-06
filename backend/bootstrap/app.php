<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\File;

// Auto-clear stale caches after deploy (routes, config, views)
// This runs only once per deploy when bootstrap/app.php changes
$cacheCheckFile = __DIR__ . '/../storage/framework/cache/.last_cache_check';
$bootstrapMtime = filemtime(__FILE__);
$shouldClear = false;

if (!File::exists($cacheCheckFile)) {
    $shouldClear = true;
} else {
    $lastCheck = (int) File::get($cacheCheckFile);
    if ($bootstrapMtime > $lastCheck) {
        $shouldClear = true;
    }
}

if ($shouldClear) {
    try {
        File::ensureDirectoryExists(__DIR__ . '/../storage/framework/cache');
        File::put($cacheCheckFile, (string) $bootstrapMtime);
        
        // Clear route cache (most common cause of 404s after deploy)
        if (File::exists(__DIR__ . '/../bootstrap/cache/routes-v7.php')) {
            File::delete(__DIR__ . '/../bootstrap/cache/routes-v7.php');
        }
        if (File::exists(__DIR__ . '/../bootstrap/cache/routes.php')) {
            File::delete(__DIR__ . '/../bootstrap/cache/routes.php');
        }
        
        // Clear config cache
        if (File::exists(__DIR__ . '/../bootstrap/cache/config.php')) {
            File::delete(__DIR__ . '/../bootstrap/cache/config.php');
        }
        
        // Clear compiled services/packages
        if (File::exists(__DIR__ . '/../bootstrap/cache/services.php')) {
            File::delete(__DIR__ . '/../bootstrap/cache/services.php');
        }
        if (File::exists(__DIR__ . '/../bootstrap/cache/packages.php')) {
            File::delete(__DIR__ . '/../bootstrap/cache/packages.php');
        }
    } catch (\Throwable $e) {
        // Silently fail - don't break the app
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/asaas/webhook',
            'webhook/basileia-church/*',
            'webhook/checkout',
            'webhooks/asaas',
            'webhooks/asaas/*',
        ]);

        // Security headers on every response
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
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
            '2fa' => \App\Http\Middleware\TwoFactorMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Hide error details in production
        if (app()->environment('production')) {
            $exceptions->dontReport(\Illuminate\Auth\AuthenticationException::class);
            $exceptions->dontReport(\Illuminate\Validation\ValidationException::class);
        }
    })->create();
