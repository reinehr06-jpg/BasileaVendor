<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legacy_customer_imports', function (Blueprint $table) {
            // Dados da assinatura/cobrança no Asaas
            $table->string('asaas_subscription_id')->nullable()->after('asaas_customer_id');
            $table->string('asaas_subscription_status')->nullable()->after('asaas_subscription_id');
            $table->string('asaas_subscription_billing_type')->nullable()->after('asaas_subscription_status');
            
            // Tipo de cobrança
            $table->enum('tipo_cobranca', ['subscription', 'installment', 'avulso'])->default('subscription')->after('asaas_subscription_billing_type');
            
            // Dados de parcelamento (cartão)
            $table->integer('parcelas_total')->default(1)->after('tipo_cobranca');
            $table->integer('parcelas_pagas')->default(0)->after('parcelas_total');
            
            // Datas críticas para cálculo de comissão
            $table->date('primeiro_pagamento_at')->nullable()->after('parcelas_pagas');
            $table->date('ultimo_pagamento_at')->nullable()->after('primeiro_pagamento_at');
            
            // Dados financeiros
            $table->decimal('valor_plano_mensal', 12, 2)->nullable()->after('ultimo_pagamento_at'); // Valor mensal base
            $table->decimal('valor_total_cobranca', 12, 2)->nullable()->after('valor_plano_mensal'); // Total Asaas
            $table->decimal('valor_marco_pago', 12, 2)->nullable()->after('valor_total_cobranca'); // Valor pago em Março
            
            // Comissão calculada
            $table->enum('comissao_tipo', ['inicial', 'recorrencia', 'inicial_antecipada'])->nullable()->after('valor_marco_pago');
            $table->decimal('comissao_vendedor_calculada', 12, 2)->default(0)->after('comissao_tipo');
            $table->decimal('comissao_gestor_calculada', 12, 2)->default(0)->after('comissao_vendedor_calculada');
            
            // Controle de reset mensal
            $table->string('comissao_mes_referencia')->nullable()->after('comissao_gestor_calculada'); // "2026-03"
            $table->timestamp('comissao_resetada_em')->nullable()->after('comissao_mes_referencia');
            
            // Sync
            $table->timestamp('asaas_synced_at')->nullable()->after('comissao_resetada_em');
            $table->string('asaas_sync_error')->nullable()->after('asaas_synced_at');
            
            $table->index('tipo_cobranca');
            $table->index('comissao_mes_referencia');
            $table->index('asaas_subscription_id');
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
