<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $table = 'chat_conversations';

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'vendedor_id',
        'status',
        'atendimento_status',
        'is_resolved',
        'last_inbound_at',
        'last_outbound_at',
        'assigned_at',
        'unread_count',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'last_inbound_at' => 'datetime',
            'last_outbound_at' => 'datetime',
            'assigned_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ChatContact::class, 'contact_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vendedor::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    public function lastMessage(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')->latest()->limit(1);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeNaoAtendido($query)
    {
        return $query->where('atendimento_status', 'nao_atendido');
    }

    public function scopeAtendido($query)
    {
        return $query->where('atendimento_status', 'atendido');
    }

    public function scopeForVendedor($query, int $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopeForEquipe($query, int $equipeId)
    {
        return $query->whereHas('vendedor', function ($q) use ($equipeId) {
            $q->where('equipe_id', $equipeId);
        });
    }

    public function markAsAtendido(): void
    {
        if ($this->atendimento_status === 'nao_atendido') {
            $this->atendimento_status = 'atendido';
            $this->save();
        }
    }

    public function updateTimestampsForMessage(string $direction): void
    {
        if ($direction === 'inbound') {
            $this->last_inbound_at = now();
            if ($this->atendimento_status === 'nao_atendido') {
                $this->unread_count = $this->unread_count + 1;
            }
        } else {
            $this->last_outbound_at = now();
            $this->atendimento_status = 'atendido';
            $this->unread_count = 0;
        }
        $this->save();
    }

    public function checkSlaAndTransfer(int $slaMinutes = 60): bool
    {
        if ($this->is_resolved) {
            return false;
        }
        if (!$this->last_inbound_at) {
            return false;
        }
        if ($this->last_outbound_at && $this->last_outbound_at > $this->last_inbound_at) {
            return false;
        }
        if (now()->diffInMinutes($this->last_inbound_at) > $slaMinutes) {
            return true;
        }
        return false;
    }
}