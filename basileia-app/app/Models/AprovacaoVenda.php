<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AprovacaoVenda extends Model
{
    protected $table = 'aprovacoes_venda';
    
    protected $fillable = [
        'venda_id',
        'tipo_aprovacao',
        'percentual_solicitado',
        'valor_solicitado',
        'limite_regra',
        'status',
        'solicitado_por',
        'aprovado_por',
        'observacao',
        'motivo_rejeicao',
    ];

    protected function casts(): array
    {
        return [
            'percentual_solicitado' => 'decimal:2',
            'valor_solicitado' => 'decimal:2',
            'limite_regra' => 'decimal:2',
        ];
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }
}
