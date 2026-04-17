<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMensagem extends Model
{
    protected $table = 'chat_mensagens';
    
    protected $fillable = [
        'conversa_id', 'sender_id', 'sender_type', 'direction',
        'tipo', 'conteudo', 'external_message_id',
        'attachment_url', 'attachment_name', 'attachment_type', 'attachment_size',
        'delivery_status', 'is_read', 'read_at'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];
    
    public function conversa(): BelongsTo
    {
        return $this->belongsTo(ChatConversa::class, 'conversa_id');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeNaoLidas($query)
    {
        return $query->where('is_read', false)->where('direction', 'inbound');
    }

    public function scopeByConversa($query, $conversaId)
    {
        return $query->where('conversa_id', $conversaId);
    }

    public function scopeRecentes($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function isInbound()
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound()
    {
        return $this->direction === 'outbound';
    }

    public function getSenderNameAttribute()
    {
        if ($this->sender_type === 'vendedor' && $this->sender_id) {
            $vendedor = Vendedor::find($this->sender_id);
            return $vendedor ? $vendedor->nome : 'Vendedor';
        }
        
        if ($this->sender_type === 'user' && $this->sender_id) {
            $user = User::find($this->sender_id);
            return $user ? $user->name : 'Admin';
        }
        
        if ($this->sender_type === 'sistema') {
            return 'Sistema';
        }
        
        return 'Cliente';
    }
}