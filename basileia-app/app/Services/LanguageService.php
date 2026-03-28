<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LanguageService
{
    protected static array $supportedLanguages = [
        'pt-BR' => [
            'language_code' => 'pt-BR',
            'country_code' => 'BR',
            'name' => 'Português (Brasil)',
            'native_name' => 'Português (Brasil)',
            'currency' => 'BRL',
            'flag' => '🇧🇷',
        ],
        'pt-PT' => [
            'language_code' => 'pt-PT',
            'country_code' => 'PT',
            'name' => 'Português (Portugal)',
            'native_name' => 'Português (Portugal)',
            'currency' => 'EUR',
            'flag' => '🇵🇹',
        ],
        'en-US' => [
            'language_code' => 'en-US',
            'country_code' => 'US',
            'name' => 'English (US)',
            'native_name' => 'English (US)',
            'currency' => 'USD',
            'flag' => '🇺🇸',
        ],
        'en-GB' => [
            'language_code' => 'en-GB',
            'country_code' => 'GB',
            'name' => 'English (UK)',
            'native_name' => 'English (UK)',
            'currency' => 'EUR',
            'flag' => '🇬🇧',
        ],
        'es-ES' => [
            'language_code' => 'es-ES',
            'country_code' => 'ES',
            'name' => 'Español (España)',
            'native_name' => 'Español (España)',
            'currency' => 'EUR',
            'flag' => '🇪🇸',
        ],
        'es-AR' => [
            'language_code' => 'es-AR',
            'country_code' => 'AR',
            'name' => 'Español (Argentina)',
            'native_name' => 'Español (Argentina)',
            'currency' => 'USD',
            'flag' => '🇦🇷',
        ],
        'es-MX' => [
            'language_code' => 'es-MX',
            'country_code' => 'MX',
            'name' => 'Español (México)',
            'native_name' => 'Español (México)',
            'currency' => 'USD',
            'flag' => '🇲🇽',
        ],
        'fr-FR' => [
            'language_code' => 'fr-FR',
            'country_code' => 'FR',
            'name' => 'Français',
            'native_name' => 'Français',
            'currency' => 'EUR',
            'flag' => '🇫🇷',
        ],
        'de-DE' => [
            'language_code' => 'de-DE',
            'country_code' => 'DE',
            'name' => 'Deutsch',
            'native_name' => 'Deutsch',
            'currency' => 'EUR',
            'flag' => '🇩🇪',
        ],
        'it-IT' => [
            'language_code' => 'it-IT',
            'country_code' => 'IT',
            'name' => 'Italiano',
            'native_name' => 'Italiano',
            'currency' => 'EUR',
            'flag' => '🇮🇹',
        ],
        'ja-JP' => [
            'language_code' => 'ja-JP',
            'country_code' => 'JP',
            'name' => '日本語',
            'native_name' => '日本語',
            'currency' => 'USD',
            'flag' => '🇯🇵',
        ],
        'zh-CN' => [
            'language_code' => 'zh-CN',
            'country_code' => 'CN',
            'name' => '中文 (中国)',
            'native_name' => '中文 (中国)',
            'currency' => 'USD',
            'flag' => '🇨🇳',
        ],
        'ko-KR' => [
            'language_code' => 'ko-KR',
            'country_code' => 'KR',
            'name' => '한국어',
            'native_name' => '한국어',
            'currency' => 'USD',
            'flag' => '🇰🇷',
        ],
    ];

    /**
     * Detecta o idioma do navegador
     */
    public static function detectLanguage(): string
    {
        $acceptLanguage = request()->header('Accept-Language', 'pt-BR');

        // Parse Accept-Language header
        $languages = static::parseAcceptLanguage($acceptLanguage);

        // Encontra o primeiro idioma suportado
        foreach ($languages as $lang) {
            if (isset(static::$supportedLanguages[$lang])) {
                return $lang;
            }
        }

        // Tenta encontrar idioma base (ex: 'en' de 'en-US')
        foreach ($languages as $lang) {
            $base = explode('-', $lang)[0];
            foreach (static::$supportedLanguages as $code => $info) {
                if (explode('-', $code)[0] === $base) {
                    return $code;
                }
            }
        }

        // Fallback para pt-BR
        return 'pt-BR';
    }

    /**
     * Parse do header Accept-Language
     */
    protected static function parseAcceptLanguage(string $header): array
    {
        $languages = [];

        foreach (explode(',', $header) as $lang) {
            $parts = explode(';', trim($lang));
            $code = trim($parts[0]);

            // Remove quality value if present
            $quality = 1.0;
            if (isset($parts[1])) {
                $q = explode('=', trim($parts[1]));
                if (isset($q[1])) {
                    $quality = (float) $q[1];
                }
            }

            // Normaliza o código (ex: pt-br -> pt-BR)
            $code = str_replace('_', '-', $code);
            $parts = explode('-', $code);
            if (count($parts) > 1) {
                $code = $parts[0] . '-' . strtoupper($parts[1]);
            }

            $languages[$code] = $quality;
        }

        // Ordena por qualidade
        arsort($languages);

        return array_keys($languages);
    }

    /**
     * Obtém o idioma por código
     */
    public static function getLanguage(string $code): ?array
    {
        return static::$supportedLanguages[$code] ?? null;
    }

    /**
     * Obtém todos os idiomas suportados
     */
    public static function getSupportedLanguages(): array
    {
        return static::$supportedLanguages;
    }

    /**
     * Obtém idiomas para o seletor de idioma (com flags e pesquisa)
     */
    public static function getLanguagesForSelector(): array
    {
        return collect(static::$supportedLanguages)
            ->map(function ($lang) {
                return [
                    'code' => $lang['language_code'],
                    'name' => $lang['name'],
                    'native_name' => $lang['native_name'],
                    'country_code' => $lang['country_code'],
                    'flag' => $lang['flag'],
                    'currency' => $lang['currency'],
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    /**
     * Obtém a moeda associada a um idioma
     */
    public static function getCurrencyForLanguage(string $langCode): string
    {
        $lang = static::$supportedLanguages[$langCode] ?? null;
        return $lang['currency'] ?? 'BRL';
    }
}
