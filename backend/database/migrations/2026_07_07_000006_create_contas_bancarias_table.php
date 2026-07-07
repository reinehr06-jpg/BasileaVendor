<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo'); // Conta Corrente, Conta Poupança, Caixa Físico
            $table->decimal('saldo', 12, 2)->default(0);
            $table->string('status')->default('ativo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_bancarias');
    }
};
