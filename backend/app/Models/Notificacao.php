<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    protected $table = 'notificacoes';
    
    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'dados',
        'lida',
        'lida_em',
    ];

    protected function casts(): array
    {
        return [
            'dados' => 'array',
            'lida' => 'boolean',
            'lida_em' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Criar notificação para todos os masters
     */
    public static function notificarMasters(string $tipo, string $titulo, string $mensagem, array $dados = []): void
    {
        $masters = User::where('perfil', 'master')->get();
        
        foreach ($masters as $master) {
            self::create([
                'user_id' => $master->id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'dados' => $dados,
            ]);
        }
    }

    /**
     * Marcar como lida
     */
    public function marcarComoLida(): void
    {
        $this->update([
            'lida' => true,
            'lida_em' => now(),
        ]);
    }
}
