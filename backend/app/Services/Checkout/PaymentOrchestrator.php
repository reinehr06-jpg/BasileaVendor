<?php

namespace App\Services\Checkout;

use App\Models\CheckoutSession;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AsaasService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentOrchestrator
{
    protected AsaasService $asaasService;

    protected LeadService $leadService;

    protected TrackingService $trackingService;

    public function __construct()
    {
        $this->asaasService = new AsaasService;
        $this->leadService = new LeadService;
        $this->trackingService = new TrackingService;
    }

    public function createSession(
        string $offerSlug,
        array $options = []
    ): CheckoutSession {
        // Buscar offer
        $offer = Offer::where('slug', $offerSlug)->where('is_active', true)->firstOrFail();

        // Resolver moeda
        $currency = $options['currency'] ?? CurrencyResolver::resolve();

        // Criar ou atualizar lead se dados foram fornecidos
        $leadId = null;
        if (! empty($options['email'])) {
            $lead = $this->leadService->createOrUpdate([
                'name' => $options['name'] ?? null,
                'email' => $options['email'],
                'phone' => $options['phone'] ?? null,
                'document' => $options['document'] ?? null,
                'currency' => $currency,
                'language' => $options['language'] ?? 'pt-BR',
                'seller_id' => $options['seller_id'] ?? null,
                'campaign' => $options['campaign_id'] ?? null,
            ]);
            $leadId = $lead->id;
        }

        // Criar sessão de checkout (usando DB direto para SQLite compatibility)
        $now = now();
        $expiresAt = now()->addHours(24);
        $token = Str::uuid();

        DB::table('checkout_sessions')->insert([
            'token' => $token,
            'offer_id' => $offer->id,
            'lead_id' => $leadId,
            'seller_id' => $options['seller_id'] ?? null,
            'campaign_id' => $options['campaign_id'] ?? null,
            'utm_params' => json_encode([
                'utm_source' => request()->get('utm_source'),
                'utm_medium' => request()->get('utm_medium'),
                'utm_campaign' => request()->get('utm_campaign'),
                'utm_content' => request()->get('utm_content'),
                'utm_term' => request()->get('utm_term'),
            ]),
            'currency' => $currency,
            'price_original' => $offer->getPriceForCurrency($currency),
            'price_final' => $offer->getPriceForCurrency($currency),
            'order_bump' => '[]',
            'coupon_discount' => 0,
            'ip' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'country_code' => $options['country_code'] ?? null,
            'language' => $options['language'] ?? 'pt-BR',
            'status' => 'active',
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        $session = CheckoutSession::where('token', $token)->firstOrFail();

        // Track view
        $this->trackingService->trackView($session);

        return $session->load('offer');
    }

    public function identifyLead(CheckoutSession $session, array $data): array
    {
        $currency = $session->currency;

        // Criar ou atualizar lead
        $lead = $this->leadService->createOrUpdate([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'document' => $data['document'] ?? null,
            'church_name' => $data['church_name'] ?? null,
            'members_count' => $data['members_count'] ?? null,
            'currency' => $currency,
            'language' => $session->language,
            'seller_id' => $session->seller_id,
            'campaign' => $session->campaign_id,
            'country_code' => $session->country_code,
        ]);

        // Atualizar sessão
        $session->update([
            'lead_id' => $lead->id,
            'identified_at' => now(),
        ]);

        // Track
        $this->trackingService->trackIdentify($session, $lead);

        // Calcular preço com dados do lead
        $pricingService = new PricingService;
        $pricing = $pricingService->calculatePrice(
            $session->offer,
            $currency,
            [
                'coupon_code' => $data['coupon_code'] ?? null,
                'order_bumps' => $data['order_bumps'] ?? [],
            ]
        );

        // Atualizar sessão com preço final
        $session->update([
            'price_final' => $pricing['totals']['total'],
            'fx_rate' => $pricing['pricing']['fx_rate'],
            'fx_quote_id' => $pricing['pricing']['fx_quote_id'],
            'fx_locked_until' => $pricing['pricing']['fx_locked_until'],
            'coupon_code' => $data['coupon_code'] ?? null,
            'coupon_discount' => $pricing['discounts']['coupon_discount'],
            'order_bump' => $pricing['order_bumps']['items'],
        ]);

        return [
            'session_token' => $session->token,
            'lead_uuid' => $lead->uuid,
            'pricing' => $pricing,
        ];
    }

    public function createPayment(CheckoutSession $session, array $paymentData): array
    {
        $lead = $session->lead;

        if (! $lead) {
            throw new \Exception('Lead não identificado');
        }

        if (! $session->isActive()) {
            throw new \Exception('Sessão de checkout expirada');
        }

        // Marcar sessão como processando
        $session->markAsProcessing();

        // Track
        $this->trackingService->trackView($session, ['step' => 'payment_started']);

        return DB::transaction(function () use ($session, $lead, $paymentData) {
            // Criar ou buscar customer no Asaas
            $asaasCustomer = $this->getOrCreateAsaasCustomer($lead);

            // Criar order
            $order = Order::create([
                'checkout_session_id' => $session->id,
                'offer_id' => $session->offer_id,
                'lead_id' => $lead->id,
                'seller_id' => $session->seller_id,
                'campaign_id' => $session->campaign_id,
                'currency' => $session->currency,
                'subtotal' => $session->price_original,
                'discount' => $session->coupon_discount,
                'order_bump_total' => collect($session->order_bump ?? [])->sum('price'),
                'total' => $session->price_final,
                'fx_rate' => $session->fx_rate,
                'payment_method' => $paymentData['payment_method'],
                'customer_asaas_id' => $asaasCustomer['id'],
                'status' => 'processing',
            ]);

            // Criar cobrança no Asaas
            $asaasPayment = $this->createAsaasCharge(
                $asaasCustomer['id'],
                $order,
                $paymentData
            );

            // Criar registro de pagamento
            $payment = Payment::create([
                'order_id' => $order->id,
                'asaas_payment_id' => $asaasPayment['id'],
                'asaas_customer_id' => $asaasCustomer['id'],
                'currency' => $session->currency,
                'amount' => $order->total,
                'amount_brl' => $session->currency !== 'BRL' ? $session->price_original : $order->total,
                'fx_rate' => $session->fx_rate,
                'billing_type' => $this->mapBillingType($paymentData['payment_method']),
                'payment_method' => $paymentData['payment_method'],
                'invoice_url' => $asaasPayment['invoiceUrl'] ?? null,
                'bank_slip_url' => $asaasPayment['bankSlipUrl'] ?? null,
                'bank_slip_barcode' => $asaasPayment['identificationField'] ?? null,
                'pix_qrcode' => $asaasPayment['pixQrCode'] ?? null,
                'pix_copy_paste' => $asaasPayment['pixCopiaECola'] ?? null,
                'credit_card_brand' => $asaasPayment['creditCardBrand'] ?? null,
                'credit_card_last_four' => $asaasPayment['creditCardNumber'] ?? null,
                'status' => 'pending',
                'due_date' => $this->calcularDataVencimento($paymentData['payment_method'], $session->offer->slug),
            ]);

            // Track
            $this->trackingService->trackPaymentCreated($session, $order);

            return [
                'success' => true,
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'payment_uuid' => $payment->uuid,
                'asaas_payment_id' => $payment->asaas_payment_id,
                'payment_method' => $paymentData['payment_method'],
                'pix_qrcode' => $payment->pix_qrcode,
                'pix_copy_paste' => $payment->pix_copy_paste,
                'bank_slip_url' => $payment->bank_slip_url,
                'bank_slip_barcode' => $payment->bank_slip_barcode,
                'invoice_url' => $payment->invoice_url,
                'total' => $order->total,
                'currency' => $session->currency,
            ];
        });
    }

    protected function getOrCreateAsaasCustomer(Lead $lead): array
    {
        // Tentar buscar customer existente
        if ($lead->document) {
            $existing = $this->asaasService->findCustomerByCpfCnpj($lead->document);
            if ($existing) {
                return $existing;
            }
        }

        // Criar novo customer
        return $this->asaasService->createCustomer(
            $lead->name,
            $lead->document ?? '',
            $lead->phone,
            $lead->email
        );
    }

    protected function createAsaasCharge(string $customerId, Order $order, array $paymentData): array
    {
        $billingType = $this->mapBillingType($paymentData['payment_method']);

        $chargeData = [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => $order->total,
            'dueDate' => $this->calcularDataVencimento($paymentData['payment_method'], $order->checkoutSession->offer->slug ?? null)->format('d-m-Y'),
            'description' => "Pedido #{$order->order_number}",
            'externalReference' => $order->order_number,
        ];

        // Adicionar dados do cartão se fornecido
        if ($billingType === 'CREDIT_CARD' && ! empty($paymentData['card'])) {
            $chargeData['creditCard'] = [
                'cardNumber' => $paymentData['card']['number'],
                'cardName' => $paymentData['card']['name'],
                'expirationDate' => $paymentData['card']['expiry'],
                'cvv' => $paymentData['card']['cvv'],
            ];

            $chargeData['creditCardHolderInfo'] = [
                'name' => $paymentData['card']['name'],
                'cpfCnpj' => preg_replace('/\D/', '', $order->lead->document ?? ''),
                'email' => $order->lead->email,
                'phone' => preg_replace('/\D/', '', $order->lead->phone ?? ''),
            ];

            // Para cartão de crédito internacional
            if ($order->currency !== 'BRL') {
                $chargeData['creditCardHolderInfo']['address'] = [
                    'street' => 'International',
                    'number' => '0',
                    'neighborhood' => 'International',
                    'city' => 'International',
                    'state' => 'INT',
                    'country' => $order->lead->country_code ?? 'US',
                    'postalCode' => '00000',
                ];
            }
        }

        return $this->asaasService->createPayment(
            $customerId,
            $order->total,
            $this->calcularDataVencimento($paymentData['payment_method'], $order->checkoutSession->offer->slug ?? null)->format('d-m-Y'),
            $billingType,
            "Pedido #{$order->order_number}",
            $order->order_number
        );
    }

    protected function mapBillingType(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'pix' => 'PIX',
            'boleto' => 'BOLETO',
            'cartao' => 'CREDIT_CARD',
            default => 'PIX',
        };
    }

    public function getPaymentByAsaasId(string $asaasPaymentId): ?Payment
    {
        return Payment::where('asaas_payment_id', $asaasPaymentId)->first();
    }

    public function processWebhook(array $payload): void
    {
        $paymentId = $payload['payment']['id'] ?? null;

        if (! $paymentId) {
            return;
        }

        $payment = $this->getPaymentByAsaasId($paymentId);

        if (! $payment) {
            Log::warning('PaymentOrchestrator: Pagamento não encontrado para webhook', [
                'asaas_payment_id' => $paymentId,
            ]);

            return;
        }

        $newStatus = $this->mapAsaasStatus($payload['payment']['status'] ?? '');

        // Atualizar pagamento
        $payment->update([
            'status' => $newStatus,
            'paid_at' => in_array($newStatus, ['confirmed', 'received']) ? now() : $payment->paid_at,
            'confirmed_at' => $newStatus === 'confirmed' ? now() : $payment->confirmed_at,
        ]);

        // Se pago, atualizar order e lead
        if (in_array($newStatus, ['confirmed', 'received'])) {
            $order = $payment->order;

            $order->markAsPaid();

            if ($order->lead) {
                $this->leadService->markAsConverted($order->lead);
            }

            if ($order->checkoutSession) {
                $order->checkoutSession->markAsCompleted();
            }

            // Track approval
            $this->trackingService->trackPaymentApproved($order);

            Log::info('PaymentOrchestrator: Pagamento aprovado', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_id' => $payment->id,
            ]);
        }
    }

    protected function mapAsaasStatus(string $asaasStatus): string
    {
        return match (strtoupper($asaasStatus)) {
            'PENDING', 'AWAITING_RISK_ANALYSIS' => 'pending',
            'CONFIRMED' => 'confirmed',
            'RECEIVED', 'RECEIVED_IN_CASH' => 'received',
            'OVERDUE' => 'overdue',
            'REFUNDED', 'REFUND_REQUESTED', 'CHARGEBACK_REQUESTED' => 'refunded',
            'CANCELED', 'DELETED' => 'cancelled',
            default => 'pending',
        };
    }

    public function resumeSession(string $token): ?array
    {
        $session = CheckoutSession::with(['offer', 'lead'])->where('token', $token)->first();

        if (! $session) {
            return null;
        }

        if (! $session->isActive()) {
            return null;
        }

        $pricingService = new PricingService;
        $pricing = $pricingService->calculatePrice(
            $session->offer,
            $session->currency
        );

        return [
            'session' => $session,
            'pricing' => $pricing,
            'lead' => $session->lead,
        ];
    }

    private function calcularDataVencimento(string $formaPagamento, ?string $offerSlug = null): Carbon
    {
        $isBoleto = in_array(strtoupper($formaPagamento), ['BOLETO', 'BOLETO_BANCARIO']);
        $isPixCartao = in_array(strtoupper($formaPagamento), ['PIX', 'CREDIT_CARD']);

        $isAnual = $offerSlug && (
            strpos(strtolower($offerSlug), 'annual') !== false ||
            strpos(strtolower($offerSlug), 'anual') !== false
        );

        if ($isAnual) {
            if ($isBoleto) {
                return now()->addDays(5);
            }

            return now()->addDays(15);
        }

        return now()->addDays(5);
    }
}
