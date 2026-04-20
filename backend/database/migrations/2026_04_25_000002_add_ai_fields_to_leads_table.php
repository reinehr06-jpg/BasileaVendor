<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->tinyInteger('ai_score')->nullable()->after('status');
            $table->string('ai_score_motivo', 200)->nullable()->after('ai_score');
            $table->string('motivo_perda', 50)->nullable()->after('ai_score_motivo');
            $table->timestamp('ai_avaliado_em')->nullable()->after('motivo_perda');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_score_motivo', 'motivo_perda', 'ai_avaliado_em']);
        });
    }
};