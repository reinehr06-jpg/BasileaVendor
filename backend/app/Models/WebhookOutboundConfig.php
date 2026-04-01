<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookOutboundConfig extends Model
{
    protected $fillable = [
        'name', 'service', 'events', 'url',
        'secret', 'active', 'retry_count',
    ];

    protected $casts = [
        'events' => 'array',
        'active' => 'boolean',
        'retry_count' => 'integer',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookOutboundLog::class, 'webhook_config_id');
    }

    public function hasEvent(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }
}
