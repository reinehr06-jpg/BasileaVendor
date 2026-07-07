<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Transferencia extends Model {
    protected $fillable = ['data', 'origem_id', 'destino_id', 'origem_nome', 'destino_nome', 'valor', 'taxa', 'descricao', 'status'];
    public function origem() { return $this->belongsTo(ContaBancaria::class, 'origem_id'); }
    public function destino() { return $this->belongsTo(ContaBancaria::class, 'destino_id'); }
}
