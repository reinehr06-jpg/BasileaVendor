<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CurrencyService
{
    protected static array $supportedCurrencies = ['BRL', 'USD', 'EUR'];
    protected static array $countryCurrencyMap = [
        // América do Sul
        'BR' => 'BRL', // Brasil
        'US' => 'USD', // EUA
        'AR' => 'USD', // Argentina (usar USD para simplificar)
        'CL' => 'USD', // Chile
        'CO' => 'USD', // Colômbia
        'PE' => 'USD', // Peru
        'VE' => 'USD', // Venezuela
        'UY' => 'USD', // Uruguai
        'PY' => 'USD', // Paraguai
        'EC' => 'USD', // Equador
        'BO' => 'USD', // Bolívia

        // Europa
        'PT' => 'EUR', // Portugal
        'ES' => 'EUR', // Espanha
        'FR' => 'EUR', // França
        'DE' => 'EUR', // Alemanha
        'IT' => 'EUR', // Itália
        'NL' => 'EUR', // Holanda
        'BE' => 'EUR', // Bélgica
        'AT' => 'EUR', // Áustria
        'IE' => 'EUR', // Irlanda
        'FI' => 'EUR', // Finlândia
        'GR' => 'EUR', // Grécia
        'CH' => 'EUR', // Suíça (usar EUR)

        // América do Norte
        'CA' => 'USD', // Canadá (usar USD)
        'MX' => 'USD', // México

        // Ásia
        'JP' => 'USD', // Japão
        'KR' => 'USD', // Coreia do Sul
        'IN' => 'USD', // Índia
        'CN' => 'USD', // China

        // África
        'ZA' => 'USD', // África do Sul
        'NG' => 'USD', // Nigéria

        // Oceania
        'AU' => 'USD', // Austrália
        'NZ' => 'USD', // Nova Zelândia

        // Reino Unido
        'GB' => 'EUR', // Reino Unido (usar EUR)
    ];

    protected static array $currencyInfo = [
        'BRL' => [
            'symbol' => 'R$',
            'decimal_separator' => ',',
            'thousand_separator' => '.',
            'decimal_places' => 2,
        ],
        'USD' => [
            'symbol' => '$',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimal_places' => 2,
        ],
        'EUR' => [
            'symbol' => '€',
            'decimal_separator' => ',',
            'thousand_separator' => '.',
            'decimal_places' => 2,
        ],
    ];

    public static function detectCurrency(?string $requestCurrency = null): string
    {
        // 1. Parâmetro na URL tem prioridade
        if ($requestCurrency && in_array(strtoupper($requestCurrency), static::$supportedCurrencies)) {
            return strtoupper($requestCurrency);
        }

        // 2. IP Geolocation
        $country = static::getCountryFromIp();
        if ($country && isset(static::$countryCurrencyMap[$country])) {
            return static::$countryCurrencyMap[$country];
        }

        // 3. Fallback para BRL
        return 'BRL';
    }

    public static function getCountryFromIp(): ?string
    {
        try {
            // Usar ip-api.com (gratuito, sem necessidade de API key)
            $ip = request()->ip();

            // Se for localhost, retornar null
            if (in_array($ip, ['127.0.0.1', '::1', '::ffff:127.0.0.1'])) {
                return null;
            }

            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get("http://ip-api.com/json/{$ip}?fields=countryCode");

            if ($response->successful()) {
                $data = $response->json();
                return $data['countryCode'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('CurrencyService: Erro ao detectar país via IP', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public static function getCurrencyInfo(string $currency): array
    {
        return static::$currencyInfo[$currency] ?? static::$currencyInfo['BRL'];
    }

    public static function formatPrice(float $value, string $currency): string
    {
        $info = static::getCurrencyInfo($currency);

        return $info['symbol'] . ' ' . number_format(
            $value,
            $info['decimal_places'],
            $info['decimal_separator'],
            $info['thousand_separator']
        );
    }

    public static function getSupportedCurrencies(): array
    {
        return static::$supportedCurrencies;
    }

    public static function getCurrenciesWithFlags(): array
    {
        return [
            'BRL' => ['symbol' => 'R$', 'country_code' => 'BR'],
            'USD' => ['symbol' => '$', 'country_code' => 'US'],
            'EUR' => ['symbol' => '€', 'country_code' => 'EU'],
        ];
    }
}
