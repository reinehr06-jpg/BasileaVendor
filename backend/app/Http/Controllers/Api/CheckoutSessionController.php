<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckoutSession;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Plano;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutSessionController extends Controller
{
    public function create(Request $request, AsaasService $asaas)
    {
        $request->validate([
            'plan_id' => 'required_without:evento_slug|integer',
            'evento_slug' => 'required_without:plan_id|string',
            'billing_type' => 'required|in:PIX,BOLETO,CREDIT_CARD',
            'installments' => 'nullable|integer|min:1|max:12',
            'customer' => 'required|array',
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'required|email',
            'customer.document' => 'required|string|max:20',
            'customer.phone' => 'nullable|string|max:20',
        ]);

        if ($request->evento_slug) {
            return $this->createEventoSession($request, $asaas);
        }

        return $this->createPlanoSession($request, $asaas);
    }

    private function createPlanoSession(Request $request, AsaasService $asaas)
    {
        $plano = Plano::findOrFail($request->plan_id);
        $billingType = $request->billing_type;

        if ($billingType === 'CREDIT_CARD') {
            $installments = $request->installments ?? 1;
        } else {
            $installments = 1;
        }

        $asaasCustomer = $asaas->createCustomer([
            'name' => $request->customer['name'],
            'email' => $request->customer['email'],
            'cpfCnpj' => preg_replace('/\D/', '', $request->customer['document']),
            'phone' => $request->customer['phone'] ?? null,
        ]);

        if (!$asaasCustomer || isset($asaasCustomer['errors'])) {
            return response()->json([
                'error' => 'Erro ao criar cliente no Asaas',
                'details' => $asaasCustomer['errors'] ?? null,
            ], 422);
        }

        $valor = $installments > 1 ? $plano->valor_anual : $plano->valor_mensal;

        $payment = $asaas->createPayment([
            'customer' => $asaasCustomer['id'],
            'billingType' => $billingType,
            'value' => (float) $valor,
            'dueDate' => now()->addDay()->format('Y-m-d'),
            'description' => "Assinatura - {$plano->nome}",
            'installmentCount' => $installments > 1 ? $installments : null,
            'installmentValue' => $installments > 1 ? round($valor / $installments, 2) : null,
        ]);

        if (!$payment || isset($payment['errors'])) {
            return response()->json([
                'error' => 'Erro ao criar cobrança no Asaas',
                'details' => $payment['errors'] ?? null,
            ], 422);
        }

        $session = CheckoutSession::create([
            'token' => Str::uuid(),
            'offer_id' => null,
            'lead_id' => null,
            'seller_id' => null,
            'currency' => 'BRL',
            'price_original' => $valor,
            'price_final' => $valor,
            'status' => 'pending',
            'expires_at' => now()->addHours(2),
        ]);

        return response()->json([
            'session_id' => $session->token,
            'payment_id' => $payment['id'],
            'checkout_url' => $payment['invoiceUrl'] ?? $payment['bankSlipUrl'] ?? null,
            'pix_qrcode' => $billingType === 'PIX' ? ($payment['pixQrCode'] ?? null) : null,
            'amount' => (float) $valor,
            'billing_type' => $billingType,
            'expires_at' => $session->expires_at->toIso8601String(),
        ]);
    }

    private function createEventoSession(Request $request, AsaasService $asaas)
    {
        $evento = \App\Models\Evento::where('slug', $request->evento_slug)->first();

        if (!$evento || !$evento->isDisponivel()) {
            return response()->json([
                'error' => 'Evento não disponível',
                'message' => 'Este evento esgotou ou expirou.',
            ], 422);
        }

        $asaasCustomer = $asaas->createCustomer([
            'name' => $request->customer['name'],
            'email' => $request->customer['email'],
            'cpfCnpj' => preg_replace('/\D/', '', $request->customer['document']),
            'phone' => $request->customer['phone'] ?? null,
        ]);

        if (!$asaasCustomer || isset($asaasCustomer['errors'])) {
            return response()->json([
                'error' => 'Erro ao criar cliente',
                'details' => $asaasCustomer['errors'] ?? null,
            ], 422);
        }

        $payment = $asaas->createPayment([
            'customer' => $asaasCustomer['id'],
            'billingType' => $request->billing_type,
            'value' => (float) $evento->valor,
            'dueDate' => now()->addDay()->format('Y-m-d'),
            'description' => "Evento: {$evento->titulo}",
        ]);

        if (!$payment || isset($payment['errors'])) {
            return response()->json([
                'error' => 'Erro ao criar cobrança',
                'details' => $payment['errors'] ?? null,
            ], 422);
        }

        return response()->json([
            'evento_slug' => $evento->slug,
            'payment_id' => $payment['id'],
            'checkout_url' => $payment['invoiceUrl'] ?? null,
            'pix_qrcode' => $request->billing_type === 'PIX' ? ($payment['pixQrCode'] ?? null) : null,
            'amount' => (float) $evento->valor,
            'billing_type' => $request->billing_type,
            'vagas_restantes' => $evento->vagasRestantes(),
        ]);
    }

    public function show(string $id)
    {
        $session = CheckoutSession::where('token', $id)
            ->orWhere('id', $id)
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Sessão não encontrada'], 404);
        }

        return response()->json($session->only([
            'token', 'status', 'currency', 'price_final', 'expires_at',
        ]));
    }
}
