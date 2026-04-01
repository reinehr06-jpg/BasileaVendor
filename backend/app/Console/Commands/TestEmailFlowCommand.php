<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Setting;
use App\Services\EmailService;
use Illuminate\Console\Command;

class TestEmailFlowCommand extends Command
{
    protected $signature = 'email:test-flow {--force : Recriar dados de teste}';
    protected $description = 'Testa o fluxo completo de email de confirmação';

    public function handle(): int
    {
        $this->info('===========================================');
        $this->info('TESTE - FLUXO DE EMAIL');
        $this->info('===========================================');
        $this->newLine();

        // Criar dados de teste
        $this->info('1. Criando dados de teste...');

        $user = User::firstOrCreate(
            ['email' => 'admin@teste.com'],
            ['name' => 'Admin Teste', 'password' => bcrypt('123456')]
        );

        $vendedor = Vendedor::firstOrCreate(
            ['email' => 'vendedor@teste.com'],
            [
                'user_id' => $user->id,
                'nome' => 'João Vendedor',
                'percentual_comissao' => 10,
                'ativo' => true
            ]
        );

        $cliente = Cliente::firstOrCreate(
            ['email' => 'cliente@igreja.com'],
            [
                'nome' => 'Pedro Silva',
                'nome_igreja' => 'Igreja Batista Central',
                'whatsapp' => '5511999999999',
                'status' => 'ativo'
            ]
        );

        $venda = Venda::updateOrCreate(
            ['id' => 1],
            [
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'plano' => 'Premium',
                'valor' => 297.00,
                'valor_final' => 297.00,
                'tipo_negociacao' => 'mensal',
                'forma_pagamento' => 'pix',
                'parcelas' => 1,
                'status' => 'Pago',
                'data_venda' => now(),
                'email_vendedor_enviado' => false,
                'email_cliente_enviado' => false,
            ]
        );

        $pagamento = Pagamento::updateOrCreate(
            ['id' => 1],
            [
                'venda_id' => $venda->id,
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'asaas_payment_id' => 'test_payment_' . time(),
                'valor' => 297.00,
                'forma_pagamento' => 'pix',
                'forma_pagamento_real' => 'pix',
                'billing_type' => 'PIX',
                'status' => 'RECEIVED',
                'data_pagamento' => now(),
                'data_vencimento' => now()->addDays(7)
            ]
        );

        $this->info("   ✓ Venda #{$venda->id} criada");
        $this->info("   ✓ Pagamento #{$pagamento->id} criado");
        $this->newLine();

        // Configurações
        Setting::set('link_videoaulas', 'https://basileiachurch.com/videos');
        Setting::set('link_termos_pdf', 'https://basileiachurch.com/termos.pdf');
        $this->info('2. Configurações carregadas');
        $this->newLine();

        // Disparar emails
        $this->info('3. Disparando emails...');

        $emailService = new EmailService();
        $emailService->dispararAutomacoes($venda, $pagamento);

        $this->info('   ✓ Jobs dispatchados para a fila');
        $this->newLine();

        $this->info('4. Os jobs foram dispatchados para processamento async');
        $this->newLine();

        // Executar queue sync (para teste imediato)
        $this->info('5. Processando jobs (sync para teste instantâneo)...');
        
        try {
            dispatch_sync(new \App\Jobs\SendEmailVendedorJob($venda, $pagamento));
            $this->info('   ✓ Email vendedor enviado');
        } catch (\Exception $e) {
            $this->error('   ✗ Erro email vendedor: ' . $e->getMessage());
        }

        try {
            dispatch_sync(new \App\Jobs\SendEmailClienteJob($venda, $pagamento));
            $this->info('   ✓ Email cliente enviado');
        } catch (\Exception $e) {
            $this->error('   ✗ Erro email cliente: ' . $e->getMessage());
        }

        $this->newLine();

        // Resultado
        $this->info('===========================================');
        $this->info('RESUMO DO TESTE');
        $this->info('===========================================');
        $this->info("Venda ID: {$venda->id}");
        $this->info("Email Vendedor: {$vendedor->user->email}");
        $this->info("Email Cliente: {$cliente->email}");
        $status = $venda->email_vendedor_enviado ? 'ENVIADO' : 'PENDENTE';
        $this->info("Status Venda: {$status}");
        $this->newLine();

        $this->info('Para ver os emails emitidos:');
        $this->info('  cat storage/logs/laravel.log | grep -i "SendEmail"');
        $this->newLine();

        $this->info('Para usar Mailtrap (emails reais):');
        $this->info('  Edite .env com credenciais SMTP');
        $this->info('  MAIL_MAILER=smtp');
        $this->info('  MAIL_HOST=smtp.mailtrap.io');
        $this->info('  MAIL_PORT=2525');
        $this->newLine();

        return Command::SUCCESS;
    }
}