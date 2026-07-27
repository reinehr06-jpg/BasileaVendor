<?php

namespace App\Services;

use App\Mail\ClienteBoasVindas;
use App\Mail\VendedorPagamentoConfirmado;
use App\Models\Comissao;
use App\Models\LogEvento;
use App\Models\Pagamento;
use App\Models\Setting;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PagamentoService
{
    public static function getCicloDeComissao(\Carbon\Carbon $dataReferencia): array
    {
        $inicio = $dataReferencia->copy()->startOfMonth()->startOfDay();
        $fim = $dataReferencia->copy()->endOfMonth()->endOfDay();

        return ['inicio' => $inicio, 'fim' => $fim];
    }

    public function sync(Pagamento $pagamento): bool
    {
        if (! $pagamento->asaas_payment_id) {
            return false;
        }

        $venda = $pagamento->venda;
        if (!$venda) {
            Log::error('PagamentoService: sync ignorado - pagamento sem venda', [
                'pagamento_id' => $pagamento->id,
                'asaas_payment_id' => $pagamento->asaas_payment_id,
            ]);
            return false;
        }
        if (!$venda->cliente) {
            Log::error('PagamentoService: sync ignorado - venda sem cliente', [
                'pagamento_id' => $pagamento->id,
                'venda_id' => $venda->id,
            ]);
            return false;
        }

        try {
            $asaas = new AsaasService;
            $asaasId = $pagamento->asaas_payment_id;
            $paymentData = null;

            if (str_starts_with($asaasId, 'sub_')) {
                try {
                    $paymentsResponse = $asaas->requestAsaas('GET', "/subscriptions/{$asaasId}/payments");
                    if (! empty($paymentsResponse['data']) && count($paymentsResponse['data']) > 0) {
                        $paymentData = $paymentsResponse['data'][0];
                        if (! empty($paymentData['id'])) {
                            $pagamento->asaas_payment_id = $paymentData['id'];
                            $pagamento->save();
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('PagamentoService: erro ao buscar pagamentos da subscription', ['error' => $e->getMessage()]);
                    return false;
                }
            } else {
                $paymentData = $asaas->getPayment($asaasId);
            }

            if (! $paymentData) {
                return false;
            }

            $statusAsaas = strtoupper($paymentData['status'] ?? '');
            $isPago = in_array($statusAsaas, ['RECEIVED', 'CONFIRMED']);
            $alreadyPago = in_array(strtoupper($pagamento->status), ['RECEIVED', 'CONFIRMED']);

            if (! empty($paymentData['bankSlipUrl'])) {
                $pagamento->bank_slip_url = $paymentData['bankSlipUrl'];
            }
            if (! empty($paymentData['identificationField'])) {
                $pagamento->linha_digitavel = $paymentData['identificationField'];
            }
            if (! empty($paymentData['transactionReceiptUrl'])) {
                $pagamento->link_pagamento = $paymentData['transactionReceiptUrl'];
            }

            if (! empty($paymentData['billingType'])) {
                $pagamento->forma_pagamento_real = self::mapBillingType($paymentData['billingType']);
            }

            if ($isPago && ! $alreadyPago) {
                $this->confirmarPagamento($pagamento, $paymentData);
                return true;
            }

            if ($statusAsaas !== strtoupper($pagamento->status)) {
                $pagamento->status = $statusAsaas;
                $pagamento->save();

                $venda = $pagamento->venda;
                if ($venda) {
                    $isCancelamento = in_array($statusAsaas, ['CANCELED', 'DELETED', 'REFUNDED', 'REFUND_REQUESTED']);
                    $isParcelado = $venda->isPagamentoParcelado();
                    $jaTemParcelaPaga = $venda->getParcelaAtual() > 0;

                    if ($isParcelado && $jaTemParcelaPaga && ! $isCancelamento) {
                        Log::info("PagamentoService: Venda parcelada #{$venda->id} mantida como PAGO (parcela {$venda->getParcelaAtual()}/{$venda->parcelas})");
                    } else {
                        $venda->status = AsaasService::mapStatus($statusAsaas);

                        if ($isCancelamento) {
                            $venda->comissao_gerada = 0;
                            $venda->valor_comissao = 0;
                        }

                        $venda->save();
                    }

                    foreach ($venda->cobrancas as $cobranca) {
                        $cobranca->status = $statusAsaas;
                        $cobranca->save();
                    }

                    $cliente = $venda->cliente;
                    if ($cliente) {
                        ClienteStatusService::atualizarCliente($cliente);
                    }
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('PagamentoService: Erro ao sincronizar', [
                'pagamento_id' => $pagamento->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private static function mapBillingType(string $billingType): string
    {
        $billingType = strtoupper($billingType);
        $formaMap = [
            'PIX' => 'pix',
            'BOLETO' => 'boleto',
            'CREDIT_CARD' => 'cartao',
            'CREDIT_CARD_RECURRING' => 'cartao',
        ];

        return $formaMap[$billingType] ?? strtolower($billingType);
    }

    /**
     * Determina se este pagamento deve gerar comissão "cheia" de cartão de crédito
     * (paga tudo de uma vez, ao invés de fatiar por mês como um boleto/pix recorrente).
     */
    private static function isComissaoCartaoIntegral(Venda $venda, Pagamento $pagamento): bool
    {
        $forma = strtoupper($pagamento->forma_pagamento_real ?? '');
        // Regra de negócio: venda recorrente (mensal/anual) paga NO CARTÃO
        // não é tratada como parcelamento no Asaas, mas o vendedor deve
        // receber o valor total da recorrência de uma vez só, na primeira cobrança.
        return in_array($forma, ['cartao']) && ! $venda->isPagamentoParcelado();
    }

    public function confirmarPagamento(Pagamento $pagamento, array $paymentData = []): void
    {
        $venda = $pagamento->venda;
        if (!$venda) {
            Log::error('PagamentoService: tentativa de confirmar pagamento sem venda', [
                'pagamento_id' => $pagamento->id,
                'asaas_payment_id' => $pagamento->asaas_payment_id,
            ]);
            return;
        }
        if (!$venda->cliente) {
            Log::error('PagamentoService: venda sem cliente. NAO confirmando pagamento.', [
                'pagamento_id' => $pagamento->id,
                'venda_id' => $venda->id,
            ]);
            return;
        }

        $statusAsaas = strtoupper($paymentData['status'] ?? '');
        if (!in_array($statusAsaas, ['RECEIVED', 'CONFIRMED'])) {
            Log::error('PagamentoService: status Asaas invalido para confirmacao', [
                'pagamento_id' => $pagamento->id,
                'status_asaas' => $statusAsaas,
            ]);
            return;
        }

        $pagamento->status = 'RECEIVED';
        $pagamento->data_pagamento = now();

        if (! empty($paymentData['billingType'])) {
            $pagamento->forma_pagamento_real = self::mapBillingType($paymentData['billingType']);
        }

        $pagamento->save();

        $statusAnterior = $venda->status;

        $pagamentosIniciais = $venda->pagamentos()
            ->where(function ($q) {
                $q->whereNull('parcela_numero')->orWhere('parcela_numero', 1);
            })
            ->get();
        $todosPagos = $pagamentosIniciais->every(fn ($p) => in_array($p->status, ['RECEIVED', 'CONFIRMED']));

        $venda->status = $todosPagos ? 'PAGO' : 'PAGAMENTO_PARCIAL';

        $this->gerarComissoes($venda, $pagamento);

        $venda->save();

        foreach ($venda->cobrancas as $cobranca) {
            $cobranca->status = 'RECEIVED';
            $cobranca->save();
        }

        $cliente = $venda->cliente;
        if ($cliente) {
            $cliente->status = 'ativo';
            $cliente->data_ultimo_pagamento = now();
            $cliente->save();

            try {
                $church = new ChurchProvisioningService;
                $church->criarConta($cliente, $venda);
            } catch (\Exception $e) {
                Log::error('[Church] Falha ao criar conta no PagamentoService', [
                    'cliente_id' => $cliente->id,
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (strtoupper($statusAnterior) !== 'PAGO') {
            $this->dispararAutomacoes($venda, $pagamento);
        }

        if ($venda->tipo_negociacao === 'anual' || $venda->tipo_negociacao === 'mensal') {
            try {
                $lifecycle = new SubscriptionLifecycleService();
                $lifecycle->ativarAssinatura($venda);
            } catch (\Exception $e) {
                Log::error('[Lifecycle] Falha ao ativar assinatura', [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("PagamentoService: Venda #{$venda->id} confirmada com sucesso.");

        LogEvento::create([
            'usuario_id' => 1,
            'entidade' => 'Pagamento',
            'entidade_id' => $pagamento->id,
            'acao' => 'Sincronização: Confirmado',
            'descricao' => 'Pagamento detectado como pago no Asaas durante sincronização.',
        ]);
    }

    /**
     * Centraliza toda a lógica de geração de comissão (vendedor + gestor),
     * cobrindo: primeiro pagamento, recorrência mensal, parcelamento e
     * comissão integral de cartão de crédito.
     */
    private function gerarComissoes(Venda $venda, Pagamento $pagamento): void
    {
        // Consolidado: toda a regra de comissão (inicial, recorrência, gestor,
        // antecipação de parcelado e trava do fim do mês) vive agora no motor
        // único, com idempotência por pagamento_id. Aqui apenas delegamos.
        \App\Services\Commission\CommissionService::gerarParaPagamento($pagamento);
    }


    private function dispararAutomacoes(Venda $venda, Pagamento $pagamento): void
    {
        try {
            $vendedorUser = $venda->vendedor?->user;
            if ($vendedorUser && $vendedorUser->email) {
                $fromEmail = Setting::get('email_vendedor_from', config('mail.from.address'));
                $fromName = config('mail.from.name', 'Basiléia Global');

                Mail::to($vendedorUser->email)
                    ->send((new VendedorPagamentoConfirmado($venda))->from($fromEmail, $fromName));
                Log::info('[Email] VendedorPagamentoConfirmado enviado', ['venda_id' => $venda->id, 'to' => $vendedorUser->email]);
            }
        } catch (\Exception $e) {
            Log::error('[Email] Falha ao enviar VendedorPagamentoConfirmado', [
                'venda_id' => $venda->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $cliente = $venda->cliente;
            if ($cliente && $cliente->email) {
                $fromEmail = Setting::get('email_cliente_from', config('mail.from.address'));
                $fromName = config('mail.from.name', 'Basiléia Global');

                Mail::to($cliente->email)
                    ->send((new ClienteBoasVindas($cliente))->from($fromEmail, $fromName));
                Log::info('[Email] ClienteBoasVindas enviado', ['cliente_id' => $cliente->id, 'to' => $cliente->email]);
            }
        } catch (\Exception $e) {
            Log::error('[Email] Falha ao enviar ClienteBoasVindas', [
                'cliente_id' => $venda->cliente_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}