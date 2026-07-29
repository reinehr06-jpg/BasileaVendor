<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices de performance para colunas de filtro/ordenação usadas nos dashboards
 * e listagens. As foreign keys já são indexadas pelo constrained(); aqui cobrimos
 * status/datas, que faziam full table scan conforme o volume cresce.
 *
 * Aditivo e defensivo: só cria o índice se a coluna existir.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendas')) {
            Schema::table('vendas', function (Blueprint $table) {
                if (Schema::hasColumn('vendas', 'status')) {
                    $table->index('status', 'idx_vendas_status');
                }
                if (Schema::hasColumn('vendas', 'created_at')) {
                    $table->index('created_at', 'idx_vendas_created_at');
                }
            });
        }

        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (Schema::hasColumn('clientes', 'status')) {
                    $table->index('status', 'idx_clientes_status');
                }
                if (Schema::hasColumn('clientes', 'user_id')) {
                    $table->index('user_id', 'idx_clientes_user_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vendas')) {
            Schema::table('vendas', function (Blueprint $table) {
                if (Schema::hasColumn('vendas', 'status')) {
                    $table->dropIndex('idx_vendas_status');
                }
                if (Schema::hasColumn('vendas', 'created_at')) {
                    $table->dropIndex('idx_vendas_created_at');
                }
            });
        }

        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (Schema::hasColumn('clientes', 'status')) {
                    $table->dropIndex('idx_clientes_status');
                }
                if (Schema::hasColumn('clientes', 'user_id')) {
                    $table->dropIndex('idx_clientes_user_id');
                }
            });
        }
    }
};
