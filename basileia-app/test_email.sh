#!/bin/bash

# ==========================================
# GUIA COMPLETO DE TESTE - FLUXO DE EMAIL
# ==========================================

echo "=========================================="
echo "GUIA DE TESTE - FLUXO DE EMAIL"
echo "=========================================="
echo ""

# ------------------------------------------
# PASSO 1: CONFIGURAR AMBIENTE
# ------------------------------------------
echo ">>> PASSO 1: Configurando ambiente..."
echo "Editando .env para usar SMTP (Mailtrap)..."

# Verifica se tem Mailtrap configurado
read -p "Você tem credenciais SMTP (Mailtrap)? (s/n): " tem_smtp

if [ "$tem_smtp" == "s" ]; then
    echo "Configure no .env:"
    echo "MAIL_MAILER=smtp"
    echo "MAIL_HOST=smtp.mailtrap.io"
    echo "MAIL_PORT=2525"  
    echo "MAIL_USERNAME=sua_username"
    echo "MAIL_PASSWORD=sua_password"
    echo "MAIL_FROM_ADDRESS=seu@email.com"
    echo "MAIL_FROM_NAME='Basiléia Church'"
else
    echo "Usando modo LOG - emails serão salvos em logs/"
    echo "Configure no .env se quiser usar SMTP real:"
    echo "MAIL_MAILER=smtp"
    echo "MAIL_HOST=smtp.mailtrap.io"
    echo "MAIL_PORT=2525"
fi
echo ""

# ------------------------------------------
# PASSO 2: RODAR MIGRATIONS
# ------------------------------------------
echo ">>> PASSO 2: Rodando migrations..."
# php artisan migrate:fresh --force
echo ""

# ------------------------------------------
# PASSO 3: CRIAR DADOS DE TESTE
# ------------------------------------------
echo ">>> PASSO 3: Criando dados de teste..."
php artisan tinker --execute="
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Setting;

// Criar usuário admin
\$user = User::firstOrCreate(
    ['email' => 'admin@teste.com'],
    ['name' => 'Admin', 'password' => bcrypt('123456')]
);

// Criar vendedor
\$vendedor = Vendedor::firstOrCreate(
    ['email' => 'vendedor@teste.com'],
    [
        'user_id' => \$user->id,
        'nome' => 'João Vendedor',
        'percentual_comissao' => 10,
        'ativo' => true
    ]
);

// Criar cliente
\$cliente = Cliente::firstOrCreate(
    ['email' => 'cliente@igreja.com'],
    [
        'nome' => 'Pedro Silva',
        'nome_igreja' => 'Igreja Batista Central',
        'whatsapp' => '5511999999999',
        'status' => 'ativo'
    ]
);

// Criar venda
\$venda = Venda::firstOrCreate(
    ['id' => 1],
    [
        'cliente_id' => \$cliente->id,
        'vendedor_id' => \$vendedor->id,
        'plano' => 'Premium',
        'valor' => 297.00,
        'valor_final' => 297.00,
        'tipo_negociacao' => 'mensal',
        'forma_pagamento' => 'pix',
        'parcelas' => 1,
        'status' => 'PAGO',
        'data_venda' => now()
    ]
);

// Criar pagamento confirmado
\$pagamento = Pagamento::firstOrCreate(
    ['id' => 1],
    [
        'venda_id' => \$venda->id,
        'cliente_id' => \$cliente->id,
        'vendedor_id' => \$vendedor->id,
        'asaas_payment_id' => 'test_payment_123',
        'valor' => 297.00,
        'forma_pagamento' => 'pix',
        'forma_pagamento_real' => 'pix',
        'billing_type' => 'PIX',
        'status' => 'RECEIVED',
        'data_pagamento' => now(),
        'data_vencimento' => now()->addDays(7)
    ]
);

// Configurações de email
Setting::set('link_videoaulas', 'https://basileiachurch.com/videos');
Setting::set('link_termos_pdf', 'https://basileiachurch.com/termos.pdf');

echo \"Dados criados com sucesso!\";
echo \"Venda ID: \$venda->id\";
echo \"Pagamento ID: \$pagamento->id\";
echo \"Email vendedor: \$vendedor->user->email\";
echo \"Email cliente: \$cliente->email\";
"
echo ""

# ------------------------------------------
# PASSO 4: DISPARAR TESTE MANUAL
# ------------------------------------------
echo ">>> PASSO 4: Disparando teste..."
echo "Opção 1: Via tinker (recomendado)"
echo "Opção 2: Via endpoint webhook"
echo ""

php artisan tinker --execute="
use App\Models\Venda;
use App\Models\Pagamento;
use App\Services\EmailService;

\$venda = Venda::find(1);
\$pagamento = Pagamento::find(1);

\$emailService = new EmailService();
\$emailService->dispararAutomacoes(\$venda, \$pagamento);

echo 'Emails dispatchados para a fila!';
"
echo ""

# ------------------------------------------
# PASSO 5: VERIFICAR QUEUE
# ------------------------------------------
echo ">>> PASSO 5: Processando fila..."
echo "Rode em outro terminal: php artisan queue:work"
echo ""

# ------------------------------------------
# PASSO 6: VERIFICAR LOGS
# ------------------------------------------
echo ">>> PASSO 6: VerificandoLogs..."
echo "Local do log: storage/logs/laravel.log"
echo ""
echo "Procure por:"
echo "  - 'SendEmailVendedorJob'"
echo "  - 'SendEmailClienteJob'"
echo "  - 'EmailService'"
echo ""

echo "=========================================="
echo "COMANDOS RESUMO:"
echo "=========================================="
echo ""
echo "# Terminal 1 - Servidor"
echo "./serve.sh"
echo ""
echo "# Terminal 2 - Queue Worker"  
echo "php artisan queue:work --sleep=3 --tries=3"
echo ""
echo "# Terminal 3 - Verificar Jobs"
echo "php artisan queue:restart"
echo "php artisan queue:failed-list"
echo ""
echo "# Verificar emails logados"
echo "tail -f storage/logs/laravel.log | grep -i email"
echo ""
echo "=========================================="