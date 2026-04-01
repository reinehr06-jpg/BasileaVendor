<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('asaas_card_id');
            $table->string('brand', 50);
            $table->string('last4', 4);
            $table->string('holder_name');
            $table->string('expiry_month', 2);
            $table->string('expiry_year', 4);
            $table->text('token');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->index(['lead_id', 'status']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('subscription_card_id')->nullable();
            $table->unsignedBigInteger('offer_id');
            $table->string('asaas_subscription_id')->nullable();
            $table->enum('billing_type', ['monthly', 'yearly']);
            $table->decimal('amount', 10, 2);
            $table->date('start_date');
            $table->date('next_billing_date')->nullable();
            $table->date('last_billing_date')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->integer('total_invoices_generated')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('subscription_card_id')->references('id')->on('subscription_cards')->onDelete('set null');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
            $table->index(['lead_id', 'status']);
            $table->index('next_billing_date');
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('asaas_payment_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->index(['subscription_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_cards');
    }
};
