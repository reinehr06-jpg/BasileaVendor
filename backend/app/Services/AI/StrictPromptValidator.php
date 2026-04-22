<?php

namespace App\Services\AI;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use App\Services\Integration\IntegrationTestService;

class StrictPromptValidator
{
    /**
     * Validar se prompt está configurado e é válido
     */
    public function validatePrompt(?string $prompt, string $tarefa): array
    {
        if (!$prompt || trim($prompt) === '') {
            return [
                'valid' => false,
                'message' => "Prompt não configurado para a tarefa '{$tarefa}'. Configure em Configurações > IA.",
                'code' => 'PROMPT_MISSING'
            ];
        }

        // Verificar comprimento mínimo
        if (strlen(trim($prompt)) < 10) {
            return [
                'valid' => false,
                'message' => "Prompt muito curto para a tarefa '{$tarefa}'. Mínimo: 10 caracteres.",
                'code' => 'PROMPT_TOO_SHORT'
            ];
        }

        // Verificar se contém palavras-chave proibidas (ex: "ignore", "esqueca", "não siga")
        $forbidden = ['ignore', 'esqueca', 'nao siga', 'não siga', 'disregard', 'forget'];
        foreach ($forbidden as $word) {
            if (stripos($prompt, $word) !== false) {
                return [
                    'valid' => false,
                    'message' => "Promoto contém palavra restrita: '{$word}'. Prompts devem ser diretos e seguidos rigidamente.",
                    'code' => 'FORBIDDEN_WORD'
                ];
            }
        }

        return ['valid' => true, 'message' => 'Prompt válido', 'code' => 'OK'];
    }

    /**
     * Validar resposta da IA contra regras do prompt
     */
    public function validateResponse(string $response, string $prompt, string $tarefa): array
    {
        // 1. Verificar se resposta está vazia
        if (empty(trim($response))) {
            return [
                'valid' => false,
                'message' => 'IA retornou resposta vazia',
                'code' => 'EMPTY_RESPONSE'
            ];
        }

        // 2. Verificar comprimento máximo (evitar respostas excessivamente longas)
        $maxLength = Setting::get('ia_max_response_length', 2000);
        if (strlen($response) > $maxLength) {
            return [
                'valid' => false,
                'message' => "Resposta excede limite de {$maxLength} caracteres",
                'code' => 'RESPONSE_TOO_LONG'
            ];
        }

        // 3. Verificar se a IA está tentando dar conselhos fora do escopo
        $outOfScopePatterns = [
            '/não é da minha competência/i',
            '/não posso ajudar com/i',
            '/como/i.*IA/i',
            '/minhas instruções são/i',
        ];

        foreach ($outOfScopePatterns as $pattern) {
            if (preg_match($pattern, $response)) {
                return [
                    'valid' => false,
                    'message' => 'IA tentou sair do escopo definido',
                    'code' => 'OUT_OF_SCOPE'
                ];
            }
        }

        // 4. Verificar se a IA está desobedecendo ao prompt
        // ( Implementação simplificada — pode ser expandida )
        return ['valid' => true, 'message' => 'Resposta válida', 'code' => 'OK'];
    }

    /**
     * Verificar se IA está habilitada
     */
    public function isIAAvailable(): bool
    {
        $iaAtivo = Setting::get('ia_ativo', false);
        $provider = Setting::get('ia_provider', 'ollama');
        
        if (!$iaAtivo) {
            return false;
        }

        // Se for OpenAI, verificar se tem API key
        if ($provider === 'openai') {
            return !empty(Setting::get('openai_api_key'));
        }

        // Se for Ollama, verificar endpoint
        if ($provider === 'ollama') {
            return !empty(Setting::get('ia_local_endpoint'));
        }

        return false;
    }

    /**
     * Obter configuração da IA atual
     */
    public function getIAConfig(): array
    {
        return [
            'provider' => Setting::get('ia_provider', 'ollama'),
            'active' => $this->isIAAvailable(),
            'model' => Setting::get('ia_local_model', 'llama3.2'),
            'endpoint' => Setting::get('ia_local_endpoint', 'http://localhost:11434/api/generate'),
            'rate_limit' => Setting::get('ia_rate_limit', 100),
        ];
    }

    /**
     * Verificação de segurança — bloqueia execução se prompt estiver ausente
     */
    public function assertPromptExists(?string $prompt, string $tarefa): void
    {
        $validation = $this->validatePrompt($prompt, $tarefa);
        
        if (!$validation['valid']) {
            Log::warning('IA prompt validation failed', [
                'tarefa' => $tarefa,
                'code' => $validation['code']
            ]);
            
            throw new \Exception($validation['message']);
        }
    }
}
