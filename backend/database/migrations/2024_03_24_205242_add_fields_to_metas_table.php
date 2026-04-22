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
        Schema::table('metas', function (Blueprint $table) {
            if (!Schema::hasColumn('metas', 'observacao')) {
                $table->text('observacao')->nullable()->after('valor_meta');
            }
            if (!Schema::hasColumn('metas', 'status')) {
                $table->enum('status', ['não iniciada', 'em andamento', 'atingida', 'não atingida', 'superada'])->default('não iniciada')->after('observacao');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metas', function (Blueprint $table) {
            //
        });
    }
};
