@extends('layouts.app')

@section('title', 'Configurações de Integrações')

@section('content')
<div class="integracoes-container">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Card 1: Configurações da API -->
    <div class="card settings-card">
        <div class="card-header">
            <h2>🔑 Asaas - Configuração da API</h2>
            <p class="text-muted">Configure as credenciais de acesso para a API do Asaas.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.update') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="asaas_environment">Ambiente de Execução <span class="required">*</span></label>
                    <select name="asaas_environment" id="asaas_environment" class="form-control" required>
                        <option value="sandbox" {{ $asaasEnvironment === 'sandbox' ? 'selected' : '' }}>🧪 Sandbox (Testes)</option>
                        <option value="production" {{ $asaasEnvironment === 'production' ? 'selected' : '' }}>🚀 Produção (Real)</option>
                    </select>
                    <small class="help-text">Define para qual URL as requisições serão enviadas.</small>
                </div>

                <div class="form-group">
                    <label for="asaas_api_key">API Key <span class="required">*</span></label>
                    <input type="password" name="asaas_api_key" id="asaas_api_key" class="form-control" value="{{ $asaasApiKey }}" required placeholder="$aact_YTU5YTE0M2M...">
                    <small class="help-text">Chave de acesso gerada no painel do Asaas.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="asaas_webhook_token">Webhook Token <span class="required">*</span></label>
                    <input type="password" name="asaas_webhook_token" id="asaas_webhook_token" class="form-control" value="{{ $asaasWebhookToken }}" required placeholder="Token de segurança do webhook">
                    <small class="help-text">Token para validar eventos de pagamento recebidos.</small>
                </div>

                <div class="form-group">
                    <label for="asaas_callback_url">URL de Callback</label>
                    <input type="url" name="asaas_callback_url" id="asaas_callback_url" class="form-control" value="{{ $asaasCallbackUrl }}" placeholder="https://seudominio.com/callback">
                    <small class="help-text">URL para redirecionamento após pagamento.</small>
                </div>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Configurações da API</button>
            </div>
        </form>

        <!-- Teste Asaas -->
        <div class="mt-4 pt-3 border-t border-border/20 text-center">
            <button type="button" 
                    id="test-asaas-btn"
                    onclick="testIntegration('asaas')" 
                    class="btn btn-outline-primary btn-sm">
                🧪 Testar Conexão Asaas
            </button>
            <span id="asaas-test-result" class="ml-2 text-sm"></span>
        </div>
    </div>

    <!-- Card 2: Configurações de Split -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>💰 Configurações de Split</h2>
            <p class="text-muted">Configure as regras globais de split de pagamentos com vendedores.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.split') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="asaas_split_global_ativo" value="1" {{ $splitGlobalAtivo ? 'checked' : '' }}>
                    <span>Ativar Split Global no Sistema</span>
                </label>
                <small class="help-text">Quando ativado, os vendedores com walletId configurado receberão repasse automático.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="asaas_juros_padrao">Juros Padrão (% ao mês)</label>
                    <input type="number" step="0.01" name="asaas_juros_padrao" id="asaas_juros_padrao" class="form-control" value="{{ $jurosPadrao }}" min="0" max="100" placeholder="0">
                    <small class="help-text">Percentual de juros aplicado em cobranças vencidas.</small>
                </div>

                <div class="form-group">
                    <label for="asaas_multa_padrao">Multa Padrão (%)</label>
                    <input type="number" step="0.01" name="asaas_multa_padrao" id="asaas_multa_padrao" class="form-control" value="{{ $multaPadrao }}" min="0" max="100" placeholder="0">
                    <small class="help-text">Percentual de multa aplicado em cobranças vencidas.</small>
                </div>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Configurações de Split</button>
            </div>
        </form>
    </div>

    <!-- Card 3: Configurações de Email -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>📧 Configurações de Email</h2>
            <p class="text-muted">Configure os emails que serão usados para envio de notificações.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.email') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="email_vendedor_from">Email do Sistema (Remetente)</label>
                    <input type="email" name="email_vendedor_from" id="email_vendedor_from" class="form-control" value="{{ $emailVendedorFrom }}" placeholder="vendas@basileiavendas.com">
                    <small class="help-text">Email usado como remetente para vendedores.</small>
                </div>

                <div class="form-group">
                    <label for="email_cliente_from">Email do Cliente (Remetente)</label>
                    <input type="email" name="email_cliente_from" id="email_cliente_from" class="form-control" value="{{ $emailClienteFrom }}" placeholder="suporte@basileiachurch.com">
                    <small class="help-text">Email usado como remetente para clientes.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email_suporte">Email de Suporte</label>
                    <input type="email" name="email_suporte" id="email_suporte" class="form-control" value="{{ $emailSuporte }}" placeholder="suporte@basileiachurch.com">
                    <small class="help-text">Email exibido no rodapé dos emails enviados.</small>
                </div>

                <div class="form-group">
                    <label for="whatsapp_suporte">WhatsApp Suporte</label>
                    <input type="text" name="whatsapp_suporte" id="whatsapp_suporte" class="form-control" value="{{ $whatsappSuporte }}" placeholder="+5511999999999">
                    <small class="help-text">Número do WhatsApp para link de suporte no email do cliente.</small>
                </div>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Configurações de Email</button>
            </div>
        </form>

        <!-- Teste Email -->
        <div class="mt-4 pt-3 border-t border-border/20">
            <div class="flex gap-2 items-center">
                <input type="email" id="test_email" class="form-control input-sm" placeholder="email@teste.com" style="width: 200px;">
                <button type="button" 
                        onclick="testEmailIntegration()" 
                        class="btn btn-outline-primary btn-sm">
                    🧪 Testar Email
                </button>
            </div>
            <span id="email-test-result" class="ml-2 text-sm"></span>
        </div>
    </div>

    <!-- Card 4: Integração Basileia Church -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>⛪ Basileia Church Webhook</h2>
            <p class="text-muted">Configure o webhook para sincronização com Basileia Church.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.church') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="basileia_church_webhook_url">Webhook URL</label>
                    <input type="url" name="basileia_church_webhook_url" id="basileia_church_webhook_url" class="form-control" value="{{ $churchWebhookUrl }}" placeholder="https://basileia.global/webhook">
                    <small class="help-text">URL do webhook Basileia Church.</small>
                </div>

                <div class="form-group">
                    <label for="basileia_church_webhook_token">Webhook Token</label>
                    <input type="password" name="basileia_church_webhook_token" id="basileia_church_webhook_token" class="form-control" value="{{ $churchWebhookToken }}" placeholder="Token de segurança">
                    <small class="help-text">Token para validar requisições.</small>
                </div>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Webhook</button>
            </div>
        </form>

        <!-- Teste Church Webhook -->
        <div class="mt-4 pt-3 border-t border-border/20 text-center">
            <button type="button" 
                    id="test-church-btn"
                    onclick="testIntegration('church')" 
                    class="btn btn-outline-primary btn-sm">
                🧪 Testar Webhook Church
            </button>
            <span id="church-test-result" class="ml-2 text-sm"></span>
        </div>
    </div>

    <!-- Card 4.1: Comercialização e Self-Service -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>🏷️ Comercialização e Self-Service</h2>
            <p class="text-muted">Personalize os termos do sistema e configure webhooks adicionais de ativação.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.commercial') }}" method="POST" class="settings-form">
            @csrf

            <h4 class="mt-3 mb-2" style="font-size: 0.9rem; color: var(--primary);">Nomes Dinâmicos (Labels)</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="label_church">Nome da "Igreja" (Singular)</label>
                    <input type="text" name="label_church" id="label_church" class="form-control" value="{{ $labelChurch }}" placeholder="Ex: Igreja, Empresa, Escola">
                </div>

                <div class="form-group">
                    <label for="label_organization">Nome da "Organização"</label>
                    <input type="text" name="label_organization" id="label_organization" class="form-control" value="{{ $labelOrganization }}" placeholder="Ex: Igreja/Empresa">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="label_pastor">Nome do "Pastor/Líder"</label>
                    <input type="text" name="label_pastor" id="label_pastor" class="form-control" value="{{ $labelPastor }}" placeholder="Ex: Pastor, Diretor, Gerente">
                </div>

                <div class="form-group">
                    <label for="label_member">Nome do "Membro/Usuário"</label>
                    <input type="text" name="label_member" id="label_member" class="form-control" value="{{ $labelMember }}" placeholder="Ex: Membro, Funcionário, Aluno">
                </div>
            </div>

            <h4 class="mt-4 mb-2" style="font-size: 0.9rem; color: var(--primary);">Webhooks de Ativação Secundários</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="external_webhook_financeiro_url">Webhook Financeiro (URL)</label>
                    <input type="url" name="external_webhook_financeiro_url" id="external_webhook_financeiro_url" class="form-control" value="{{ $financeiroWebhookUrl }}" placeholder="https://financeiro.seuapp.com/webhook">
                    <small class="help-text">URL para notificar sistemas de faturamento externos.</small>
                </div>

                <div class="form-group">
                    <label for="external_webhook_financeiro_token">Webhook Financeiro (Token)</label>
                    <input type="password" name="external_webhook_financeiro_token" id="external_webhook_financeiro_token" class="form-control" value="{{ $financeiroWebhookToken }}" placeholder="Token de segurança">
                </div>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Configurações Comerciais</button>
            </div>
        </form>
    </div>

    <!-- Card 5: Google Calendar -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>📅 Google Calendar</h2>
            <p class="text-muted">Configure integração com Google Calendar para agendamentos.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.google-calendar') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="google_calendar_client_id">Client ID</label>
                    <input type="text" name="google_calendar_client_id" id="google_calendar_client_id" class="form-control" value="{{ $googleCalendarClientId }}" placeholder="seu-client-id.apps.googleusercontent.com">
                </div>

                <div class="form-group">
                    <label for="google_calendar_client_secret">Client Secret</label>
                    <input type="password" name="google_calendar_client_secret" id="google_calendar_client_secret" class="form-control" value="{{ $googleCalendarClientSecret }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="google_calendar_redirect_uri">Redirect URI</label>
                    <input type="url" name="google_calendar_redirect_uri" id="google_calendar_redirect_uri" class="form-control" value="{{ $googleCalendarRedirectUri }}">
                </div>

                <div class="form-group">
                    <label for="google_calendar_id">Calendar ID</label>
                    <input type="text" name="google_calendar_id" id="google_calendar_id" class="form-control" value="{{ $googleCalendarId }}" placeholder="primary">
                </div>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Google Calendar</button>
            </div>
        </form>

        <!-- Teste Google Calendar -->
        <div class="mt-4 pt-3 border-t border-border/20 text-center">
            <button type="button" 
                    id="test-calendar-btn"
                    onclick="testIntegration('calendar')" 
                    class="btn btn-outline-primary btn-sm">
                🧪 Testar Google Calendar
            </button>
            <span id="calendar-test-result" class="ml-2 text-sm"></span>
        </div>
    </div>

    <!-- Card 6: OpenAI -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>🤖 OpenAI (IA Cloud)</h2>
            <p class="text-muted">Configure a API da OpenAI para inteligência artificial.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.ia') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="openai_api_key">OpenAI API Key</label>
                    <input type="password" name="openai_api_key" id="openai_api_key" class="form-control" value="{{ $openaiApiKey }}" placeholder="sk-...">
                </div>

                <div class="form-group">
                    <label for="ia_provider">Provedor de IA</label>
                    <select name="ia_provider" id="ia_provider" class="form-control">
                        <option value="ollama" {{ $iaProvider === 'ollama' ? 'selected' : '' }}>Ollama (Local)</option>
                        <option value="openai" {{ $iaProvider === 'openai' ? 'selected' : '' }}>OpenAI (Cloud)</option>
                    </select>
                </div>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Configurações IA</button>
            </div>
        </form>

        <!-- Teste OpenAI -->
        <div class="mt-4 pt-3 border-t border-border/20 text-center">
            <button type="button" 
                    id="test-openai-btn"
                    onclick="testIntegration('openai')" 
                    class="btn btn-outline-primary btn-sm">
                🧪 Testar OpenAI
            </button>
            <span id="openai-test-result" class="ml-2 text-sm"></span>
        </div>
    </div>

    <!-- Card 7: Ollama (IA Local) -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>🦙 Ollama (IA Local)</h2>
            <p class="text-muted">Configure endpoint para IA local (Ollama).</p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ia_local_endpoint">Endpoint URL</label>
                <input type="url" name="ia_local_endpoint" id="ia_local_endpoint" class="form-control" value="{{ $iaLocalEndpoint }}" placeholder="http://localhost:11434/api/generate">
            </div>

            <div class="form-group">
                <label for="ia_local_model">Modelo</label>
                <input type="text" name="ia_local_model" id="ia_local_model" class="form-control" value="{{ $iaLocalModel }}" placeholder="llama3.2">
            </div>
        </div>

        <!-- Teste Ollama -->
        <div class="mt-4 pt-3 border-t border-border/20 text-center">
            <button type="button" 
                    id="test-ollama-btn"
                    onclick="testIntegration('ollama')" 
                    class="btn btn-outline-primary btn-sm">
                🧪 Testar Ollama
            </button>
            <span id="ollama-test-result" class="ml-2 text-sm"></span>
        </div>
    </div>

    <!-- Card 8: Teste Completo -->
    <div class="card settings-card mt-4">
        <div class="card-header">
            <h2>🔬 Teste Completo das Integrações</h2>
            <p class="text-muted">Execute todos os testes de uma vez para verificar o status geral.</p>
        </div>
        <div class="card-body text-center">
            <button type="button" 
                    id="test-all-btn"
                    onclick="testAllIntegrations()" 
                    class="btn btn-primary btn-lg">
                🚀 Testar Todas as Integrações
            </button>
            <div id="all-tests-results" class="mt-4 text-left"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function testIntegration(service) {
    const btn = document.getElementById(`test-${service}-btn`);
    const resultSpan = document.getElementById(`${service}-test-result`);
    
    if (!btn || !resultSpan) return;
    
    btn.disabled = true;
    btn.innerHTML = '🔄 Testando...';
    resultSpan.innerHTML = '';
    
    try {
        const response = await fetch(`/master/configuracoes/integracoes/test/${service}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultSpan.innerHTML = `<span class="text-emerald-600 font-bold">✅ ${data.message}</span>`;
        } else {
            resultSpan.innerHTML = `<span class="text-red-600 font-bold">❌ ${data.message}</span>`;
        }
    } catch (error) {
        resultSpan.innerHTML = `<span class="text-red-600 font-bold">❌ Erro: ${error.message}</span>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = `🧪 Testar ${service.toUpperCase()}`;
    }
}

async function testEmailIntegration() {
    const email = document.getElementById('test_email')?.value;
    const btn = document.getElementById('test-email-btn');
    const resultSpan = document.getElementById('email-test-result');
    
    if (!email) {
        resultSpan.innerHTML = '<span class="text-red-600">⚠️ Informe um email</span>';
        return;
    }
    
    if (btn) btn.disabled = true;
    if (btn) btn.innerHTML = '🔄 Enviando...';
    resultSpan.innerHTML = '';
    
    try {
        const response = await fetch('/master/configuracoes/integracoes/test/email', {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email_teste: email })
        });
        
        const data = await response.json();
        resultSpan.innerHTML = data.success 
            ? `<span class="text-emerald-600 font-bold">✅ ${data.message}</span>`
            : `<span class="text-red-600 font-bold">❌ ${data.message}</span>`;
    } catch (error) {
        resultSpan.innerHTML = `<span class="text-red-600 font-bold">❌ ${error.message}</span>`;
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '🧪 Testar Email';
        }
    }
}

async function testAllIntegrations() {
    const btn = document.getElementById('test-all-btn');
    btn.disabled = true;
    btn.innerHTML = '🔄 Testando Tudo...';
    
    try {
        const response = await fetch('/master/configuracoes/integracoes/test/all', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await response.json();
        const results = document.getElementById('all-tests-results');
        
        let html = '<div class="space-y-2">';
        html += '<h4 class="font-bold mb-2">Resumo dos Testes</h4>';
        
        for (const [service, result] of Object.entries(data.tests)) {
            const icon = result.success ? '✅' : '❌';
            const bgClass = result.success ? 'bg-emerald-50' : 'bg-red-50';
            const textClass = result.success ? 'text-emerald-700' : 'text-red-700';
            html += `<div class="p-3 rounded ${bgClass} border">
                <div class="flex items-center justify-between">
                    <span class="font-bold uppercase">${service}</span>
                    <span class="${textClass}">${icon} ${result.message}</span>
                </div>
            </div>`;
        }
        
        html += `<div class="mt-3 p-3 rounded bg-blue-50 border border-blue-200">
            <strong>Total:</strong> ${data.summary.success}/${data.summary.total} sucessos
        </div>`;
        html += '</div>';
        
        results.innerHTML = html;
    } catch (error) {
        document.getElementById('all-tests-results').innerHTML = 
            `<div class="text-red-600">Erro ao executar testes: ${error.message}</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🚀 Testar Todas as Integrações';
    }
}
</script>
@endpush

@endsection
