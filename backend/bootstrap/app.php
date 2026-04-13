<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__));
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';
$isLocal = $appEnv === 'local';

return $app
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) use ($isLocal): void {
        if ($isLocal) {
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

        if (!$isLocal) {
            $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        }
        
        $middleware->group('admin.security', [
            \App\Http\Middleware\Security\AdminSecurity::class,
            \App\Http\Middleware\Security\RateLimitByRole::class,
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);
        
        $middleware->alias([
            'admin.security' => \App\Http\Middleware\Security\AdminSecurity::class,
            'role.rate.limit' => \App\Http\Middleware\Security\RateLimitByRole::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'api.key' => \App\Http\Middleware\ApiKeyAuth::class,
            '2fa' => \App\Http\Middleware\TwoFactorMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) use ($isLocal): void {
        if (!$isLocal) {
            $exceptions->dontReport(\Illuminate\Auth\AuthenticationException::class);
            $exceptions->dontReport(\Illuminate\Validation\ValidationException::class);
        }
    })->create();