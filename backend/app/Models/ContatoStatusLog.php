<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContatoStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'contato_id', 'status_anterior', 'status_novo',
        'usuario_id', 'motivo', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function contato(): BelongsTo
    {
        return $this->belongsTo(Contato::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}