<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadTransferHistory extends Model
{
    protected $table = 'lead_transfer_history';

    protected $fillable = [
        'lead_id',
        'from_vendedor_id',
        'to_vendedor_id',
        'tenant_id',
        'motivo',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadInbound::class, 'lead_id');
    }

    public function fromVendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'from_vendedor_id');
    }

    public function toVendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'to_vendedor_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}