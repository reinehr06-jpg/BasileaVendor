<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysadminToken extends Model
{
    protected $fillable = [
        'token_hash',
        'user_id',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
