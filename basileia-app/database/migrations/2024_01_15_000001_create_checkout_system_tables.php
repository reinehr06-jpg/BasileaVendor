<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de ofertas (planos/produtos)
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('benefits')->nullable();
            $table->decimal('price_brl', 10, 2);
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->decimal('price_eur', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->integer('installments_max')->default(1);
            $table->decimal('installment_value_brl', 10, 2)->nullable();
            $table->string('guarantee_text')->default('7 dias');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabela de leads (captura ANTES do pagamento)
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('document')->nullable()->index();
            $table->string('church_name')->nullable();
            $table->integer('members_count')->nullable();
            $table->string('ip')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('country_name')->nullable();
            $table->string('currency', 3)->default('BRL');
            $table->string('language', 10)->default('pt-BR');
            $table->string('source')->nullable(); // utm_source, etc
            $table->string('campaign')->nullable(); // utm_campaign
            $table->string('referrer')->nullable();
            $table->string('seller_id')->nullable()->index();
            $table->string('seller_name')->nullable();
            $table->enum('status', ['new', 'contacted', 'converted', 'abandoned'])->default('new');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'status']);
            $table->index(['seller_id', 'status']);
        });

        // Tabela de sessões de checkout
        Schema::create('checkout_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('seller_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->text('utm_params')->nullable();
            $table->string('currency', 3)->default('BRL');
            $table->decimal('price_original', 10, 2);
            $table->decimal('price_final', 10, 2);
            $table->decimal('fx_rate', 10, 6)->nullable();
            $table->string('fx_quote_id')->nullable();
            $table->timestamp('fx_locked_until')->nullable();
            $table->text('order_bump')->nullable();
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('language', 10)->default('pt-BR');
            $table->enum('status', ['active', 'processing', 'completed', 'abandoned', 'expired'])->default('active');
            $table->timestamp('identified_at')->nullable();
            $table->timestamp('payment_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'status']);
            $table->index(['lead_id', 'status']);
            $table->index(['expires_at']);
            $table->index(['created_at', 'status']);
        });

        // Tabela de pedidos/orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_number')->unique();
            $table->foreignId('checkout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('seller_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('currency', 3)->default('BRL');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('order_bump_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('fx_rate', 10, 6)->nullable();
            $table->string('payment_method'); // pix, boleto, credit_card
            $table->string('customer_asaas_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->text('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_number']);
            $table->index(['lead_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['campaign_id', 'status']);
            $table->index(['created_at', 'status']);
        });

        // Tabela de pagamentos
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('asaas_payment_id')->nullable()->unique();
            $table->string('asaas_customer_id')->nullable();
            $table->string('currency', 3)->default('BRL');
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_brl', 10, 2)->nullable();
            $table->decimal('fx_rate', 10, 6)->nullable();
            $table->string('billing_type'); // PIX, BOLETO, CREDIT_CARD
            $table->string('payment_method'); // pix, boleto, cartao
            $table->string('invoice_url')->nullable();
            $table->string('bank_slip_url')->nullable();
            $table->string('bank_slip_barcode')->nullable();
            $table->text('pix_qrcode')->nullable();
            $table->text('pix_copy_paste')->nullable();
            $table->string('credit_card_brand')->nullable();
            $table->string('credit_card_last_four')->nullable();
            $table->enum('status', ['pending', 'waiting', 'confirmed', 'received', 'overdue', 'refunded', 'cancelled'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['asaas_payment_id']);
            $table->index(['order_id', 'status']);
            $table->index(['status']);
        });

        // Tabela de eventos de pagamento (webhooks)
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('asaas_event_id')->nullable();
            $table->string('asaas_payment_id')->nullable()->index();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('payload');
            $table->timestamp('processed_at')->nullable();
            $table->string('processing_error')->nullable();
            $table->timestamps();

            $table->unique(['asaas_event_id'], 'payment_events_asaas_event_id_unique');
            $table->index(['asaas_payment_id', 'event_type']);
            $table->index(['processed_at']);
        });

        // Tabela de tracking events
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('event_name')->index();
            $table->string('session_token')->nullable()->index();
            $table->foreignId('checkout_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('seller_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->text('properties')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('landing_page')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['event_name', 'created_at']);
            $table->index(['session_token', 'event_name']);
            $table->index(['campaign_id', 'event_name']);
        });

        // Tabela de cupons
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('uses_count')->default(0);
            $table->text('applicable_offers')->nullable();
            $table->text('applicable_currencies')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('checkout_sessions');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('offers');
    }
};
