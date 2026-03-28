<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaFiscal extends Model
{
    protected $table = 'notas_fiscais';
    protected $fillable = [
        'vendedor_id', 'descricao', 'valor', 'arquivo_path', 'tipo', 'mes_referencia',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
}
