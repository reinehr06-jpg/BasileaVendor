<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE users SET perfil = LOWER(perfil) WHERE perfil IS NOT NULL");
    }

    public function down(): void
    {
        // Not reversible
    }
};
