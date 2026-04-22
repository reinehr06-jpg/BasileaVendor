<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('termos_aceitos')->default(false)->after('email');
            $table->timestamp('termos_aceitos_em')->nullable()->after('termos_aceitos');
            $table->boolean('split_configurado')->default(false)->after('termos_aceitos_em');
            $table->boolean('tour_completo')->default(false)->after('split_configurado');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['termos_aceitos', 'termos_aceitos_em', 'split_configurado', 'tour_completo']);
        });
    }
};