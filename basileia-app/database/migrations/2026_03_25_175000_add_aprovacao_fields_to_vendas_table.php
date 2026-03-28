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
        Schema::table('vendas', function (Blueprint $table) {
            // Campos de aprovação comercial
            $table->boolean('requer_aprovacao')->default(false)->after('parcelas');
            $table->string('status_aprovacao')->default('liberado')->after('requer_aprovacao'); // liberado, pendente, aprovado, rejeitado
            $table->foreignId('aprovado_por')->nullable()->after('status_aprovacao')->constrained('users')->onDelete('set null');
            $table->timestamp('aprovado_em')->nullable()->after('aprovado_por');
            
            // Modo de cobrança expandido
            $table->string('modo_cobranca_asaas')->nullable()->after('modo_cobranca'); // PAYMENT, INSTALLMENT, SUBSCRIPTION
            $table->string('asaas_subscription_id')->nullable()->after('modo_cobranca_asaas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn([
                'requer_aprovacao',
                'status_aprovacao',
                'aprovado_em',
                'modo_cobranca_asaas',
                'asaas_subscription_id',
            ]);
            $table->dropForeign(['aprovado_por']);
            $table->dropColumn('aprovado_por');
        });
    }
};
