<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legacy_customer_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('legacy_customer_payments', 'installment_number')) {
                $table->integer('installment_number')->nullable()->after('asaas_subscription_id');
            }
            if (!Schema::hasColumn('legacy_customer_payments', 'total_installments')) {
                $table->integer('total_installments')->nullable()->after('installment_number');
            }
            if (!Schema::hasColumn('legacy_customer_payments', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('billing_type');
            }
            if (!Schema::hasColumn('legacy_customer_payments', 'asaas_installment_id')) {
                $table->string('asaas_installment_id')->nullable()->after('asaas_subscription_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('legacy_customer_payments', function (Blueprint $table) {
            $table->dropColumn([
                'installment_number',
                'total_installments',
                'payment_method',
                'asaas_installment_id'
            ]);
        });
    }
};
