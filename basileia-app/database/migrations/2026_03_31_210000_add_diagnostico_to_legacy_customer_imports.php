<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legacy_customer_imports', function (Blueprint $table) {
            // Status diagnóstico calculado pelo sistema (não vem do Asaas)
            if (!Schema::hasColumn('legacy_customer_imports', 'diagnostico_status')) {
                $table->enum('diagnostico_status', [
                    'ATIVO',        // Pagando normalmente
                    'CHURN',        // Já pagou, mas tem cobrança vencida/pendente atual
                    'CANCELADO',    // Nunca pagou ou subscription cancelada
                    'PENDENTE',     // Aguarda atribuição de vendedor / análise
                ])->default('PENDENTE')->after('subscription_status');
            }

            // Flags de histórico de pagamento
            if (!Schema::hasColumn('legacy_customer_imports', 'tem_pagamento_confirmado')) {
                $table->boolean('tem_pagamento_confirmado')->default(false)->after('diagnostico_status');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'tem_pagamento_pendente_atual')) {
                $table->boolean('tem_pagamento_pendente_atual')->default(false)->after('tem_pagamento_confirmado');
            }

            // Datas adicionais de rastreamento
            if (!Schema::hasColumn('legacy_customer_imports', 'ultimo_pagamento_confirmado_at')) {
                $table->date('ultimo_pagamento_confirmado_at')->nullable()->after('ultimo_pagamento_at');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'proximo_vencimento_at')) {
                $table->date('proximo_vencimento_at')->nullable()->after('ultimo_pagamento_confirmado_at');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'dias_sem_pagar')) {
                $table->integer('dias_sem_pagar')->default(0)->after('proximo_vencimento_at');
            }

            // Vínculo com o sistema local (criado após confirmar cliente)
            if (!Schema::hasColumn('legacy_customer_imports', 'local_venda_id')) {
                $table->foreignId('local_venda_id')->nullable()->constrained('vendas')->nullOnDelete()->after('local_cliente_id');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'confirmado_em')) {
                $table->timestamp('confirmado_em')->nullable()->after('local_venda_id');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'confirmado_por')) {
                $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete()->after('confirmado_em');
            }

            $table->index('diagnostico_status');
            $table->index('tem_pagamento_pendente_atual');
        });
    }

    public function down(): void
    {
        Schema::table('legacy_customer_imports', function (Blueprint $table) {
            $table->dropIndex(['diagnostico_status']);
            $table->dropIndex(['tem_pagamento_pendente_atual']);
            $table->dropColumn([
                'diagnostico_status',
                'tem_pagamento_confirmado',
                'tem_pagamento_pendente_atual',
                'ultimo_pagamento_confirmado_at',
                'proximo_vencimento_at',
                'dias_sem_pagar',
                'confirmado_em',
            ]);
            $table->dropForeign(['local_venda_id']);
            $table->dropColumn('local_venda_id');
            $table->dropForeign(['confirmado_por']);
            $table->dropColumn('confirmado_por');
        });
    }
};
