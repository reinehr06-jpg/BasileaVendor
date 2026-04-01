<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BasileiaChurchWebhookController extends Controller
{
    /**
     * Endpoint para receber dados do cliente do Basileia Church
     * e enviar status da compra para liberar/bloquear o sistema
     */
    public function syncCliente(Request $request)
    {
        // Validar token de segurança
        $token = $request->header('X-Webhook-Token');
        $tokenConfigurado = Setting::get('basileia_church_webhook_token', '');
        
        if ($token !== $tokenConfigurado) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        $request->validate([
            'cpf_cnpj' => 'required|string',
            'action' => 'required|in:check_status,create_account',
        ]);

        $documento = preg_replace('/\D/', '', $request->cpf_cnpj);
        $cliente = Cliente::where('documento', $documento)->first();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'error' => 'Cliente não encontrado',
                'status' => 'nao_encontrado',
            ], 404);
        }

        if ($request->action === 'check_status') {
            return $this->checkStatus($cliente);
        }

        if ($request->action === 'create_account') {
            return $this->createAccount($cliente);
        }

        return response()->json(['error' => 'Ação inválida'], 400);
    }

    /**
     * Verificar status do cliente (se está ativo ou bloqueado)
     */
    private function checkStatus(Cliente $cliente): \Illuminate\Http\JsonResponse
    {
        // Buscar última venda paga do cliente
        $ultimaVenda = Venda::where('cliente_id', $cliente->id)
            ->whereIn('status', ['PAGO', 'Pago', 'RECEIVED'])
            ->orderByDesc('created_at')
            ->first();

        $status = 'bloqueado';
        $plano = null;
        $dataExpiracao = null;

        if ($ultimaVenda) {
            $status = 'ativo';
            $plano = $ultimaVenda->plano;
            
            // Calcular data de expiração
            if ($ultimaVenda->tipo_negociacao === 'mensal') {
                // Mensal: expira 30 dias após o último pagamento
                $dataExpiracao = $ultimaVenda->updated_at->addDays(30)->format('Y-m-d');
            } else {
                // Anual: expira 365 dias após a venda
                $dataExpiracao = $ultimaVenda->created_at->addYear()->format('Y-m-d');
            }
        }

        return response()->json([
            'success' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nome' => $cliente->nome_igreja ?? $cliente->nome,
                'documento' => $cliente->documento,
                'email' => $cliente->email,
                'telefone' => $cliente->whatsapp ?? $cliente->telefone,
            ],
            'status' => $status,
            'plano' => $plano,
            'data_expiracao' => $dataExpiracao,
        ]);
    }

    /**
     * Criar conta para o cliente no Basileia Church
     */
    private function createAccount(Cliente $cliente): \Illuminate\Http\JsonResponse
    {
        // Gerar senha automática
        $senha = $this->gerarSenha();

        return response()->json([
            'success' => true,
            'message' => 'Conta criada com sucesso',
            'cliente' => [
                'id' => $cliente->id,
                'nome' => $cliente->nome_igreja ?? $cliente->nome,
                'email' => $cliente->email,
                'telefone' => $cliente->whatsapp ?? $cliente->telefone,
                'login' => $cliente->email ?? $cliente->documento,
                'senha' => $senha,
            ],
        ]);
    }

    /**
     * Gerar senha automática
     */
    private function gerarSenha(): string
    {
        $maiusculas = strtoupper(Str::random(2));
        $minusculas = Str::random(4);
        $numeros = random_int(10, 99);
        
        return $maiusculas . $minusculas . $numeros;
    }

    /**
     * Endpoint para receber notificação de pagamento do Asaas
     * e enviar status atualizado para o Basileia Church
     */
    public function webhookAsaas(Request $request)
    {
        // Verificar token do webhook
        $token = $request->header('asaas-access-token');
        $tokenConfigurado = Setting::get('asaas_webhook_token', '');
        
        if ($token !== $tokenConfigurado) {
            Log::warning('Webhook Asaas: Token inválido', ['token_recebido' => $token]);
            return response()->json(['error' => 'Token inválido'], 401);
        }

        $event = $request->input('event');
        $payment = $request->input('payment');

        Log::info('Webhook Asaas recebido', [
            'event' => $event,
            'payment_id' => $payment['id'] ?? null,
        ]);

        // Processar evento de pagamento
        if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'])) {
            $this->processarPagamentoRecebido($payment);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Processar pagamento recebido e enviar status para o Basileia Church
     */
    private function processarPagamentoRecebido(array $payment): void
    {
        $externalReference = $payment['externalReference'] ?? null;
        
        if (!$externalReference || !str_starts_with($externalReference, 'venda_')) {
            return;
        }

        $vendaId = str_replace('venda_', '', $externalReference);
        $venda = Venda::find($vendaId);
        
        if (!$venda) {
            Log::warning('Webhook: Venda não encontrada', ['venda_id' => $vendaId]);
            return;
        }

        $cliente = $venda->cliente;
        if (!$cliente) {
            return;
        }

        // Enviar status para o Basileia Church
        $urlChurch = Setting::get('basileia_church_webhook_url', '');
        if (empty($urlChurch)) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Http::post($urlChurch, [
                'cpf_cnpj' => $cliente->documento,
                'status' => 'ativo',
                'plano' => $venda->plano,
                'venda_id' => $venda->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar status para Basileia Church', [
                'venda_id' => $venda->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
