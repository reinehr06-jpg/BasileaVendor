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
        Schema::table('vendedores', function (Blueprint $table) {
            // Campos para Split Asaas
            $table->string('asaas_wallet_id')->nullable()->after('meta_mensal');
            $table->boolean('split_ativo')->default(false)->after('asaas_wallet_id');
            $table->enum('tipo_split', ['percentual', 'fixo'])->default('percentual')->after('split_ativo');
            $table->decimal('valor_split_inicial', 10, 2)->default(0)->after('tipo_split');
            $table->decimal('valor_split_recorrencia', 10, 2)->default(0)->after('valor_split_inicial');
            $table->timestamp('wallet_validado_em')->nullable()->after('valor_split_recorrencia');
            $table->string('wallet_status')->default('pendente')->after('wallet_validado_em'); // pendente, validado, erro
            
            // Campos de comissão expandidos
            $table->decimal('comissao_inicial', 5, 2)->default(10)->after('wallet_status');
            $table->decimal('comissao_recorrencia', 5, 2)->default(10)->after('comissao_inicial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_wallet_id',
                'split_ativo',
                'tipo_split',
                'valor_split_inicial',
                'valor_split_recorrencia',
                'wallet_validado_em',
                'wallet_status',
                'comissao_inicial',
                'comissao_recorrencia',
            ]);
        });
    }
};