<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected string $baseUrl = 'https://api.exchangerate-api.com/v4';
    protected int $cacheTtl = 600; // 10 minutos

    /**
     * Obtém taxa de conversão entre duas moedas
     * Cache de 10 minutos conforme solicitado
     */
    public function getRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1.0;
        }

        $cacheKey = "exchange_rate_{$from}_{$to}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($from, $to) {
            return $this->fetchRateFromApi($from, $to);
        });
    }

    /**
     * Converte valor de uma moeda para outra
     */
    public function convert(float $value, string $from, string $to): float
    {
        $rate = $this->getRate($from, $to);
        return round($value * $rate, 2);
    }

    /**
     * Obtém todas as taxas de uma moeda base
     */
    public function getAllRates(string $base = 'BRL'): array
    {
        $cacheKey = "exchange_rates_all_{$base}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($base) {
            return $this->fetchAllRatesFromApi($base);
        });
    }

    /**
     * Busca taxa direto da API
     */
    protected function fetchRateFromApi(string $from, string $to): float
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/latest/{$from}");

            if ($response->successful()) {
                $data = $response->json();
                $rate = $data['rates'][$to] ?? null;

                if ($rate) {
                    Log::info('ExchangeRateService: Taxa obtida', [
                        'from' => $from,
                        'to' => $to,
                        'rate' => $rate,
                    ]);
                    return (float) $rate;
                }
            }

            Log::warning('ExchangeRateService: API não retornou taxa, usando fallback', [
                'from' => $from,
                'to' => $to,
            ]);

        } catch (\Exception $e) {
            Log::error('ExchangeRateService: Erro ao buscar taxa', [
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback hardcoded
        return $this->getFallbackRate($from, $to);
    }

    /**
     * Busca todas as taxas de uma moeda base
     */
    protected function fetchAllRatesFromApi(string $base): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/latest/{$base}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['rates'] ?? [];
            }

        } catch (\Exception $e) {
            Log::error('ExchangeRateService: Erro ao buscar todas as taxas', [
                'base' => $base,
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Taxas de fallback caso a API falhe
     * Atualizadas manualmente
     */
    protected function getFallbackRate(string $from, string $to): float
    {
        $rates = [
            'BRL' => [
                'USD' => 0.18,
                'EUR' => 0.17,
            ],
            'USD' => [
                'BRL' => 5.50,
                'EUR' => 0.92,
            ],
            'EUR' => [
                'BRL' => 6.00,
                'USD' => 1.09,
            ],
        ];

        if (isset($rates[$from][$to])) {
            return $rates[$from][$to];
        }

        // Se não encontrou, retorna 1 (moeda igual)
        return 1.0;
    }

    /**
     * Obtém o symbol/formato da moeda
     */
    public static function getCurrencySymbol(string $currency): string
    {
        return match ($currency) {
            'BRL' => 'R$',
            'USD' => '$',
            'EUR' => '€',
            default => $currency,
        };
    }
}
