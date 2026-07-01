<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsaasEvent extends Model
{
    protected $fillable = [
        'asaas_event_id',
        'event_name',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
