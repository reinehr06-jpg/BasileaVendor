<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Drop any leftover temp table
            Schema::dropIfExists('aprovacoes_venda_old');

            // Rename current table
            Schema::rename('aprovacoes_venda', 'aprovacoes_venda_old');

            // Create new table with updated enum (no indexes yet to avoid conflicts)
            Schema::create('aprovacoes_venda', function (Blueprint $table) {
                $table->id();
                $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
                $table->string('tipo_aprovacao')->default('DESCONTO');
                $table->decimal('percentual_solicitado', 10, 2)->default(0);
                $table->decimal('valor_solicitado', 10, 2)->nullable();
                $table->decimal('limite_regra', 10, 2)->default(5.00);
                $table->string('status')->default('PENDENTE');
                $table->foreignId('solicitado_por')->constrained('users')->onDelete('cascade');
                $table->foreignId('aprovado_por')->nullable()->constrained('users')->onDelete('set null');
                $table->text('observacao')->nullable();
                $table->text('motivo_rejeicao')->nullable();
                $table->timestamps();
            });

            // Copy data
            DB::statement('INSERT INTO aprovacoes_venda (id, venda_id, tipo_aprovacao, percentual_solicitado, valor_solicitado, limite_regra, status, solicitado_por, aprovado_por, observacao, motivo_rejeicao, created_at, updated_at) SELECT id, venda_id, tipo_aprovacao, percentual_solicitado, valor_solicitado, limite_regra, status, solicitado_por, aprovado_por, observacao, motivo_rejeicao, created_at, updated_at FROM aprovacoes_venda_old');

            // Drop old table
            Schema::dropIfExists('aprovacoes_venda_old');

            // Add indexes after data migration
            Schema::table('aprovacoes_venda', function (Blueprint $table) {
                $table->index(['status', 'created_at']);
                $table->index(['venda_id']);
            });
        } else {
            // For MySQL/PostgreSQL
            DB::statement("ALTER TABLE aprovacoes_venda MODIFY COLUMN tipo_aprovacao ENUM('DESCONTO','REPASSE','EXCECAO_COMERCIAL','VALOR_PERFORMANCE') DEFAULT 'DESCONTO'");
        }
    }

    public function down(): void
    {
        // No rollback needed for this fix
    }
};
