<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'ai_score')) {
                $table->tinyInteger('ai_score')->nullable()->after('status');
            }
            if (!Schema::hasColumn('leads', 'ai_score_motivo')) {
                $table->string('ai_score_motivo', 200)->nullable()->after('ai_score');
            }
            if (!Schema::hasColumn('leads', 'motivo_perda')) {
                $table->string('motivo_perda', 50)->nullable()->after('ai_score_motivo');
            }
            if (!Schema::hasColumn('leads', 'ai_avaliado_em')) {
                $table->timestamp('ai_avaliado_em')->nullable()->after('motivo_perda');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_score_motivo', 'motivo_perda', 'ai_avaliado_em']);
        });
    }
};