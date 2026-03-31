@extends('layouts.app')
@section('title', 'Configurações do Sistema')

@section('content')
<style>
    /* Design System - Materio Premium */
    :root {
        --materio-primary: #9155FD;
        --materio-primary-light: #F4EFFF;
        --materio-secondary: #8A8D93;
        --materio-bg: #F4F5FA;
        --materio-surface: #FFFFFF;
        --materio-text-main: #4D5156;
        --materio-text-muted: #89898E;
        --materio-border: #E6E6E9;
        --materio-shadow: 0 4px 18px 0 rgba(0,0,0,0.1);
        --materio-radius: 10px;
        --materio-success: #56CA00;
        --materio-info: #16B1FF;
        --materio-warning: #FFB400;
        --materio-error: #FF4C51;
    }

    .settings-page {
        animation: fadeIn 0.4s ease-out;
        max-width: 1240px;
        margin: 0 auto;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Nav Tabs */
    .materio-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--materio-border);
        padding-bottom: 2px;
        overflow-x: auto;
    }

    .materio-tab-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 24px;
        border: none;
        background: transparent;
        color: var(--materio-text-muted);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        border-radius: 8px 8px 0 0;
        white-space: nowrap;
        position: relative;
    }

    .materio-tab-btn i { font-size: 1.1rem; }
    .materio-tab-btn:hover { color: var(--materio-primary); background: rgba(145, 85, 253, 0.05); }
    .materio-tab-btn.active { color: var(--materio-primary); }
    .materio-tab-btn.active::after {
        content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px;
        background: var(--materio-primary); border-radius: 3px 3px 0 0;
    }

    /* Content Cards */
    .materio-card {
        background: var(--materio-surface);
        border-radius: var(--materio-radius);
        box-shadow: var(--materio-shadow);
        padding: 30px;
        border: 1px solid var(--materio-border);
        margin-bottom: 24px;
    }

    .section-header {
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--materio-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header h4 {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--materio-text-main);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .section-subtitle {
        color: var(--materio-text-muted);
        font-size: 0.85rem;
        margin-top: 4px;
    }

    /* Forms */
    .materio-form-group { margin-bottom: 20px; }
    .materio-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--materio-text-main);
        margin-bottom: 6px;
    }

    .required { color: var(--materio-error); margin-left: 2px; }

    .help-text { font-size: 0.75rem; color: var(--materio-text-muted); margin-top: 4px; display: block; }

    .materio-input, .materio-select, .materio-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--materio-border);
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
        background: #fff;
    }

    .materio-input:focus, .materio-select:focus { border-color: var(--materio-primary); box-shadow: 0 0 0 3px rgba(145, 85, 253, 0.1); }

    .materio-btn-primary {
        background: var(--materio-primary); color: white; border: none; padding: 10px 24px;
        border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(145, 85, 253, 0.2);
    }

    .materio-btn-primary:hover { background: #8043e6; transform: translateY(-1px); }

    .materio-btn-outline {
        background: transparent; color: var(--materio-primary); border: 1.5px solid var(--materio-primary);
        padding: 8px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;
    }

    .materio-btn-outline:hover { background: rgba(145, 85, 253, 0.05); }

    /* Grid */
    .materio-row { display: flex; flex-wrap: wrap; gap: 20px; }
    .materio-col-6 { flex: 1; min-width: 300px; }
    .materio-col-4 { width: calc(33.33% - 14px); min-width: 250px; }
    .materio-col-12 { width: 100%; }

    /* Custom Checkbox Switch */
    .materio-switch {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 8px 0;
    }
    .switch-input { display: none; }
    .switch-slider {
        width: 38px; height: 20px; background: #BDC3C7; border-radius: 20px;
        position: relative; transition: 0.3s;
    }
    .switch-slider::before {
        content: ''; position: absolute; width: 14px; height: 14px; background: #fff;
        border-radius: 50%; top: 3px; left: 3px; transition: 0.3s;
    }
    .switch-input:checked + .switch-slider { background: var(--materio-primary); }
    .switch-input:checked + .switch-slider::before { transform: translateX(18px); }

    /* Stats Grid */
    .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 30px; }
    .status-item { background: var(--materio-bg); padding: 18px; border-radius: 12px; text-align: center; border: 1px solid var(--materio-border); }
    .status-label { font-size: 0.7rem; color: var(--materio-text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; }
    .status-value { font-size: 1.25rem; font-weight: 800; color: var(--materio-text-main); }
    .status-val-active { color: var(--materio-success); }
    .status-val-inactive { color: var(--materio-error); }

    /* Info Box */
    .materio-info-box {
        background: #F0F4FF; border: 1px solid #D6E1FF; padding: 16px; border-radius: 8px; margin-top: 15px;
    }
    .materio-info-box h5 { color: #025091; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; }
    .materio-info-box ul { list-style: none; padding: 0; margin: 0; font-size: 0.82rem; color: #023D70; }
    .materio-info-box li { margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }

    /* Table Styles */
    .table-container { overflow-x: auto; border: 1px solid var(--materio-border); border-radius: 8px; }
    .materio-table { width: 100%; border-collapse: collapse; background: #fff; }
    .materio-table th { background: #F9FAFB; padding: 12px 16px; text-align: left; font-size: 0.75rem; color: var(--materio-text-muted); font-weight: 800; text-transform: uppercase; border-bottom: 1px solid var(--materio-border); }
    .materio-table td { padding: 14px 16px; border-bottom: 1px solid var(--materio-border); font-size: 0.88rem; color: var(--materio-text-main); }
    .materio-table tr:hover { background: rgba(145, 85, 253, 0.02); }

    .badge { padding: 4px 10px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; }
    .bg-soft-primary { background: rgba(145, 85, 253, 0.1); color: var(--materio-primary); }
    .bg-soft-success { background: rgba(86, 202, 0, 0.1); color: var(--materio-success); }
    .bg-soft-danger { background: rgba(255, 76, 81, 0.1); color: var(--materio-error); }
    .bg-soft-warning { background: rgba(255, 180, 0, 0.1); color: var(--materio-warning); }

    /* Filter Bar */
    .filter-bar { background: #F9FAFB; padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--materio-border); }

    .action-btn {
        width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px; border: 1px solid var(--materio-border); color: var(--materio-text-muted);
        transition: 0.2s; cursor: pointer; background: #fff;
    }
    .action-btn:hover { color: var(--materio-primary); border-color: var(--materio-primary); background: var(--materio-primary-light); }
    .action-danger:hover { color: var(--materio-error); border-color: var(--materio-error); background: #FFF0F0; }

    /* HUB STYLES */
    .hub-card {
        display: flex;
        flex-direction: column;
        background: var(--materio-surface);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid var(--materio-border);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        height: 100%;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .hub-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border-color: var(--materio-primary);
    }
    .hub-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 16px;
    }
    .hub-content h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--materio-text-main);
        margin-bottom: 8px;
    }
    .hub-content p {
        font-size: 0.85rem;
        color: var(--materio-text-muted);
        line-height: 1.5;
        margin: 0;
    }
    #setting-search:focus {
        border-color: var(--materio-primary) !important;
        box-shadow: 0 0 0 4px rgba(145, 85, 253, 0.15) !important;
    }
</style>

<div class="settings-page">
    @if(session('success'))
        <div class="materio-card" style="padding: 16px; border-left: 5px solid var(--materio-success); background: #f6ffed; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: #389e0d; font-weight: 600;">
                <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="materio-card" style="padding: 16px; border-left: 5px solid var(--materio-error); background: #fff2f0; margin-bottom: 20px;">
            <div style="color: #cf1322; font-weight: 700; margin-bottom: 8px;"><i class="fas fa-exclamation-circle"></i> Houve um problema ao salvar:</div>
            <ul style="color: #cf1322; font-size: 0.9rem; padding-left: 20px; margin:0;">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- HUB DE CONFIGURAÇÕES (ESTILO WINDOWS) --}}
    @if(!$activeTab)
    <div id="settings-hub" class="animate-up">
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--materio-text-main); margin-bottom: 30px;">Configurações</h1>
            <div style="position: relative; max-width: 600px; margin: 0 auto;">
                <i class="fas fa-search" style="position: absolute; left: 20px; top: 18px; color: var(--materio-text-muted); font-size: 1.2rem;"></i>
                <input type="text" id="setting-search" placeholder="Localizar uma configuração..." 
                       style="width: 100%; padding: 16px 20px 16px 55px; border-radius: 50px; border: 2px solid var(--materio-border); font-size: 1.1rem; outline: none; transition: all 0.3s; box-shadow: var(--shadow-sm);"
                       onkeyup="filterSettings()">
            </div>
        </div>

        <div class="materio-row" id="settings-grid">
            {{-- Perfil --}}
            <div class="materio-col-4 setting-card" data-tags="perfil conta nome email administrador">
                <a href="?tab=geral" class="hub-card">
                    <div class="hub-icon" style="background: #e0f2fe; color: #0369a1;"><i class="fas fa-user-gear"></i></div>
                    <div class="hub-content">
                        <h3>Perfil & Conta</h3>
                        <p>Alterar seu nome, e-mail e dados de acesso.</p>
                    </div>
                </a>
            </div>

            {{-- Segurança --}}
            <div class="materio-col-4 setting-card" data-tags="segurança senha proteção acesso">
                <a href="?tab=seguranca" class="hub-card">
                    <div class="hub-icon" style="background: #fef2f2; color: #dc2626;"><i class="fas fa-shield-halved"></i></div>
                    <div class="hub-content">
                        <h3>Segurança</h3>
                        <p>Gerenciar sua senha e proteção de conta.</p>
                    </div>
                </a>
            </div>

            {{-- Integração Asaas --}}
            <div class="materio-col-4 setting-card" data-tags="asaas integração gateway pagamento split webhook">
                <a href="?tab=integracoes" class="hub-card">
                    <div class="hub-icon" style="background: #f0fdf4; color: #166534;"><i class="fas fa-wallet"></i></div>
                    <div class="hub-content">
                        <h3>Integração Asaas</h3>
                        <p>API Keys, Webhooks, Ambiente e Split Global.</p>
                    </div>
                </a>
            </div>

            {{-- Site & Checkout --}}
            <div class="materio-col-4 setting-card" data-tags="site checkout externo url api keys contratacao">
                <a href="?tab=integracoes" class="hub-card">
                    <div class="hub-icon" style="background: #fff7ed; color: #9a3412;"><i class="fas fa-globe"></i></div>
                    <div class="hub-content">
                        <h3>Site & Checkout</h3>
                        <p>Configurar URL de checkout e chaves do site.</p>
                    </div>
                </a>
            </div>

            {{-- Links de Eventos --}}
            <div class="materio-col-4 setting-card" data-tags="eventos links pagamentos permanentes temporários">
                <a href="{{ route('master.integracoes.eventos') }}" class="hub-card">
                    <div class="hub-icon" style="background: #fdf4ff; color: #86198f;"><i class="fas fa-link"></i></div>
                    <div class="hub-content">
                        <h3>Links de Eventos</h3>
                        <p>Gestão de links recorrentes e temporários.</p>
                    </div>
                </a>
            </div>

            {{-- Comissões --}}
            <div class="materio-col-4 setting-card" data-tags="comissões regras repasse porcentagem fixo">
                <a href="?tab=comissoes" class="hub-card">
                    <div class="hub-icon" style="background: #eff6ff; color: #1e40af;"><i class="fas fa-coins"></i></div>
                    <div class="hub-content">
                        <h3>Regras de Comissões</h3>
                        <p>Valores fixos e taxas de repasse por plano.</p>
                    </div>
                </a>
            </div>

            {{-- Clientes Legados --}}
            <div class="materio-col-4 setting-card" data-tags="legados importação histórico clientes antigos csv">
                <a href="?tab=legados" class="hub-card">
                    <div class="hub-icon" style="background: #f5f3ff; color: #5b21b6;"><i class="fas fa-database"></i></div>
                    <div class="hub-content">
                        <h3>Clientes Legados</h3>
                        <p>Importação e sincronização de base antiga.</p>
                    </div>
                </a>
            </div>

            {{-- Cartões Salvos --}}
            <div class="materio-col-4 setting-card" data-tags="cartões cartao tokens renovação automática">
                <a href="?tab=cartoes" class="hub-card">
                    <div class="hub-icon" style="background: #fff1f2; color: #9f1239;"><i class="fas fa-credit-card"></i></div>
                    <div class="hub-content">
                        <h3>Cartões Salvos</h3>
                        <p>Visualizar clientes com métodos tokenizados.</p>
                    </div>
                </a>
            </div>

            {{-- Basileia Vendas Logs --}}
            <div class="materio-col-4 setting-card" data-tags="logs vendas asaas sistema integrações status">
                <a href="{{ route('master.integracoes.vendas') }}" class="hub-card">
                    <div class="hub-icon" style="background: #f0f9ff; color: #075985;"><i class="fas fa-store"></i></div>
                    <div class="hub-content">
                        <h3>Monitor de Vendas</h3>
                        <p>Logs de Webhook e status geral de cobranças.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    @endif

    @if($activeTab)
    <div style="margin-bottom: 20px;">
        <a href="{{ route('master.configuracoes') }}" class="materio-btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Voltar ao Hub
        </a>
    </div>

    <div class="materio-tabs">
        <button class="materio-tab-btn {{ $activeTab === 'geral' ? 'active' : '' }}" onclick="configSwitchTab('geral')">
            <i class="fas fa-user-circle"></i> Perfil
        </button>
        <button class="materio-tab-btn {{ $activeTab === 'seguranca' ? 'active' : '' }}" onclick="configSwitchTab('seguranca')">
            <i class="fas fa-lock"></i> Segurança
        </button>
        <button class="materio-tab-btn {{ $activeTab === 'integracoes' ? 'active' : '' }}" onclick="configSwitchTab('integracoes')">
            <i class="fas fa-link"></i> Integrações
        </button>
        <button class="materio-tab-btn {{ $activeTab === 'legados' ? 'active' : '' }}" onclick="configSwitchTab('legados')">
            <i class="fas fa-history"></i> Clientes Legados
        </button>
        <button class="materio-tab-btn {{ $activeTab === 'comissoes' ? 'active' : '' }}" onclick="configSwitchTab('comissoes')">
            <i class="fas fa-percent"></i> Regras de Planos
        </button>
        <button class="materio-tab-btn {{ $activeTab === 'cartoes' ? 'active' : '' }}" onclick="configSwitchTab('cartoes')">
            <i class="fas fa-credit-card"></i> Cartões Salvos
        </button>
    </div>

    <div class="tab-content">
        <!-- 1. PERFIL -->
        <div id="tab-geral" class="tab-pane" style="display: {{ $activeTab === 'geral' ? 'block' : 'none' }}">
            <div class="materio-card">
                <div class="section-header">
                    <h4><i class="fas fa-id-card"></i> Informações do Administrador</h4>
                </div>
                <form action="{{ route('master.configuracoes.geral.update') }}" method="POST">
                    @csrf
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Nome Completo</label>
                                <input type="text" name="name" class="materio-input" value="{{ $user->name }}" required>
                            </div>
                        </div>
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Email de Acesso</label>
                                <input type="email" name="email" class="materio-input" value="{{ $user->email }}" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="materio-btn-primary">Atualizar Perfil</button>
                </form>
            </div>
        </div>

        <!-- 2. SEGURANÇA -->
        <div id="tab-seguranca" class="tab-pane" style="display: {{ $activeTab === 'seguranca' ? 'block' : 'none' }}">
            <div class="materio-card">
                <div class="section-header">
                    <h4><i class="fas fa-shield-alt"></i> Alterar Senha</h4>
                </div>
                <form action="{{ route('master.configuracoes.seguranca.update') }}" method="POST">
                    @csrf
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Senha Atual</label>
                                <input type="password" name="current_password" class="materio-input" required placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Nova Senha</label>
                                <input type="password" name="password" class="materio-input" required placeholder="Mínimo 8 caracteres">
                            </div>
                        </div>
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Confirmar Nova Senha</label>
                                <input type="password" name="password_confirmation" class="materio-input" required placeholder="Confirme a nova senha">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="materio-btn-primary">Salvar Nova Senha</button>
                </form>
            </div>
        </div>

        <!-- 3. INTEGRAÇÕES -->
        <div id="tab-integracoes" class="tab-pane" style="display: {{ $activeTab === 'integracoes' ? 'block' : 'none' }}">
            <!-- Seção Asaas -->
            <div class="materio-card">
                <div class="section-header">
                    <div>
                        <h4>🔑 Asaas Gateway</h4>
                        <div class="section-subtitle">Credenciais principais para cobranças e pagamentos.</div>
                    </div>
                    <button type="button" class="materio-btn-outline" onclick="testarConexao()">Testar Conexão</button>
                </div>
                <form action="{{ route('master.configuracoes.integracoes.update') }}" method="POST">
                    @csrf
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Ambiente Asaas <span class="required">*</span></label>
                                <select name="asaas_environment" class="materio-select" required>
                                    <option value="sandbox" {{ $integracoes['asaasEnvironment'] === 'sandbox' ? 'selected' : '' }}>🧪 Sandbox (Testes)</option>
                                    <option value="production" {{ $integracoes['asaasEnvironment'] === 'production' ? 'selected' : '' }}>🚀 Produção (Real)</option>
                                </select>
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">Webhook Token <span class="required">*</span></label>
                                <input type="password" name="asaas_webhook_token" class="materio-input" value="{{ $integracoes['asaasWebhookToken'] }}" required>
                                <span class="help-text">Token definido no painel do Asaas para segurança do webhook.</span>
                            </div>
                        </div>
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">API Key <span class="required">*</span></label>
                                <input type="password" name="asaas_api_key" class="materio-input" value="{{ $integracoes['asaasApiKey'] }}" required>
                                <span class="help-text">Chave de acesso total à API do Asaas.</span>
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">URL de Callback (Opcional)</label>
                                <input type="url" name="asaas_callback_url" class="materio-input" value="{{ $integracoes['asaasCallbackUrl'] }}" placeholder="https://seudominio.com/callback">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label" style="color: var(--materio-primary)">🔗 URL do Checkout Externo <span class="required">*</span></label>
                                <input type="url" name="checkout_external_url" class="materio-input" value="{{ $integracoes['checkoutExternalUrl'] }}" required placeholder="https://seucheckout.com/pagar?id=" style="border-color: var(--materio-primary); background: #fbf8ff;">
                                <span class="help-text">Endereço da página onde seus clientes finalizam o pagamento.</span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="materio-btn-primary">Atualizar Gateway</button>
                </form>
            </div>

            <!-- Seção Split -->
            <div class="materio-card">
                <div class="section-header">
                    <h4>💰 Split de Pagamentos e Regras Financeiras</h4>
                </div>
                <form action="{{ route('master.configuracoes.integracoes.split') }}" method="POST">
                    @csrf
                    <div class="materio-form-group">
                        <label class="materio-switch">
                            <input type="checkbox" name="asaas_split_global_ativo" value="1" class="switch-input" {{ $integracoes['splitGlobalAtivo'] ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                            <div>
                                <span style="font-weight: 700; display: block;">Ativar Split Global</span>
                                <span class="help-text">Vendedores com Wallet ID configurado receberão suas comissões automaticamente via Asaas.</span>
                            </div>
                        </label>
                    </div>
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Juros Padrão (% ao mês)</label>
                                <input type="number" step="0.01" name="asaas_juros_padrao" class="materio-input" value="{{ $integracoes['jurosPadrao'] }}">
                            </div>
                        </div>
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Multa Padrão (%)</label>
                                <input type="number" step="0.01" name="asaas_multa_padrao" class="materio-input" value="{{ $integracoes['multaPadrao'] }}">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="materio-btn-primary">Salvar Config. Financeiras</button>
                </form>
            </div>

            <!-- Seção Email -->
            <div class="materio-card">
                <div class="section-header">
                    <h4>📧 Comunicação e Suporte</h4>
                </div>
                <form action="{{ route('master.configuracoes.integracoes.email') }}" method="POST">
                    @csrf
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Email Remetente (Sistema)</label>
                                <input type="email" name="email_vendedor_from" class="materio-input" value="{{ $integracoes['emailVendedorFrom'] }}">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">Email Suporte</label>
                                <input type="email" name="email_suporte" class="materio-input" value="{{ $integracoes['emailSuporte'] }}">
                            </div>
                        </div>
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Email Remetente (Clientes)</label>
                                <input type="email" name="email_cliente_from" class="materio-input" value="{{ $integracoes['emailClienteFrom'] }}">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">WhatsApp Suporte (Somente números)</label>
                                <input type="text" name="whatsapp_suporte" class="materio-input" value="{{ $integracoes['whatsappSuporte'] }}" placeholder="5599999999999">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="materio-btn-primary">Salvar Comunicações</button>
                </form>
            </div>

            <!-- Seção Google -->
            <div class="materio-row">
                <!-- Google Calendar -->
                <div class="materio-col-6">
                    <div class="materio-card">
                        <div class="section-header"><h4>📅 Google Calendar</h4></div>
                        <form action="{{ route('master.configuracoes.integracoes.google-calendar') }}" method="POST">
                            @csrf
                            <div class="materio-form-group">
                                <label class="materio-label">Client ID</label>
                                <input type="text" name="google_calendar_client_id" class="materio-input" value="{{ $integracoes['googleCalendarClientId'] }}">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">Client Secret</label>
                                <input type="password" name="google_calendar_client_secret" class="materio-input" value="{{ $integracoes['googleCalendarClientSecret'] }}">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">Redirect URI</label>
                                <input type="url" name="google_calendar_redirect_uri" class="materio-input" value="{{ $integracoes['googleCalendarRedirectUri'] }}" placeholder="{{ url('/auth/google/callback') }}">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-switch">
                                    <input type="checkbox" name="google_calendar_ativo" value="1" class="switch-input" {{ $integracoes['googleCalendarAtivo'] ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                    <span>Ativar Calendário</span>
                                </label>
                            </div>
                            <button type="submit" class="materio-btn-primary">Salvar Calendar</button>
                        </form>
                    </div>
                </div>
                <!-- Google Gmail -->
                <div class="materio-col-6">
                    <div class="materio-card">
                        <div class="section-header"><h4>✉️ Google Gmail</h4></div>
                        <form action="{{ route('master.configuracoes.integracoes.google-gmail') }}" method="POST">
                            @csrf
                            <div class="materio-form-group">
                                <label class="materio-label">Client ID</label>
                                <input type="text" name="google_gmail_client_id" class="materio-input" value="{{ $integracoes['googleGmailClientId'] }}">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">Client Secret</label>
                                <input type="password" name="google_gmail_client_secret" class="materio-input" value="{{ $integracoes['googleGmailClientSecret'] }}">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-label">Email para Envios</label>
                                <input type="email" name="google_gmail_email" class="materio-input" value="{{ $integracoes['googleGmailEmail'] }}" placeholder="contato@empresa.com">
                            </div>
                            <div class="materio-form-group">
                                <label class="materio-switch">
                                    <input type="checkbox" name="google_gmail_ativo" value="1" class="switch-input" {{ $integracoes['googleGmailAtivo'] ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                    <span>Ativar Gmail API</span>
                                </label>
                            </div>
                            <button type="submit" class="materio-btn-primary">Salvar Gmail API</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Seção Church Sync -->
            <div class="materio-card">
                <div class="section-header"><h4>⛪ Basileia Church Sync</h4></div>
                <form action="{{ route('master.configuracoes.integracoes.church') }}" method="POST">
                    @csrf
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Church Webhook URL</label>
                                <input type="url" name="basileia_church_webhook_url" class="materio-input" value="{{ $integracoes['churchWebhookUrl'] }}">
                            </div>
                        </div>
                        <div class="materio-col-6">
                            <div class="materio-form-group">
                                <label class="materio-label">Security Token</label>
                                <input type="password" name="basileia_church_webhook_token" class="materio-input" value="{{ $integracoes['churchWebhookToken'] }}">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="materio-btn-primary">Atualizar Church Sync</button>
                    
                    <div class="materio-info-box">
                        <h5>🔗 Endpoints disponíveis:</h5>
                        <ul>
                            <li><i class="fas fa-link"></i> <code>GET/POST /webhook/basileia-church/sync</code> - Sincronização de status</li>
                            <li><i class="fas fa-link"></i> <code>POST /webhook/asaas</code> - Escuta principal do Gateway</li>
                        </ul>
                    </div>
                </form>
            </div>
            
            <!-- Carteiras/Sellers Table -->
            <div class="materio-card">
                <div class="section-header"><h4>👥 Status de Carteiras (Vendedores)</h4></div>
                <div class="table-container">
                    <table class="materio-table">
                        <thead>
                            <tr>
                                <th>Vendedor</th>
                                <th>Wallet ID</th>
                                <th>Status</th>
                                <th>Validado em</th>
                                <th>Comissão</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($integracoes['vendedoresComSplit'] as $v)
                            <tr>
                                <td style="font-weight: 700;">{{ $v->user->name ?? 'N/A' }}</td>
                                <td><code>{{ $v->asaas_wallet_id }}</code></td>
                                <td>
                                    <span class="badge {{ $v->wallet_status === 'validado' ? 'bg-soft-success' : 'bg-soft-warning' }}">
                                        {{ strtoupper($v->wallet_status ?? 'Pendente') }}
                                    </span>
                                </td>
                                <td>{{ $v->wallet_validado_em ? \Carbon\Carbon::parse($v->wallet_validado_em)->format('d/m/Y H:i') : 'Nunca' }}</td>
                                <td>{{ $v->comissao_inicial }}% / {{ $v->comissao_recorrencia }}%</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Aba de split está desativada ou nenhum vendedor configurado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. LEGADOS -->
        <div id="tab-legados" class="tab-pane" style="display: {{ $activeTab === 'legados' ? 'block' : 'none' }}">
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-label">Base Total</span>
                    <span class="status-value">{{ $legados['stats']['total'] }}</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Importados</span>
                    <span class="status-value status-val-active">{{ $legados['stats']['imported'] }}</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Não Encontrados</span>
                    <span class="status-value status-val-inactive">{{ $legados['stats']['not_found'] }}</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Ativos CRM</span>
                    <span class="status-value status-val-active" style="color:#16B1FF">{{ $legados['stats']['active'] }}</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Inadimplentes</span>
                    <span class="status-value status-val-inactive">{{ $legados['stats']['overdue'] }}</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Pendente Repasse</span>
                    <span class="status-value" style="color:var(--materio-primary)">R$ {{ number_format($legados['stats']['commission_pending_value'], 2, ',', '.') }}</span>
                </div>
            </div>

            <div class="materio-card">
                <div class="section-header">
                    <div>
                        <h4>📚 Clientes Legados (Base Antiga)</h4>
                        <div class="section-subtitle">Gestão de históricos e sincronização de recorrências.</div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="{{ route('master.legados.commissions') }}" class="materio-btn-outline" style="background:#fdf4ff; border-color:#d946ef; color:#d946ef;"><i class="fas fa-money-bill-wave"></i> Comissões Pendentes</a>
                        <button class="materio-btn-outline" onclick="location.href='{{ route('master.legados.template') }}'"><i class="fas fa-download"></i> Modelo CSV</button>
                        <button class="materio-btn-primary" onclick="openImportModal()"><i class="fas fa-upload"></i> Importar CSV</button>
                        <button class="materio-btn-primary" style="background:var(--materio-success)" onclick="batchImportAsaas()"><i class="fas fa-sync"></i> Sincronizar Tudo do Asaas</button>
                        <button class="materio-btn-primary" style="background:#000" onclick="location.href='{{ route('master.legados.create') }}'"><i class="fas fa-plus"></i> Novo Manual</button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filter-bar">
                    <form action="{{ route('master.configuracoes', ['tab' => 'legados']) }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <input type="hidden" name="tab" value="legados">
                        <input type="text" name="search" class="materio-input" style="flex: 2; min-width: 200px;" value="{{ request('search') }}" placeholder="Nome, documento ou email...">
                        <select name="vendedor_id" class="materio-select" style="flex: 1; min-width: 150px;">
                            <option value="">Vendedores</option>
                            @foreach($legados['vendedores'] as $v)
                                <option value="{{ $v->id }}" {{ request('vendedor_id') == $v->id ? 'selected' : '' }}>{{ $v->user->name }}</option>
                            @endforeach
                        </select>
                        <select name="import_status" class="materio-select" style="flex: 1; min-width: 150px;">
                            <option value="">Status Import.</option>
                            <option value="IMPORTED" {{ request('import_status') == 'IMPORTED' ? 'selected' : '' }}>Importado</option>
                            <option value="PENDING" {{ request('import_status') == 'PENDING' ? 'selected' : '' }}>Pendente</option>
                            <option value="NOT_FOUND" {{ request('import_status') == 'NOT_FOUND' ? 'selected' : '' }}>Não Encontrado</option>
                        </select>
                        <button type="submit" class="materio-btn-primary">Filtrar</button>
                        <a href="{{ route('master.configuracoes', ['tab' => 'legados']) }}" class="materio-btn-outline">Limpar</a>
                    </form>
                </div>

                <div class="table-container">
                    <table class="materio-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Status Asaas</th>
                                <th>Subscrição</th>
                                <th>Importação</th>
                                <th style="text-align: right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($legados['recentImports'] as $import)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--materio-text-main);">{{ $import->nome }}</div>
                                    <div style="font-size: 0.75rem; color: var(--materio-text-muted);">{{ $import->documento }}</div>
                                </td>
                                <td>{{ $import->vendedor->user->name ?? '---' }}</td>
                                <td>
                                    @php
                                        $csClass = match($import->customer_status) {
                                            'ACTIVE' => 'chip-success',
                                            'OVERDUE' => 'chip-danger',
                                            'INACTIVE' => 'chip-secondary',
                                            default => 'chip-secondary'
                                        };
                                    @endphp
                                    <span class="status-chip {{ $csClass }}">{{ $import->customer_status }}</span>
                                </td>
                                <td>
                                    <span class="status-chip {{ $import->subscription_status === 'ACTIVE' ? 'chip-info' : 'chip-secondary' }}">
                                        {{ $import->subscription_status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $import->import_status === 'IMPORTED' ? 'bg-soft-success' : 'bg-soft-warning' }}">
                                        {{ strtoupper($import->import_status) }}
                                    </span>
                                </td>
                                <td style="text-align: right; display: flex; gap: 4px; justify-content: flex-end;">
                                    <a href="{{ route('master.legados.show', $import->id) }}" class="action-btn" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                                    <form action="{{ route('master.legados.sync', $import->id) }}" method="POST" style="margin:0;">
                                        @csrf <button type="submit" class="action-btn" title="Sincronizar Agora"><i class="fas fa-sync"></i></button>
                                    </form>
                                    <form action="{{ route('master.legados.destroy', $import->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('Deseja realmente excluir este histórico?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn action-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum registro encontrado para os filtros aplicados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 24px;">
                    {{ $legados['recentImports']->links() }}
                </div>
            </div>
        </div>

        <!-- 5. COMISSÕES -->
        <div id="tab-comissoes" class="tab-pane" style="display: {{ $activeTab === 'comissoes' ? 'block' : 'none' }}">
            <div class="materio-card">
                <div class="section-header">
                    <h4><i class="fas fa-coins"></i> Regras de Repasse Fixo por Categoria de Plano</h4>
                </div>
                <div class="table-container">
                    <table class="materio-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Plano / Categoria</th>
                                <th>Limite Membros</th>
                                <th>Vendedor (1ª / Rec.)</th>
                                <th>Gestor (1ª / Rec.)</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comissoes['rules'] as $rule)
                            <form action="{{ route('master.configuracoes.comissoes.update', $rule->id) }}" method="POST">
                                @csrf @method('PUT')
                                <tr>
                                    <td>
                                        <label class="materio-switch" style="gap:0;">
                                            <input type="checkbox" name="active" value="1" class="switch-input" {{ $rule->active ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="switch-slider" style="width: 32px; height: 16px;"></span>
                                        </label>
                                    </td>
                                    <td style="font-weight: 800; color: var(--materio-primary)">{{ $rule->plano_nome }}</td>
                                    <td>Até {{ $rule->max_membros }}</td>
                                    <td>
                                        <div style="display: flex; gap: 4px; align-items: center;">
                                            R$ <input type="number" step="0.01" name="seller_fixed_value_first_payment" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->seller_fixed_value_first_payment }}">
                                            / R$ <input type="number" step="0.01" name="seller_fixed_value_recurring" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->seller_fixed_value_recurring }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 4px; align-items: center;">
                                            R$ <input type="number" step="0.01" name="manager_fixed_value_first_payment" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->manager_fixed_value_first_payment }}">
                                            / R$ <input type="number" step="0.01" name="manager_fixed_value_recurring" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->manager_fixed_value_recurring }}">
                                        </div>
                                    </td>
                                    <td>
                                        <button type="submit" class="materio-btn-primary" style="padding: 6px 12px; font-size: 0.75rem;">Atualizar</button>
                                    </td>
                                </tr>
                            </form>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="materio-info-box" style="margin-top:24px; border-style: dashed;">
                    <h5>🛎️ Prioridade de Cálculo:</h5>
                    <p style="font-size: 0.85rem; color: var(--materio-text-muted); margin:0;">
                        O sistema prioriza os <strong>valores fixos</strong> configurados acima. Caso o plano vendido não esteja mapeado ou a regra esteja inativa, o cálculo utilizará a <strong>porcentagem individual</strong> cadastrada no perfil do vendedor.
                    </p>
                </div>
            </div>
        <!-- 6. CARTÕES SALVOS -->
        <div id="tab-cartoes" class="tab-pane" style="display: {{ $activeTab === 'cartoes' ? 'block' : 'none' }}">
            <div class="materio-card">
                <div class="section-header">
                    <div>
                        <h4><i class="fas fa-credit-card"></i> Cartões Tokenizados para Renovação</h4>
                        <div class="section-subtitle">Clientes com dados de pagamento salvos para cobrança automática.</div>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="materio-table">
                        <thead>
                            <tr>
                                <th>Cliente / Email</th>
                                <th>Bandeira</th>
                                <th>Final do Cartão</th>
                                <th>Token (Asaas)</th>
                                <th>Salvo em</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cartoes['clientes'] as $cliente)
                            <tr>
                                <td style="font-weight: 700;">
                                    {{ $cliente->nome }}
                                    <div style="font-size: 0.75rem; font-weight: 400; color: var(--materio-text-muted);">{{ $cliente->email }}</div>
                                </td>
                                <td>
                                    @if($cliente->card_brand)
                                        <span class="badge bg-soft-primary" style="text-transform: uppercase;">
                                            {{ $cliente->card_brand }}
                                        </span>
                                    @else
                                        <span class="text-muted">Desconhecida</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-credit-card" style="color: var(--materio-text-muted);"></i>
                                        <strong>**** {{ $cliente->card_last_digits ?? '----' }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <code>{{ $cliente->credit_card_token }}</code>
                                </td>
                                <td>
                                    {{ $cliente->updated_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle" style="font-size: 2rem; display: block; margin-bottom: 12px; opacity: 0.3;"></i>
                                    Nenhum cartão salvo encontrado até o momento.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="materio-info-box" style="margin-top: 24px; background: #fffbe6; border-color: #ffe58f;">
                    <h5 style="color: #856404;"><i class="fas fa-shield-alt"></i> Segurança e Conformidade (PCI)</h5>
                    <p style="font-size: 0.85rem; color: #856404; margin: 0;">
                        O sistema <strong>não armazena</strong> o número completo, CVV ou validade dos cartões. Apenas o <strong>token de acesso seguro</strong> fornecido pelo Asaas é mantido, garantindo conformidade total e segurança máxima para os dados dos clientes.
                    </p>
                </div>
            </div>
    @endif
</div>

<!-- Modal Upload CSV -->
<div id="modal-import-csv" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div class="materio-card" style="width: 550px; padding: 0; overflow: hidden; border: none;">
        <div class="section-header" style="padding: 24px; background: #F9FAFB; margin: 0; border-bottom: 1px solid var(--materio-border);">
            <h4 style="margin: 0;"><i class="fas fa-file-import" style="color:var(--materio-primary)"></i> Importação via Planilha CSV</h4>
            <i class="fas fa-times" style="cursor: pointer; color: var(--materio-text-muted); font-size: 1.2rem;" onclick="closeImportModal()"></i>
        </div>
        <form method="POST" action="{{ route('master.legados.importCsv') }}" enctype="multipart/form-data" style="padding: 24px;">
            @csrf
            <div class="materio-form-group">
                <label class="materio-label">Selecione o arquivo CSV (Ponto-e-vírgula)</label>
                <input type="file" name="csv_file" class="materio-input" required accept=".csv,.txt">
                <span class="help-text">O arquivo deve ser codificado em UTF-8 ou ISO-8859-1.</span>
            </div>
            <div style="background: var(--materio-primary-light); color: #6d28d9; padding: 15px; border-radius: 8px; font-size: 0.8rem; margin-bottom: 24px; line-height: 1.5;">
                <i class="fas fa-info-circle"></i> <strong>Colunas Requeridas:</strong><br>
                nome, documento, email, telefone, vendedor, gestor, plano, valor_original, valor_recorrente, data_venda.
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="materio-btn-outline" onclick="closeImportModal()">Cancelar Operação</button>
                <button type="submit" class="materio-btn-primary">Processar Arquivo</button>
            </div>
        </form>
    </div>
</div>

<script>
    // HUB FILTER
    function filterSettings() {
        const input = document.getElementById('setting-search');
        const filter = input.value.toLowerCase();
        const cards = document.getElementsByClassName('setting-card');

        for (let i = 0; i < cards.length; i++) {
            const title = cards[i].querySelector('h3').innerText.toLowerCase();
            const desc = cards[i].querySelector('p').innerText.toLowerCase();
            const tags = cards[i].getAttribute('data-tags').toLowerCase();
            
            if (title.indexOf(filter) > -1 || desc.indexOf(filter) > -1 || tags.indexOf(filter) > -1) {
                cards[i].style.display = "";
                cards[i].style.animation = "fadeIn 0.3s ease";
            } else {
                cards[i].style.display = "none";
            }
        }
    }

    // Navegação entre Abas (Unificada para Hub e Abas)
    function configSwitchTab(tabId) {
        if (!tabId) {
            window.location.href = "{{ route('master.configuracoes') }}";
            return;
        }
        
        try {
            // Se estiver no Hub, redirecionar para a aba
            if (!document.querySelector('.materio-tabs')) {
                window.location.href = "?tab=" + tabId;
                return;
            }

            const panes = document.querySelectorAll('.tab-pane');
            const buttons = document.querySelectorAll('.materio-tab-btn');
            
            panes.forEach(p => p.style.display = 'none');
            buttons.forEach(b => b.classList.remove('active'));

            const targetPane = document.getElementById('tab-' + tabId);
            if (targetPane) {
                targetPane.style.display = 'block';
            }

            // Ativa o botão correspondente
            buttons.forEach(b => {
                const onclick = b.getAttribute('onclick');
                if (onclick && onclick.includes("'" + tabId + "'")) {
                    b.classList.add('active');
                }
            });

            // Atualiza URL para manter estado
            if (history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set('tab', tabId);
                window.history.pushState({path:url.toString()}, '', url.toString());
            }
        } catch (e) {
            console.error('Erro ao trocar aba:', e);
            window.location.href = "?tab=" + tabId;
        }
    }

    // Modais e Ações Bulk
    function openImportModal() { document.getElementById('modal-import-csv').style.display = 'flex'; }
    function closeImportModal() { document.getElementById('modal-import-csv').style.display = 'none'; }

    function batchImportAsaas() {
        if(confirm('Atenção: O sistema fará uma varredura completa em sua conta Asaas. Isso pode levar alguns minutos se você tiver milhares de clientes. Deseja continuar?')) {
            const f = document.createElement('form');
            f.method = 'POST'; f.action = "{{ route('master.legados.importBatch') }}";
            f.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
            document.body.appendChild(f);
            f.submit();
        }
    }

    // Teste de Conexão API
    function testarConexao() {
        const btn = event.currentTarget;
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        btn.disabled = true;

        fetch('{{ route("master.configuracoes.integracoes.testar") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(d => {
            alert(d.success ? '🍏 ' + d.message : '🍎 ' + d.message);
        })
        .catch(e => alert('🍎 Erro técnico ao tentar conectar: ' + e.message))
        .finally(() => {
            btn.innerHTML = original;
            btn.disabled = false;
        });
    }

    // Inicialização
    window.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        
        if (tab && document.getElementById('tab-' + tab)) {
            configSwitchTab(tab);
        }
    });
</script>
@endsection
