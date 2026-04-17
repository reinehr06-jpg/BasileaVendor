<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatProviderConfig extends Model
{
    protected $table = 'chat_provider_configs';

    protected $fillable = [
        'tenant_id',
        'provider',
        'name',
        'config_json',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'config_json' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function getConfigAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true);
    }

    public function setConfigAttribute($value)
    {
        $this->attributes['config_json'] = is_array($value) ? json_encode($value) : $value;
    }
}