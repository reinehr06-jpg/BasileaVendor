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
        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'requer_aprovacao')) {
                $table->boolean('requer_aprovacao')->default(false)->after('parcelas');
            }
            if (!Schema::hasColumn('vendas', 'status_aprovacao')) {
                $table->string('status_aprovacao')->default('liberado')->after('requer_aprovacao');
            }
            if (!Schema::hasColumn('vendas', 'aprovado_por')) {
                $table->foreignId('aprovado_por')->nullable()->after('status_aprovacao')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('vendas', 'aprovado_em')) {
                $table->timestamp('aprovado_em')->nullable()->after('aprovado_por');
            }
            if (!Schema::hasColumn('vendas', 'modo_cobranca_asaas')) {
                $table->string('modo_cobranca_asaas')->nullable()->after('modo_cobranca');
            }
            if (!Schema::hasColumn('vendas', 'asaas_subscription_id')) {
                $table->string('asaas_subscription_id')->nullable()->after('modo_cobranca_asaas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn([
                'requer_aprovacao',
                'status_aprovacao',
                'aprovado_em',
                'modo_cobranca_asaas',
                'asaas_subscription_id',
            ]);
            $table->dropForeign(['aprovado_por']);
            $table->dropColumn('aprovado_por');
        });
    }
};
