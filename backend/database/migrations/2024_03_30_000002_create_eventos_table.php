<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('eventos')) {
            Schema::create('eventos', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('titulo');
                $table->text('descricao')->nullable();
                $table->decimal('valor', 12, 2);
                $table->string('moeda', 3)->default('BRL');
                $table->unsignedInteger('vagas_total');
                $table->unsignedInteger('vagas_ocupadas')->default(0);
                $table->string('whatsapp_vendedor')->nullable();
                $table->string('phone_vendedor')->nullable();
                $table->timestamp('data_inicio')->nullable();
                $table->timestamp('data_fim')->nullable();
                $table->enum('status', ['ativo', 'esgotado', 'expirado'])->default('ativo');
                $table->string('asaas_payment_link_id')->nullable();
                $table->string('checkout_url')->nullable();
                $table->foreignId('created_by')->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
