<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatAtividade extends Model
{
    protected $table = 'chat_atividades';
    
    protected $fillable = [
        'conversa_id', 'vendedor_id', 'acao', 'detalhes'
    ];
    
    const ACOES = [
        'criacao_conversa' => 'Criação de conversa',
        'atribuicao_vendedor' => 'Atribuição ao vendedor',
        'primeira_resposta' => 'Primeira resposta do vendedor',
        'mensagem_recebida' => 'Mensagem recebida do cliente',
        'mensagem_enviada' => 'Mensagem enviada ao cliente',
        'transferencia' => 'Transferência de vendedor',
        'inatividade' => 'Repasse por inatividade',
        'status_alterado' => 'Alteração de status',
        'conversa_fixada' => 'Conversa fixada',
        'conversa_desfixada' => 'Conversa desfixada',
        'contato_criado' => 'Contato criado',
    ];
    
    public function conversa(): BelongsTo
    {
        return $this->belongsTo(ChatConversa::class, 'conversa_id');
    }
    
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function scopeByConversa($query, $conversaId)
    {
        return $query->where('conversa_id', $conversaId);
    }

    public function scopeByAcao($query, $acao)
    {
        return $query->where('acao', $acao);
    }

    public function getAcaoLabelAttribute()
    {
        return self::ACOES[$this->acao] ?? $this->acao;
    }
}