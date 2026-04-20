<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_customer_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('local_cliente_id')->nullable();
            $table->string('local_cliente_cpf_cnpj')->nullable();
            $table->string('asaas_customer_id')->nullable();
            $table->json('asaas_customer_data')->nullable();
            $table->string('nome')->nullable();
            $table->string('documento')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->unsignedBigInteger('plano_id')->nullable();
            $table->decimal('plano_valor_original', 12, 2)->nullable();
            $table->decimal('plano_valor_recorrente', 12, 2)->nullable();
            $table->date('data_venda_original')->nullable();
            $table->enum('customer_status', ['ACTIVE', 'INACTIVE', 'OVERDUE', 'CANCELLED', 'NONE'])->default('NONE');
            $table->enum('subscription_status', ['ACTIVE', 'INACTIVE', 'CANCELLED', 'NONE'])->default('NONE');
            $table->enum('import_status', [
                'PENDING',
                'PROCESSING',
                'IMPORTED',
                'NOT_FOUND',
                'CONFLICT',
                'INVALID_DOCUMENT',
                'NEEDS_REVIEW'
            ])->default('PENDING');
            $table->boolean('generate_old_sale_commission')->default(false);
            $table->boolean('generate_recurring_commission')->default(true);
            $table->unsignedBigInteger('imported_by')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('local_cliente_cpf_cnpj');
            $table->index('asaas_customer_id');
            $table->index('import_status');
            $table->index('customer_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_customer_imports');
    }
};
