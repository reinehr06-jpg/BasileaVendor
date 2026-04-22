<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->text('ia_ultima_analise')->nullable()->after('comissao');
            $table->timestamp('ia_analise_em')->nullable()->after('ia_ultima_analise');
        });
    }

    public function down(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn(['ia_ultima_analise', 'ia_analise_em']);
        });
    }
};