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
        // Disable CSRF for local development
        if (app()->environment('local')) {
            $middleware->validateCsrfTokens(except: [
                '*',
            ]);
        } else {
            $middleware->validateCsrfTokens(except: [
                'api/asaas/webhook',
                'webhook/basileia-church/*',
                'webhook/checkout',
                'webhooks/asaas',
                'webhooks/asaas/*',
            ]);
        }

        // Security headers - only in production
        if (!app()->environment('local')) {
            $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        }
        
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
