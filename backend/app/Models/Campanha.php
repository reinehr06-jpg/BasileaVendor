<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campanha extends Model
{
    protected $fillable = [
        'nome', 'descricao', 'canal', 'status',
        'data_inicio', 'data_fim',
        'utm_source', 'utm_medium', 'utm_campaign',
        'utm_content', 'utm_term', 'ref_param',
        'custo_total', 'moeda', 'criado_por',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
        'custo_total' => 'decimal:2',
    ];

    // Relações
    public function contatos(): HasMany
    {
        return $this->hasMany(Contato::class, 'campanha_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    // Métricas computadas — use estas queries no Controller
    public function totalLeads(): int
    {
        return $this->contatos()->count();
    }

    public function totalConvertidos(): int
    {
        return $this->contatos()->where('status', 'convertido')->count();
    }

    public function totalPerdidos(): int
    {
        return $this->contatos()->whereIn('status', ['perdido', 'lead_ruim'])->count();
    }

    public function taxaConversao(): float
    {
        $total = $this->totalLeads();
        if ($total === 0) return 0.0;
        return round(($this->totalConvertidos() / $total) * 100, 2);
    }

    public function custoporLead(): ?float
    {
        if (!$this->custo_total || $this->totalLeads() === 0) return null;
        return round($this->custo_total / $this->totalLeads(), 2);
    }

    public function tempoMedioConversao(): ?float
    {
        // Retorna média em horas entre entry_date e data de conversão
        return $this->contatos()
            ->where('status', 'convertido')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - entry_date)) / 3600) as media')
            ->value('media');
    }

    // Scopes
    public function scopeAtivas($query)  { return $query->where('status', 'ativa'); }
    public function scopePorCanal($query, string $canal) { return $query->where('canal', $canal); }
}
