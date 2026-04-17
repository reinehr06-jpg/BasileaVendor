<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id',
        'contact_id',
        'vendedor_id',
        'direction',
        'content',
        'type',
        'external_message_id',
        'source_id',
        'media_url',
        'media_type',
        'is_delivered',
        'is_read',
        'delivered_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_delivered' => 'boolean',
            'is_read' => 'boolean',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ChatContact::class, 'contact_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vendedor::class);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeByExternalId($query, ?string $externalId)
    {
        if ($externalId) {
            return $query->where('external_message_id', $externalId);
        }
        return $query;
    }

    public function scopeBySourceId($query, ?string $sourceId)
    {
        if ($sourceId) {
            return $query->where('source_id', $sourceId);
        }
        return $query;
    }

    public static function existsByExternalId(?string $externalId): bool
    {
        if (!$externalId) {
            return false;
        }
        return self::where('external_message_id', $externalId)->exists();
    }

    public static function existsBySourceId(?string $sourceId): bool
    {
        if (!$sourceId) {
            return false;
        }
        return self::where('source_id', $sourceId)->exists();
    }

    public function markAsDelivered(): void
    {
        $this->is_delivered = true;
        $this->delivered_at = now();
        $this->save();
    }

    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }
}