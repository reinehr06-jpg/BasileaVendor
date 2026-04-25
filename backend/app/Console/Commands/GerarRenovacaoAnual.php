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
    protected $signature = 'vendas:gerar-renovacao-anual {--dry-run} {--data=}';

    protected $description = 'Gera cobrancas de renovacao (anual/mensal) para vendas que completam seu ciclo';

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

        // Vendas Anuais: Vencimento hoje (há 1 ano)
        $dataAnual = $dataReferencia->copy()->subYear();
        $vendasAnuais = Venda::where('tipo_negociacao', 'anual')
            ->whereIn('status', ['PAGO', 'Pago', 'RECEIVED'])
            ->whereNull('asaas_subscription_id')
            ->where(function($q) use ($dataAnual) {
                $q->whereDate('data_venda', $dataAnual)
                  ->orWhereDate('created_at', $dataAnual);
            })
            ->get();

        // Vendas Mensais: Vencimento hoje (há 1 mês)
        $dataMensal = $dataReferencia->copy()->subMonth();
        $vendasMensais = Venda::where('tipo_negociacao', 'mensal')
            ->whereIn('status', ['PAGO', 'Pago', 'RECEIVED'])
            ->whereNull('asaas_subscription_id')
            ->where(function($q) use ($dataMensal) {
                $q->whereDate('data_venda', $dataMensal)
                  ->orWhereDate('created_at', $dataMensal);
            })
            ->get();

        $vendasParaRenovar = $vendasAnuais->concat($vendasMensais);

        if ($vendasParaRenovar->isEmpty()) {
            $this->info('Nenhuma venda para renovar hoje.');
            return self::SUCCESS;
        }

        $this->info("📋 Encontradas {$vendasParaRenovar->count()} vendas para renovação.");
        $this->newLine();

        $sucesso = 0;
        $erro = 0;

        foreach ($vendasParaRenovar as $venda) {
            $this->line("  Venda #{$venda->id} - {$venda->cliente->nome_igreja} - R$ {$venda->valor_final}");

            // Evitar renovação duplicada se já existir uma venda posterior para o mesmo cliente/vendedor/plano
            $jaRenovada = Venda::where('cliente_id', $venda->cliente_id)
                ->where('vendedor_id', $venda->vendedor_id)
                ->where('plano', $venda->plano)
                ->where('created_at', '>', $venda->created_at)
                ->exists();

            if ($jaRenovada) {
                $this->warn("    ⚠️ Já renovada anteriormente. Pulando.");
                continue;
            }

            if ($dryRun) {
                $this->line("    [DRY RUN] Criaria cobrança de R$ {$venda->valor_final}");
                continue;
            }

            try {
                $novaVenda = $this->criarRenovacao($venda);
                $sucesso++;
                $this->info("    ✅ Renovação criada com sucesso (#{$novaVenda->id})");
                
                // Criar notificação para o Master
                Notificacao::notificarMasters(
                    'renovacao_gerada',
                    '🔄 Renovação Gerada Automática',
                    "Uma cobrança de renovação foi gerada automaticamente para {$venda->cliente->nome_igreja}.\n\n" .
                    "• Venda original: #{$venda->id}\n" .
                    "• Nova venda: #{$novaVenda->id}\n" .
                    "• Ciclo: {$venda->tipo_negociacao}\n" .
                    "• Valor: R$ {$venda->valor_final}\n" .
                    "• Plano: {$venda->plano}",
                    [
                        'venda_original_id' => $venda->id,
                        'nova_venda_id' => $novaVenda->id,
                        'valor' => $venda->valor_final,
                    ]
                );
            } catch (\Exception $e) {
                $erro++;
                $this->error("    ❌ Erro: {$e->getMessage()}");
                Log::error("Erro ao gerar renovação", [
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

        return self::SUCCESS;
    }

    private function criarRenovacao(Venda $vendaOriginal): Venda
    {
        $asaas = new AsaasService();
        $cliente = $vendaOriginal->cliente;
        $vendedor = $vendaOriginal->vendedor;

        // Criar nova venda com os mesmos dados da original
        $novaVenda = Venda::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'valor' => $vendaOriginal->valor_final,
            'valor_original' => $vendaOriginal->valor_original ?? $vendaOriginal->valor_final,
            'valor_final' => $vendaOriginal->valor_final,
            'comissao_gerada' => 0,
            'status' => 'Aguardando pagamento',
            'plano' => $vendaOriginal->plano,
            'forma_pagamento' => $vendaOriginal->forma_pagamento ?? 'PIX',
            'tipo_negociacao' => $vendaOriginal->tipo_negociacao,
            'desconto' => $vendaOriginal->desconto,
            'parcelas' => $vendaOriginal->parcelas ?? 1,
            'origem' => 'renovacao_automatica',
            'data_venda' => Carbon::now(),
        ]);

        // Determinar vencimento (5 dias para boleto, 15 para outros)
        $isBoleto = in_array(strtoupper($vendaOriginal->forma_pagamento ?? ''), ['BOLETO', 'BOLETO_BANCARIO']);
        $diasVencimento = $isBoleto ? 5 : 15;
        $dataVencimento = Carbon::now()->addDays($diasVencimento)->format('Y-m-d');
        
        $cicloNome = $vendaOriginal->tipo_negociacao === 'anual' ? 'Anual' : 'Mensal';
        $descricaoCobranca = "Basiléia - Renovação Plano {$vendaOriginal->plano} ({$cicloNome})";

        // Determinar split se aplicável
        $split = [];
        if ($vendedor && $vendedor->isAptoSplit()) {
            $split = $asaas->buildSplitArray($vendedor, $vendaOriginal->valor_final, 'recorrencia');
        }

        $paymentPayload = [
            'customer' => $cliente->asaas_customer_id,
            'billingType' => $vendaOriginal->forma_pagamento ?? 'PIX',
            'value' => $vendaOriginal->valor_final,
            'dueDate' => $dataVencimento,
            'description' => $descricaoCobranca,
            'externalReference' => "venda_{$novaVenda->id}",
        ];

        if (!empty($split)) {
            $paymentPayload['split'] = $split;
        }

        // Se for cartão e parcelado
        if ($paymentPayload['billingType'] === 'CREDIT_CARD' && $vendaOriginal->parcelas > 1) {
            $paymentPayload['totalValue'] = $vendaOriginal->valor_final;
            $paymentPayload['installmentCount'] = $vendaOriginal->parcelas;
            unset($paymentPayload['value']);
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
            'forma_pagamento' => $formaMap[$paymentPayload['billingType']] ?? 'pix',
            'status' => 'pendente',
            'data_vencimento' => $dataVencimento,
            'link_pagamento' => $paymentData['invoiceUrl'] ?? null,
            'invoice_url' => $paymentData['invoiceUrl'] ?? null,
            'bank_slip_url' => $paymentData['bankSlipUrl'] ?? null,
            'nota_fiscal_status' => 'pendente',
        ]);

        $novaVenda->update(['modo_cobranca_asaas' => 'PAYMENT']);

        Log::info("Renovação automática gerada", [
            'venda_original_id' => $vendaOriginal->id,
            'nova_venda_id' => $novaVenda->id,
            'ciclo' => $vendaOriginal->tipo_negociacao,
        ]);
        
        return $novaVenda;
    }
}
