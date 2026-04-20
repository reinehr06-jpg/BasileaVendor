<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\IAProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements IAProviderInterface
{
    private string $endpoint;
    private string $model;
    private float $temperature = 0.7;
    private int $maxTokens = 500;

    public function __construct()
    {
        $endpoint = config('services.ia_local.endpoint', 'http://localhost:11434/api/generate');
        
        // Detectar se é formato OpenAI-compatible (ngrok) ou antigo
        if (str_contains($endpoint, '/v1/')) {
            // OpenAI-compatible via ngrok
            $this->endpoint = $endpoint . '/chat/completions';
        } else {
            // Formato antigo /api/generate
            $this->endpoint = $endpoint;
        }
        
        $this->model = config('services.ia_local.model', 'llama3.2');
    }

    /**
     * Método para AIService (novo formato OpenAI-compatible)
     */
    public function generate(string $prompt, int $timeout = 15): string
    {
        // Verificar formato do endpoint
        if (str_contains($this->endpoint, '/v1/chat/completions')) {
            return $this->generateOpenAI($prompt, $timeout);
        }
        
        // Formato antigo
        return $this->generateLegacy($prompt);
    }

    private function generateOpenAI(string $prompt, int $timeout): string
    {
        try {
            $response = Http::timeout($timeout)->post($this->endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'stream' => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? '';
            }

            Log::warning('OllamaProvider: Falhou', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return '';

        } catch (\Exception $e) {
            Log::error('OllamaProvider: Erro', ['mensagem' => $e->getMessage()]);
            return '';
        }
    }

    private function generateLegacy(string $prompt): string
    {
        try {
            $response = Http::timeout(90)->post($this->endpoint, [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

            if ($response->successful()) {
                return $response->json('response') ?? '';
            }

            return '';

        } catch (\Exception $e) {
            Log::error('OllamaProvider: Erro legacy', ['mensagem' => $e->getMessage()]);
            return '';
        }
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
