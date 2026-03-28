<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar gestor_id na tabela vendedores (quem é o gestor deste vendedor)
        Schema::table('vendedores', function (Blueprint $table) {
            $table->foreignId('gestor_id')->nullable()->after('usuario_id')->constrained('users')->onDelete('set null');
            $table->boolean('is_gestor')->default(false)->after('gestor_id');
            $table->decimal('comissao_gestor_primeira', 12, 2)->default(0)->after('comissao_recorrencia');
            $table->decimal('comissao_gestor_recorrencia', 12, 2)->default(0)->after('comissao_gestor_primeira');
        });

        // Tabela de permissões do gestor
        Schema::create('gestor_permissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('ver_vendas')->default(true);
            $table->boolean('ver_clientes')->default(true);
            $table->boolean('ver_comissoes')->default(true);
            $table->boolean('ver_pagamentos')->default(true);
            $table->boolean('ver_relatorios')->default(true);
            $table->boolean('criar_vendas')->default(true);
            $table->boolean('cancelar_vendas')->default(false);
            $table->boolean('estornar_vendas')->default(false);
            $table->boolean('gerenciar_vendedores')->default(false);
            $table->boolean('ver_configuracoes')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestor_permissoes');
        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropForeign(['gestor_id']);
            $table->dropColumn(['gestor_id', 'is_gestor', 'comissao_gestor_primeira', 'comissao_gestor_recorrencia']);
        });
    }
};
