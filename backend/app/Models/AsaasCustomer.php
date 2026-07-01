<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsaasCustomer extends Model
{
    protected $fillable = [
        'asaas_customer_id',
        'financial_status',
        'first_paid_at',
        'last_paid_at',
        'asaas_raw_data',
    ];

    protected $casts = [
        'first_paid_at' => 'datetime',
        'last_paid_at' => 'datetime',
        'asaas_raw_data' => 'array',
    ];
}
