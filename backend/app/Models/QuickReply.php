<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickReply extends Model
{
    protected $table = 'quick_replies';

    protected $fillable = [
        'tenant_id',
        'vendedor_id',
        'shortcut',
        'content',
        'category',
        'is_global',
    ];

    protected function casts(): array
    {
        return [
            'is_global' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function scopeForVendedor($query, int $vendedorId)
    {
        return $query->where(function ($q) use ($vendedorId) {
            $q->where('is_global', true)
              ->orWhere('vendedor_id', $vendedorId);
        });
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }
}