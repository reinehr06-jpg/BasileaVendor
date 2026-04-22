<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de controle de idempotência para webhooks do Asaas.
     * Impede processamento duplicado do mesmo evento de pagamento.
     */
    public function up(): void
    {
        if (!Schema::hasTable('webhook_eventos')) {
            Schema::create('webhook_eventos', function (Blueprint $table) {
                $table->id();
                $table->string('asaas_payment_id')->unique(); // bloqueia duplicata no banco
                $table->string('evento', 50);
                $table->timestamp('processado_em');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_eventos');
    }
};
