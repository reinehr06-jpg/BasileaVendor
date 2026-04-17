<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatContact extends Model
{
    protected $table = 'chat_contacts';
    
    protected $fillable = [
        'nome', 'telefone', 'email', 'avatar_url', 'tags',
        'source', 'source_id', 'gestor_id'
    ];
    
    protected $casts = [
        'tags' => 'array',
    ];
    
    public function conversas(): HasMany
    {
        return $this->hasMany(ChatConversa::class, 'contact_id');
    }
    
    public function ultimaConversa(): HasMany
    {
        return $this->hasMany(ChatConversa::class, 'contact_id')
            ->orderBy('last_message_at', 'desc');
    }
    
    public function conversasAtivas(): HasMany
    {
        return $this->hasMany(ChatConversa::class, 'contact_id')
            ->whereIn('status', ['aberta', 'pendente'])
            ->orderBy('last_message_at', 'desc');
    }
    
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function scopeByGestor($query, $gestorId)
    {
        return $query->where('gestor_id', $gestorId);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nome', 'like', "%{$search}%")
              ->orWhere('telefone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public static function normalizePhone($phone)
    {
        $clean = preg_replace('/\D/', '', $phone);
        if (strlen($clean) === 11 && substr($clean, 0, 2) === '55') {
            return '+' . $clean;
        }
        if (strlen($clean) === 10) {
            return '+55' . $clean;
        }
        if (strlen($clean) === 11) {
            return '+55' . $clean;
        }
        return '+55' . $clean;
    }
}