<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFieldValue extends Model
{
    protected $table = 'lead_field_values';

    protected $fillable = [
        'lead_id',
        'field_id',
        'value',
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

    public function field(): BelongsTo
    {
        return $this->belongsTo(LeadField::class, 'field_id');
    }
}