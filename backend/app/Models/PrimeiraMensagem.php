<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrimeiraMensagem extends Model
{
    protected $table = 'primeira_mensagens';

    protected $fillable = [
        'user_id', 'perfil', 'titulo', 'mensagem', 'ativa', 'status',
        'aprovada_por', 'rejeitada_por', 'motivo_rejeicao',
    ];

    protected $casts = ['ativa' => 'boolean'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovada_por');
    }

    public function rejeitador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejeitada_por');
    }

    public function ativar(): void
    {
        static::where('user_id', $this->user_id)->update(['ativa' => false]);
        $this->update(['ativa' => true, 'status' => 'aprovada']);
    }

    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente_aprovacao');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativa', true);
    }

    public function scopeRascunhos($query)
    {
        return $query->where('status', 'rascunho');
    }
}