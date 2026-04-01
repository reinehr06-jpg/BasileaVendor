<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $table = 'commission_rules';

    protected $fillable = [
        'plano_nome',
        'seller_fixed_value_first_payment',
        'seller_fixed_value_recurring',
        'manager_fixed_value_first_payment',
        'manager_fixed_value_recurring',
        'active',
    ];

    protected $casts = [
        'seller_fixed_value_first_payment' => 'decimal:2',
        'seller_fixed_value_recurring' => 'decimal:2',
        'manager_fixed_value_first_payment' => 'decimal:2',
        'manager_fixed_value_recurring' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Buscar regra ativa para um plano específico.
     */
    public static function forPlan(string $planoNome): ?self
    {
        return self::where('plano_nome', $planoNome)
            ->where('active', true)
            ->first();
    }
}
