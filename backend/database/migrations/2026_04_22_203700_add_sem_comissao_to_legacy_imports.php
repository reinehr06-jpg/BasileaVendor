<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement('ALTER TABLE legacy_customer_imports DROP CONSTRAINT IF EXISTS legacy_customer_imports_comissao_tipo_check');
            } catch (\Exception $e) {}

            DB::statement("ALTER TABLE legacy_customer_imports ALTER COLUMN comissao_tipo TYPE VARCHAR(50)");
            
            DB::statement("ALTER TABLE legacy_customer_imports ADD CONSTRAINT legacy_customer_imports_comissao_tipo_check 
                CHECK (comissao_tipo IN ('inicial', 'recorrencia', 'inicial_antecipada', 'sem_comissao'))");
        } else {
            Schema::table('legacy_customer_imports', function (Blueprint $table) {
                $table->string('comissao_tipo', 50)->change();
            });
        }
    }

    public function down(): void
    {
        // Não reverter para não quebrar dados existentes
    }
};
