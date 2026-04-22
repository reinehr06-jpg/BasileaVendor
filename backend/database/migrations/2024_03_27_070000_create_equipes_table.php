<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('equipes')) {
            Schema::create('equipes', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->foreignId('gestor_id')->constrained('users')->onDelete('cascade');
                $table->decimal('meta_mensal', 12, 2)->default(0);
                $table->string('cor', 7)->default('#4C1D95');
                $table->enum('status', ['ativa', 'inativa'])->default('ativa');
                $table->timestamps();

                $table->unique(['gestor_id']);
            });
        }

        Schema::table('vendedores', function (Blueprint $table) {
            if (!Schema::hasColumn('vendedores', 'equipe_id')) {
                $table->foreignId('equipe_id')->nullable()->after('gestor_id')->constrained('equipes')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropForeign(['equipe_id']);
            $table->dropColumn('equipe_id');
        });
        Schema::dropIfExists('equipes');
    }
};
