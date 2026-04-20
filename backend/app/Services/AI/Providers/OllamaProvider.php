<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\IAProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements IAProviderInterface
{
    private string $endpoint;
    private string $model;

    public function __construct()
    {
        $this->endpoint = config('services.ia_local.endpoint', 'http://localhost:11434/api/generate');
        $this->model = config('services.ia_local.model', 'llama3.2');
    }

    public function gerarSugestoes(string $contexto, int $quantidade = 5): array
    {
        try {
            $response = Http::timeout(90)->post($this->endpoint, [
                'model' => $this->model,
                'prompt' => $this->buildPrompt($contexto, $quantidade),
                'stream' => false,
            ]);

            if ($response->successful()) {
                return $this->parseResposta($response->json('response'), $quantidade);
            }

            Log::warning('OllamaProvider: Falhou', ['status' => $response->status()]);
            return [];

        } catch (\Exception $e) {
            Log::error('OllamaProvider: Erro', ['mensagem' => $e->getMessage()]);
            return [];
        }
    }

    private function buildPrompt(string $contexto, int $qtd): string
    {
        return <<<PROMPT
Você é um especialista em vendas consultivas. Crie {$qtd} primeiras mensagens para um lead novo.

Contexto: {$contexto}

Regras:
- Máximo 160 caracteres por mensagem
- Tom profissional mas amigável
- Não seja genérico, use o contexto fornecido
- Gere apenas as mensagens numeradas, sem explicações

Formato:
1. [mensagem]
2. [mensagem]
...
PROMPT;
    }

    private function parseResposta(string $texto, int $qtd): array
    {
        $linhas = explode("\n", trim($texto));
        $resultado = [];

        foreach ($linhas as $linha) {
            $limpa = trim(preg_replace('/^\d+[\.\)]\s*/', '', $linha));
            if (mb_strlen($limpa) >= 20 && mb_strlen($limpa) <= 200) {
                $resultado[] = $limpa;
            }
            if (count($resultado) >= $qtd) break;
        }

        return $resultado;
    }
}
