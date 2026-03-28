<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyCommission extends Model
{
    protected $table = 'legacy_commissions';

    protected $fillable = [
        'legacy_import_id',
        'legacy_payment_id',
        'vendedor_id',
        'gestor_id',
        'cliente_id',
        'commission_type',
        'reference_month',
        'base_amount',
        'seller_commission_amount',
        'gestor_commission_amount',
        'status',
        'generated_at',
        'released_at',
        'asaas_reference_id',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'seller_commission_amount' => 'decimal:2',
            'gestor_commission_amount' => 'decimal:2',
            'generated_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function legacyImport(): BelongsTo
    {
        return $this->belongsTo(LegacyCustomerImport::class, 'legacy_import_id');
    }

    public function legacyPayment(): BelongsTo
    {
        return $this->belongsTo(LegacyCustomerPayment::class, 'legacy_payment_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function getTotalAmountAttribute(): float
    {
        return ($this->seller_commission_amount ?? 0) + ($this->gestor_commission_amount ?? 0);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'GENERATED' => 'success',
            'PAID' => 'info',
            'PENDING_RULE', 'PENDING_CONFIRMATION' => 'warning',
            'BLOCKED' => 'danger',
            'ERROR' => 'danger',
            default => 'secondary',
        };
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['PENDING_RULE', 'PENDING_CONFIRMATION']);
    }

    public function isGenerated(): bool
    {
        return $this->status === 'GENERATED';
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }
}
