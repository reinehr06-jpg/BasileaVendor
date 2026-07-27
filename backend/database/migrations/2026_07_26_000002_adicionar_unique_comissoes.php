<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            $table->unique(['pagamento_id', 'gerente_id'], 'comissoes_pagamento_gerente_unique');
        });
    }

    public function down(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            $table->dropUnique('comissoes_pagamento_gerente_unique');
        });
    }
};
