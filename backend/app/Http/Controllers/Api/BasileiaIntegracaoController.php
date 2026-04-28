<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Plano;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BasileiaIntegracaoController extends Controller
{
    protected AsaasService $asaasService;

    public function __construct()
    {
        $this->asaasService = new AsaasService();
    }

    /**
     * Middleware de autenticação por Token Bearer
     */
    private function validateSecret(Request $request)
    {
        $token = $request->bearerToken();
        $secret = config('services.church.secret', '');

        if (empty($secret) || $token !== $secret) {
            return false;
        }
        return true;
    }

    /**
     * Cancelar plano/assinatura
     * POST /api/integracao/cancelar-assinatura
     */
    public function cancelarAssinatura(Request $request)
    {
        if (!$this->validateSecret($request)) {
            return response()->json(['error' => 'Não autorizado'], 401);
        }

        $validator = Validator::make($request->all(), [
            'venda_id' => 'required|exists:vendas,id',
            'motivo' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $venda = Venda::find($request->venda_id);

        if ($venda->asaas_subscription_id) {
            $success = $this->asaasService->cancelSubscription($venda->asaas_subscription_id);
            if (!$success) {
                return response()->json(['error' => 'Falha ao cancelar assinatura no Asaas'], 500);
            }
        }

        $venda->update([
            'status' => 'Cancelado',
            'renovacao_ativa' => false,
            'observacao_interna' => $venda->observacao_interna . "\nCancelado via API pelo Basiléia Church. Motivo: " . ($request->motivo ?? 'Não informado')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assinatura cancelada com sucesso',
            'venda_id' => $venda->id
        ]);
    }

    /**
     * Atualizar plano/valor
     * POST /api/integracao/atualizar-plano
     */
    public function atualizarPlano(Request $request)
    {
        if (!$this->validateSecret($request)) {
            return response()->json(['error' => 'Não autorizado'], 401);
        }

        $validator = Validator::make($request->all(), [
            'venda_id' => 'required|exists:vendas,id',
            'novo_plano_id' => 'required|exists:planos,id',
            'valor' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $venda = Venda::find($request->venda_id);
        $plano = Plano::find($request->novo_plano_id);
        $valor = $request->valor ?? $plano->valor;

        if ($venda->asaas_subscription_id) {
            try {
                $this->asaasService->updateSubscription($venda->asaas_subscription_id, [
                    'value' => $valor,
                    'description' => "Atualização de Plano: {$plano->nome}"
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Falha ao atualizar assinatura no Asaas: ' . $e->getMessage()], 500);
            }
        }

        $venda->update([
            'plano_id' => $plano->id,
            'plano' => $plano->nome,
            'valor' => $valor,
            'observacao_interna' => $venda->observacao_interna . "\nPlano atualizado via API para: {$plano->nome}"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plano atualizado com sucesso',
            'venda_id' => $venda->id,
            'novo_plano' => $plano->nome,
            'novo_valor' => $valor
        ]);
    }

    /**
     * Contratar novo serviço (Auto-contratação)
     * POST /api/integracao/contratar
     */
    public function contratar(Request $request)
    {
        if (!$this->validateSecret($request)) {
            return response()->json(['error' => 'Não autorizado'], 401);
        }

        $validator = Validator::make($request->all(), [
            'nome_igreja' => 'required|string|max:255',
            'documento' => 'required|string|max:20', // CPF ou CNPJ
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:20',
            'plano_id' => 'required|exists:planos,id',
            'tipo_pagamento' => 'required|in:pix,boleto,cartao',
            'vendedor_id' => 'nullable|exists:vendedores,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 1. Criar ou buscar cliente
            $cliente = Cliente::where('documento', preg_replace('/\D/', '', $request->documento))->first();
            if (!$cliente) {
                $cliente = Cliente::create([
                    'nome_igreja' => $request->nome_igreja,
                    'documento' => preg_replace('/\D/', '', $request->documento),
                    'email' => $request->email,
                    'whatsapp' => preg_replace('/\D/', '', $request->whatsapp),
                    'status' => 'Lead'
                ]);
            }

            // 2. Criar cliente no Asaas se necessário
            $asaasCustomer = $this->asaasService->createCustomer(
                $cliente->nome_igreja,
                $cliente->documento,
                $cliente->whatsapp,
                $cliente->email
            );

            $plano = Plano::find($request->plano_id);

            // 3. Criar Venda
            $venda = Venda::create([
                'cliente_id' => $cliente->id,
                'vendedor_id' => $request->vendedor_id,
                'plano_id' => $plano->id,
                'plano' => $plano->nome,
                'valor' => $plano->valor,
                'status' => 'Aguardando pagamento',
                'forma_pagamento' => $request->tipo_pagamento,
                'tipo_negociacao' => 'assinatura',
                'origem' => 'API_CHURCH'
            ]);

            // 4. Criar Assinatura no Asaas
            $billingType = match($request->tipo_pagamento) {
                'pix' => 'PIX',
                'boleto' => 'BOLETO',
                'cartao' => 'CREDIT_CARD',
                default => 'PIX'
            };

            $subscription = $this->asaasService->createSubscription([
                'customer' => $asaasCustomer['id'],
                'billingType' => $billingType,
                'value' => $plano->valor,
                'nextDueDate' => now()->addDays(3)->format('Y-m-d'),
                'cycle' => 'MONTHLY',
                'description' => "Assinatura - {$plano->nome}",
                'externalReference' => (string) $venda->id
            ]);

            $venda->update([
                'asaas_subscription_id' => $subscription['id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contratação iniciada com sucesso',
                'venda_id' => $venda->id,
                'subscription_id' => $subscription['id'],
                'invoice_url' => $subscription['invoiceUrl'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao contratar via API', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            return response()->json(['error' => 'Falha interna ao processar contratação: ' . $e->getMessage()], 500);
        }
    }
}
