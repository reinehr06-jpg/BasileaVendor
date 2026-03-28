<?php

namespace App\Services\Checkout;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyResolver
{
    protected static array $countryCurrencyMap = [
        // América do Sul
        'BR' => 'BRL',
        'AR' => 'USD',
        'CL' => 'USD',
        'CO' => 'USD',
        'PE' => 'USD',
        'VE' => 'USD',
        'UY' => 'USD',
        'PY' => 'USD',
        'EC' => 'USD',
        'BO' => 'USD',

        // América do Norte
        'US' => 'USD',
        'CA' => 'USD',
        'MX' => 'USD',

        // Europa
        'PT' => 'EUR',
        'ES' => 'EUR',
        'FR' => 'EUR',
        'DE' => 'EUR',
        'IT' => 'EUR',
        'NL' => 'EUR',
        'BE' => 'EUR',
        'AT' => 'EUR',
        'IE' => 'EUR',
        'FI' => 'EUR',
        'GR' => 'EUR',
        'CH' => 'EUR',
        'GB' => 'EUR',

        // Ásia
        'JP' => 'USD',
        'KR' => 'USD',
        'IN' => 'USD',
        'CN' => 'USD',

        // Oceania
        'AU' => 'USD',
        'NZ' => 'USD',

        // África
        'ZA' => 'USD',
        'NG' => 'USD',
    ];

    protected static array $currencyConfig = [
        'BRL' => [
            'symbol' => 'R$',
            'code' => 'BRL',
            'name' => 'Real Brasileiro',
            'decimal_separator' => ',',
            'thousand_separator' => '.',
            'decimal_places' => 2,
            'position' => 'before',
        ],
        'USD' => [
            'symbol' => '$',
            'code' => 'USD',
            'name' => 'US Dollar',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
            'position' => 'before',
        ],
        'EUR' => [
            'symbol' => '€',
            'code' => 'EUR',
            'name' => 'Euro',
            'decimal_separator' => ',',
            'thousand_separator' => '.',
            'decimal_places' => 2,
            'position' => 'after',
        ],
    ];

    public static function resolve(?string $ip = null, ?string $requestedCurrency = null): string
    {
        // 1. Se moeda foi solicitada explicitamente e é válida
        if ($requestedCurrency && self::isValidCurrency($requestedCurrency)) {
            return strtoupper($requestedCurrency);
        }

        // 2. Detectar por IP
        $country = self::getCountryFromIp($ip);
        if ($country && isset(self::$countryCurrencyMap[$country])) {
            return self::$countryCurrencyMap[$country];
        }

        // 3. Fallback para BRL
        return 'BRL';
    }

    public static function getCountryFromIp(?string $ip = null): ?string
    {
        $ip = $ip ?? request()->ip();

        // Se for localhost, retornar null
        if (in_array($ip, ['127.0.0.1', '::1', '::ffff:127.0.0.1', ''])) {
            return null;
        }

        $cacheKey = 'country_ip_' . md5($ip);

        return Cache::remember($cacheKey, 3600, function () use ($ip) {
            try {
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=countryCode");

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['countryCode'] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning('CurrencyResolver: Erro ao detectar país', [
                    'ip' => $ip,
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        });
    }

    public static function getCountryName(string $countryCode): ?string
    {
        $countries = [
            'BR' => 'Brasil',
            'US' => 'Estados Unidos',
            'PT' => 'Portugal',
            'ES' => 'Espanha',
            'FR' => 'França',
            'DE' => 'Alemanha',
            'IT' => 'Itália',
            'GB' => 'Reino Unido',
            'AR' => 'Argentina',
            'MX' => 'México',
            'CO' => 'Colômbia',
            'CL' => 'Chile',
        ];

        return $countries[$countryCode] ?? null;
    }

    public static function isValidCurrency(string $currency): bool
    {
        return isset(self::$currencyConfig[strtoupper($currency)]);
    }

    public static function getConfig(string $currency): array
    {
        return self::$currencyConfig[strtoupper($currency)] ?? self::$currencyConfig['BRL'];
    }

    public static function getAvailableCurrencies(): array
    {
        return array_keys(self::$currencyConfig);
    }

    public static function formatPrice(float $amount, string $currency): string
    {
        $config = self::getConfig($currency);

        $formatted = number_format(
            $amount,
            $config['decimal_places'],
            $config['decimal_separator'],
            $config['thousand_separator']
        );

        return $config['position'] === 'before'
            ? $config['symbol'] . ' ' . $formatted
            : $formatted . ' ' . $config['symbol'];
    }
}
