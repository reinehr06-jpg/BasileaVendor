<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookOutboundLog extends Model
{
    protected $fillable = [
        'webhook_config_id', 'event_type', 'payload',
        'http_status', 'response_body', 'status',
        'attempts', 'next_retry_at', 'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'http_status' => 'integer',
        'attempts' => 'integer',
        'next_retry_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(WebhookOutboundConfig::class, 'webhook_config_id');
    }
}
