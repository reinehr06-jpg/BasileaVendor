<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            if (!Schema::hasColumn('comissoes', 'pagamento_id')) {
                $table->foreignId('pagamento_id')->nullable()->after('venda_id')->constrained('pagamentos')->onDelete('set null');
            }
            if (!Schema::hasColumn('comissoes', 'gerente_id')) {
                $table->foreignId('gerente_id')->nullable()->after('vendedor_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('comissoes', 'valor_gerente')) {
                $table->decimal('valor_gerente', 12, 2)->default(0)->after('valor_comissao');
            }
            if (!Schema::hasColumn('comissoes', 'eligible_at')) {
                $table->timestamp('eligible_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('comissoes', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('eligible_at');
            }
            if (!Schema::hasColumn('comissoes', 'paid_via_split')) {
                $table->boolean('paid_via_split')->default(false)->after('released_at');
            }
            if (!Schema::hasColumn('comissoes', 'split_transfer_id')) {
                $table->string('split_transfer_id')->nullable()->after('paid_via_split');
            }
            if (!Schema::hasColumn('comissoes', 'percentual_gerente')) {
                $table->decimal('percentual_gerente', 5, 2)->default(0)->after('percentual_aplicado');
            }
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
