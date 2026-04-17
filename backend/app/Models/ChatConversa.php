<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversa extends Model
{
    protected $table = 'chat_conversas';
    
    protected $fillable = [
        'contact_id', 'gestor_id', 'vendedor_id', 'status',
        'pinned', 'is_atendido', 'first_response_at',
        'last_inbound_at', 'last_outbound_at', 'last_message_at',
        'assigned_at', 'unread_at', 'unread_count', 'transfer_count'
    ];
    
    protected $casts = [
        'pinned' => 'boolean',
        'is_atendido' => 'boolean',
        'first_response_at' => 'datetime',
        'last_inbound_at' => 'datetime',
        'last_outbound_at' => 'datetime',
        'last_message_at' => 'datetime',
        'assigned_at' => 'datetime',
        'unread_at' => 'datetime',
    ];
    
    public function contact(): BelongsTo
    {
        return $this->belongsTo(ChatContact::class, 'contact_id');
    }
    
    public function mensagens(): HasMany
    {
        return $this->hasMany(ChatMensagem::class, 'conversa_id')
            ->orderBy('created_at', 'asc');
    }
    
    public function ultimoMensagem(): HasMany
    {
        return $this->hasOne(ChatMensagem::class, 'conversa_id')
            ->orderBy('created_at', 'desc');
    }
    
    public function atividades(): HasMany
    {
        return $this->hasMany(ChatAtividade::class, 'conversa_id')
            ->orderBy('created_at', 'desc');
    }
    
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
    
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function scopeAbertas($query)
    {
        return $query->where('status', 'aberta');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeResolvidas($query)
    {
        return $query->where('status', 'resolvida');
    }

    public function scopeAtendidos($query)
    {
        return $query->where('is_atendido', true);
    }

    public function scopeNaoAtendidos($query)
    {
        return $query->where('is_atendido', false);
    }

    public function scopeByVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopeByGestor($query, $gestorId)
    {
        return $query->where('gestor_id', $gestorId);
    }

    public function scopeOrderByLastMessage($query)
    {
        return $query->orderBy('last_message_at', 'desc');
    }

    public function scopeOrderByPinned($query)
    {
        return $query->orderByDesc('pinned');
    }

    public function marcarComoAtendido()
    {
        if (!$this->is_atendido) {
            $this->update([
                'is_atendido' => true,
                'first_response_at' => now()
            ]);
        }
    }

    public function adicionarMensagemEntrada()
    {
        $this->update([
            'last_inbound_at' => now(),
            'last_message_at' => now(),
            'unread_count' => $this->unread_count + 1,
            'unread_at' => now()
        ]);
    }

    public function adicionarMensagemSaida()
    {
        $this->update([
            'last_outbound_at' => now(),
            'last_message_at' => now()
        ]);
        
        if (!$this->is_atendido) {
            $this->marcarComoAtendido();
        }
    }
}