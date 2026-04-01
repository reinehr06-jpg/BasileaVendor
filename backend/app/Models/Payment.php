<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'order_id',
        'asaas_payment_id',
        'asaas_customer_id',
        'currency',
        'amount',
        'amount_brl',
        'fx_rate',
        'billing_type',
        'payment_method',
        'invoice_url',
        'bank_slip_url',
        'bank_slip_barcode',
        'pix_qrcode',
        'pix_copy_paste',
        'credit_card_brand',
        'credit_card_last_four',
        'status',
        'due_date',
        'paid_at',
        'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_brl' => 'decimal:2',
        'fx_rate' => 'decimal:6',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'waiting']);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['confirmed', 'received']);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'received',
            'paid_at' => now(),
            'confirmed_at' => now(),
        ]);
    }

    public function markAsRefunded(): void
    {
        $this->update(['status' => 'refunded']);
    }
}
