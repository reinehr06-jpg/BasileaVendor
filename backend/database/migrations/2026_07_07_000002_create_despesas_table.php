<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor', 10, 2);
            $table->string('categoria')->nullable();
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            $table->string('fornecedor_nome')->nullable();
            $table->string('conta')->nullable();
            $table->string('nf')->nullable();
            $table->string('status')->default('Agendado'); // Agendado, Pago, Vencido
            $table->timestamps();

            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
