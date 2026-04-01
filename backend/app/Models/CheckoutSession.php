<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CheckoutSession extends Model
{
    protected $fillable = [
        'token',
        'offer_id',
        'lead_id',
        'seller_id',
        'campaign_id',
        'utm_params',
        'currency',
        'price_original',
        'price_final',
        'fx_rate',
        'fx_quote_id',
        'fx_locked_until',
        'order_bump',
        'coupon_code',
        'coupon_discount',
        'ip',
        'user_agent',
        'country_code',
        'language',
        'status',
        'identified_at',
        'payment_started_at',
        'completed_at',
        'abandoned_at',
        'expires_at',
    ];

    protected $casts = [
        'price_original' => 'decimal:2',
        'price_final' => 'decimal:2',
        'fx_rate' => 'decimal:6',
        'coupon_discount' => 'decimal:2',
        'identified_at' => 'datetime',
        'payment_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'abandoned_at' => 'datetime',
        'expires_at' => 'datetime',
        'fx_locked_until' => 'datetime',
    ];

    protected $attributes = [
        'utm_params' => '{}',
        'order_bump' => '[]',
    ];

    // Mutators for JSON fields (SQLite compatibility)
    public function setUtmParamsAttribute($value)
    {
        $this->attributes['utm_params'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setOrderBumpAttribute($value)
    {
        $this->attributes['order_bump'] = is_array($value) ? json_encode($value) : $value;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = \Illuminate\Support\Str::uuid();
            }
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addHours(24);
            }
        });
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'session_token', 'token');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function markAsIdentified(): void
    {
        if (!$this->identified_at) {
            $this->update(['identified_at' => now()]);
        }
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'payment_started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsAbandoned(): void
    {
        $this->update([
            'status' => 'abandoned',
            'abandoned_at' => now(),
        ]);
    }

    public function isFxRateLocked(): bool
    {
        return $this->fx_locked_until && $this->fx_locked_until->isFuture();
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }
}
