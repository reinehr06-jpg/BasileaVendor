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
        'nome',
        'documento',
        'email',
        'telefone',
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
    ];

    protected function casts(): array
    {
        return [
            'asaas_customer_data' => 'array',
            'plano_valor_original' => 'decimal:2',
            'plano_valor_recorrente' => 'decimal:2',
            'data_venda_original' => 'date',
            'generate_old_sale_commission' => 'boolean',
            'generate_recurring_commission' => 'boolean',
            'imported_at' => 'datetime',
            'last_sync_at' => 'datetime',
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
