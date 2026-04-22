<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'inicio_assinatura')) {
                $table->date('inicio_assinatura')->nullable()->after('data_venda');
            }
            if (!Schema::hasColumn('vendas', 'proximo_vencimento')) {
                $table->date('proximo_vencimento')->nullable()->after('inicio_assinatura');
            }
            if (!Schema::hasColumn('vendas', 'status_assinatura')) {
                $table->string('status_assinatura')->default('ativa')->after('proximo_vencimento');
            }
            if (!Schema::hasColumn('vendas', 'renovacao_ativa')) {
                $table->boolean('renovacao_ativa')->default(true)->after('status_assinatura');
            }
            if (!Schema::hasColumn('vendas', 'ciclo_meses')) {
                $table->integer('ciclo_meses')->default(12)->after('renovacao_ativa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['inicio_assinatura', 'proximo_vencimento', 'status_assinatura', 'renovacao_ativa', 'ciclo_meses']);
        });
    }
};
