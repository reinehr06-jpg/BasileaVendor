<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    protected $fillable = [
        'descricao', 'data', 'valor', 'categoria', 
        'origem', 'conta', 'nf', 'status'
    ];
}
