<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_value',
        'max_uses',
        'uses_count',
        'applicable_offers',
        'applicable_currencies',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'applicable_offers' => 'array',
        'applicable_currencies' => 'array',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    public function isApplicableToOffer(int $offerId): bool
    {
        if (empty($this->applicable_offers)) {
            return true;
        }

        return in_array($offerId, $this->applicable_offers);
    }

    public function isApplicableToCurrency(string $currency): bool
    {
        if (empty($this->applicable_currencies)) {
            return true;
        }

        return in_array(strtoupper($currency), $this->applicable_currencies);
    }

    public function calculateDiscount(float $orderValue): float
    {
        if ($this->min_order_value && $orderValue < $this->min_order_value) {
            return 0;
        }

        return match($this->type) {
            'percent' => $orderValue * ($this->value / 100),
            'fixed' => min($this->value, $orderValue),
            default => 0,
        };
    }

    public function incrementUsage(): void
    {
        $this->increment('uses_count');
    }

    public static function findByCode(string $code): ?static
    {
        return static::where('code', strtoupper($code))->first();
    }
}
