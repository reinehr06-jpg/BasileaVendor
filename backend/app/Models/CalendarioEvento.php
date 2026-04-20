<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarioEvento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'tipo', 'titulo', 'descricao',
        'data_hora_inicio', 'data_hora_fim',
        'cliente_id', 'contato_id', 'vendedor_id',
        'recorrencia', 'google_event_id', 'status',
        'criado_por', 'notificado_em',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
        'notificado_em' => 'datetime',
        'recorrencia' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function contato(): BelongsTo
    {
        return $this->belongsTo(Contato::class, 'contato_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function scopeAgendados($query)
    {
        return $query->where('status', 'agendado');
    }

    public function scopeConcluidos($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopeProximos($query, int $dias = 7)
    {
        return $query->whereBetween('data_hora_inicio', [now(), now()->addDays($dias)])
            ->where('status', 'agendado')
            ->orderBy('data_hora_inicio');
    }

    public function scopeVencidos($query)
    {
        return $query->where('status', 'agendado')
            ->where('data_hora_inicio', '<', now());
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeDoDia($query)
    {
        return $query->whereDate('data_hora_inicio', today());
    }

    public function getCorPorTipo(): string
    {
        return match($this->tipo) {
            'follow_up' => '#4C1D95',
            'reuniao' => '#16B1FF',
            'lembrete' => '#FFB400',
            'vencimento' => '#FF4C51',
            default => '#8A8D93',
        };
    }
}