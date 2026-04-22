<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanhas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->enum('canal', [
                'meta_ads', 'google_ads', 'whatsapp_link',
                'instagram', 'tiktok_ads', 'formulario_web',
                'landing_page', 'organico', 'importacao', 'outro'
            ]);
            $table->enum('status', ['ativa', 'pausada', 'encerrada'])->default('ativa');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();

            // Rastreamento UTM
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable(); // chave principal de match
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('ref_param')->nullable(); // para links ?ref=nome-campanha

            // Custo (opcional para CPL)
            $table->decimal('custo_total', 10, 2)->nullable();
            $table->string('moeda', 3)->default('BRL');

            // Dono
            $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['utm_campaign']);
            $table->index(['ref_param']);
                $table->index(['canal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanhas');
    }
};
