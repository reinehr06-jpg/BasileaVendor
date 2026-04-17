<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadSchedule extends Model
{
    protected $table = 'lead_schedules';

    protected $fillable = [
        'lead_id',
        'vendedor_id',
        'tenant_id',
        'scheduled_at',
        'status',
        'notes',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'is_completed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadInbound::class, 'lead_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->where('is_completed', false);
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_at', '<', now())->where('is_completed', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function complete(): void
    {
        $this->is_completed = true;
        $this->status = 'completed';
        $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->scheduled_at < now() && !$this->is_completed;
    }
}