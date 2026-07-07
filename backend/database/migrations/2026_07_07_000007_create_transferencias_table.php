<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->unsignedBigInteger('destino_id')->nullable();
            $table->string('origem_nome')->nullable(); // fallback se n tiver id
            $table->string('destino_nome')->nullable(); // fallback se n tiver id
            $table->decimal('valor', 12, 2);
            $table->decimal('taxa', 12, 2)->default(0);
            $table->string('descricao')->nullable();
            $table->string('status')->default('Concluída'); // Concluída, Pendente
            $table->timestamps();

            $table->foreign('origem_id')->references('id')->on('contas_bancarias')->onDelete('set null');
            $table->foreign('destino_id')->references('id')->on('contas_bancarias')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
