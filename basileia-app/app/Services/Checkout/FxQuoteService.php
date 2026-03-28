<?php

namespace App\Services\Checkout;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FxQuoteService
{
    protected string $baseUrl = 'https://api.exchangerate-api.com/v4';
    protected int $cacheTtl = 600; // 10 minutos

    protected static array $fallbackRates = [
        'BRL' => ['USD' => 0.18, 'EUR' => 0.17],
        'USD' => ['BRL' => 5.50, 'EUR' => 0.92],
        'EUR' => ['BRL' => 6.00, 'USD' => 1.09],
    ];

    public function getQuote(string $from, string $to): array
    {
        if ($from === $to) {
            return [
                'rate' => 1.0,
                'quote_id' => Str::uuid(),
                'locked_until' => now()->addMinutes(30),
                'from' => $from,
                'to' => $to,
            ];
        }

        $rate = $this->getRate($from, $to);
        $quoteId = Str::uuid();

        // Travar cotação por 30 minutos
        Cache::put("fx_quote_{$quoteId}", [
            'rate' => $rate,
            'from' => $from,
            'to' => $to,
            'locked_until' => now()->addMinutes(30),
        ], 1800);

        return [
            'rate' => $rate,
            'quote_id' => $quoteId,
            'locked_until' => now()->addMinutes(30),
            'from' => $from,
            'to' => $to,
        ];
    }

    public function getRate(string $from, string $to): float
    {
        $cacheKey = "fx_rate_{$from}_{$to}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($from, $to) {
            return $this->fetchRateFromApi($from, $to);
        });
    }

    protected function fetchRateFromApi(string $from, string $to): float
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/latest/{$from}");

            if ($response->successful()) {
                $data = $response->json();
                $rate = $data['rates'][$to] ?? null;

                if ($rate) {
                    return (float) $rate;
                }
            }
        } catch (\Exception $e) {
            Log::error('FxQuoteService: Erro ao buscar taxa', [
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback
        return self::$fallbackRates[$from][$to] ?? 1.0;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $rate = $this->getRate($from, $to);
        return round($amount * $rate, 2);
    }

    public function convertWithQuote(float $amount, string $quoteId): ?array
    {
        $quote = Cache::get("fx_quote_{$quoteId}");

        if (!$quote) {
            return null;
        }

        return [
            'original_amount' => $amount,
            'converted_amount' => round($amount * $quote['rate'], 2),
            'rate' => $quote['rate'],
            'from' => $quote['from'],
            'to' => $quote['to'],
        ];
    }

    public function validateQuote(string $quoteId): bool
    {
        $quote = Cache::get("fx_quote_{$quoteId}");

        if (!$quote) {
            return false;
        }

        return now()->lte($quote['locked_until']);
    }

    public function getLockedQuote(string $quoteId): ?array
    {
        return Cache::get("fx_quote_{$quoteId}");
    }
}
