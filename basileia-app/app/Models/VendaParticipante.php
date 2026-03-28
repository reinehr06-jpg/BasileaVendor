<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendaParticipante extends Model
{
    protected $table = 'venda_participantes';
    
    protected $fillable = [
        'venda_id',
        'user_id',
        'vendedor_id',
        'papel',
        'tipo_repasse',
        'valor_repasse',
        'wallet_id_asaas',
        'split_ativo',
        'split_status',
    ];

    protected function casts(): array
    {
        return [
            'valor_repasse' => 'decimal:4',
            'split_ativo' => 'boolean',
        ];
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
}
