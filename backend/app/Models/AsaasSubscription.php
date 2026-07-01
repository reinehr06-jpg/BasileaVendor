<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsaasSubscription extends Model
{
    protected $fillable = [
        'asaas_subscription_id',
        'asaas_customer_id',
        'status',
        'next_due_date',
        'deleted',
        'asaas_raw_data',
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'deleted' => 'boolean',
        'asaas_raw_data' => 'array',
    ];
}
