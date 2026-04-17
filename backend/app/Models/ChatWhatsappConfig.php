<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatWhatsappConfig extends Model
{
    protected $table = 'chat_whatsapp_configs';
    
    protected $fillable = [
        'gestor_id', 'numero_telefone', 'numero_id',
        'api_token', 'webhook_verify_token', 'provider', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGestor($query, $gestorId)
    {
        return $query->where('gestor_id', $gestorId);
    }

    public function isConfigured()
    {
        return !empty($this->numero_telefone) && !empty($this->api_token);
    }

    public function getNumeroFormatadoAttribute()
    {
        if (!$this->numero_telefone) return null;
        
        $phone = preg_replace('/\D/', '', $this->numero_telefone);
        if (strlen($phone) === 12) {
            return '+' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . ' ' . substr($phone, 4, 5) . '-' . substr($phone, 9);
        }
        return $this->numero_telefone;
    }
}