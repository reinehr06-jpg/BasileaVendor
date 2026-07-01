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
        Schema::create('asaas_customers', function (Blueprint $table) {
            $table->id();
            $table->string('asaas_customer_id')->unique();
            $table->string('financial_status')->nullable();
            $table->timestamp('first_paid_at')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->json('asaas_raw_data')->nullable();
            $table->timestamps();
        });

        Schema::create('asaas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('asaas_subscription_id')->unique();
            $table->string('asaas_customer_id')->index();
            $table->string('status')->nullable();
            $table->date('next_due_date')->nullable();
            $table->boolean('deleted')->default(false);
            $table->json('asaas_raw_data')->nullable();
            $table->timestamps();
        });

        Schema::create('asaas_payments', function (Blueprint $table) {
            $table->id();
            $table->string('asaas_payment_id')->unique();
            $table->string('asaas_customer_id')->index();
            $table->string('asaas_subscription_id')->nullable()->index();
            $table->string('status')->nullable();
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('client_payment_date')->nullable();
            $table->date('confirmed_date')->nullable();
            $table->boolean('deleted')->default(false);
            $table->boolean('refunded')->default(false);
            $table->json('asaas_raw_data')->nullable();
            $table->timestamps();
        });

        Schema::create('asaas_events', function (Blueprint $table) {
            $table->id();
            $table->string('asaas_event_id')->unique();
            $table->string('event_name');
            $table->json('payload');
            $table->enum('status', ['PENDING', 'DONE'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asaas_events');
        Schema::dropIfExists('asaas_payments');
        Schema::dropIfExists('asaas_subscriptions');
        Schema::dropIfExists('asaas_customers');
    }
};
