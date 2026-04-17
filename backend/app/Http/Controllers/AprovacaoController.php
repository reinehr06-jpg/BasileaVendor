<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Venda;
use App\Models\AprovacaoVenda;
use App\Models\Pagamento;
use App\Models\Cobranca;
use App\Models\Setting;
use App\Models\Notificacao;
use App\Services\AsaasService;
use Carbon\Carbon;

class AprovacaoController extends Controller
{
    /**
     * Limite de desconto que requer aprovação
     */
    private const LIMITE_DESCONTO = 5.00;

    /**
     * Exibir página de aprovações comerciais (Master)
     */
    public function index()
    {
        $pendentes = AprovacaoVenda::where('status', 'PENDENTE')
            ->with(['venda.cliente', 'venda.vendedor.user', 'solicitadoPor'])
            ->orderByDesc('created_at')
            ->get();

        $aprovadas = AprovacaoVenda::where('status', 'APROVADO')
            ->with(['venda.cliente', 'venda.vendedor.user', 'aprovadoPor'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        $rejeitadas = AprovacaoVenda::where('status', 'REJEITADO')
            ->with(['venda.cliente', 'venda.vendedor.user', 'aprovadoPor'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        return view('master.aprovacoes.index', compact('pendentes', 'aprovadas', 'rejeitadas'));
    }

    /**
     * Aprovar uma venda
     */
    public function aprovar(Request $request, $id)
    {
        $aprovacao = AprovacaoVenda::findOrFail($id);
        
        if ($aprovacao->status !== 'PENDENTE') {
            return back()->with('error', 'Esta aprovação já foi processada.');
        }

        try {
            DB::beginTransaction();

            $aprovacao->update([
                'status' => 'APROVADO',
                'aprovado_por' => Auth::id(),
                'observacao' => $request->observacao,
            ]);

            $venda = $aprovacao->venda;
            
            // Atualizar status da venda
            $venda->update([
                'status_aprovacao' => 'aprovado',
                'aprovado_por' => Auth::id(),
                'aprovado_em' => now(),
                'status' => 'Aguardando pagamento',
                'requer_aprovacao' => false,
            ]);

            // Gerar cobrança no Asaas
            $this->gerarCobrancaAposAprovacao($venda);

            DB::commit();

            // Notificar vendedor
            Notificacao::create([
                'user_id' => $venda->vendedor->user->id,
                'tipo' => 'venda_aprovada',
                'titulo' => '✅ Venda Aprovada',
                'mensagem' => "Sua venda #{$venda->id} foi aprovada! A cobrança já foi gerada.",
                'dados' => ['venda_id' => $venda->id],
            ]);

            return back()->with('success', 'Venda aprovada com sucesso! A cobrança foi gerada no Asaas.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao aprovar venda", ['venda_id' => $aprovacao->venda_id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Erro ao aprovar venda: ' . $e->getMessage());
        }
    }

    /**
     * Gerar cobrança no Asaas após aprovação
     */
    private function gerarCobrancaAposAprovacao(Venda $venda): void
    {
        $asaas = new AsaasService();
        $cliente = $venda->cliente;
        $vendedor = $venda->vendedor;

        // Verificar/criar cliente no Asaas
        if (!$cliente->asaas_customer_id) {
            $documento = preg_replace('/\D/', '', $cliente->documento);
            $telefone = preg_replace('/\D/', '', $cliente->whatsapp ?? $cliente->telefone ?? '');
            
            $customerData = $asaas->createCustomer(
                $cliente->nome_igreja ?? $cliente->nome,
                $documento,
                $telefone,
                $cliente->email
            );
            
            $cliente->asaas_customer_id = $customerData['id'];
            $cliente->save();
        }

        $isAnual = in_array(strtolower($venda->tipo_negociacao ?? ''), ['anual', 'annual']);
        $isBoleto = $venda->forma_pagamento === 'BOLETO';
        $dataVencimento = Carbon::now()->addDays($isAnual && $isBoleto ? 5 : 3)->format('Y-m-d');
        $descricaoCobranca = "Basiléia - Plano {$venda->plano} ({$venda->tipo_negociacao})";

        // Determinar split se aplicável
        $split = [];
        if ($vendedor && $vendedor->isAptoSplit()) {
            $split = $asaas->buildSplitArray($vendedor, $venda->valor_final, 'inicial');
        }

        $paymentData = null;

        // Se for Boleto Mensal com Recorrência Automática, criar assinatura
        if ($venda->forma_pagamento === 'BOLETO' && $venda->tipo_negociacao === 'mensal') {
            $subscriptionPayload = [
                'customer' => $cliente->asaas_customer_id,
                'billingType' => 'BOLETO',
                'value' => $venda->valor_final,
                'nextDueDate' => $dataVencimento,
                'cycle' => 'MONTHLY',
                'description' => $descricaoCobranca,
                'externalReference' => "venda_{$venda->id}",
            ];
            
            if (!empty($split)) {
                $subscriptionPayload['split'] = $split;
            }
            
            $paymentData = $asaas->requestAsaas('POST', '/subscriptions', $subscriptionPayload);
            
            $venda->update([
                'modo_cobranca_asaas' => 'SUBSCRIPTION',
                'asaas_subscription_id' => $paymentData['id'] ?? null,
            ]);
        } else {
            // Cobrança avulsa ou parcelada
            $paymentPayload = [
                'customer' => $cliente->asaas_customer_id,
                'billingType' => $venda->forma_pagamento,
                'value' => $venda->valor_final,
                'dueDate' => $dataVencimento,
                'description' => $descricaoCobranca,
                'externalReference' => "venda_{$venda->id}",
            ];
            
            // Se for parcelado
            if ($venda->forma_pagamento === 'CREDIT_CARD' && $venda->parcelas > 1) {
                $paymentPayload['totalValue'] = $venda->valor_final;
                $paymentPayload['installmentCount'] = $venda->parcelas;
                unset($paymentPayload['value']);
            }
            
            if (!empty($split)) {
                $paymentPayload['split'] = $split;
            }
            
            $paymentData = $asaas->requestAsaas('POST', '/payments', $paymentPayload);
            
            $venda->update(['modo_cobranca_asaas' => 'PAYMENT']);
        }

        // Criar registro de cobrança
        Cobranca::updateOrCreate(
            ['venda_id' => $venda->id],
            [
                'asaas_id' => $paymentData['id'] ?? null,
                'status' => $paymentData['status'] ?? 'PENDING',
                'link' => $paymentData['invoiceUrl'] ?? ($paymentData['bankSlipUrl'] ?? null),
            ]
        );

        // Criar registro de pagamento
        $formaMap = ['PIX' => 'pix', 'BOLETO' => 'boleto', 'CREDIT_CARD' => 'cartao'];
        
        $pagamentoExistente = Pagamento::where('venda_id', $venda->id)->first();
        
        if ($pagamentoExistente) {
            // Atualizar pagamento existente
            $pagamentoExistente->update([
                'cliente_id' => $cliente->id,
                'vendedor_id' => $venda->vendedor_id,
                'asaas_payment_id' => $paymentData['id'] ?? $pagamentoExistente->asaas_payment_id,
                'valor' => $venda->valor_final,
                'forma_pagamento' => $formaMap[$venda->forma_pagamento] ?? 'pix',
                'status' => 'pendente',
                'data_vencimento' => $dataVencimento,
                'link_pagamento' => $paymentData['invoiceUrl'] ?? $pagamentoExistente->link_pagamento,
                'invoice_url' => $paymentData['invoiceUrl'] ?? $pagamentoExistente->invoice_url,
                'bank_slip_url' => $paymentData['bankSlipUrl'] ?? $pagamentoExistente->bank_slip_url,
                'linha_digitavel' => $paymentData['identificationField'] ?? $pagamentoExistente->linha_digitavel,
            ]);
            $novoPagamento = $pagamentoExistente;
        } else {
            // Criar novo pagamento
            $novoPagamento = Pagamento::create([
                'venda_id' => $venda->id,
                'cliente_id' => $cliente->id,
                'vendedor_id' => $venda->vendedor_id,
                'asaas_payment_id' => $paymentData['id'] ?? null,
                'valor' => $venda->valor_final,
                'forma_pagamento' => $formaMap[$venda->forma_pagamento] ?? 'pix',
                'status' => 'pendente',
                'data_vencimento' => $dataVencimento,
                'link_pagamento' => $paymentData['invoiceUrl'] ?? null,
                'invoice_url' => $paymentData['invoiceUrl'] ?? null,
                'bank_slip_url' => $paymentData['bankSlipUrl'] ?? null,
                'linha_digitavel' => $paymentData['identificationField'] ?? null,
                'nota_fiscal_status' => 'pendente',
            ]);
        }

        // Sincronizar pagamento para buscar o link do boleto se não veio na criação
        if ($venda->forma_pagamento === 'BOLETO' && empty($novoPagamento->bank_slip_url)) {
            try {
                // Aguardar um pouco para o Asaas gerar o boleto
                sleep(2);
                $pagamentoService = new \App\Services\PagamentoService();
                $pagamentoService->sync($novoPagamento);
                
                // Recarregar pagamento para pegar os dados atualizados
                $novoPagamento->refresh();
            } catch (\Exception $e) {
                Log::warning("Erro ao sincronizar pagamento após aprovação", [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Cobrança gerada após aprovação", [
            'venda_id' => $venda->id,
            'payment_id' => $paymentData['id'] ?? null,
            'bank_slip_url' => $novoPagamento->bank_slip_url,
            'link_pagamento' => $novoPagamento->link_pagamento,
        ]);
    }

    /**
     * Rejeitar uma venda
     */
    public function rejeitar(Request $request, $id)
    {
        $aprovacao = AprovacaoVenda::findOrFail($id);
        
        if ($aprovacao->status !== 'PENDENTE') {
            return back()->with('error', 'Esta aprovação já foi processada.');
        }

        $aprovacao->update([
            'status' => 'REJEITADO',
            'aprovado_por' => Auth::id(),
            'motivo_rejeicao' => $request->observacao,
        ]);

        // Atualizar status da venda
        $aprovacao->venda->update([
            'status_aprovacao' => 'rejeitado',
            'status' => 'Rejeitado',
        ]);

        // Notificar vendedor
        Notificacao::create([
            'user_id' => $aprovacao->venda->vendedor->user->id,
            'tipo' => 'venda_rejeitada',
            'titulo' => '❌ Venda Rejeitada',
            'mensagem' => "Sua venda #{$aprovacao->venda_id} foi rejeitada.\nMotivo: {$request->observacao}",
            'dados' => ['venda_id' => $aprovacao->venda_id],
        ]);

        return back()->with('success', 'Venda rejeitada. O vendedor será notificado.');
    }

    /**
     * Verificar se uma venda requer aprovação
     * Chamado durante a criação da venda
     */
    public static function verificarAprovacao(Venda $venda): bool
    {
        $desconto = $venda->percentual_desconto ?? 0;
        
        // Verificar se excede o limite
        if ($desconto > self::LIMITE_DESCONTO) {
            // Criar registro de aprovação
            AprovacaoVenda::create([
                'venda_id' => $venda->id,
                'tipo_aprovacao' => 'DESCONTO',
                'percentual_solicitado' => $desconto,
                'limite_regra' => self::LIMITE_DESCONTO,
                'status' => 'PENDENTE',
                'solicitado_por' => Auth::id(),
            ]);

            // Atualizar venda
            $venda->update([
                'requer_aprovacao' => true,
                'status_aprovacao' => 'pendente',
            ]);

            return true; // Requer aprovação
        }

        return false; // Não requer aprovação
    }
}
