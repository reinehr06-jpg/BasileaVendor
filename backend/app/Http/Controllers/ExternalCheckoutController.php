<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\AsaasService;

class ExternalCheckoutController extends Controller
{
    protected AsaasService $asaasService;

    public function __construct()
    {
        $this->asaasService = new AsaasService;
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
