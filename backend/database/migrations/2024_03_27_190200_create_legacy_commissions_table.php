<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('legacy_commissions')) {
            Schema::create('legacy_commissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('legacy_import_id');
                $table->unsignedBigInteger('legacy_payment_id')->nullable();
                $table->unsignedBigInteger('vendedor_id');
                $table->unsignedBigInteger('gestor_id')->nullable();
                $table->unsignedBigInteger('cliente_id');
                $table->enum('commission_type', ['OLD_SALE', 'RECURRING'])->default('RECURRING');
                $table->string('reference_month')->nullable();
                $table->decimal('base_amount', 12, 2)->nullable();
                $table->decimal('seller_commission_amount', 12, 2)->nullable();
                $table->decimal('gestor_commission_amount', 12, 2)->nullable();
                $table->enum('status', [
                    'PENDING_RULE',
                    'PENDING_CONFIRMATION',
                    'GENERATED',
                    'BLOCKED',
                    'PAID',
                    'ERROR'
                ])->default('PENDING_RULE');
                $table->timestamp('generated_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->string('asaas_reference_id')->nullable();
                $table->string('source')->default('LEGACY_IMPORT');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('legacy_import_id');
                $table->index('legacy_payment_id');
                $table->index(['vendedor_id', 'reference_month']);
                $table->index(['gestor_id', 'reference_month']);
                $table->index('status');
                $table->index(['legacy_import_id', 'commission_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_commissions');
    }
};
