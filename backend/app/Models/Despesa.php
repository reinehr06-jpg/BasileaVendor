<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $fillable = [
        'descricao', 'data_vencimento', 'data_pagamento', 'valor', 
        'categoria', 'fornecedor_id', 'fornecedor_nome', 'conta', 'nf', 'status'
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
