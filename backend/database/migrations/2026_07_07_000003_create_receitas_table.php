<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receitas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->date('data');
            $table->decimal('valor', 10, 2);
            $table->string('categoria')->nullable();
            $table->string('origem')->nullable();
            $table->string('conta')->nullable();
            $table->string('nf')->nullable();
            $table->string('status')->default('Recebido'); // Recebido, Aguardando recebimento, Parcialmente recebido
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receitas');
    }
};
