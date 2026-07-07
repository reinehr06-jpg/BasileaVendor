<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = 'fornecedores';

    protected $fillable = [
        'nome', 'documento', 'email', 'telefone', 'contato_responsavel', 'endereco', 'status'
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}
