<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comissao extends Model
{
    protected $table = 'comissoes';

    protected $fillable = [
        'vendedor_id', 'cliente_id', 'venda_id', 'pagamento_id', 'gerente_id',
        'tipo_comissao', 'percentual_aplicado', 'percentual_gerente',
        'valor_venda', 'valor_comissao', 'valor_gerente',
        'status', 'data_pagamento', 'competencia',
        'eligible_at', 'released_at', 'paid_via_split', 'split_transfer_id',
        // Campos de Split legado
        'asaas_split_status',
        'asaas_split_payload',
        'asaas_wallet_id',
        'split_valor_recebido',
    ];

    protected function casts(): array
    {
        return [
            'percentual_aplicado' => 'decimal:2',
            'percentual_gerente' => 'decimal:2',
            'valor_venda' => 'decimal:2',
            'valor_comissao' => 'decimal:2',
            'valor_gerente' => 'decimal:2',
            'data_pagamento' => 'date',
            'eligible_at' => 'datetime',
            'released_at' => 'datetime',
            'paid_via_split' => 'boolean',
            'asaas_split_payload' => 'array',
            'split_valor_recebido' => 'decimal:2',
        ];
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function gerente()
    {
        return $this->belongsTo(User::class, 'gerente_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function pagamento()
    {
        return $this->belongsTo(Pagamento::class, 'pagamento_id');
    }
}
