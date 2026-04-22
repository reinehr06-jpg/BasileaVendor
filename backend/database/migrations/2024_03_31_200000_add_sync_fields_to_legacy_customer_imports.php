<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legacy_customer_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('legacy_customer_imports', 'asaas_subscription_id')) {
                $table->string('asaas_subscription_id')->nullable()->after('asaas_customer_id');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'asaas_subscription_status')) {
                $table->string('asaas_subscription_status')->nullable()->after('asaas_subscription_id');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'asaas_subscription_billing_type')) {
                $table->string('asaas_subscription_billing_type')->nullable()->after('asaas_subscription_id');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'tipo_cobranca')) {
                $table->enum('tipo_cobranca', ['subscription', 'installment', 'avulso'])->default('subscription')->after('asaas_subscription_billing_type');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'parcelas_total')) {
                $table->integer('parcelas_total')->default(1)->after('tipo_cobranca');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'parcelas_pagas')) {
                $table->integer('parcelas_pagas')->default(0)->after('parcelas_total');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'primeiro_pagamento_at')) {
                $table->date('primeiro_pagamento_at')->nullable()->after('parcelas_pagas');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'ultimo_pagamento_at')) {
                $table->date('ultimo_pagamento_at')->nullable()->after('primeiro_pagamento_at');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'valor_plano_mensal')) {
                $table->decimal('valor_plano_mensal', 12, 2)->nullable()->after('ultimo_pagamento_at');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'valor_total_cobranca')) {
                $table->decimal('valor_total_cobranca', 12, 2)->nullable()->after('valor_plano_mensal');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'valor_marco_pago')) {
                $table->decimal('valor_marco_pago', 12, 2)->nullable()->after('valor_total_cobranca');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'comissao_tipo')) {
                $table->enum('comissao_tipo', ['inicial', 'recorrencia', 'inicial_antecipada'])->nullable()->after('valor_marco_pago');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'comissao_vendedor_calculada')) {
                $table->decimal('comissao_vendedor_calculada', 12, 2)->default(0)->after('comissao_tipo');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'comissao_gestor_calculada')) {
                $table->decimal('comissao_gestor_calculada', 12, 2)->default(0)->after('comissao_vendedor_calculada');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'comissao_mes_referencia')) {
                $table->string('comissao_mes_referencia')->nullable()->after('comissao_gestor_calculada');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'comissao_resetada_em')) {
                $table->timestamp('comissao_resetada_em')->nullable()->after('comissao_mes_referencia');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'asaas_synced_at')) {
                $table->timestamp('asaas_synced_at')->nullable()->after('comissao_resetada_em');
            }
            if (!Schema::hasColumn('legacy_customer_imports', 'asaas_sync_error')) {
                $table->string('asaas_sync_error')->nullable()->after('asaas_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('legacy_customer_imports', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_subscription_id', 'asaas_subscription_status', 'asaas_subscription_billing_type',
                'tipo_cobranca', 'parcelas_total', 'parcelas_pagas',
                'primeiro_pagamento_at', 'ultimo_pagamento_at',
                'valor_plano_mensal', 'valor_total_cobranca', 'valor_marco_pago',
                'comissao_tipo', 'comissao_vendedor_calculada', 'comissao_gestor_calculada',
                'comissao_mes_referencia', 'comissao_resetada_em',
                'asaas_synced_at', 'asaas_sync_error',
            ]);
        });
    }
};
