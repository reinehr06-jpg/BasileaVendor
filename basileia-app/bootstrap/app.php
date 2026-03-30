<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
            'api/checkout/*',
            'webhook/saque',
            'webhook/basileia-church/*',
            'webhook/checkout',
        ]);
        $middleware->append(\App\Http\Middleware\ClearStaleCache::class);
        
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
