<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeadInbound extends Model
{
    protected $table = 'leads';

    protected $fillable = [
        'tenant_id',
        'vendedor_id',
        'chat_contact_id',
        'cliente_id',
        'name',
        'phone',
        'email',
        'message',
        'source',
        'status',
        'etapa',
        'agendamento_id',
        'motivo_perda',
        'first_contact_at',
        'converted_at',
        'meta',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'page_url',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'first_contact_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public const ETAPA_NOVO = 'novo';
    public const ETAPA_CONTATO = 'contato';
    public const ETAPA_PROPOSTA = 'proposta';
    public const ETAPA_GANHO = 'ganho';
    public const ETAPA_PERDIDO = 'perdido';

    public const ETAPAS = [
        self::ETAPA_NOVO => 'Novo',
        self::ETAPA_CONTATO => 'Em Contato',
        self::ETAPA_PROPOSTA => 'Proposta',
        self::ETAPA_GANHO => 'Ganho',
        self::ETAPA_PERDIDO => 'Perdido',
    ];

    public const STATUS_NOVO = 'novo';
    public const STATUS_CONTATADO = 'contatado';
    public const STATUS_CONVERTIDO = 'convertido';
    public const STATUS_PERDIDO = 'perdido';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function chatContact(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Chat\ChatContact::class, 'chat_contact_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LeadSchedule::class, 'lead_id');
    }

    public function activeSchedule(): HasOne
    {
        return $this->hasOne(LeadSchedule::class, 'lead_id')->where('status', 'pending');
    }

    public function transferHistory(): HasMany
    {
        return $this->hasMany(LeadTransferHistory::class, 'lead_id');
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(LeadFieldValue::class, 'lead_id');
    }

    public function scopeNovo($query)
    {
        return $query->where('status', 'novo');
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByEtapa($query, string $etapa)
    {
        return $query->where('etapa', $etapa);
    }

    public function scopeForVendedor($query, int $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopeAtrasado($query)
    {
        return $query->where('etapa', '!=', self::ETAPA_GANHO)
            ->where('etapa', '!=', self::ETAPA_PERDIDO)
            ->where('created_at', '<', now()->subHours(48));
    }

    public function getTempoNaFilaAttribute(): string
    {
        if (!$this->created_at) return '0min';
        
        $diff = $this->created_at->diff(now());
        if ($diff->d > 0) return $diff->d . 'd';
        if ($diff->h > 0) return $diff->h . 'h';
        return $diff->i . 'min';
    }

    public function getTempoSemContatoAttribute(): string
    {
        if (!$this->created_at) return '0min';
        if ($this->first_contact_at) return '0min';
        
        return $this->created_at->diffForHumans();
    }

    public function markAsContatado(): void
    {
        if (!$this->first_contact_at) {
            $this->first_contact_at = now();
        }
        $this->status = self::STATUS_CONTATADO;
        if ($this->etapa === self::ETAPA_NOVO) {
            $this->etapa = self::ETAPA_CONTATO;
        }
        $this->save();
    }

    public function markAsConvertido(): void
    {
        $this->etapa = self::ETAPA_GANHO;
        $this->status = self::STATUS_CONVERTIDO;
        $this->converted_at = now();
        $this->save();
    }

    public function markAsPerdido(string $motivo): void
    {
        $this->etapa = self::ETAPA_PERDIDO;
        $this->status = self::STATUS_PERDIDO;
        $this->motivo_perda = $motivo;
        $this->save();
    }

    public function isAtrasado(): bool
    {
        if (in_array($this->etapa, [self::ETAPA_GANHO, self::ETAPA_PERDIDO])) {
            return false;
        }
        return $this->created_at->diffInMinutes() > 48 * 60;
    }
}