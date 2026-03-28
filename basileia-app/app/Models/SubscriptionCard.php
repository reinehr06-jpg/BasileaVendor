<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionCard extends Model
{
    protected $table = 'subscription_cards';

    protected $fillable = [
        'lead_id',
        'asaas_card_id',
        'brand',
        'last4',
        'holder_name',
        'expiry_month',
        'expiry_year',
        'token',
        'status',
    ];

    protected $hidden = ['token'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expiry_year < now()->format('Y') || 
               ($this->expiry_year == now()->format('Y') && $this->expiry_month < now()->format('m')));
    }

    public function getBrandIconAttribute(): string
    {
        $brands = [
            'visa' => 'fab fa-cc-visa',
            'mastercard' => 'fab fa-cc-mastercard',
            'amex' => 'fab fa-cc-amex',
            'elo' => 'fas fa-credit-card',
            'hipercard' => 'fas fa-credit-card',
            'discover' => 'fab fa-cc-discover',
            'diners' => 'fab fa-cc-diners-club',
            'jcb' => 'fab fa-cc-jcb',
        ];

        return $brands[$this->brand] ?? 'fas fa-credit-card';
    }

    public function getBrandColorAttribute(): string
    {
        $colors = [
            'visa' => '#1a1f71',
            'mastercard' => '#eb001b',
            'amex' => '#006fcf',
            'elo' => '#FFCB05',
            'hipercard' => '#D4242C',
            'discover' => '#FF6000',
            'diners' => '#004B87',
            'jcb' => '#0E4C96',
        ];

        return $colors[$this->brand] ?? '#6B7280';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
