<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    protected $table = 'equipes';
    protected $fillable = [
        'nome',
        'gestor_id',
        'meta_mensal',
        'cor',
        'status',
    ];

    protected $casts = [
        'meta_mensal' => 'decimal:2',
    ];

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function vendedores()
    {
        return $this->hasMany(Vendedor::class, 'equipe_id');
    }

    public function totalVendedoresAtivos()
    {
        return $this->vendedores()->whereHas('user', function ($q) {
            $q->where('status', 'ativo');
        })->count();
    }
}
