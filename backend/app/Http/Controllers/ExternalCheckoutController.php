<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\CheckoutSession;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalCheckoutController extends Controller
{
    protected AsaasService $asaasService;

    public function __construct()
    {
        $this->asaasService = new AsaasService();
    }

    /**
     * Checkout via UUID da transação local (checkout_hash da Venda)
     * GET /checkout/{uuid}
     */
    public function byUuid(string $uuid, ?string $metodo = null)
    {
        $venda = Venda::where('checkout_hash', $uuid)->first();

        if (!$venda) {
            abort(404, 'Transação não encontrada');
        }

        return $this->renderCheckout($venda, $metodo);
    }

    /**
     * Checkout via Asaas Payment ID
     * GET /checkout/asaas/{asaas_payment_id}
     */
    public function byAsaas(string $asaasPaymentId, ?string $metodo = null)
    {
        $pagamento = Pagamento::where('asaas_payment_id', $asaasPaymentId)->first();

        if (!$pagamento) {
            $asaasData = $this->asaasService->getPayment($asaasPaymentId);

            if (!$asaasData || isset($asaasData['errors'])) {
                abort(404, 'Pagamento não encontrado');
            }

            $pagamento = Pagamento::where('asaas_payment_id', $asaasPaymentId)->first();

            if (!$pagamento) {
                abort(404, 'Pagamento não encontrado');
            }
        }

        $venda = $pagamento->venda;

        if (!$venda) {
            abort(404, 'Venda não encontrada');
        }

        return $this->renderCheckout($venda, $metodo);
    }

    protected function renderCheckout(Venda $venda, ?string $metodo = null)
    {
        $billingType = $metodo ?? $this->mapForma($venda->forma_pagamento ?? 'pix');

        $vencimentoDias = $this->calcularVencimentoDias($billingType, $venda->plano ?? '');

        return view('checkout-new.index', [
            'session_token' => $venda->checkout_hash,
            'pricing' => [
                'pricing' => [
                    'base_currency' => 'BRL',
                    'subtotal' => $venda->valor_original ?? $venda->valor,
                    'discount' => 0,
                    'total' => $venda->valor,
                ],
            ],
            'currency' => 'BRL',
            'language' => 'pt-BR',
            'seller_id' => $venda->vendedor_id,
            'vencimento_dias' => $vencimentoDias,
            'campaign_id' => null,
            'availableLanguages' => [
                ['code' => 'pt-BR', 'flag' => '🇧🇷', 'currency' => 'BRL'],
            ],
            'currentLanguage' => ['code' => 'pt-BR', 'flag' => '🇧🇷'],
            'paymentMethods' => ['pix', 'cartao', 'boleto'],
            'is_external_checkout' => true,
            'pre_selected_method' => $billingType,
            'venda' => $venda,
            'cliente' => $venda->cliente,
        ]);
    }

    protected function mapForma(?string $forma): string
    {
        return match(strtoupper($forma)) {
            'PIX' => 'pix',
            'BOLETO', 'BOLETO_BANCARIO' => 'boleto',
            'CREDIT_CARD', 'CARTAO' => 'cartao',
            default => 'pix',
        };
    }

    protected function calcularVencimentoDias(string $formaPagamento, string $planoNome): array
    {
        $isAnual = stripos($planoNome, 'annual') !== false 
            || stripos($planoNome, 'anual') !== false 
            || stripos($planoNome, '12x') !== false;

        if ($isAnual) {
            return [
                'pix' => 15,
                'cartao' => 15,
                'boleto' => 3,
            ];
        }

        return [
            'pix' => 5,
            'cartao' => 5,
            'boleto' => 5,
        ];
    }
}

    /**
     * Checkout via UUID da transação local
     * GET /checkout/{uuid}
     */
    public function byUuid(string $uuid, ?string $metodo = null)
    {
        $order = Order::where('uuid', $uuid)->first();

        if (! $order) {
            abort(404, 'Transação não encontrada');
        }

        return $this->renderCheckout($order, $metodo);
    }

    /**
     * Checkout via Asaas Payment ID
     * GET /checkout/asaas/{asaas_payment_id}
     */
    public function byAsaas(string $asaasPaymentId, ?string $metodo = null)
    {
        $payment = Payment::where('asaas_payment_id', $asaasPaymentId)->first();

        if (! $payment) {
            $asaasData = $this->asaasService->getPayment($asaasPaymentId);

            if (! $asaasData || isset($asaasData['errors'])) {
                abort(404, 'Pagamento não encontrado');
            }

            $order = Order::where('asaas_payment_id', $asaasPaymentId)->first();

            if (! $order) {
                abort(404, 'Transação não encontrada');
            }
        }

        return $this->renderCheckout($order, $metodo);
    }

    protected function renderCheckout(Order $order, ?string $metodo = null)
    {
        $session = $order->checkoutSession;
        $offer = $order->offer;

        if (! $session) {
            abort(404, 'Sessão de checkout não encontrada');
        }

        $payment = $order->payment;
        $billingType = $metodo ?? $order->payment_method ?? 'pix';

        $vencimentoDias = $this->calcularVencimentoDias($billingType, $session->offer->slug ?? '');

        return view('checkout-new.index', [
            'session_token' => $session->token,
            'pricing' => [
                'pricing' => [
                    'base_currency' => 'BRL',
                    'subtotal' => $order->subtotal,
                    'discount' => $order->discount ?? 0,
                    'total' => $order->total,
                ],
            ],
            'currency' => $session->currency ?? 'BRL',
            'language' => $session->language ?? 'pt-BR',
            'seller_id' => $session->seller_id,
            'vencimento_dias' => $vencimentoDias,
            'campaign_id' => $session->campaign_id,
            'availableLanguages' => [
                ['code' => 'pt-BR', 'flag' => '🇧🇷', 'currency' => 'BRL'],
                ['code' => 'en', 'flag' => '🇺🇸', 'currency' => 'USD'],
            ],
            'currentLanguage' => ['code' => $session->language ?? 'pt-BR', 'flag' => '🇧🇷'],
            'paymentMethods' => ['pix', 'cartao', 'boleto'],
            'is_external_checkout' => true,
            'pre_selected_method' => $billingType,
            'order' => $order,
            'payment' => $payment,
        ]);
    }

    protected function calcularVencimentoDias(string $formaPagamento, string $offerSlug): array
    {
        $isAnual = stripos($offerSlug, 'annual') !== false || stripos($offerSlug, 'anual') !== false;

        if ($isAnual) {
            return [
                'pix' => 15,
                'cartao' => 15,
                'boleto' => 3,
            ];
        }

        return [
            'pix' => 5,
            'cartao' => 5,
            'boleto' => 5,
        ];
    }
}
