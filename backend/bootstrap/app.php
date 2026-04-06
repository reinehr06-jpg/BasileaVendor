<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Auto-clear stale caches after deploy (routes, config, views)
// Uses plain PHP functions only - no Laravel facades available at this stage
$cacheCheckFile = __DIR__ . '/../storage/framework/cache/.last_cache_check';
$bootstrapMtime = @filemtime(__FILE__);
$shouldClear = false;

if ($bootstrapMtime !== false) {
    if (!file_exists($cacheCheckFile)) {
        $shouldClear = true;
    } else {
        $lastCheck = (int) @file_get_contents($cacheCheckFile);
        if ($bootstrapMtime > $lastCheck) {
            $shouldClear = true;
        }
    }
}

if ($shouldClear) {
    try {
        $cacheDir = __DIR__ . '/../storage/framework/cache';
        $bootstrapCacheDir = __DIR__ . '/cache';
        
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents($cacheCheckFile, (string) $bootstrapMtime);
        
        // Clear route cache (most common cause of 404s after deploy)
        @unlink($bootstrapCacheDir . '/routes-v7.php');
        @unlink($bootstrapCacheDir . '/routes.php');
        
        // Clear config cache
        @unlink($bootstrapCacheDir . '/config.php');
        
        // Clear compiled services/packages
        @unlink($bootstrapCacheDir . '/services.php');
        @unlink($bootstrapCacheDir . '/packages.php');
        
        // Clear compiled views
        $viewsCache = __DIR__ . '/../storage/framework/views';
        if (is_dir($viewsCache)) {
            foreach (glob($viewsCache . '/*') as $f) {
                @unlink($f);
            }
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
