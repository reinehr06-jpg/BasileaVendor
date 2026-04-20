<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPrompt extends Model
{
    protected $table = 'ai_prompts';

    protected $fillable = [
        'nome',
        'funcao',
        'cor',
        'prompt_personalizado',
        'ativo',
        'criado_por',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public static function funcoesDisponiveis(): array
    {
        return [
            'score_lead' => 'Score de qualidade',
            'sugestao_resposta' => 'Respostas rápidas',
            'proxima_acao' => 'Próxima ação',
            'resumo_conversa' => 'Resumir conversas',
            'motivo_perda' => 'Classificar perda',
            'observacao_contato' => 'Observação CRM',
            'primeira_mensagem' => 'Primeira mensagem',
            'analise_vendedor' => 'Análise mensal',
            'analise_campanha' => 'Análise campanha',
        ];
    }

    public static function coresPredefinidas(): array
    {
        return [
            '#4C1D95' => 'Roxo (padrão)',
            '#059669' => 'Verde',
            '#DC2626' => 'Vermelho',
            '#D97706' => 'Amarelo',
            '#2563EB' => 'Azul',
            '#DB2777' => 'Pink',
        ];
    }
}