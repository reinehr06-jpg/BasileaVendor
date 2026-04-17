<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Plano;
use App\Models\Venda;
use App\Models\Vendedor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    protected AsaasService $asaasService;
    protected ExchangeRateService $exchangeRateService;

    public function __construct()
    {
        $this->asaasService = new AsaasService;
        $this->exchangeRateService = new ExchangeRateService;
    }

    public function criarVendaECheckout(array $clienteData, Plano $plano, ?int $vendedorId, string $formaPagamento): Venda
    {
        $documento = preg_replace('/\D/', '', $clienteData['documento']);

        $cliente = Cliente::where('documento', $documento)->first();

        if (! $cliente) {
            $cliente = Cliente::create([
                'nome' => $clienteData['nome'],
                'email' => $clienteData['email'],
                'documento' => $documento,
                'contato' => $clienteData['telefone'] ?? null,
                'nome_igreja' => $clienteData['nome_igreja'] ?? null,
                'quantidade_membros' => $clienteData['quantidade_membros'] ?? 1,
                'status' => 'pendente',
            ]);
        }

        $valorPlano = $plano->valor_mensal ?? 97;

        $venda = Venda::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedorId,
            'plano_id' => $plano->id,
            'plano' => $plano->nome,
            'valor' => $valorPlano,
            'valor_original' => $valorPlano,
            'valor_final' => $valorPlano,
            'forma_pagamento' => $formaPagamento,
            'status' => 'AGUARDANDO_PAGAMENTO',
            'data_venda' => now()->format('Y-m-d'),
            'checkout_hash' => Str::random(32),
            'checkout_status' => 'PENDENTE',
            'origem' => 'checkout_proprio',
        ]);

        return $venda;
    }

    public function criarPagamento(Venda $venda, array $dados, string $currency = 'BRL'): array
    {
        $cliente = $venda->cliente;

        // Atualizar plano e membros se fornecidos (Checkout Dinâmico)
        if (isset($dados['plano_id'])) {
            $novoPlano = Plano::find($dados['plano_id']);
            if ($novoPlano) {
                $venda->update([
                    'plano_id' => $novoPlano->id,
                    'plano' => $novoPlano->nome,
                    'valor' => $novoPlano->valor_mensal,
                    'valor_original' => $novoPlano->valor_mensal * 1.5, // Simulação de preço original
                    'valor_final' => $novoPlano->valor_mensal,
                ]);
            }
        }

        if (isset($dados['quantidade_membros'])) {
            $cliente->update(['quantidade_membros' => $dados['quantidade_membros']]);
        }

        $asaasCustomer = $this->asaasService->findCustomerByCpfCnpj($cliente->documento);

        if (! $asaasCustomer) {
            $asaasCustomer = $this->asaasService->createCustomer(
                $cliente->nome,
                $cliente->documento,
                $cliente->contato,
                $cliente->email
            );
        }

        $billingType = match ($dados['payment_method'] ?? $dados['forma_pagamento'] ?? 'cartao') {
            'pix' => 'PIX',
            'boleto' => 'BOLETO',
            'cartao' => 'CREDIT_CARD',
            default => 'CREDIT_CARD',
        };

        // Calcular valor na moeda selecionada
        $valorOriginal = $venda->valor;
        if ($currency !== 'BRL') {
            $valorConvertido = $this->exchangeRateService->convert($valorOriginal, 'BRL', $currency);
        } else {
            $valorConvertido = $valorOriginal;
        }

        $split = [];
        if ($venda->vendedor_id && $currency === 'BRL') {
            $vendedor = Vendedor::find($venda->vendedor_id);
            if ($vendedor && $vendedor->isAptoSplit()) {
                $split = $this->asaasService->buildSplitArray($vendedor, $venda->valor, 'inicial');
            }
        }

        // --- NOVO: Verificar se já existe um pagamento pendente para esta venda (exceto cartão que é imediato) ---
        if ($billingType !== 'CREDIT_CARD') {
            $pagamentoExistente = Pagamento::where('venda_id', $venda->id)
                ->where('forma_pagamento', $dados['payment_method'] ?? $dados['forma_pagamento'] ?? 'cartao')
                ->whereIn('status', ['pendente', 'PENDING', 'AWAITED'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($pagamentoExistente && $pagamentoExistente->asaas_payment_id) {
                try {
                    $paymentData = $this->asaasService->getPayment($pagamentoExistente->asaas_payment_id);
                    
                    if (!in_array($paymentData['status'], ['DELETED', 'REFUNDED', 'CANCELLED'])) {
                        return [
                            'success' => true,
                            'payment_id' => $paymentData['id'],
                            'billing_type' => $paymentData['billingType'],
                            'redirect_url' => route('checkout.sucesso', $venda->checkout_hash),
                            'pix_qrcode' => $paymentData['pixQrCode'] ?? null,
                            'pix_copia_cola' => $paymentData['pixCopiaECola'] ?? null,
                            'barcode' => $paymentData['identificationField'] ?? null,
                            'boleto_url' => $paymentData['bankSlipUrl'] ?? null,
                            'currency' => $currency,
                            'converted_value' => $paymentData['value'],
                            'from_cache' => true
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('[Checkout] Falha ao recuperar pagamento existente, criando novo...', ['error' => $e->getMessage()]);
                }
            }
        }

        $isAnual = $venda && in_array(strtolower($venda->tipo_negociacao ?? ''), ['anual', 'annual']);
        $isBoleto = in_array(strtoupper($billingType), ['BOLETO', 'BOLETO_BANCARIO']);
        $diasVencimento = ($isAnual && !$isBoleto) ? 15 : 5;

        $asaasData = [
            'customer' => $asaasCustomer['id'],
            'billingType' => $billingType,
            'value' => number_format($valorConvertido, 2, '.', ''),
            'dueDate' => now()->addDays($diasVencimento)->format('Y-m-d'),
            'description' => "Assinatura {$venda->plano} - {$cliente->nome}",
            'split' => $split,
            'externalReference' => "venda_{$venda->id}",
        ];

        if ($currency !== 'BRL') {
            $asaasData['currency'] = $currency;
        }

        if ($billingType === 'CREDIT_CARD' && ! empty($dados['card_number'])) {
            $asaasData['creditCard'] = [
                'cardNumber' => preg_replace('/\D/', '', $dados['card_number']),
                'cardName' => $dados['card_name'],
                'expirationMonth' => explode('/', $dados['card_expiry'])[0],
                'expirationYear' => '20' . explode('/', $dados['card_expiry'])[1],
                'cvv' => $dados['card_cvv'],
            ];
            $asaasData['creditCardHolderInfo'] = [
                'name' => $dados['card_name'],
                'cpfCnpj' => preg_replace('/\D/', '', $cliente->documento),
                'email' => $cliente->email,
                'phone' => preg_replace('/\D/', '', $cliente->contato ?? $cliente->whatsapp ?? ''),
                'postalCode' => '00000000', // Default if not found
                'addressNumber' => '0',
            ];
            $asaasData['dueDate'] = now()->format('Y-m-d');
        }

        try {
            $paymentData = $this->asaasService->requestAsaas('POST', '/payments', $asaasData);
            
            // Salvar token do cartão se retornado
            if (isset($paymentData['creditCardToken'])) {
                $cliente->update([
                    'credit_card_token' => $paymentData['creditCardToken'],
                    'card_brand' => $paymentData['creditCardBrand'] ?? null,
                    'card_last_digits' => substr(preg_replace('/\D/', '', $dados['card_number'] ?? ''), -4)
                ]);
                Log::info('[Checkout] Cartão tokenizado para cliente: ' . $cliente->id);
            }

            $pagamento = Pagamento::create([
                'venda_id' => $venda->id,
                'cliente_id' => $venda->cliente_id,
                'vendedor_id' => $venda->vendedor_id,
                'asaas_payment_id' => $paymentData['id'],
                'valor' => $valorConvertido,
                'billing_type' => $billingType,
                'forma_pagamento' => $dados['payment_method'] ?? $dados['forma_pagamento'] ?? 'cartao',
                'status' => $this->asaasService->mapStatus($paymentData['status']),
                'data_vencimento' => $asaasData['dueDate'],
                'link_pagamento' => $paymentData['invoiceUrl'] ?? null,
            ]);

            return [
                'success' => true,
                'payment_id' => $paymentData['id'],
                'billing_type' => $billingType,
                'redirect_url' => route('checkout.sucesso', $venda->checkout_hash),
                'pix_qrcode' => $paymentData['pixQrCode'] ?? null,
                'pix_copia_cola' => $paymentData['pixCopiaECola'] ?? null,
                'barcode' => null,
                'boleto_url' => $paymentData['bankSlipUrl'] ?? null,
                'currency' => $currency,
                'converted_value' => $valorConvertido,
            ];

        } catch (\Exception $e) {
            Log::error('[Checkout] Exceção ao criar pagamento', [
                'venda_id' => $venda->id,
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function buscarPix(string $paymentId): array
    {
        try {
            $data = $this->asaasService->requestAsaas('GET', "/payments/{$paymentId}");

            return [
                'status' => $data['status'],
                'pix_qrcode' => $data['pixQrCode'] ?? null,
                'pix_copia_cola' => $data['pixCopiaECola'] ?? null,
                'valor' => $data['value'],
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function webhookPagamento(array $data): void
    {
        $paymentId = $data['payment']['id'] ?? null;

        if (! $paymentId) {
            return;
        }

        $pagamento = Pagamento::where('asaas_payment_id', $paymentId)->first();

        if (! $pagamento) {
            Log::warning('[Checkout] Pagamento não encontrado para webhook', [
                'payment_id' => $paymentId,
            ]);

            return;
        }

        $venda = $pagamento->venda;

        $status = $this->asaasService->mapStatus($data['payment']['status'] ?? '');

        $pagamento->update([
            'status' => $status,
            'data_pagamento' => $data['payment']['paymentDate'] ?? null,
        ]);

        if ($status === 'RECEIVED' || $status === 'CONFIRMED') {
            $venda->update([
                'status' => 'PAGO',
                'checkout_status' => 'PAGO',
            ]);

            $venda->cliente->update([
                'status' => 'ativo',
                'data_ultimo_pagamento' => now(),
            ]);
        }

        Log::info('[Checkout] Webhook processado', [
            'payment_id' => $paymentId,
            'status' => $status,
            'venda_id' => $venda->id,
        ]);
    }
}
