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
        Schema::create('sysadmin_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->index(); // frontend, backend, database
            $table->string('level')->index(); // error, info, warning
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamps();

            // Filtro por data é o mais usado no painel — índice para não varrer a tabela.
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sysadmin_logs');
    }
};
