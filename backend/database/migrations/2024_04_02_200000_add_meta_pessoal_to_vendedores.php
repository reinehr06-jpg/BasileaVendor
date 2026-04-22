<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            if (!Schema::hasColumn('vendedores', 'meta_pessoal')) {
                $table->decimal('meta_pessoal', 12, 2)->default(0)->after('meta_mensal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            if (Schema::hasColumn('vendedores', 'meta_pessoal')) {
                $table->dropColumn('meta_pessoal');
            }
        });
    }
};
