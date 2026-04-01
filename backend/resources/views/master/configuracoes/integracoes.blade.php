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
    </div>

    <!-- Card 4: Integração Basileia Church -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>⛪ Integração Basileia Church</h2>
            <p class="text-muted">Configure a comunicação com o sistema Basileia Church.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.church') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="basileia_church_webhook_url">URL Webhook do Church</label>
                    <input type="url" name="basileia_church_webhook_url" id="basileia_church_webhook_url" class="form-control" value="{{ $churchWebhookUrl }}" placeholder="https://church.basileia.com/api/webhook">
                    <small class="help-text">URL para onde enviaremos os dados de cadastro e status do cliente.</small>
                </div>

                <div class="form-group">
                    <label for="basileia_church_webhook_token">Token de Segurança</label>
                    <input type="text" name="basileia_church_webhook_token" id="basileia_church_webhook_token" class="form-control" value="{{ $churchWebhookToken }}" placeholder="Token de segurança para autenticação">
                    <small class="help-text">Token usado para autenticar requisições do Church.</small>
                </div>
            </div>

            <div class="info-box" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; margin-top: 10px;">
                <h4 style="font-size: 0.9rem; color: #0369a1; margin-bottom: 8px;">🔗 Endpoints disponíveis:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #0c4a6e; font-size: 0.85rem;">
                    <li><strong>GET/POST</strong> <code>/webhook/basileia-church/sync</code> - Verificar status do cliente</li>
                    <li><strong>POST</strong> <code>/webhook/asaas</code> - Receber notificações do Asaas</li>
                </ul>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Configurações do Church</button>
            </div>
        </form>
    </div>

    <!-- Card 5: Google Calendar -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>📅 Google Calendar</h2>
            <p class="text-muted">Configure a integração com Google Calendar para sincronização de eventos de vendas.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.google-calendar') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="google_calendar_client_id">Client ID <span class="required">*</span></label>
                    <input type="text" name="google_calendar_client_id" id="google_calendar_client_id" class="form-control" value="{{ $googleCalendarClientId }}" placeholder="xxxx.apps.googleusercontent.com">
                    <small class="help-text">Client ID obtido no Google Cloud Console.</small>
                </div>

                <div class="form-group">
                    <label for="google_calendar_client_secret">Client Secret <span class="required">*</span></label>
                    <input type="password" name="google_calendar_client_secret" id="google_calendar_client_secret" class="form-control" value="{{ $googleCalendarClientSecret }}" placeholder="GOCSPX-xxxx">
                    <small class="help-text">Client Secret obtido no Google Cloud Console.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="google_calendar_redirect_uri">Redirect URI</label>
                    <input type="url" name="google_calendar_redirect_uri" id="google_calendar_redirect_uri" class="form-control" value="{{ $googleCalendarRedirectUri }}" placeholder="{{ url('/auth/google/callback') }}">
                    <small class="help-text">URL de callback para OAuth2. Use: {{ url('/auth/google/callback') }}</small>
                </div>

                <div class="form-group">
                    <label for="google_calendar_id">Calendar ID</label>
                    <input type="text" name="google_calendar_id" id="google_calendar_id" class="form-control" value="{{ $googleCalendarId }}" placeholder="primary">
                    <small class="help-text">ID do calendário. Use "primary" para o principal.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="google_calendar_ativo" value="1" {{ $googleCalendarAtivo ? 'checked' : '' }}>
                    <span>Ativar Integração com Google Calendar</span>
                </label>
                <small class="help-text">Quando ativado, eventos de vendas serão sincronizados automaticamente.</small>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Google Calendar</button>
            </div>
        </form>
    </div>

    <!-- Card 6: Google Gmail -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>✉️ Google Gmail</h2>
            <p class="text-muted">Configure o envio de emails via API do Gmail para notificações.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.google-gmail') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="google_gmail_client_id">Client ID <span class="required">*</span></label>
                    <input type="text" name="google_gmail_client_id" id="google_gmail_client_id" class="form-control" value="{{ $googleGmailClientId }}" placeholder="xxxx.apps.googleusercontent.com">
                    <small class="help-text">Pode ser o mesmo Client ID do Calendar se estiver no mesmo projeto.</small>
                </div>

                <div class="form-group">
                    <label for="google_gmail_client_secret">Client Secret <span class="required">*</span></label>
                    <input type="password" name="google_gmail_client_secret" id="google_gmail_client_secret" class="form-control" value="{{ $googleGmailClientSecret }}" placeholder="GOCSPX-xxxx">
                    <small class="help-text">Client Secret para autenticação OAuth2 do Gmail.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="google_gmail_redirect_uri">Redirect URI</label>
                    <input type="url" name="google_gmail_redirect_uri" id="google_gmail_redirect_uri" class="form-control" value="{{ $googleGmailRedirectUri }}" placeholder="{{ url('/auth/google-gmail/callback') }}">
                    <small class="help-text">URL de callback para OAuth2 do Gmail.</small>
                </div>

                <div class="form-group">
                    <label for="google_gmail_email">Email Remetente</label>
                    <input type="email" name="google_gmail_email" id="google_gmail_email" class="form-control" value="{{ $googleGmailEmail }}" placeholder="envios@seudominio.com">
                    <small class="help-text">Email que será usado como remetente das notificações.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="google_gmail_ativo" value="1" {{ $googleGmailAtivo ? 'checked' : '' }}>
                    <span>Ativar Integração com Google Gmail</span>
                </label>
                <small class="help-text">Quando ativado, emails serão enviados via API do Gmail em vez do SMTP padrão.</small>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">💾 Salvar Google Gmail</button>
            </div>
        </form>
    </div>

    <!-- Card 7: Status da Integração -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>📊 Status da Integração</h2>
            <p class="text-muted">Verifique se a integração está funcionando corretamente.</p>
        </div>

        <div class="settings-form">
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-label">Ambiente</span>
                    <span class="status-value {{ $asaasEnvironment === 'production' ? 'status-production' : 'status-sandbox' }}">
                        {{ $asaasEnvironment === 'production' ? '🚀 Produção' : '🧪 Sandbox' }}
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">API Key</span>
                    <span class="status-value {{ !empty($asaasApiKey) ? 'status-active' : 'status-inactive' }}">
                        {{ !empty($asaasApiKey) ? '✅ Configurada' : '❌ Não configurada' }}
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Webhook</span>
                    <span class="status-value {{ !empty($asaasWebhookToken) ? 'status-active' : 'status-inactive' }}">
                        {{ !empty($asaasWebhookToken) ? '✅ Configurado' : '❌ Não configurado' }}
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Split Global</span>
                    <span class="status-value {{ $splitGlobalAtivo ? 'status-active' : 'status-inactive' }}">
                        {{ $splitGlobalAtivo ? '✅ Ativo' : '⏸️ Inativo' }}
                    </span>
                </div>
            </div>

            <div class="form-actions border-top" style="margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="testarConexao()">
                    🔍 Testar Conexão
                </button>
            </div>
        </div>
    </div>

    <!-- Card 4: Vendedores com Split -->
    <div class="card settings-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2>👥 Vendedores com Split Configurado</h2>
            <p class="text-muted">Lista de vendedores que possuem walletId e split ativo.</p>
        </div>

        <div class="settings-form">
            @if($vendedoresComSplit->count() > 0)
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Wallet ID</th>
                            <th>Status</th>
                            <th>Tipo Split</th>
                            <th>Comissão Inicial</th>
                            <th>Comissão Recorrência</th>
                            <th>Última Validação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendedoresComSplit as $v)
                        <tr>
                            <td class="font-bold">{{ $v->user->name ?? 'N/A' }}</td>
                            <td><code>{{ substr($v->asaas_wallet_id, 0, 20) }}...</code></td>
                            <td>
                                <span class="badge badge-{{ $v->wallet_status === 'validado' ? 'success' : ($v->wallet_status === 'erro' ? 'danger' : 'warning') }}">
                                    {{ $v->wallet_status }}
                                </span>
                            </td>
                            <td>{{ ucfirst($v->tipo_split) }}</td>
                            <td>{{ $v->comissao_inicial }}%</td>
                            <td>{{ $v->comissao_recorrencia }}%</td>
                            <td>{{ $v->wallet_validado_em ? $v->wallet_validado_em->format('d/m/Y H:i') : 'Nunca' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <p>Nenhum vendedor com split configurado.</p>
                <small class="text-muted">Configure o walletId dos vendedores na aba "Comissões e Repasse" do cadastro de cada vendedor.</small>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .integracoes-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .settings-card {
        padding: 0;
        overflow: hidden;
    }

    .card-header {
        background: #fafafa;
        padding: 24px 30px;
        border-bottom: 1px solid var(--border);
    }
    
    .card-header h2 {
        font-size: 1.25rem;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .settings-form {
        padding: 30px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .required {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(88, 28, 135, 0.1);
    }

    .help-text {
        display: block;
        margin-top: 6px;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .form-actions {
        margin-top: 32px;
        padding-top: 24px;
        display: flex;
        justify-content: flex-end;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: 0.2s;
        font-size: 1rem;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #f1f5f9;
        color: var(--text-main);
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: 600;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
    }

    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-weight: 500;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .border-top {
        border-top: 1px solid var(--border);
    }

    /* Status Grid */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    @media (max-width: 768px) {
        .status-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .status-item {
        background: #f8fafc;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
    }

    .status-label {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .status-value {
        display: block;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .status-active { color: #16a34a; }
    .status-inactive { color: #dc2626; }
    .status-production { color: #2563eb; }
    .status-sandbox { color: #ca8a04; }

    /* Table */
    .table-responsive {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    .report-table th {
        background: #f8fafc;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-size: 0.75rem;
        border-bottom: 1px solid var(--border);
    }

    .report-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .report-table tr:hover td {
        background: #f8fafc;
    }

    .font-bold {
        font-weight: 700;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .badge-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.85rem;
    }
</style>

<script>
function testarConexao() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Testando...';
    btn.disabled = true;

    fetch('{{ route("master.configuracoes.integracoes.testar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Conexão bem-sucedida!\n\n' + data.message);
        } else {
            alert('❌ Falha na conexão!\n\n' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Erro ao testar conexão: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endsection
