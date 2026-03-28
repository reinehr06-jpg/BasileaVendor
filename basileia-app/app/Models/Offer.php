<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'benefits',
        'price_brl',
        'price_usd',
        'price_eur',
        'discount_percent',
        'installments_max',
        'installment_value_brl',
        'guarantee_text',
        'features',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'features' => 'array',
        'price_brl' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'price_eur' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'installment_value_brl' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function checkoutSessions(): HasMany
    {
        return $this->hasMany(CheckoutSession::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getPriceForCurrency(string $currency): float
    {
        return match(strtoupper($currency)) {
            'USD' => $this->price_usd ?? $this->price_brl,
            'EUR' => $this->price_eur ?? $this->price_brl,
            default => $this->price_brl,
        };
    }
}
