<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SecurityLogService
{
    /**
     * Log security events
     */
    public static function logLoginAttempt(string $email, bool $success, string $ip, string $userAgent = '', $failureReason = null): void
    {
        $context = [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'timestamp' => now()->toDateTimeString(),
        ];

        if ($success) {
            Log::info('Login successful', $context);
        } else {
            $context['failure_reason'] = $failureReason ?? 'unknown';
            Log::warning('Login failed', $context);
        }
    }

    /**
     * Log admin access
     */
    public static function logAdminAccess(string $action, array $details = []): void
    {
        $user = Auth::user();
        
        $context = [
            'admin_id' => $user->id ?? null,
            'admin_email' => $user->email ?? null,
            'action' => $action,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'details' => $details,
        ];

        Log::info('Admin action performed', $context);
    }

    /**
     * Log security violations
     */
    public static function logSecurityViolation(string $violationType, string $description, array $context = []): void
    {
        $logContext = array_merge([
            'violation_type' => $violationType,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ], $context);

        Log::critical('Security violation: ' . $description, $logContext);
    }

    /**
     * Log account lockout
     */
    public static function logAccountLockout(string $email, int $attempts, string $ip): void
    {
        Log::warning('Account locked due to failed attempts', [
            'email' => $email,
            'ip' => $ip,
            'failed_attempts' => $attempts,
            'locked_until' => now()->addMinutes(30)->toDateTimeString(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log 2FA events
     */
    public static function logTwoFactorEvent(string $email, string $eventType, bool $success = true): void
    {
        Log::info('2FA event: ' . $eventType, [
            'email' => $email,
            'event_type' => $eventType,
            'success' => $success,
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}