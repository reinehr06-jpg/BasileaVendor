<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegacyCustomerImport extends Model
{
    protected $table = 'legacy_customer_imports';

    protected $fillable = [
        'local_cliente_id',
        'local_cliente_cpf_cnpj',
        'asaas_customer_id',
        'asaas_customer_data',
        'asaas_subscription_id',
        'asaas_subscription_status',
        'asaas_subscription_billing_type',
        'nome',
        'documento',
        'email',
        'telefone',
        'tipo_cobranca',
        'parcelas_total',
        'parcelas_pagas',
        'valor_plano_mensal',
        'valor_total_cobranca',
        'primeiro_pagamento_at',
        'ultimo_pagamento_at',
        'ultimo_pagamento_confirmado_at',
        'proximo_vencimento_at',
        'dias_sem_pagar',
        'diagnostico_status',
        'tem_pagamento_confirmado',
        'tem_pagamento_pendente_atual',
        'comissao_tipo',
        'comissao_vendedor_calculada',
        'comissao_gestor_calculada',
        'comissao_mes_referencia',
        'comissao_resetada_em',
        'asaas_synced_at',
        'asaas_sync_error',
        'multi_asaas_ids',
        'vendedor_id',
        'gestor_id',
        'plano_id',
        'plano_valor_original',
        'plano_valor_recorrente',
        'data_venda_original',
        'customer_status',
        'subscription_status',
        'import_status',
        'generate_old_sale_commission',
        'generate_recurring_commission',
        'imported_by',
        'imported_at',
        'last_sync_at',
        'notes',
        'local_venda_id',
        'confirmado_em',
        'confirmado_por',
    ];

    protected function casts(): array
    {
        return [
            'asaas_customer_data' => 'array',
            'multi_asaas_ids' => 'array',
            'plano_valor_original' => 'decimal:2',
            'plano_valor_recorrente' => 'decimal:2',
            'data_venda_original' => 'date',
            'generate_old_sale_commission' => 'boolean',
            'generate_recurring_commission' => 'boolean',
            'imported_at' => 'datetime',
            'last_sync_at' => 'datetime',
            'primeiro_pagamento_at' => 'date',
            'ultimo_pagamento_at' => 'date',
            'ultimo_pagamento_confirmado_at' => 'date',
            'proximo_vencimento_at' => 'date',
            'comissao_resetada_em' => 'datetime',
            'confirmado_em' => 'datetime',
            'asaas_synced_at' => 'datetime',
        ];
    }

    public function localCliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'local_cliente_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LegacyCustomerPayment::class, 'legacy_import_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(LegacyCommission::class, 'legacy_import_id');
    }

    public function isActive(): bool
    {
        return $this->customer_status === 'ACTIVE';
    }

    public function hasValidCommercialLink(): bool
    {
        return $this->vendedor_id && $this->plano_id;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->import_status) {
            'IMPORTED' => 'success',
            'PENDING', 'PROCESSING' => 'warning',
            'NOT_FOUND' => 'danger',
            'CONFLICT' => 'warning',
            'INVALID_DOCUMENT' => 'danger',
            'NEEDS_REVIEW' => 'info',
            default => 'secondary',
        };
    }

    public function getCustomerStatusColorAttribute(): string
    {
        return match ($this->customer_status) {
            'ACTIVE' => 'success',
            'INACTIVE' => 'secondary',
            'OVERDUE' => 'warning',
            'CANCELLED' => 'danger',
            'NONE' => 'secondary',
            default => 'secondary',
        };
    }
}
