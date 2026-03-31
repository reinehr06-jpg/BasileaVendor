<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyCustomerPayment extends Model
{
    protected $table = 'legacy_customer_payments';

    protected $fillable = [
        'legacy_import_id',
        'asaas_payment_id',
        'asaas_subscription_id',
        'asaas_installment_id',
        'installment_number',
        'total_installments',
        'billing_type',
        'payment_method',
        'value',
        'due_date',
        'paid_at',
        'status',
        'description',
        'is_recurring',
        'reference_month',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'date',
            'is_recurring' => 'boolean',
            'raw_payload' => 'array',
        ];
    }

    public function legacyImport(): BelongsTo
    {
        return $this->belongsTo(LegacyCustomerImport::class, 'legacy_import_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'RECEIVED' && $this->paid_at !== null;
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'OVERDUE';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'RECEIVED' => 'success',
            'PENDING' => 'warning',
            'OVERDUE' => 'danger',
            'CONFIRMED' => 'info',
            'CANCELLED' => 'secondary',
            default => 'secondary',
        };
    }
}
