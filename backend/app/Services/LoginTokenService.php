<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class LoginTokenService
{
    const TOKEN_TTL_MINUTES = 5;
    const TOKEN_LENGTH = 12;

    public static function generate(?string $email = null, ?string $ip = null, ?string $userAgent = null): string
    {
        $token = Str::random(self::TOKEN_LENGTH);
        $tokenHash = hash('sha256', $token);

        $payload = [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now()->toIso8601String(),
            'used' => false,
        ];

        Cache::put("login_token:{$tokenHash}", $payload, now()->addMinutes(self::TOKEN_TTL_MINUTES));

        return $token;
    }

    public static function validate(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);
        $payload = Cache::get("login_token:{$tokenHash}");

        if (!$payload) {
            return null;
        }

        if ($payload['used'] ?? false) {
            return null;
        }

        return [
            'token' => $token,
            'hash' => $tokenHash,
            'email' => $payload['email'],
            'ip' => $payload['ip'],
            'user_agent' => $payload['user_agent'],
            'created_at' => $payload['created_at'],
        ];
    }

    public static function markUsed(string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $payload = Cache::get("login_token:{$tokenHash}");

        if (!$payload) {
            return false;
        }

        $payload['used'] = true;
        Cache::put("login_token:{$tokenHash}", $payload, now()->addMinutes(self::TOKEN_TTL_MINUTES));

        return true;
    }

    public static function invalidate(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        Cache::forget("login_token:{$tokenHash}");
    }

    public static function getLoginUrl(?string $email = null): string
    {
        $token = self::generate(
            $email,
            request()->ip(),
            request()->userAgent()
        );

        return route('login.token', ['token' => $token]);
    }

    public static function invalidateAllForEmail(string $email): void
    {
        $keys = Cache::getPrefix() . 'login_token:*';
    }
}