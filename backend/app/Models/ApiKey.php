<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'name', 'service', 'allowed_ips',
        'rate_limit', 'last_used_at', 'active',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'rate_limit' => 'integer',
        'active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['key'];

    public static function generate(): string
    {
        return 'bv_' . bin2hex(random_bytes(24));
    }

    public static function createKey(array $data): self
    {
        $key = self::generate();
        $data['key'] = hash('sha256', $key);
        return self::create($data);
    }

    public static function verify(string $key): ?self
    {
        $hashed = hash('sha256', $key);
        return self::where('key', $hashed)->where('active', true)->first();
    }

    public function markUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
