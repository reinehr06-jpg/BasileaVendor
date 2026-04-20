<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contato extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'email', 'telefone', 'whatsapp', 'documento', 'status', 'motivo_perda',
        'campanha_id', 'canal_origem', 'utm_source', 'utm_medium', 'utm_campaign',
        'utm_content', 'utm_term', 'ref_param', 'entry_date',
        'agente_id', 'vendedor_id', 'gestor_id',
        'nome_igreja', 'nome_pastor', 'nome_responsavel', 'localidade',
        'moeda', 'quantidade_membros', 'cep', 'endereco', 'numero',
        'complemento', 'bairro', 'cidade', 'estado', 'pais',
        'tags', 'observacoes', 'cliente_id_legado',
        'ai_score', 'ai_score_motivo', 'ai_avaliado_em', 'ai_proxima_acao', 'ai_observacao',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
        'ai_avaliado_em' => 'datetime',
        'tags' => 'array',
    ];

    public function agente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agente_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function campanha(): BelongsTo
    {
        return $this->belongsTo(Campanha::class, 'campanha_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ContatoStatusLog::class)->orderByDesc('created_at');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'contato_id');
    }

    public function cambiarStatus(string $novoStatus, ?string $motivo = null): void
    {
        $this->statusLogs()->create([
            'status_anterior' => $this->status,
            'status_novo' => $novoStatus,
            'usuario_id' => auth()->id(),
            'motivo' => $motivo,
            'created_at' => now(),
        ]);

        $this->update([
            'status' => $novoStatus,
            'motivo_perda' => in_array($novoStatus, ['perdido', 'lead_ruim']) ? $motivo : $this->motivo_perda,
        ]);
    }

    public function scopePorCampanha($query, $campanhaId)
    {
        return $query->where('campanha_id', $campanhaId);
    }

    public function scopePorCanal($query, $canal)
    {
        return $query->where('canal_origem', $canal);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('entry_date', [$inicio, $fim]);
    }

    public function scopePorAgente($query, $agenteId)
    {
        return $query->where('agente_id', $agenteId);
    }

    public function scopeLeads($query)
    {
        return $query->where('status', 'lead');
    }

    public function scopeConvertidos($query)
    {
        return $query->where('status', 'convertido');
    }

    public function scopePerdidos($query)
    {
        return $query->whereIn('status', ['perdido', 'lead_ruim']);
    }
}