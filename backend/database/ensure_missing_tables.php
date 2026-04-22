<?php
/**
 * ensure_missing_tables.php
 * 
 * Script que garante que TODAS as tabelas necessárias existam no banco PostgreSQL.
 * Roda DEPOIS do php artisan migrate e cria apenas as que estiverem faltando.
 * Isso contorna problemas de tracking do Laravel migrations.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== Verificando tabelas faltantes ===\n";

// ---- campanhas ----
if (!Schema::hasTable('campanhas')) {
    echo "Criando tabela: campanhas\n";
    Schema::create('campanhas', function (Blueprint $table) {
        $table->id();
        $table->string('nome');
        $table->string('descricao')->nullable();
        $table->string('canal', 50)->default('outro');
        $table->string('status', 20)->default('ativa');
        $table->date('data_inicio')->nullable();
        $table->date('data_fim')->nullable();
        $table->string('utm_source')->nullable();
        $table->string('utm_medium')->nullable();
        $table->string('utm_campaign')->nullable();
        $table->string('utm_content')->nullable();
        $table->string('utm_term')->nullable();
        $table->string('ref_param')->nullable();
        $table->decimal('custo_total', 10, 2)->nullable();
        $table->string('moeda', 3)->default('BRL');
        $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
        $table->timestamps();
        $table->index(['status']);
        $table->index(['utm_campaign']);
        $table->index(['ref_param']);
        $table->index(['canal']);
    });
} else {
    echo "OK: campanhas\n";
}

// ---- contatos ----
if (!Schema::hasTable('contatos')) {
    echo "Criando tabela: contatos\n";
    Schema::create('contatos', function (Blueprint $table) {
        $table->id();
        $table->string('nome');
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('whatsapp')->nullable();
        $table->string('documento')->nullable();
        $table->string('status', 20)->default('lead');
        $table->text('motivo_perda')->nullable();
        $table->foreignId('campanha_id')->nullable()->constrained('campanhas')->nullOnDelete();
        $table->string('canal_origem')->nullable();
        $table->string('utm_source')->nullable();
        $table->string('utm_medium')->nullable();
        $table->string('utm_campaign')->nullable();
        $table->string('utm_content')->nullable();
        $table->string('utm_term')->nullable();
        $table->string('ref_param')->nullable();
        $table->timestamp('entry_date')->nullable();
        $table->foreignId('agente_id')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->nullOnDelete();
        $table->foreignId('gestor_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('nome_igreja')->nullable();
        $table->string('nome_pastor')->nullable();
        $table->string('nome_responsavel')->nullable();
        $table->string('localidade')->nullable();
        $table->string('moeda')->nullable();
        $table->integer('quantidade_membros')->nullable();
        $table->string('cep')->nullable();
        $table->string('endereco')->nullable();
        $table->string('numero')->nullable();
        $table->string('complemento')->nullable();
        $table->string('bairro')->nullable();
        $table->string('cidade')->nullable();
        $table->string('estado')->nullable();
        $table->string('pais')->nullable();
        $table->json('tags')->nullable();
        $table->text('observacoes')->nullable();
        $table->string('cliente_id_legado')->nullable();
        $table->tinyInteger('ai_score')->nullable();
        $table->string('ai_score_motivo')->nullable();
        $table->timestamp('ai_avaliado_em')->nullable();
        $table->text('ai_proxima_acao')->nullable();
        $table->text('ai_observacao')->nullable();
        $table->timestamps();
        $table->softDeletes();
        $table->index(['status', 'created_at']);
        $table->index(['campanha_id']);
        $table->index(['canal_origem']);
        $table->index(['agente_id']);
        $table->index(['vendedor_id']);
        $table->index(['entry_date']);
    });
} else {
    echo "OK: contatos\n";
}

// ---- contato_status_logs ----
if (!Schema::hasTable('contato_status_logs')) {
    echo "Criando tabela: contato_status_logs\n";
    Schema::create('contato_status_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('contato_id')->constrained('contatos')->cascadeOnDelete();
        $table->string('status_anterior')->nullable();
        $table->string('status_novo');
        $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
        $table->text('motivo')->nullable();
        $table->timestamp('created_at')->useCurrent();
        $table->index(['contato_id', 'created_at']);
    });
} else {
    echo "OK: contato_status_logs\n";
}

// ---- primeira_mensagens ----
if (!Schema::hasTable('primeira_mensagens')) {
    echo "Criando tabela: primeira_mensagens\n";
    Schema::create('primeira_mensagens', function (Blueprint $table) {
        $table->id();
        $table->string('titulo');
        $table->text('conteudo');
        $table->string('canal', 50)->nullable();
        $table->boolean('ativo')->default(true);
        $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
        $table->timestamps();
    });
} else {
    echo "OK: primeira_mensagens\n";
}

// ---- calendario_eventos ----
if (!Schema::hasTable('calendario_eventos')) {
    echo "Criando tabela: calendario_eventos\n";
    Schema::create('calendario_eventos', function (Blueprint $table) {
        $table->id();
        $table->string('titulo');
        $table->text('descricao')->nullable();
        $table->timestamp('data_inicio');
        $table->timestamp('data_fim')->nullable();
        $table->boolean('dia_inteiro')->default(false);
        $table->string('tipo', 30)->default('evento');
        $table->string('cor', 7)->default('#4C1D95');
        $table->foreignId('contato_id')->nullable()->constrained('contatos')->nullOnDelete();
        $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
        $table->timestamps();
        $table->index(['usuario_id', 'data_inicio']);
        $table->index(['contato_id']);
    });
} else {
    echo "OK: calendario_eventos\n";
}

// ---- terms_documents ----
if (!Schema::hasTable('terms_documents')) {
    echo "Criando tabela: terms_documents\n";
    Schema::create('terms_documents', function (Blueprint $table) {
        $table->id();
        $table->string('tipo', 30);
        $table->string('titulo');
        $table->text('conteudo');
        $table->string('versao', 20)->default('1.0');
        $table->boolean('ativo')->default(true);
        $table->timestamps();
        $table->index(['tipo', 'ativo']);
    });
} else {
    echo "OK: terms_documents\n";
}

// ---- terms_acceptances ----
if (!Schema::hasTable('terms_acceptances')) {
    echo "Criando tabela: terms_acceptances\n";
    Schema::create('terms_acceptances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('terms_document_id')->constrained('terms_documents')->cascadeOnDelete();
        $table->timestamp('accepted_at');
        $table->string('ip_address')->nullable();
        $table->timestamps();
        $table->unique(['user_id', 'terms_document_id']);
    });
} else {
    echo "OK: terms_acceptances\n";
}

// ---- ai_prompts ----
if (!Schema::hasTable('ai_prompts')) {
    echo "Criando tabela: ai_prompts\n";
    Schema::create('ai_prompts', function (Blueprint $table) {
        $table->id();
        $table->string('nome');
        $table->string('funcao');
        $table->string('cor', 7)->default('#4C1D95');
        $table->text('prompt_personalizado');
        $table->boolean('ativo')->default(true);
        $table->foreignId('criado_por')->constrained('users');
        $table->timestamps();
    });
} else {
    echo "OK: ai_prompts\n";
}

// ---- ai_logs ----
if (!Schema::hasTable('ai_logs')) {
    echo "Criando tabela: ai_logs\n";
    Schema::create('ai_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('tarefa', 50);
        $table->text('input')->nullable();
        $table->text('output')->nullable();
        $table->unsignedSmallInteger('duracao_ms')->nullable();
        $table->boolean('sucesso')->default(true);
        $table->text('erro')->nullable();
        $table->timestamp('executado_em')->nullable();
        $table->index(['tarefa', 'executado_em']);
        $table->index(['user_id', 'executado_em']);
    });
} else {
    echo "OK: ai_logs\n";
}

// ---- ia_evaluations ----
if (!Schema::hasTable('ia_evaluations')) {
    echo "Criando tabela: ia_evaluations\n";
    Schema::create('ia_evaluations', function (Blueprint $table) {
        $table->id();
        $table->string('tipo', 50);
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        $table->json('input_data')->nullable();
        $table->json('output_data')->nullable();
        $table->boolean('sucesso')->default(true);
        $table->text('erro')->nullable();
        $table->unsignedInteger('duracao_ms')->nullable();
        $table->timestamps();
        $table->index(['tipo', 'created_at']);
    });
} else {
    echo "OK: ia_evaluations\n";
}

// ---- login_logs ----
if (!Schema::hasTable('login_logs')) {
    echo "Criando tabela: login_logs\n";
    Schema::create('login_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->string('status', 20)->default('success');
        $table->timestamp('created_at')->useCurrent();
        $table->index(['user_id', 'created_at']);
    });
} else {
    echo "OK: login_logs\n";
}

// ---- lead_inbound_logs ----
if (!Schema::hasTable('lead_inbound_logs')) {
    echo "Criando tabela: lead_inbound_logs\n";
    Schema::create('lead_inbound_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null');
        $table->string('source', 50);
        $table->string('ad_id')->nullable();
        $table->string('adgroup_id')->nullable();
        $table->string('campaign_id')->nullable();
        $table->string('status', 20)->default('pending');
        $table->text('error_message')->nullable();
        $table->timestamps();
        $table->index(['tenant_id', 'source', 'created_at']);
        $table->index(['tenant_id', 'status']);
    });
} else {
    echo "OK: lead_inbound_logs\n";
}

// ---- Adicionar colunas faltantes na tabela leads ----
if (Schema::hasTable('leads')) {
    Schema::table('leads', function (Blueprint $table) {
        if (!Schema::hasColumn('leads', 'tenant_id')) {
            $table->unsignedBigInteger('tenant_id')->nullable();
        }
        if (!Schema::hasColumn('leads', 'vendedor_id')) {
            $table->unsignedBigInteger('vendedor_id')->nullable();
        }
        if (!Schema::hasColumn('leads', 'chat_contact_id')) {
            $table->unsignedBigInteger('chat_contact_id')->nullable();
        }
        if (!Schema::hasColumn('leads', 'cliente_id')) {
            $table->unsignedBigInteger('cliente_id')->nullable();
        }
        if (!Schema::hasColumn('leads', 'message')) {
            $table->text('message')->nullable();
        }
        if (!Schema::hasColumn('leads', 'meta')) {
            $table->json('meta')->nullable();
        }
        if (!Schema::hasColumn('leads', 'utm_source')) {
            $table->string('utm_source')->nullable();
        }
        if (!Schema::hasColumn('leads', 'utm_medium')) {
            $table->string('utm_medium')->nullable();
        }
        if (!Schema::hasColumn('leads', 'utm_campaign')) {
            $table->string('utm_campaign')->nullable();
        }
        if (!Schema::hasColumn('leads', 'utm_content')) {
            $table->string('utm_content')->nullable();
        }
        if (!Schema::hasColumn('leads', 'page_url')) {
            $table->string('page_url')->nullable();
        }
        if (!Schema::hasColumn('leads', 'etapa')) {
            $table->string('etapa', 20)->default('novo');
        }
        if (!Schema::hasColumn('leads', 'agendamento_id')) {
            $table->unsignedBigInteger('agendamento_id')->nullable();
        }
        if (!Schema::hasColumn('leads', 'motivo_perda')) {
            $table->text('motivo_perda')->nullable();
        }
        if (!Schema::hasColumn('leads', 'first_contact_at')) {
            $table->timestamp('first_contact_at')->nullable();
        }
        if (!Schema::hasColumn('leads', 'converted_at')) {
            $table->timestamp('converted_at')->nullable();
        }
        if (!Schema::hasColumn('leads', 'ai_score')) {
            $table->tinyInteger('ai_score')->nullable();
        }
        if (!Schema::hasColumn('leads', 'ai_score_motivo')) {
            $table->string('ai_score_motivo', 200)->nullable();
        }
        if (!Schema::hasColumn('leads', 'ai_avaliado_em')) {
            $table->timestamp('ai_avaliado_em')->nullable();
        }
    });
    echo "OK: leads (colunas verificadas)\n";
}

// ---- Adicionar colunas de onboarding em users ----
if (Schema::hasTable('users')) {
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'onboarding_completed')) {
            $table->boolean('onboarding_completed')->default(false);
        }
        if (!Schema::hasColumn('users', 'onboarding_step')) {
            $table->unsignedTinyInteger('onboarding_step')->default(0);
        }
    });
    echo "OK: users (onboarding verificado)\n";
}

// ---- Adicionar campos IA em vendedores ----
if (Schema::hasTable('vendedores')) {
    Schema::table('vendedores', function (Blueprint $table) {
        if (!Schema::hasColumn('vendedores', 'ia_enabled')) {
            $table->boolean('ia_enabled')->default(false);
        }
        if (!Schema::hasColumn('vendedores', 'ia_auto_score')) {
            $table->boolean('ia_auto_score')->default(false);
        }
    });
    echo "OK: vendedores (IA verificado)\n";
}

echo "=== Todas as tabelas verificadas! ===\n";
