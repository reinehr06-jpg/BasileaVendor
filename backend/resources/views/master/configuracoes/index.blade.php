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
        
        {{-- ZONA DE PERIGO --}}
        <div style="background: #FFF5F5; border: 2px solid #FF4C51; border-radius: 12px; padding: 24px; margin-bottom: 32px;">
            <h3 style="color: #FF4C51; margin: 0 0 12px 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-exclamation-triangle"></i> Zona de Perigo
            </h3>
            <p style="color: #666; font-size: 0.9rem; margin: 0 0 16px 0;">
                Esta ação é IRREVERSÍVEL. Todos os dados serão apagados permanentemente.
            </p>
            <button type="button" onclick="limparBanco()" class="btn btn-danger" style="background: #FF4C51; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; cursor: pointer;">
                <i class="fas fa-trash-alt"></i> Limpar Banco de Dados
            </button>
        </div>
        
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

            {{-- Clientes Asaas Sincronização --}}
            <div class="materio-col-4 setting-card" data-tags="asaas clientes legados importação sincronização">
                <a href="{{ route('master.clientes-asaas.index') }}" class="hub-card">
                    <div class="hub-icon" style="background: #fffafa; color: #f97316;"><i class="fas fa-cloud-arrow-down"></i></div>
                    <div class="hub-content">
                        <h3 style="display:flex; align-items:center; gap:6px;">
                            Clientes Asaas 
                            <span style="font-size:0.6rem; background:#f97316; color:white; padding:1px 5px; border-radius:8px;">MARÇO</span>
                        </h3>
                        <p>Importe vendas e identifique o "primeiro mês" das assinaturas.</p>
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

        <button class="materio-tab-btn {{ $activeTab === 'comissoes' ? 'active' : '' }}" onclick="configSwitchTab('comissoes')">
            <i class="fas fa-percent"></i> Regras de Planos
        </button>
        <button class="materio-tab-btn {{ $activeTab === 'cartoes' ? 'active' : '' }}" onclick="configSwitchTab('cartoes')">
            <i class="fas fa-credit-card"></i> Cartões Salvos
        </button>
    </div>

    <div class="tab-content" style="display: block !important;">
        <!-- 1. PERFIL -->
        <div id="tab-geral" class="tab-pane" style="display: {{ $activeTab === 'geral' ? 'block' : 'none' }} !important;">
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
        <div id="tab-seguranca" class="tab-pane" style="display: {{ $activeTab === 'seguranca' ? 'block' : 'none' }} !important;">
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

        <!-- 3. INTEGRAÇÕES - SUB-HUB PROFISSIONAL -->
        <div id="tab-integracoes" class="tab-pane" style="display: {{ $activeTab === 'integracoes' ? 'block' : 'none' }} !important;">

            <style>
                /* Sub-Hub de Integrações */
                .integ-hub { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
                .integ-card {
                    display: flex; flex-direction: column; align-items: center; justify-content: center;
                    padding: 22px 16px; border-radius: 14px; border: 2px solid var(--materio-border);
                    background: var(--materio-surface); cursor: pointer; transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
                    text-align: center; gap: 10px; text-decoration: none; position: relative; overflow: hidden;
                }
                .integ-card:hover { border-color: var(--materio-primary); transform: translateY(-4px); box-shadow: 0 8px 20px rgba(145,85,253,0.15); }
                .integ-card.active { border-color: var(--materio-primary); background: #fbf8ff; box-shadow: 0 4px 16px rgba(145,85,253,0.2); }
                .integ-card.active::after {
                    content: '✓'; position: absolute; top: 8px; right: 10px;
                    font-size: 0.7rem; background: var(--materio-primary); color: white;
                    border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-weight: 900;
                }
                .integ-logo { font-size: 2rem; line-height: 1; }
                .integ-card-title { font-size: 0.82rem; font-weight: 700; color: var(--materio-text-main); line-height: 1.3; }
                .integ-card-desc { font-size: 0.7rem; color: var(--materio-text-muted); }
                .integ-badge-on { display: inline-block; font-size: 0.6rem; padding: 2px 6px; border-radius: 10px; background: #dcfce7; color: #166534; font-weight: 700; }
                .integ-badge-off { display: inline-block; font-size: 0.6rem; padding: 2px 6px; border-radius: 10px; background: #f0f0f0; color: #888; font-weight: 700; }

                /* Painel de formulário */
                .integ-panel { display: none; animation: fadeIn 0.3s ease; }
                .integ-panel.visible { display: block; }
                .integ-panel-header {
                    display: flex; align-items: center; gap: 14px; margin-bottom: 24px;
                    padding: 20px; background: linear-gradient(135deg, #f4efff 0%, #fff 100%);
                    border-radius: 12px; border: 1px solid #e9d9ff;
                }
                .integ-panel-icon { font-size: 2.2rem; }
                .integ-panel-title { font-size: 1.2rem; font-weight: 800; color: var(--materio-text-main); margin: 0; }
                .integ-panel-sub { font-size: 0.83rem; color: var(--materio-text-muted); }
                .back-to-integ-hub { background: none; border: none; color: var(--materio-primary); font-weight: 700; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; padding: 0; margin-bottom: 20px; }
                .back-to-integ-hub:hover { text-decoration: underline; }
            </style>

            {{-- SUB-HUB CARDS --}}
            <div id="integ-hub-view">
                <p style="color: var(--materio-text-muted); font-size: 0.9rem; margin-bottom: 18px;">Selecione uma integração para configurar:</p>
                <div class="integ-hub">
                    {{-- Asaas --}}
                    <div class="integ-card {{ $integracoes['asaasApiKey'] ? 'active' : '' }}" onclick="showIntegPanel('asaas')">
                        <div class="integ-logo">💳</div>
                        <div class="integ-card-title">Asaas Gateway</div>
                        <div class="integ-card-desc">API, Webhook, Ambiente</div>
                        <span class="{{ $integracoes['asaasApiKey'] ? 'integ-badge-on' : 'integ-badge-off' }}">
                            {{ $integracoes['asaasApiKey'] ? 'CONECTADO' : 'INATIVO' }}
                        </span>
                    </div>
                    {{-- Checkout --}}
                    <div class="integ-card {{ $integracoes['checkoutExternalUrl'] ? 'active' : '' }}" onclick="showIntegPanel('checkout')">
                        <div class="integ-logo">🌐</div>
                        <div class="integ-card-title">Site & Checkout</div>
                        <div class="integ-card-desc">URL de pagamento externo</div>
                        <span class="{{ $integracoes['checkoutExternalUrl'] ? 'integ-badge-on' : 'integ-badge-off' }}">
                            {{ $integracoes['checkoutExternalUrl'] ? 'CONFIGURADO' : 'PENDENTE' }}
                        </span>
                    </div>
                    {{-- Split --}}
                    <div class="integ-card {{ $integracoes['splitGlobalAtivo'] ? 'active' : '' }}" onclick="showIntegPanel('split')">
                        <div class="integ-logo">💰</div>
                        <div class="integ-card-title">Split & Repasse</div>
                        <div class="integ-card-desc">Comissões automáticas</div>
                        <span class="{{ $integracoes['splitGlobalAtivo'] ? 'integ-badge-on' : 'integ-badge-off' }}">
                            {{ $integracoes['splitGlobalAtivo'] ? 'ATIVO' : 'INATIVO' }}
                        </span>
                    </div>
                    {{-- Comunicação --}}
                    <div class="integ-card {{ $integracoes['emailSuporte'] ? 'active' : '' }}" onclick="showIntegPanel('email')">
                        <div class="integ-logo">📧</div>
                        <div class="integ-card-title">Comunicação</div>
                        <div class="integ-card-desc">Email & WhatsApp suporte</div>
                        <span class="{{ $integracoes['emailSuporte'] ? 'integ-badge-on' : 'integ-badge-off' }}">
                            {{ $integracoes['emailSuporte'] ? 'CONFIGURADO' : 'PENDENTE' }}
                        </span>
                    </div>
                    {{-- Google Calendar --}}
                    <div class="integ-card {{ $integracoes['googleCalendarAtivo'] ? 'active' : '' }}" onclick="showIntegPanel('gcal')">
                        <div class="integ-logo">📅</div>
                        <div class="integ-card-title">Google Calendar</div>
                        <div class="integ-card-desc">Sincronização de eventos</div>
                        <span class="{{ $integracoes['googleCalendarAtivo'] ? 'integ-badge-on' : 'integ-badge-off' }}">
                            {{ $integracoes['googleCalendarAtivo'] ? 'ATIVO' : 'INATIVO' }}
                        </span>
                    </div>
                    {{-- Google Gmail --}}
                    <div class="integ-card {{ $integracoes['googleGmailAtivo'] ? 'active' : '' }}" onclick="showIntegPanel('gmail')">
                        <div class="integ-logo">✉️</div>
                        <div class="integ-card-title">Google Gmail</div>
                        <div class="integ-card-desc">Envio de email via API</div>
                        <span class="{{ $integracoes['googleGmailAtivo'] ? 'integ-badge-on' : 'integ-badge-off' }}">
                            {{ $integracoes['googleGmailAtivo'] ? 'ATIVO' : 'INATIVO' }}
                        </span>
                    </div>
                    {{-- Basileia Church --}}
                    <div class="integ-card {{ $integracoes['churchWebhookUrl'] ? 'active' : '' }}" onclick="showIntegPanel('church')">
                        <div class="integ-logo">⛪</div>
                        <div class="integ-card-title">Basileia Church</div>
                        <div class="integ-card-desc">Sincronização de membros</div>
                        <span class="{{ $integracoes['churchWebhookUrl'] ? 'integ-badge-on' : 'integ-badge-off' }}">
                            {{ $integracoes['churchWebhookUrl'] ? 'CONFIGURADO' : 'PENDENTE' }}
                        </span>
                    </div>
                    {{-- Carteiras --}}
                    <div class="integ-card" onclick="showIntegPanel('wallets')">
                        <div class="integ-logo">👥</div>
                        <div class="integ-card-title">Carteiras</div>
                        <div class="integ-card-desc">Status dos vendedores</div>
                        <span class="integ-badge-on">VER</span>
                    </div>
                </div>
            </div>

            {{-- PAINEL: Asaas --}}
            <div id="integ-panel-asaas" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon">💳</div>
                    <div>
                        <div class="integ-panel-title">Asaas Gateway</div>
                        <div class="integ-panel-sub">Configure a API Key, Webhook Token e o ambiente de cobrança.</div>
                    </div>
                    <button type="button" class="materio-btn-outline" style="margin-left:auto;" onclick="testarConexao()">Testar Conexão</button>
                </div>
                <div class="materio-card">
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
                                    <label class="materio-label">Webhook Token</label>
                                    <input type="password" name="asaas_webhook_token" class="materio-input" value="{{ $integracoes['asaasWebhookToken'] }}">
                                    <span class="help-text">Token definido no painel do Asaas para segurança do webhook.</span>
                                </div>
                            </div>
                            <div class="materio-col-6">
                                <div class="materio-form-group">
                                    <label class="materio-label">API Key <span class="required">*</span></label>
                                    <input type="password" name="asaas_api_key" class="materio-input" value="{{ $integracoes['asaasApiKey'] }}">
                                    <span class="help-text">Chave de acesso total à API do Asaas.</span>
                                </div>
                                <div class="materio-form-group">
                                    <label class="materio-label">URL de Callback</label>
                                    <input type="url" name="asaas_callback_url" class="materio-input" value="{{ $integracoes['asaasCallbackUrl'] }}" placeholder="https://seudominio.com/callback">
                                </div>
                                {{-- CAMPO CHECKOUT OCULTO para manter ao salvar --}}
                                <input type="hidden" name="checkout_external_url" value="{{ $integracoes['checkoutExternalUrl'] }}">
                            </div>
                        </div>
                        <button type="submit" class="materio-btn-primary">Salvar Configurações do Asaas</button>
                    </form>
                </div>
            </div>

            {{-- PAINEL: Site & Checkout (Os 6 Passos) --}}
            <div id="integ-panel-checkout" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon" style="background:var(--primary-dark); color:white;">🛒</div>
                    <div>
                        <div class="integ-panel-title">Integração Externa de Checkout</div>
                        <div class="integ-panel-sub">Siga os 6 passos abaixo para conectar qualquer Checkout ao Basileia.</div>
                    </div>
                </div>
                
                <div class="materio-card">
                    <form action="{{ route('master.configuracoes.integracoes.update') }}" method="POST">
                        @csrf
                        {{-- Preservando as configurações antigas --}}
                        <input type="hidden" name="asaas_api_key" value="{{ $integracoes['asaasApiKey'] ?? '' }}">
                        <input type="hidden" name="asaas_webhook_token" value="{{ $integracoes['asaasWebhookToken'] ?? '' }}">
                        <input type="hidden" name="asaas_environment" value="{{ $integracoes['asaasEnvironment'] ?? '' }}">
                        <input type="hidden" name="asaas_callback_url" value="{{ $integracoes['asaasCallbackUrl'] ?? '' }}">

                        {{-- PASSO 1 & 2: Secret --}}
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-bottom:20px;">
                            <h4 style="font-size:1rem; font-weight:800; color:var(--materio-text-main); margin-bottom:8px;"><span style="background:var(--primary); color:white; padding:2px 8px; border-radius:6px; font-size:0.8rem; margin-right:6px;">Passos 1 e 2</span> Webhook Secret</h4>
                            <p style="font-size:0.85rem; color:var(--materio-text-muted); margin-bottom:12px;">Esta chave protege seu sistema para que apenas o seu Checkout possa enviar pagamentos pagos para cá.</p>
                            
                            <div style="display:flex; gap:10px;">
                                <input type="text" id="webhook_secret" name="checkout_webhook_secret" class="materio-input" 
                                       value="{{ \App\Models\Setting::get('checkout_webhook_secret') }}" 
                                       placeholder="Clique em Gerar para criar um secret..." readonly style="background:#f1f5f9; font-family:monospace; font-weight:700;">
                                <button type="button" class="materio-btn-secondary" onclick="gerarSecretCheckout()" title="Gerar ou Substituir Chave">
                                    <i class="fas fa-key"></i> Gerar
                                </button>
                                <button type="button" class="materio-btn-primary" onclick="copiarTexto('webhook_secret')" title="Copiar Chave">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p style="font-size:0.75rem; color:#dc2626; margin-top:8px; font-weight:600;"><i class="fas fa-info-circle"></i> Após gerar, clique no botão Copiar e cole no seu sistema de Checkout (geralmente em Configurações > Sistemas).</p>
                        </div>

                        {{-- PASSO 1.5: API Key do Checkout --}}
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-bottom:20px;">
                            <h4 style="font-size:1rem; font-weight:800; color:var(--materio-text-main); margin-bottom:8px;"><span style="background:var(--primary); color:white; padding:2px 8px; border-radius:6px; font-size:0.8rem; margin-right:6px;">Passo 1.5</span> API Key do Checkout</h4>
                            <p style="font-size:0.85rem; color:var(--materio-text-muted); margin-bottom:12px;">Copie a <code>ck_live_...</code> do seu Checkout e cole aqui. Esta chave autentica requisições que o Basileia Vendas faz ativamente ao Checkout — como consultar status, cancelar transações ou buscar detalhes de pagamento.</p>
                            
                            <div style="display:flex; gap:10px;">
                                <input type="password" id="checkout_api_key" name="checkout_api_key" class="materio-input" 
                                       value="{{ $integracoes['checkoutApiKey'] ?? '' }}" 
                                       placeholder="Cole aqui a ck_live_... do seu Checkout" 
                                       style="font-family:monospace; font-weight:600;">
                                <button type="button" class="materio-btn-primary" onclick="toggleApiKeyVisibility()" title="Mostrar/Ocultar Chave" style="min-width:50px;">
                                    <i class="fas fa-eye" id="toggle-api-key-icon"></i>
                                </button>
                                <button type="button" class="materio-btn-primary" onclick="copiarTexto('checkout_api_key')" title="Copiar Chave">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p style="font-size:0.75rem; color:var(--materio-text-muted); margin-top:8px;"><i class="fas fa-info-circle"></i> A API Key permite que o Vendas consulte, cancele e busque transações diretamente no Checkout.</p>
                        </div>

                        {{-- PASSO 3 & 4: Webhook URL --}}
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-bottom:20px;">
                            <h4 style="font-size:1rem; font-weight:800; color:var(--materio-text-main); margin-bottom:8px;"><span style="background:var(--primary); color:white; padding:2px 8px; border-radius:6px; font-size:0.8rem; margin-right:6px;">Passos 3 e 4</span> Endpoint do Webhook</h4>
                            <p style="font-size:0.85rem; color:var(--materio-text-muted); margin-bottom:12px;">Para onde o seu Checkout deve enviar o sinal de "PAGO" ou "RECUSADO"?</p>
                            
                            <div style="display:flex; gap:10px;">
                                <input type="text" id="webhook_url" class="materio-input" 
                                       value="{{ url('/api/webhook/checkout') }}" 
                                       readonly style="background:#f1f5f9; font-family:monospace; color:#0369a1; font-weight:700;">
                                <button type="button" class="materio-btn-primary" onclick="copiarTexto('webhook_url')" title="Copiar Endpoint">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p style="font-size:0.75rem; color:#dc2626; margin-top:8px; font-weight:600;"><i class="fas fa-info-circle"></i> Cole esta URL no cadastro de webhook do seu Checkout.</p>
                        </div>

                        {{-- PASSO 5: URL do Checkout --}}
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-bottom:20px;">
                            <h4 style="font-size:1rem; font-weight:800; color:var(--materio-text-main); margin-bottom:8px;"><span style="background:var(--primary); color:white; padding:2px 8px; border-radius:6px; font-size:0.8rem; margin-right:6px;">Passo 5</span> URL Base do Checkout</h4>
                            <p style="font-size:0.85rem; color:var(--materio-text-muted); margin-bottom:12px;">Para onde o Basileia deve redirecionar o cliente na hora da compra? (Ex: `https://pay.seucheckout.com/`)</p>
                            
                            <input type="url" name="checkout_external_url" class="materio-input"
                                   value="{{ $integracoes['checkoutExternalUrl'] ?? '' }}"
                                   placeholder="Ex: https://checkout.basileia-vendas.com/"
                                   style="font-size: 1rem; padding: 14px; border-color: var(--materio-primary);">
                        </div>
                        
                        {{-- PASSO 6: Testar e Entender --}}
                        <div class="materio-info-box" style="margin-bottom: 20px; border-color:#0284c7; background:#f0f9ff;">
                            <h5 style="color:#0369a1;"><span style="background:#0284c7; color:white; padding:2px 8px; border-radius:6px; font-size:0.8rem; margin-right:6px;">Passo 6</span> Como testar a automação:</h5>
                            <p style="font-size:0.8rem; color:var(--materio-text-main); margin-top:6px; line-height:1.6;">
                                1. Crie uma <b>Venda</b> no Basileia (O link de checkout será gerado instantaneamente).<br>
                                2. Copie e abra o link gerado (Sua tela de checkout deve abrir montada).<br>
                                3. Faça um pagamento teste (PIX ou Boleto).<br>
                                4. Aguarde e veja se essa Venda muda para "PAGO" no seu painel.
                            </p>
                        </div>

                        <button type="submit" class="materio-btn-primary" style="width:100%; padding: 14px 32px; font-size: 1.1rem; font-weight:800;">
                            <i class="fas fa-save"></i> Salvar Integração de Checkout
                        </button>
                    </form>
                </div>

                {{-- TESTE DE CONEXAO --}}
                <div class="materio-card" style="margin-top: 20px;">
                    <div class="section-header">
                        <h4><i class="fas fa-vial"></i> Testar Conexões</h4>
                    </div>
                    <div class="materio-row">
                        <div class="materio-col-6">
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px;">
                                <h5 style="font-size:0.95rem; font-weight:700; margin-bottom:8px;"><i class="fas fa-key" style="color:var(--materio-primary);"></i> Testar API Key</h5>
                                <p style="font-size:0.8rem; color:var(--materio-text-muted); margin-bottom:14px;">Verifica se a API Key do Checkout está válida e acessível.</p>
                                <button type="button" class="materio-btn-primary" id="btn-test-api" onclick="testarCheckoutApi()">
                                    <i class="fas fa-plug"></i> Testar API Key
                                </button>
                            </div>
                        </div>
                        <div class="materio-col-6">
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px;">
                                <h5 style="font-size:0.95rem; font-weight:700; margin-bottom:8px;"><i class="fas fa-satellite-dish" style="color:var(--materio-info);"></i> Testar Webhook</h5>
                                <p style="font-size:0.8rem; color:var(--materio-text-muted); margin-bottom:14px;">Envia um evento simulado para validar o endpoint do webhook.</p>
                                <button type="button" class="materio-btn-primary" id="btn-test-webhook" onclick="testarWebhook()" style="background:var(--materio-info);">
                                    <i class="fas fa-broadcast-tower"></i> Testar Webhook
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PAINEL: Split --}}
            <div id="integ-panel-split" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon">💰</div>
                    <div>
                        <div class="integ-panel-title">Split & Repasse Automático</div>
                        <div class="integ-panel-sub">Configure as regras de comissão e taxas padrões.</div>
                    </div>
                </div>
                <div class="materio-card">
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
                        <button type="submit" class="materio-btn-primary">Salvar Configurações Financeiras</button>
                    </form>
                </div>
            </div>

            {{-- PAINEL: Email --}}
            <div id="integ-panel-email" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon">📧</div>
                    <div>
                        <div class="integ-panel-title">Comunicação & Suporte</div>
                        <div class="integ-panel-sub">Configure os canais de comunicação com clientes e time.</div>
                    </div>
                </div>
                <div class="materio-card">
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
            </div>

            {{-- PAINEL: Google Calendar --}}
            <div id="integ-panel-gcal" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon">📅</div>
                    <div>
                        <div class="integ-panel-title">Google Calendar</div>
                        <div class="integ-panel-sub">Sincronize eventos de vendas com o Google Calendar.</div>
                    </div>
                </div>
                <div class="materio-card">
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
                                <span>Ativar Google Calendar</span>
                            </label>
                        </div>
                        <button type="submit" class="materio-btn-primary">Salvar Google Calendar</button>
                    </form>
                </div>
            </div>

            {{-- PAINEL: Google Gmail --}}
            <div id="integ-panel-gmail" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon">✉️</div>
                    <div>
                        <div class="integ-panel-title">Google Gmail API</div>
                        <div class="integ-panel-sub">Envie e-mails usando sua conta do Gmail via API.</div>
                    </div>
                </div>
                <div class="materio-card">
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

            {{-- PAINEL: Basileia Church --}}
            <div id="integ-panel-church" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon">⛪</div>
                    <div>
                        <div class="integ-panel-title">Basileia Church Sync</div>
                        <div class="integ-panel-sub">Sincronize membros e status com o sistema da Igreja.</div>
                    </div>
                </div>
                <div class="materio-card">
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
                        <div class="materio-info-box" style="margin-top: 16px;">
                            <h5>🔗 Endpoints disponíveis:</h5>
                            <ul>
                                <li><i class="fas fa-link"></i> <code>GET/POST /webhook/basileia-church/sync</code> - Sincronização de status</li>
                                <li><i class="fas fa-link"></i> <code>POST /webhook/asaas</code> - Escuta principal do Gateway</li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>

            {{-- PAINEL: Carteiras --}}
            <div id="integ-panel-wallets" class="integ-panel">
                <button class="back-to-integ-hub" onclick="hideIntegPanels()"><i class="fas fa-arrow-left"></i> Voltar às Integrações</button>
                <div class="integ-panel-header">
                    <div class="integ-panel-icon">👥</div>
                    <div>
                        <div class="integ-panel-title">Status de Carteiras</div>
                        <div class="integ-panel-sub">Vendedores com Wallet ID e status de split configurados.</div>
                    </div>
                </div>
                <div class="materio-card">
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
                                <tr><td colspan="5" style="text-align:center; padding: 30px; color: var(--materio-text-muted);">Nenhum vendedor com split configurado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>



        <!-- 5. COMISSÕES -->
        <div id="tab-comissoes" class="tab-pane" style="display: {{ $activeTab === 'comissoes' ? 'block' : 'none' }} !important;">
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
                            <tr>
                                <td>
                                    <form id="form-rule-active-{{ $rule->id }}" action="{{ route('master.configuracoes.comissoes.update', $rule->id) }}" method="POST" style="margin:0;">
                                        @csrf @method('PUT')
                                        <label class="materio-switch" style="gap:0;">
                                            <input type="checkbox" name="active" value="1" class="switch-input" {{ $rule->active ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="switch-slider" style="width: 32px; height: 16px;"></span>
                                        </label>
                                    </form>
                                </td>
                                <td style="font-weight: 800; color: var(--materio-primary)">{{ $rule->plano_nome }}</td>
                                <td>Até {{ $rule->max_membros }}</td>
                                <td>
                                    <div style="display: flex; gap: 4px; align-items: center;">
                                        R$ <input type="number" step="0.01" name="seller_fixed_value_first_payment" form="form-rule-{{ $rule->id }}" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->seller_fixed_value_first_payment }}">
                                        / R$ <input type="number" step="0.01" name="seller_fixed_value_recurring" form="form-rule-{{ $rule->id }}" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->seller_fixed_value_recurring }}">
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px; align-items: center;">
                                        R$ <input type="number" step="0.01" name="manager_fixed_value_first_payment" form="form-rule-{{ $rule->id }}" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->manager_fixed_value_first_payment }}">
                                        / R$ <input type="number" step="0.01" name="manager_fixed_value_recurring" form="form-rule-{{ $rule->id }}" class="materio-input" style="width: 75px; padding: 6px; font-weight: 700;" value="{{ $rule->manager_fixed_value_recurring }}">
                                    </div>
                                </td>
                                <td>
                                    <form id="form-rule-{{ $rule->id }}" action="{{ route('master.configuracoes.comissoes.update', $rule->id) }}" method="POST" style="margin:0;">
                                        @csrf @method('PUT')
                                        <button type="submit" class="materio-btn-primary" style="padding: 6px 12px; font-size: 0.75rem;">Atualizar</button>
                                    </form>
                                </td>
                            </tr>
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
        </div>

        <!-- 6. CARTÕES SALVOS -->
        <div id="tab-cartoes" class="tab-pane" style="display: {{ $activeTab === 'cartoes' ? 'block' : 'none' }} !important;">
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
        </div>
    </div>
    @endif
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

    // ===== SUB-HUB DE INTEGRAÇÕES =====
    function showIntegPanel(panelId) {
        // Esconder o hub de cards
        const hub = document.getElementById('integ-hub-view');
        if (hub) hub.style.display = 'none';

        // Esconder todos os painéis
        document.querySelectorAll('.integ-panel').forEach(p => {
            p.classList.remove('visible');
            p.style.display = 'none';
        });

        // Mostrar o painel selecionado
        const panel = document.getElementById('integ-panel-' + panelId);
        if (panel) {
            panel.style.display = 'block';
            panel.classList.add('visible');
        }
    }

    function hideIntegPanels() {
        // Esconder todos os painéis
        document.querySelectorAll('.integ-panel').forEach(p => {
            p.classList.remove('visible');
            p.style.display = 'none';
        });

        // Mostrar o hub de cards
        const hub = document.getElementById('integ-hub-view');
        if (hub) hub.style.display = 'block';
    }

    // Inicialização
    window.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        
        if (tab && document.getElementById('tab-' + tab)) {
            configSwitchTab(tab);
        }
    });

    // ===== FUNÇÕES DO CHECKOUT AUTOMÁTICO =====
    function gerarSecretCheckout() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';
        let secret = 'whsec_';
        for(let i = 0; i < 32; i++) {
            secret += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('webhook_secret').value = secret;
    }

    function copiarTexto(elementId) {
        const inp = document.getElementById(elementId);
        inp.select();
        inp.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(inp.value);
        
        // feedback visual rápido
        const originalBg = inp.style.background;
        inp.style.background = '#dcfce7'; // green light
        setTimeout(() => { inp.style.background = originalBg; }, 600);
        // Toast style alert
        const btn = inp.nextElementSibling;
        const origIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copiado';
        btn.style.width = '120px';
        setTimeout(() => { btn.innerHTML = origIcon; btn.style.width = ''; }, 2000);
    }

    function toggleApiKeyVisibility() {
        const inp = document.getElementById('checkout_api_key');
        const icon = document.getElementById('toggle-api-key-icon');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function testarCheckoutApi() {
        const btn = document.getElementById('btn-test-api');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';
        btn.disabled = true;

        fetch('{{ route("master.configuracoes.integracoes.test-checkout-api") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(d => {
            showTestResult(d.success, d.message, d.detail || '');
        })
        .catch(e => {
            showTestResult(false, 'Erro ao conectar: ' + e.message);
        })
        .finally(() => {
            btn.innerHTML = '<i class="fas fa-plug"></i> Testar API Key';
            btn.disabled = false;
        });
    }

    function testarWebhook() {
        const btn = document.getElementById('btn-test-webhook');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';
        btn.disabled = true;

        fetch('{{ route("master.configuracoes.integracoes.test-webhook") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(d => {
            showTestResult(d.success, d.message, d.detail || '');
        })
        .catch(e => {
            showTestResult(false, 'Erro ao testar webhook: ' + e.message);
        })
        .finally(() => {
            btn.innerHTML = '<i class="fas fa-broadcast-tower"></i> Testar Webhook';
            btn.disabled = false;
        });
    }

    function showTestResult(success, message, detail) {
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;animation:fadeIn 0.2s ease;';

        const icon = success ? 'fa-check-circle' : 'fa-times-circle';
        const color = success ? '#56CA00' : '#FF4C51';
        const title = success ? 'Sucesso!' : 'Falha!';
        const bgColor = success ? '#f6ffed' : '#fff2f0';
        const borderColor = success ? '#b7eb8f' : '#ffccc7';

        overlay.innerHTML = `
            <div style="background:${bgColor};border:2px solid ${borderColor};border-radius:16px;padding:32px 40px;max-width:480px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <i class="fas ${icon}" style="font-size:3rem;color:${color};margin-bottom:16px;display:block;"></i>
                <h3 style="font-size:1.3rem;font-weight:800;color:${color};margin:0 0 12px 0;">${title}</h3>
                <p style="font-size:0.9rem;color:#4d5156;margin:0 0 8px 0;line-height:1.5;">${message}</p>
                ${detail ? `<pre style="font-size:0.72rem;color:#666;background:#f5f5f5;padding:10px;border-radius:6px;text-align:left;max-height:150px;overflow:auto;margin-top:12px;white-space:pre-wrap;">${detail}</pre>` : ''}
                <button onclick="this.closest('div').parentElement.remove()" style="margin-top:20px;background:${color};color:white;border:none;padding:10px 32px;border-radius:8px;font-weight:700;font-size:0.9rem;cursor:pointer;">Entendi</button>
            </div>
        `;

        document.body.appendChild(overlay);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove(); });
    }

    // Função para limpar banco
    function limparBanco() {
        if (!confirm('⚠️ ATENÇÃO!\n\nIsso vai apagar TODOS os dados do sistema!\n\nExceto: Usuário admin\n\nContinuar?')) return;
        if (!confirm('Tem certeza ABSOLUTAMENTE certeza?\n\nTodos os vendedores, vendas, clientes e pagamentos serão apagados!')) return;
        
        fetch('/master/limpar-banco', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert('✅ Banco de dados limpo com sucesso!');
                location.reload();
            } else {
                alert('❌ Erro: ' + (d.error || 'Desconhecido'));
            }
        })
        .catch(e => alert('❌ Erro: ' + e.message));
    }

</script>
@endsection
