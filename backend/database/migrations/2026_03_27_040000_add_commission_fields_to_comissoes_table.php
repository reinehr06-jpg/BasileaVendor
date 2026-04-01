<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            // Novos campos para o sistema de comissão completo
            $table->foreignId('pagamento_id')->nullable()->after('venda_id')->constrained('pagamentos')->onDelete('set null');
            $table->foreignId('gerente_id')->nullable()->after('vendedor_id')->constrained('users')->onDelete('set null');
            $table->decimal('valor_gerente', 12, 2)->default(0)->after('valor_comissao');
            $table->timestamp('eligible_at')->nullable()->after('status');
            $table->timestamp('released_at')->nullable()->after('eligible_at');
            $table->boolean('paid_via_split')->default(false)->after('released_at');
            $table->string('split_transfer_id')->nullable()->after('paid_via_split');
            $table->decimal('percentual_gerente', 5, 2)->default(0)->after('percentual_aplicado');
            
            // Renomear tipo_comissao para aceitar os novos valores
            // FIRST_PAYMENT, RECURRING (além dos valores existentes)
        });
    }

    public function down(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            $table->dropForeign(['pagamento_id']);
            $table->dropForeign(['gerente_id']);
            $table->dropColumn([
                'pagamento_id', 'gerente_id', 'valor_gerente',
                'eligible_at', 'released_at', 'paid_via_split',
                'split_transfer_id', 'percentual_gerente'
            ]);
        });
    }
};
