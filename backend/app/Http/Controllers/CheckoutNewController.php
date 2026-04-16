<?php

namespace App\Http\Controllers;

use App\Models\CheckoutSession;
use App\Models\Evento;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AsaasService;
use App\Services\Checkout\PaymentOrchestrator;
use App\Services\Checkout\PricingService;
use App\Services\Checkout\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutNewController extends Controller
{
    protected PaymentOrchestrator $orchestrator;

    protected PricingService $pricingService;

    protected TrackingService $trackingService;

    public function __construct()
    {
        $this->orchestrator = new PaymentOrchestrator;
        $this->pricingService = new PricingService;
        $this->trackingService = new TrackingService;
    }

    /**
     * Iniciar sessão de checkout
     * GET /co/{offerSlug}
     */
    public function start(Request $request, string $offerSlug)
    {
        try {
            $session = $this->orchestrator->createSession($offerSlug, [
                'currency' => $request->get('currency'),
                'language' => $request->get('lang'),
                'seller_id' => $request->get('seller'),
                'campaign_id' => $request->get('campaign'),
                'email' => $request->get('email'),
            ]);

            // Calcular preço
            $pricing = $this->pricingService->calculatePrice(
                $session->offer,
                $session->currency
            );

            // Available languages with flags
            $availableLanguages = [
                ['code' => 'pt-BR', 'flag' => '🇧🇷', 'currency' => 'BRL', 'name' => 'Portuguese', 'native_name' => 'Português', 'country_code' => 'BR'],
                ['code' => 'en', 'flag' => '🇺🇸', 'currency' => 'USD', 'name' => 'English', 'native_name' => 'English', 'country_code' => 'US'],
                ['code' => 'es', 'flag' => '🇪🇸', 'currency' => 'EUR', 'name' => 'Spanish', 'native_name' => 'Español', 'country_code' => 'ES'],
            ];

            // Current language info
            $currentLanguage = collect($availableLanguages)->firstWhere('code', $session->language) ?? $availableLanguages[0];

            // Payment methods based on currency
            $paymentMethods = $session->currency === 'BRL'
                ? ['pix', 'cartao', 'boleto']
                : ['cartao'];

            // Vencimento: dias por forma + ciclo (detecta Annual no slug)
            $offerSlug = $session->offer->slug;
            $isAnual = stripos($offerSlug, 'annual') !== false || stripos($offerSlug, 'anual') !== false;

            $vencimentoDias = $isAnual ? [
                'pix' => 15,
                'cartao' => 15,
                'boleto' => 3,
            ] : [
                'pix' => 5,
                'cartao' => 5,
                'boleto' => 5,
            ];

            return view('checkout-new.index', [
                'session_token' => $session->token,
                'pricing' => $pricing,
                'currency' => $session->currency,
                'language' => $session->language,
                'seller_id' => $session->seller_id,
                'vencimento_dias' => $vencimentoDias,
                'campaign_id' => $session->campaign_id,
                'availableLanguages' => $availableLanguages,
                'currentLanguage' => $currentLanguage,
                'paymentMethods' => $paymentMethods,
            ]);

        } catch (\Exception $e) {
            Log::error('CheckoutNew: Erro ao iniciar sessão', [
                'offer' => $offerSlug,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Oferta não encontrada');
        }
    }

    /**
     * Retomar sessão abandonada
     * GET /co/resume/{token}
     */
    public function resume(string $token)
    {
        $data = $this->orchestrator->resumeSession($token);

        if (! $data) {
            return redirect()->route('checkout.new.start', ['offerSlug' => 'default'])
                ->with('error', 'Sessão expirada. Por favor, inicie novamente.');
        }

        return view('checkout-new.index', [
            'session_token' => $data['session']->token,
            'pricing' => $data['pricing'],
            'currency' => $data['session']->currency,
            'language' => $data['session']->language,
            'seller_id' => $data['session']->seller_id,
            'campaign_id' => $data['session']->campaign_id,
            'lead' => $data['lead'],
        ]);
    }

    /**
     * Identificar lead (passo 1 do formulário)
     * POST /co/identify
     */
    public function identify(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string|exists:checkout_sessions,token',
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'church_name' => 'nullable|string',
            'members_count' => 'nullable|integer|min:1',
        ]);

        try {
            $session = CheckoutSession::where('token', $request->session_token)->firstOrFail();

            $result = $this->orchestrator->identifyLead($session, [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'document' => $request->document,
                'church_name' => $request->church_name,
                'members_count' => $request->members_count,
                'coupon_code' => $request->coupon_code,
                'order_bumps' => $request->order_bumps,
            ]);

            return response()->json([
                'success' => true,
                'lead_uuid' => $result['lead_uuid'],
                'pricing' => $result['pricing'],
            ]);

        } catch (\Exception $e) {
            Log::error('CheckoutNew: Erro ao identificar lead', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar dados. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Calcular preço (API para atualização em tempo real)
     * POST /co/pricing
     */
    public function calculatePricing(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'coupon_code' => 'nullable|string',
            'order_bumps' => 'nullable|array',
        ]);

        try {
            $session = CheckoutSession::where('token', $request->session_token)->firstOrFail();

            $pricing = $this->pricingService->calculatePrice(
                $session->offer,
                $session->currency,
                [
                    'coupon_code' => $request->coupon_code,
                    'order_bumps' => $request->order_bumps,
                ]
            );

            return response()->json([
                'success' => true,
                'pricing' => $pricing,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular preço',
            ], 500);
        }
    }

    /**
     * Validar cupom
     * POST /co/validate-coupon
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'coupon_code' => 'required|string',
        ]);

        try {
            $session = CheckoutSession::where('token', $request->session_token)->firstOrFail();

            $result = $this->pricingService->validateCoupon(
                $request->coupon_code,
                $session->price_final,
                $session->currency,
                $session->offer_id
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => 'Erro ao validar cupom',
            ], 500);
        }
    }

    /**
     * Processar pagamento (passo 2)
     * POST /co/pay
     */
    public function pay(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'payment_method' => 'required|in:pix,boleto,cartao',
            'card' => 'required_if:payment_method,cartao|array',
            'card.number' => 'required_if:payment_method,cartao|string',
            'card.name' => 'required_if:payment_method,cartao|string',
            'card.expiry' => 'required_if:payment_method,cartao|string',
            'card.cvv' => 'required_if:payment_method,cartao|string',
        ]);

        try {
            $session = CheckoutSession::with(['offer', 'lead'])
                ->where('token', $request->session_token)
                ->firstOrFail();

            $result = $this->orchestrator->createPayment($session, [
                'payment_method' => $request->payment_method,
                'card' => $request->card,
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('CheckoutNew: Erro ao processar pagamento', [
                'session_token' => $request->session_token,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Erro ao processar pagamento',
            ], 500);
        }
    }

    /**
     * Página de sucesso
     * GET /co/success/{orderNumber}
     */
    public function success(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['offer', 'lead', 'payment'])
            ->firstOrFail();

        return view('checkout-new.success', [
            'order' => $order,
        ]);
    }

    /**
     * Buscar status do pagamento (polling)
     * GET /co/payment-status/{paymentUuid}
     */
    public function paymentStatus(string $paymentUuid)
    {
        $payment = Payment::where('uuid', $paymentUuid)->first();

        if (! $payment) {
            return response()->json(['error' => 'Pagamento não encontrado'], 404);
        }

        return response()->json([
            'status' => $payment->status,
            'is_paid' => $payment->isPaid(),
            'updated_at' => $payment->updated_at,
        ]);
    }

    /**
     * Buscar status da sessão de checkout (polling)
     * GET /co/session-status/{token}
     */
    public function sessionStatus(string $token)
    {
        $session = CheckoutSession::where('token', $token)->first();

        if (! $session) {
            return response()->json(['error' => 'Sessão não encontrada'], 404);
        }

        $order = $session->order;
        $payment = $order?->payment;

        return response()->json([
            'status' => $payment?->status ?? 'pending',
            'is_paid' => $payment?->status === 'CONFIRMED',
            'due_date' => $payment?->due_date?->toIso8601String(),
        ]);
    }

    /**
     * Checkout de evento com vagas limitadas
     * GET /co/evento/{slug}
     */
    public function evento(string $slug)
    {
        $evento = Evento::where('slug', $slug)->firstOrFail();

        if (! $evento->isDisponivel()) {
            return view('checkout-new.esgotado', ['evento' => $evento]);
        }

        return view('checkout-new.evento', ['evento' => $evento]);
    }

    /**
     * Processar pagamento de evento
     * POST /co/evento/{slug}/pay
     */
    public function eventoPay(Request $request, string $slug, AsaasService $asaas)
    {
        $evento = Evento::where('slug', $slug)->firstOrFail();

        if (! $evento->isDisponivel()) {
            return back()->withErrors(['error' => 'Este evento não está mais disponível.'])->withInput();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'document' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20',
            'billing_type' => 'required|in:PIX,BOLETO,CREDIT_CARD',
        ]);

        $cpf = preg_replace('/\D/', '', $request->document);

        $customer = $asaas->createCustomer([
            'name' => $request->name,
            'email' => $request->email,
            'cpfCnpj' => $cpf,
            'phone' => $request->phone,
        ]);

        if (! $customer || isset($customer['errors'])) {
            return back()->withErrors(['error' => 'Erro ao processar dados. Verifique o CPF/CNPJ.'])->withInput();
        }

        $payment = $asaas->createPayment([
            'customer' => $customer['id'],
            'billingType' => $request->billing_type,
            'value' => (float) $evento->valor,
            'dueDate' => now()->addDay()->format('Y-m-d'),
            'description' => "Evento: {$evento->titulo}",
        ]);

        if (! $payment || isset($payment['errors'])) {
            return back()->withErrors(['error' => 'Erro ao gerar cobrança. Tente novamente.'])->withInput();
        }

        $evento->incrementarVagas();

        return view('checkout-new.evento-pagamento', [
            'evento' => $evento,
            'payment' => $payment,
            'billing_type' => $request->billing_type,
        ]);
    }
}
