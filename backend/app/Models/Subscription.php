<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'lead_id',
        'subscription_card_id',
        'offer_id',
        'asaas_subscription_id',
        'billing_type',
        'amount',
        'start_date',
        'next_billing_date',
        'last_billing_date',
        'status',
        'total_invoices_generated',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'next_billing_date' => 'date',
        'last_billing_date' => 'date',
        'total_invoices_generated' => 'integer',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(SubscriptionCard::class, 'subscription_card_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isYearly(): bool
    {
        return $this->billing_type === 'yearly';
    }

    public function isMonthly(): bool
    {
        return $this->billing_type === 'monthly';
    }

    public function getRemainingMonthsAttribute(): int
    {
        if (!$this->isYearly()) {
            return 0;
        }

        $start = $this->start_date;
        $next = $this->next_billing_date ?? $start->copy()->addYear();
        return now()->diffInMonths($next, false);
    }

    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'active' => 'Ativa',
            'paused' => 'Pausada',
            'cancelled' => 'Cancelada',
            'expired' => 'Expirada',
            default => $this->status,
        };
    }

    public function getFormattedBillingTypeAttribute(): string
    {
        return $this->billing_type === 'yearly' ? 'Anual' : 'Mensal';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNeedsRenewal($query)
    {
        return $query->where('status', 'active')
            ->where('next_billing_date', '<=', now()->addDays(1));
    }
}
