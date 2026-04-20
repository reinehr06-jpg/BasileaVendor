<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\IAProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements IAProviderInterface
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-3.5-turbo');
    }

    public function gerarSugestoes(string $contexto, int $quantidade = 5): array
    {
        if (!$this->apiKey) {
            Log::warning('OpenAIProvider: API key não configurada');
            return [];
        }

        try {
            $response = Http::timeout(90)
                ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Você é um especialista em vendas consultivas que cria primeiras mensagens personalizadas para leads.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($contexto, $quantidade)
                        ]
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return $this->parseResposta($content, $quantidade);
            }

            Log::warning('OpenAIProvider: Falhou', ['status' => $response->status()]);
            return [];

        } catch (\Exception $e) {
            Log::error('OpenAIProvider: Erro', ['mensagem' => $e->getMessage()]);
            return [];
        }
    }

    private function buildPrompt(string $contexto, int $qtd): string
    {
        return <<<PROMPT
Crie {$qtd} primeiras mensagens para um lead novo baseado no contexto a seguir.

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
