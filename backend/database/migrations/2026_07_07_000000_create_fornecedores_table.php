<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('documento')->nullable(); // CNPJ/CPF
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('contato_responsavel')->nullable();
            $table->text('endereco')->nullable();
            $table->string('status')->default('Ativo'); // Ativo / Inativo
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fornecedores');
    }
};
