<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'vendedores';
    protected $fillable = [
        'usuario_id', 'gestor_id', 'is_gestor', 'equipe_id',
        'comissao',
        'percentual_comissao',
        'telefone',
        'meta_mensal',
        'meta_pessoal',
        'status',
        // Campos de Split Asaas
        'asaas_wallet_id',
        'split_ativo',
        'tipo_split',
        'valor_split_inicial',
        'valor_split_recorrencia',
        'wallet_validado_em',
        'wallet_status',
        'comissao_inicial',
        'comissao_recorrencia',
        'comissao_gestor_primeira',
        'comissao_gestor_recorrencia',
    ];

    protected $casts = [
        'split_ativo' => 'boolean',
        'is_gestor' => 'boolean',
        'valor_split_inicial' => 'decimal:2',
        'valor_split_recorrencia' => 'decimal:2',
        'comissao_inicial' => 'decimal:2',
        'comissao_recorrencia' => 'decimal:2',
        'comissao_gestor_primeira' => 'decimal:2',
        'comissao_gestor_recorrencia' => 'decimal:2',
        'wallet_validado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }

    public function comissoes()
    {
        return $this->hasMany(Comissao::class);
    }
    
    /**
     * Verifica se o vendedor está apto para split
     */
    public function isAptoSplit(): bool
    {
        return $this->split_ativo 
            && !empty($this->asaas_wallet_id) 
            && $this->wallet_status === 'validado';
    }

    /**
     * Verificar se este vendedor é gestor
     */
    public function ehGestor(): bool
    {
        return $this->is_gestor;
    }

    /**
     * Vendedores que este gestor gerencia
     */
    public function vendedoresGerenciados()
    {
        return $this->hasMany(Vendedor::class, 'gestor_id', 'usuario_id');
    }
}
