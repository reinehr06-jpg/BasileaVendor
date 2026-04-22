<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('calendario_eventos')) {
            Schema::create('calendario_eventos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('tipo', ['follow_up', 'reuniao', 'lembrete', 'vencimento']);
                $table->string('titulo');
                $table->text('descricao')->nullable();
                $table->dateTime('data_hora_inicio');
                $table->dateTime('data_hora_fim')->nullable();
                $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
                $table->foreignId('contato_id')->nullable()->constrained('contatos')->nullOnDelete();
                $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->nullOnDelete();
                $table->json('recorrencia')->nullable();
                $table->string('google_event_id')->nullable()->unique();
                $table->enum('status', ['agendado', 'concluido', 'cancelado', 'faltou'])->default('agendado');
                $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
                $table->timestamp('notificado_em')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'data_hora_inicio']);
                $table->index(['status', 'data_hora_inicio']);
                $table->index(['contato_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_eventos');
    }
};