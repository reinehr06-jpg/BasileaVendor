<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legacy_customer_payments', function (Blueprint $table) {
            $table->integer('installment_number')->nullable()->after('asaas_subscription_id');
            $table->integer('total_installments')->nullable()->after('installment_number');
            $table->string('payment_method')->nullable()->after('billing_type');
            $table->string('asaas_installment_id')->nullable()->after('asaas_subscription_id');
            
            $table->index('asaas_installment_id');
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
