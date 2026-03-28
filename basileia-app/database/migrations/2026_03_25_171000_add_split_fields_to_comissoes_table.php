<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('comissoes')) {
            return;
        }
        Schema::table('comissoes', function (Blueprint $table) {
            // Campos para rastreamento de split
            $table->string('asaas_split_status')->nullable()->after('competencia'); // enviado, processado, erro, divergencia
            $table->json('asaas_split_payload')->nullable()->after('asaas_split_status');
            $table->string('asaas_wallet_id')->nullable()->after('asaas_split_payload');
            $table->decimal('split_valor_recebido', 12, 2)->nullable()->after('asaas_wallet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_split_status',
                'asaas_split_payload',
                'asaas_wallet_id',
                'split_valor_recebido',
            ]);
        });
    }
};