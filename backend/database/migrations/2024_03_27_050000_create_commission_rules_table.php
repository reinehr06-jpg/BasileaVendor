<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Regras de comissão por plano.
     * Define valores fixos de comissão para vendedor e gerente.
     */
    public function up(): void
    {
        if (!Schema::hasTable('commission_rules')) {
            Schema::create('commission_rules', function (Blueprint $table) {
                $table->id();
                $table->string('plano_nome'); // Nome do plano (Essential, Growth, etc)
                $table->decimal('seller_fixed_value_first_payment', 12, 2)->default(0);
                $table->decimal('seller_fixed_value_recurring', 12, 2)->default(0);
                $table->decimal('manager_fixed_value_first_payment', 12, 2)->default(0);
                $table->decimal('manager_fixed_value_recurring', 12, 2)->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
                
                $table->unique(['plano_nome']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
