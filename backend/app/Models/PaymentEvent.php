<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentEvent extends Model
{
    protected $table = 'payment_events';

    protected $fillable = [
        'asaas_event_id',
        'asaas_payment_id',
        'payment_id',
        'event_type',
        'status_from',
        'status_to',
        'payload',
        'processed_at',
        'processing_error',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    public function markAsProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'processed_at' => now(),
            'processing_error' => $error,
        ]);
    }

    public static function isProcessedBefore(string $eventId): bool
    {
        return static::where('asaas_event_id', $eventId)->exists();
    }
}
