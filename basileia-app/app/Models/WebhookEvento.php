<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Controle de idempotência para webhooks do Asaas.
 * Impede processamento duplicado do mesmo evento de pagamento.
 */
class WebhookEvento extends Model
{
    public $timestamps = false;

    protected $table = 'webhook_eventos';

    protected $fillable = [
        'asaas_payment_id',
        'evento',
        'processado_em',
    ];

    protected $casts = [
        'processado_em' => 'datetime',
    ];
}
