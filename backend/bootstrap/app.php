<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Encryption\Encrypter;

// FIX: Auto generate APP_KEY se não existir - resolve MissingAppKeyException
$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath) && file_exists(__DIR__ . '/../.env.example')) {
    copy(__DIR__ . '/../.env.example', $envPath);
}

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    if (!preg_match('/^APP_KEY=.+$/m', $envContent) || preg_match('/^APP_KEY=$/m', $envContent)) {
        $key = 'base64:' . base64_encode(Encrypter::generateKey('AES-256-CBC'));
        
        if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
            $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $envContent);
        } else {
            $envContent .= "\nAPP_KEY={$key}\n";
        }
        
        file_put_contents($envPath, $envContent);
        
        // Atualiza variável de ambiente
        putenv("APP_KEY={$key}");
        $_ENV['APP_KEY'] = $key;
        $_SERVER['APP_KEY'] = $key;
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
        // Always allow CSRF for Asaas webhooks
        $middleware->validateCsrfTokens(except: [
            'api/asaas/webhook',
            'webhook/basileia-church/*',
            'webhook/checkout',
            'webhooks/asaas',
            'webhooks/asaas/*',
        ]);

        // Always add security headers
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\EnsureFluxoOnboarding::class);
        
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
            'gestor' => \App\Http\Middleware\CheckGestor::class,
            'vendedor' => \App\Http\Middleware\CheckVendedor::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'master' => \App\Http\Middleware\CheckMaster::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Hide error details in production
        if (app()->environment('production')) {
            $exceptions->dontReport(\Illuminate\Auth\AuthenticationException::class);
            $exceptions->dontReport(\Illuminate\Validation\ValidationException::class);
        }
    })->create();