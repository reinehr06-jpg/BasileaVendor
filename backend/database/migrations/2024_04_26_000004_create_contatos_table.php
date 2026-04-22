<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contatos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('documento')->nullable();

            $table->enum('status', ['lead', 'convertido', 'perdido', 'lead_ruim'])->default('lead');
            $table->text('motivo_perda')->nullable();

            $table->foreignId('campanha_id')->nullable()->constrained('campanhas')->nullOnDelete();
            $table->string('canal_origem')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('ref_param')->nullable();
            $table->timestamp('entry_date');

            $table->foreignId('agente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->nullOnDelete();
            $table->foreignId('gestor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('nome_igreja')->nullable();
            $table->string('nome_pastor')->nullable();
            $table->string('nome_responsavel')->nullable();
            $table->string('localidade')->nullable();
            $table->string('moeda')->nullable();
            $table->integer('quantidade_membros')->nullable();
            $table->string('cep')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado')->nullable();
            $table->string('pais')->nullable();

            $table->json('tags')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('cliente_id_legado')->nullable();

            $table->tinyInteger('ai_score')->nullable();
            $table->string('ai_score_motivo')->nullable();
            $table->timestamp('ai_avaliado_em')->nullable();
            $table->text('ai_proxima_acao')->nullable();
            $table->text('ai_observacao')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['campanha_id']);
            $table->index(['canal_origem']);
            $table->index(['agente_id']);
            $table->index(['vendedor_id']);
            $table->index(['entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contatos');
    }
};