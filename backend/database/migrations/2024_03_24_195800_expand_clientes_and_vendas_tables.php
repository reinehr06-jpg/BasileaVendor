<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'nome_igreja')) {
                $table->string('nome_igreja')->nullable()->after('nome');
            }
            if (!Schema::hasColumn('clientes', 'nome_pastor')) {
                $table->string('nome_pastor')->nullable()->after('nome_igreja');
            }
            if (!Schema::hasColumn('clientes', 'localidade')) {
                $table->string('localidade')->nullable()->after('nome_pastor');
            }
            if (!Schema::hasColumn('clientes', 'moeda')) {
                $table->string('moeda')->default('BRL')->after('localidade');
            }
            if (!Schema::hasColumn('clientes', 'quantidade_membros')) {
                $table->integer('quantidade_membros')->default(0)->after('moeda');
            }
            if (!Schema::hasColumn('clientes', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('contato');
            }
        });

        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'plano')) {
                $table->string('plano')->nullable()->after('status');
            }
            if (!Schema::hasColumn('vendas', 'forma_pagamento')) {
                $table->string('forma_pagamento')->nullable()->after('plano');
            }
            if (!Schema::hasColumn('vendas', 'tipo_negociacao')) {
                $table->string('tipo_negociacao')->default('mensal')->after('forma_pagamento');
            }
            if (!Schema::hasColumn('vendas', 'desconto')) {
                $table->decimal('desconto', 5, 2)->default(0)->after('tipo_negociacao');
            }
            if (!Schema::hasColumn('vendas', 'observacao')) {
                $table->text('observacao')->nullable()->after('desconto');
            }
            if (!Schema::hasColumn('vendas', 'origem')) {
                $table->string('origem')->default('manual')->after('observacao');
            }
            if (!Schema::hasColumn('vendas', 'data_venda')) {
                $table->date('data_venda')->nullable()->after('origem');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['nome_igreja', 'nome_pastor', 'localidade', 'moeda', 'quantidade_membros', 'whatsapp']);
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['plano', 'forma_pagamento', 'tipo_negociacao', 'desconto', 'observacao', 'origem', 'data_venda']);
        });
    }
};
