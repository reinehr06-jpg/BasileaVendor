<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestorPermissao extends Model
{
    protected $table = 'gestor_permissoes';

    protected $fillable = [
        'user_id',
        'ver_vendas', 'ver_clientes', 'ver_comissoes', 'ver_pagamentos', 'ver_relatorios',
        'criar_vendas', 'cancelar_vendas', 'estornar_vendas', 'gerenciar_vendedores', 'ver_configuracoes',
    ];

    protected $casts = [
        'ver_vendas' => 'boolean',
        'ver_clientes' => 'boolean',
        'ver_comissoes' => 'boolean',
        'ver_pagamentos' => 'boolean',
        'ver_relatorios' => 'boolean',
        'criar_vendas' => 'boolean',
        'cancelar_vendas' => 'boolean',
        'estornar_vendas' => 'boolean',
        'gerenciar_vendedores' => 'boolean',
        'ver_configuracoes' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar se gestor tem uma permissão específica.
     */
    public function pode(string $permissao): bool
    {
        return $this->{$permissao} ?? false;
    }
}
