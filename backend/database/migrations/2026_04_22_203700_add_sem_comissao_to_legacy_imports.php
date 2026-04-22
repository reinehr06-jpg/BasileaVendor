<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Para Postgres, alterar ENUM via Check Constraint ou recriar coluna
        // O Laravel usa Check Constraints por baixo do pano para enums no Postgres
        
        // 1. Remover a constraint antiga (se existir)
        // O nome padrão do Laravel para enums no Postgres segue o padrão: table_column_check
        try {
            DB::statement('ALTER TABLE legacy_customer_imports DROP CONSTRAINT IF EXISTS legacy_customer_imports_comissao_tipo_check');
        } catch (\Exception $e) {
            // Ignora se não existir
        }

        // 2. Alterar a coluna para aceitar a nova opção (sem_comissao)
        // Vamos transformar em string primeiro para garantir compatibilidade e depois voltar para enum se necessário,
        // mas para resolver o bug agora, string ou enum com a nova constraint resolvem.
        
        DB::statement("ALTER TABLE legacy_customer_imports ALTER COLUMN comissao_tipo TYPE VARCHAR(50)");
        
        // 3. Adicionar a nova constraint incluindo 'sem_comissao'
        DB::statement("ALTER TABLE legacy_customer_imports ADD CONSTRAINT legacy_customer_imports_comissao_tipo_check 
            CHECK (comissao_tipo IN ('inicial', 'recorrencia', 'inicial_antecipada', 'sem_comissao'))");
    }

    public function down(): void
    {
        // Não reverter para não quebrar dados existentes
    }
};
