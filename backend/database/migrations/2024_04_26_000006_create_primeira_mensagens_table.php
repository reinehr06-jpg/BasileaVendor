<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('primeira_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('perfil');
            $table->string('titulo');
            $table->text('mensagem');
            $table->boolean('ativa')->default(false);
            $table->enum('status', ['rascunho', 'pendente_aprovacao', 'aprovada', 'rejeitada'])->default('rascunho');
            $table->foreignId('aprovada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejeitada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_rejeicao')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status']);
            $table->unique(['user_id', 'ativa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('primeira_mensagens');
    }
};