<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsaasPayment extends Model
{
    protected $fillable = [
        'asaas_payment_id',
        'asaas_customer_id',
        'asaas_subscription_id',
        'status',
        'due_date',
        'payment_date',
        'client_payment_date',
        'confirmed_date',
        'deleted',
        'refunded',
        'asaas_raw_data',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'client_payment_date' => 'date',
        'confirmed_date' => 'date',
        'deleted' => 'boolean',
        'refunded' => 'boolean',
        'asaas_raw_data' => 'array',
    ];
}
