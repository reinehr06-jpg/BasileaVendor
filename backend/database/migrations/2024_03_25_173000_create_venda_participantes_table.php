<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('venda_participantes')) {
            Schema::create('venda_participantes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
                
                // Papel do participante
                $table->enum('papel', ['VENDEDOR', 'SUPERVISOR', 'ADM', 'OUTRO'])->default('VENDEDOR');
                
                // Tipo de repasse
                $table->enum('tipo_repasse', ['PERCENTUAL', 'VALOR_FIXO'])->default('PERCENTUAL');
                $table->decimal('valor_repasse', 10, 4)->default(0); // Percentual ou valor fixo
                
                // Dados do Split Asaas
                $table->string('wallet_id_asaas')->nullable();
                $table->boolean('split_ativo')->default(false);
                $table->string('split_status')->default('pendente'); // pendente, enviado, processado, recusado
                
                $table->timestamps();
                
                $table->index(['venda_id', 'papel']);
                $table->index(['vendedor_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venda_participantes');
    }
};
