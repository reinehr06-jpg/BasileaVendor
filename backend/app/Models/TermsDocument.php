<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermsDocument extends Model
{
    protected $fillable = [
        'tipo', 'titulo', 'versao', 'conteudo_html', 'conteudo', 'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function acceptances(): HasMany
    {
        return $this->hasMany(TermsAcceptance::class, 'terms_document_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public static function ativoPorTipo(string $tipo): ?self
    {
        return static::porTipo($tipo)->ativos()->latest('versao')->first();
    }
}