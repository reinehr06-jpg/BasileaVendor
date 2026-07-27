<?php

namespace App\Services\Asaas;

use App\Models\Vendedor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SplitService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function validateWallet(string $walletId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/wallets/{$walletId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'valid' => true,
                    'wallet' => $data,
                    'message' => 'Wallet validado com sucesso.'
                ];
            }

            return [
                'valid' => false,
                'wallet' => null,
                'message' => 'Wallet não encontrado ou inválido.'
            ];
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao validar wallet', ['walletId' => $walletId, 'error' => $e->getMessage()]);
            return [
                'valid' => false,
                'wallet' => null,
                'message' => 'Erro ao validar wallet: ' . $e->getMessage()
            ];
        }
    }

    public function buildSplitArray(Vendedor $vendedor, float $valorVenda, string $tipoVenda = 'inicial'): array
    {
        if (!$vendedor->isAptoSplit()) {
            return [];
        }

        $split = [];
        
        if ($vendedor->tipo_split === 'percentual') {
            $percentual = $tipoVenda === 'inicial' 
                ? $vendedor->valor_split_inicial 
                : $vendedor->valor_split_recorrencia;
            
            if ($percentual > 0) {
                $split[] = [
                    'walletId' => $vendedor->asaas_wallet_id,
                    'percentualValue' => $percentual,
                ];
            }
        } else {
            $valorFixo = $tipoVenda === 'inicial'
                ? $vendedor->valor_split_inicial
                : $vendedor->valor_split_recorrencia;
            
            if ($valorFixo > 0 && $valorFixo <= $valorVenda) {
                $split[] = [
                    'walletId' => $vendedor->asaas_wallet_id,
                    'fixedValue' => $valorFixo,
                ];
            }
        }

        return $split;
    }
}
