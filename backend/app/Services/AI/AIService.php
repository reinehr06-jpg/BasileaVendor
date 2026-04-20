<?php

namespace App\Services\AI;

use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIService
{
    private const RATE_LIMITS = [
        'sugestao_resposta'   => 100,
        'resumo_conversa'     => 50,
        'proxima_acao'        => 50,
        'observacao_contato'  => 30,
        'score_lead'          => 50,
        'motivo_perda'        => 30,
        'primeira_mensagem'   => 20,
        'analise_vendedor'    => 10,
        'analise_campanha'    => 10,
    ];

    private const TIMEBOX = [
        'sugestao_resposta'   => 15,
        'resumo_conversa'     => 15,
        'proxima_acao'        => 10,
        'observacao_contato'  => 10,
        'score_lead'          => 15,
        'motivo_perda'        => 15,
        'primeira_mensagem'   => 30,
        'analise_vendedor'    => 60,
        'analise_campanha'    => 60,
    ];

    private $provider;
    private $promptBuilder;
    private $parser;

    public function __construct()
    {
        $this->promptBuilder = new PromptBuilder();
        $this->parser = new AIResponseParser();
        $this->provider = $this->resolveProvider();
    }

    private function resolveProvider()
    {
        $provider = config('services.ia.provider', 'ollama');

        if ($provider === 'openai') {
            return new OpenAIProvider();
        }

        return new OllamaProvider();
    }

    public function executar(string $tarefa, array $contexto, ?int $userId = null): array
    {
        $startTime = microtime(true);

        // Verificar rate limit
        if (!$this->checkRateLimit($tarefa)) {
            return [
                'success' => false,
                'error' => 'Rate limit excedido para esta tarefa',
                'tarefa' => $tarefa,
            ];
        }

        try {
            // Construir prompt
            $prompt = $this->buildPrompt($tarefa, $contexto);

            // Executar via provider
            $timeout = self::TIMEBOX[$tarefa] ?? 15;
            $rawResponse = $this->provider->generate($prompt, $timeout);

            // Processar resposta
            $output = $this->parseOutput($tarefa, $rawResponse);

            // Log de sucesso
            $this->logExecution($tarefa, $contexto, $output, $startTime, $userId, true);

            return [
                'success' => true,
                'output' => $output,
                'tarefa' => $tarefa,
            ];

        } catch (\Exception $e) {
            Log::error('AIService: Erro execução', [
                'tarefa' => $tarefa,
                'erro' => $e->getMessage(),
            ]);

            // Log de erro
            $this->logExecution($tarefa, $contexto, null, $startTime, $userId, false, $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tarefa' => $tarefa,
            ];
        }
    }

    private function buildPrompt(string $tarefa, array $contexto): string
    {
        return match ($tarefa) {
            'primeira_mensagem' => $this->promptBuilder->primeiraMensagem($contexto),
            'sugestao_resposta' => $this->promptBuilder->sugestaoResposta($contexto),
            'resumo_conversa' => $this->promptBuilder->resumoConversa($contexto),
            'motivo_perda' => $this->promptBuilder->motivoPerda($contexto),
            'score_lead' => $this->promptBuilder->scoreLead($contexto),
            'analise_vendedor' => $this->promptBuilder->analiseVendedor($contexto),
            'analise_campanha' => $this->promptBuilder->analiseCampanha($contexto),
            'observacao_contato' => $this->promptBuilder->observacaoContato($contexto),
            'proxima_acao' => $this->promptBuilder->proximaAcao($contexto),
            default => $contexto['prompt'] ?? '',
        };
    }

    private function parseOutput(string $tarefa, string $raw): mixed
    {
        return match ($tarefa) {
            'score_lead' => $this->parser->scoreLead($raw),
            'motivo_perda' => $this->parser->motivoPerda($raw),
            'sugestao_resposta' => $this->parser->sugestaoResposta($raw),
            'analise_vendedor' => $this->parser->analiseVendedor($raw),
            'analise_campanha' => $this->parser->analiseCampanha($raw),
            'proxima_acao' => $this->parser->proximaAcao($raw),
            'observacao_contato' => $this->parser->observacaoContato($raw),
            'resumo_conversa' => $this->parser->resumoConversa($raw),
            default => $raw,
        };
    }

    private function checkRateLimit(string $tarefa): bool
    {
        $limit = self::RATE_LIMITS[$tarefa] ?? 100;
        $cacheKey = "ia_rate_limit_{$tarefa}";

        $current = Cache::get($cacheKey, 0);

        if ($current >= $limit) {
            Log::warning('AIService: Rate limit excedido', ['tarefa' => $tarefa, 'limite' => $limit]);
            return false;
        }

        Cache::put($cacheKey, $current + 1, now()->addHour());

        return true;
    }

    private function logExecution(
        string $tarefa,
        array $input,
        mixed $output,
        float $startTime,
        ?int $userId,
        bool $sucesso,
        ?string $erro = null
    ): void {
        $duracao = (int) ((microtime(true) - $startTime) * 1000);

        try {
            DB::table('ai_logs')->insert([
                'user_id' => $userId,
                'tarefa' => $tarefa,
                'input' => json_encode($input),
                'output' => is_string($output) ? $output : json_encode($output),
                'duracao_ms' => $duracao,
                'sucesso' => $sucesso,
                'erro' => $erro,
                'executado_em' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('AIService: Falha ao logar', ['erro' => $e->getMessage()]);
        }
    }

    public function getRateLimit(string $tarefa): int
    {
        return self::RATE_LIMITS[$tarefa] ?? 100;
    }

    public function getTimeout(string $tarefa): int
    {
        return self::TIMEBOX[$tarefa] ?? 15;
    }

    public static function tarefas(): array
    {
        return array_keys(self::RATE_LIMITS);
    }
}