<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'numero', 'solicitante', 'fornecedor_id', 'valor', 'status', 'data_solicitacao'
    ];

    protected $casts = [
        'data_solicitacao' => 'date',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
