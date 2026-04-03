<?php

namespace App\Services;

use App\Models\SecurityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SecurityLogService
{
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

    public static function logAccountLockout(string $email, int $attempts, string $ip): void
    {
        Log::warning('Account locked due to failed attempts', [
            'email' => $email,
            'ip' => $ip,
            'failed_attempts' => $attempts,
            'locked_until' => now()->addMinutes(15)->toDateTimeString(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public static function logTwoFactorEvent($userId, string $eventType, string $result = 'success'): void
    {
        $user = Auth::user();

        Log::info('2FA event: ' . $eventType, [
            'user_id' => $userId,
            'email' => $user?->email ?? 'unknown',
            'event_type' => $eventType,
            'result' => $result,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
