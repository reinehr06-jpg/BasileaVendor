<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legacy_import_id')->constrained('legacy_customer_imports')->cascadeOnDelete();
            $table->string('asaas_payment_id')->nullable();
            $table->string('asaas_subscription_id')->nullable();
            $table->enum('billing_type', ['PIX', 'BOLETO', 'CREDIT_CARD', 'UNDEFINED'])->default('UNDEFINED');
            $table->decimal('value', 12, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('status')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('reference_month')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('asaas_payment_id');
            $table->index('asaas_subscription_id');
            $table->index('reference_month');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_customer_payments');
    }
};
