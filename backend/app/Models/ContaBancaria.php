<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContaBancaria extends Model {
    protected $table = 'contas_bancarias';
    protected $fillable = ['nome', 'tipo', 'saldo', 'status'];
}
