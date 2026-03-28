<?php

namespace App\Services\Checkout;

use App\Models\Coupon;
use App\Models\Offer;

class PricingService
{
    protected FxQuoteService $fxService;

    public function __construct(?FxQuoteService $fxService = null)
    {
        $this->fxService = $fxService ?? new FxQuoteService();
    }

    public function calculatePrice(
        Offer $offer,
        string $currency,
        array $options = []
    ): array {
        $baseCurrency = 'BRL';
        $basePrice = $offer->price_brl;

        // Preço fixo por moeda (se configurado)
        $fixedPrice = match(strtoupper($currency)) {
            'USD' => $offer->price_usd,
            'EUR' => $offer->price_eur,
            default => null,
        };

        $fxRate = null;
        $fxQuoteId = null;

        if ($fixedPrice) {
            // Usar preço fixo
            $originalPrice = $fixedPrice;
            $convertedPrice = $fixedPrice;
        } elseif ($currency !== $baseCurrency) {
            // Converter usando exchange rate
            $quote = $this->fxService->getQuote($baseCurrency, $currency);
            $originalPrice = $basePrice;
            $convertedPrice = round($basePrice * $quote['rate'], 2);
            $fxRate = $quote['rate'];
            $fxQuoteId = $quote['quote_id'];
        } else {
            $originalPrice = $basePrice;
            $convertedPrice = $basePrice;
        }

        // Desconto inicial (se configurado)
        $discountPercent = $offer->discount_percent ?? 0;
        $discountAmount = 0;

        if ($discountPercent > 0) {
            $discountAmount = $convertedPrice * ($discountPercent / 100);
        }

        // Cupom
        $couponDiscount = 0;
        $coupon = null;

        if (!empty($options['coupon_code'])) {
            $coupon = Coupon::findByCode($options['coupon_code']);
            if ($coupon && $coupon->isValid() && $coupon->isApplicableToCurrency($currency)) {
                $couponDiscount = $coupon->calculateDiscount($convertedPrice - $discountAmount);
            }
        }

        // Order bump
        $orderBumpTotal = 0;
        $orderBumpItems = [];

        if (!empty($options['order_bumps'])) {
            foreach ($options['order_bumps'] as $bump) {
                $bumpPrice = $bump['price'] ?? 0;
                if ($bumpPrice > 0) {
                    $orderBumpTotal += $bumpPrice;
                    $orderBumpItems[] = $bump;
                }
            }
        }

        // Cálculo final
        $subtotal = $convertedPrice;
        $totalDiscount = $discountAmount + $couponDiscount;
        $total = $subtotal - $totalDiscount + $orderBumpTotal;
        $total = max(0, $total);

        // Parcelamento
        $installments = $this->calculateInstallments($total, $offer->installments_max ?? 1, $currency);

        return [
            'offer' => [
                'id' => $offer->id,
                'name' => $offer->name,
                'slug' => $offer->slug,
            ],
            'pricing' => [
                'base_currency' => $baseCurrency,
                'base_price' => $basePrice,
                'currency' => $currency,
                'original_price' => $originalPrice,
                'converted_price' => $convertedPrice,
                'fx_rate' => $fxRate,
                'fx_quote_id' => $fxQuoteId,
                'fx_locked_until' => $fxQuoteId ? now()->addMinutes(30) : null,
            ],
            'discounts' => [
                'percent' => $discountPercent,
                'amount' => $discountAmount,
                'coupon_code' => $coupon ? $coupon->code : null,
                'coupon_discount' => $couponDiscount,
                'total_discount' => $totalDiscount,
            ],
            'order_bumps' => [
                'items' => $orderBumpItems,
                'total' => $orderBumpTotal,
            ],
            'totals' => [
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'order_bump_total' => $orderBumpTotal,
                'total' => $total,
            ],
            'installments' => $installments,
            'formatted' => [
                'original_price' => CurrencyResolver::formatPrice($originalPrice, $currency),
                'final_price' => CurrencyResolver::formatPrice($total, $currency),
                'installment' => $installments['best_option']['formatted'] ?? null,
            ],
            'guarantee' => $offer->guarantee_text,
            'features' => $offer->features ?? [],
        ];
    }

    protected function calculateInstallments(float $total, int $maxInstallments, string $currency): array
    {
        $installments = [];

        // Sem juros até 3x
        $noInterestMax = min(3, $maxInstallments);

        for ($i = 1; $i <= $maxInstallments; $i++) {
            $value = $total / $i;
            $hasInterest = $i > $noInterestMax;

            if ($hasInterest) {
                // Simular juros simples (apenas exemplo)
                $interestRate = 0.02 * ($i - $noInterestMax);
                $value = ($total * (1 + $interestRate)) / $i;
            }

            $installments[] = [
                'number' => $i,
                'value' => round($value, 2),
                'total' => round($value * $i, 2),
                'has_interest' => $hasInterest,
                'formatted' => CurrencyResolver::formatPrice($value, $currency),
                'formatted_total' => CurrencyResolver::formatPrice($value * $i, $currency),
            ];
        }

        // Melhor opção (1x sem juros)
        $bestOption = $installments[0];

        return [
            'options' => $installments,
            'max_installments' => $maxInstallments,
            'no_interest_max' => $noInterestMax,
            'best_option' => $bestOption,
        ];
    }

    public function validateCoupon(string $code, float $orderValue, string $currency, ?int $offerId = null): array
    {
        $coupon = Coupon::findByCode($code);

        if (!$coupon) {
            return [
                'valid' => false,
                'error' => 'Cupom não encontrado',
            ];
        }

        if (!$coupon->isValid()) {
            return [
                'valid' => false,
                'error' => 'Cupom expirado ou esgotado',
            ];
        }

        if (!$coupon->isApplicableToCurrency($currency)) {
            return [
                'valid' => false,
                'error' => 'Cupom não disponível para esta moeda',
            ];
        }

        if ($offerId && !$coupon->isApplicableToOffer($offerId)) {
            return [
                'valid' => false,
                'error' => 'Cupom não aplicável a esta oferta',
            ];
        }

        $discount = $coupon->calculateDiscount($orderValue);

        if ($discount <= 0 && $coupon->min_order_value) {
            return [
                'valid' => false,
                'error' => 'Valor mínimo do pedido não atingido',
            ];
        }

        return [
            'valid' => true,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'discount' => $discount,
            'formatted_discount' => CurrencyResolver::formatPrice($discount, $currency),
            'description' => $coupon->description,
        ];
    }
}
