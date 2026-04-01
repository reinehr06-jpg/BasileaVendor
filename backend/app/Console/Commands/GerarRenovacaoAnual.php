<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venda;
use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Cobranca;
use App\Models\Notificacao;
use App\Services\AsaasService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GerarRenovacaoAnual extends Command
{
    protected $signature = 'vendas:gerar-renovacao-anual 
                            {--dry-run : Apenas simula, não cria cobranças}
                            {--data= : Data específica para verificar (Y-m-d)}';

    protected $description = 'Gera cobranças de renovação anual para vendas que completam 1 ano';

    public function handle()
    {
        $dataReferencia = $this->option('data') 
            ? Carbon::parse($this->option('data')) 
            : Carbon::today();

        $dryRun = $this->option('dry-run');

        $this->info("🔍 Verificando vendas para renovação em: {$dataReferencia->format('d/m/Y')}");
        
        if ($dryRun) {
            $this->warn("⚠️  MODO DRY RUN - Nenhuma cobrança será criada");
        }

        // Buscar vendas anuais que completam 1 ano hoje
        $vendasParaRenovar = Venda::where('tipo_negociacao', 'anual')
            ->whereIn('status', ['PAGO', 'Pago', 'RECEIVED'])
            ->whereDate('data_venda', $dataReferencia->subYear()) // Há exatamente 1 ano
            ->orWhere(function($q) use ($dataReferencia) {
                // Ou vendas criadas há 1 ano (fallback)
                $q->where('tipo_negociacao', 'anual')
                  ->whereIn('status', ['PAGO', 'Pago', 'RECEIVED'])
                  ->whereDate('created_at', $dataReferencia->subYear());
            })
            ->with(['cliente', 'vendedor'])
            ->get();

        // Filtrar apenas vendas que não têm renovação já criada
        $vendasParaRenovar = $vendasParaRenovar->filter(function ($venda) {
            $jaRenovada = Venda::where('cliente_id', $venda->cliente_id)
                ->where('vendedor_id', $venda->vendedor_id)
                ->where('plano', $venda->plano)
                ->where('tipo_negociacao', 'anual')
                ->where('created_at', '>', $venda->created_at)
                ->exists();
            return !$jaRenovada;
        });

        if ($vendasParaRenovar->isEmpty()) {
            $this->info("✅ Nenhuma venda para renovar hoje.");
            return 0;
        }

        $this->info("📋 Encontradas {$vendasParaRenovar->count()} vendas para renovar:");
        $this->newLine();

        $sucesso = 0;
        $erro = 0;

        foreach ($vendasParaRenovar as $venda) {
            $this->line("  Venda #{$venda->id} - {$venda->cliente->nome_igreja} - R$ {$venda->valor_final}");

            if ($dryRun) {
                $this->line("    [DRY RUN] Criaria cobrança de R$ {$venda->valor_final}");
                continue;
            }

            try {
                $novaVenda = $this->criarRenovacao($venda);
                $sucesso++;
                $this->info("    ✅ Renovação criada com sucesso");
                
                // Criar notificação para o Master
                Notificacao::notificarMasters(
                    'renovacao_anual',
                    '🔄 Renovação Anual Gerada',
                    "Uma cobrança de renovação foi gerada automaticamente para {$venda->cliente->nome_igreja}.\n\n" .
                    "• Venda original: #{$venda->id}\n" .
                    "• Nova venda: #{$novaVenda->id}\n" .
                    "• Vendedor atual: {$venda->vendedor->user->name}\n" .
                    "• Valor: R$ {$venda->valor_final}\n" .
                    "• Plano: {$venda->plano}\n\n" .
                    "⚠️ Verifique se o vendedor ainda é o mesmo e se a comissão precisa ser alterada.",
                    [
                        'venda_original_id' => $venda->id,
                        'nova_venda_id' => $novaVenda->id,
                        'vendedor_id' => $venda->vendedor_id,
                        'valor' => $venda->valor_final,
                    ]
                );
            } catch (\Exception $e) {
                $erro++;
                $this->error("    ❌ Erro: {$e->getMessage()}");
                Log::error("Erro ao gerar renovação anual", [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("📊 Resumo:");
        $this->line("  ✅ Sucesso: {$sucesso}");
        if ($erro > 0) {
            $this->error("  ❌ Erros: {$erro}");
        }

        return 0;
    }

    private function criarRenovacao(Venda $vendaOriginal): Venda
    {
        $asaas = new AsaasService();
        $cliente = $vendaOriginal->cliente;
        $vendedor = $vendaOriginal->vendedor;

        // Criar nova venda
        $novaVenda = Venda::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'valor' => $vendaOriginal->valor_final,
            'valor_original' => $vendaOriginal->valor_original ?? $vendaOriginal->valor_final,
            'valor_final' => $vendaOriginal->valor_final,
            'comissao_gerada' => 0,
            'status' => 'Aguardando pagamento',
            'plano' => $vendaOriginal->plano,
            'forma_pagamento' => $vendaOriginal->forma_pagamento,
            'tipo_negociacao' => 'anual',
            'desconto' => $vendaOriginal->desconto,
            'parcelas' => $vendaOriginal->parcelas,
            'origem' => 'renovacao_automatica',
            'data_venda' => Carbon::now(),
        ]);

        // Criar cobrança no Asaas
        $dataVencimento = Carbon::now()->addDays(3)->format('Y-m-d');
        $descricaoCobranca = "Basiléia - Renovação Plano {$vendaOriginal->plano} (Anual)";

        // Determinar split se aplicável
        $split = [];
        if ($vendedor->isAptoSplit()) {
            $split = $asaas->buildSplitArray($vendedor, $vendaOriginal->valor_final, 'recorrencia');
        }

        $paymentPayload = [
            'customer' => $cliente->asaas_customer_id,
            'billingType' => $vendaOriginal->forma_pagamento,
            'value' => $vendaOriginal->valor_final,
            'dueDate' => $dataVencimento,
            'description' => $descricaoCobranca,
            'externalReference' => "venda_{$novaVenda->id}",
        ];

        // Se for parcelado
        if ($vendaOriginal->forma_pagamento === 'CREDIT_CARD' && $vendaOriginal->parcelas > 1) {
            $paymentPayload['totalValue'] = $vendaOriginal->valor_final;
            $paymentPayload['installmentCount'] = $vendaOriginal->parcelas;
            unset($paymentPayload['value']);
        }

        if (!empty($split)) {
            $paymentPayload['split'] = $split;
        }

        $paymentData = $asaas->requestAsaas('POST', '/payments', $paymentPayload);

        // Criar registro de cobrança
        Cobranca::create([
            'venda_id' => $novaVenda->id,
            'asaas_id' => $paymentData['id'] ?? null,
            'status' => $paymentData['status'] ?? 'PENDING',
            'link' => $paymentData['invoiceUrl'] ?? ($paymentData['bankSlipUrl'] ?? null),
        ]);

        // Criar registro de pagamento
        $formaMap = ['PIX' => 'pix', 'BOLETO' => 'boleto', 'CREDIT_CARD' => 'cartao'];
        Pagamento::create([
            'venda_id' => $novaVenda->id,
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'asaas_payment_id' => $paymentData['id'] ?? null,
            'valor' => $vendaOriginal->valor_final,
            'forma_pagamento' => $formaMap[$vendaOriginal->forma_pagamento] ?? 'pix',
            'status' => 'pendente',
            'data_vencimento' => $dataVencimento,
            'link_pagamento' => $paymentData['invoiceUrl'] ?? null,
            'invoice_url' => $paymentData['invoiceUrl'] ?? null,
            'bank_slip_url' => $paymentData['bankSlipUrl'] ?? null,
            'nota_fiscal_status' => 'pendente',
        ]);

        // Atualizar status da nova venda
        $novaVenda->update(['modo_cobranca_asaas' => 'PAYMENT']);

        Log::info("Renovação anual criada", [
            'venda_original_id' => $vendaOriginal->id,
            'nova_venda_id' => $novaVenda->id,
            'asaas_payment_id' => $paymentData['id'] ?? null,
        ]);
        
        return $novaVenda;
    }
}
