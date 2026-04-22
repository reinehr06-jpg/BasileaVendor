@extends('layouts.app')
@section('title', 'Configurações')

@section('content')
<style>
    /* Design System - Materio Premium */
    :root {
        --materio-primary: #9155FD;
        --materio-primary-light: #F4EFFF;
        --materio-bg: #F4F5FA;
        --materio-surface: #FFFFFF;
        --materio-text-main: #4D5156;
        --materio-text-muted: #89898E;
        --materio-border: #E6E6E9;
        --materio-shadow: 0 4px 18px 0 rgba(0,0,0,0.1);
        --materio-radius: 12px;
        --materio-error: #FF4C51;
        --materio-success: #56CA00;
        --materio-warning: #FFB400;
        --materio-info: #16B1FF;
    }

    .settings-page {
        animation: fadeIn 0.4s ease-out;
        max-width: 1100px;
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
        padding: 14px 24px;
        border: none;
        background: transparent;
        color: var(--materio-text-muted);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        border-radius: 10px 10px 0 0;
        white-space: nowrap;
        position: relative;
    }

    .materio-tab-btn i { font-size: 1.2rem; }
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
        padding: 35px;
        border: 1px solid var(--materio-border);
        margin-bottom: 24px;
        position: relative;
    }

    .section-header {
        margin-bottom: 30px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--materio-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header h4 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--materio-text-main);
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }

    /* Forms */
    .materio-form-group { margin-bottom: 22px; }
    .materio-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--materio-text-main);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .materio-input, .materio-textarea, .materio-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--materio-border);
        border-radius: 10px;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.2s;
        background: #fff;
        color: var(--materio-text-main);
    }

    .materio-input:focus, .materio-textarea:focus, .materio-select:focus { 
        border-color: var(--materio-primary); 
        box-shadow: 0 0 0 4px rgba(145, 85, 253, 0.1);
    }

    .materio-btn-primary {
        background: var(--materio-primary); color: white; border: none; padding: 12px 28px;
        border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(145, 85, 253, 0.3);
        display: inline-flex; align-items: center; gap: 8px;
    }

    .materio-btn-primary:hover { background: #8043e6; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(145, 85, 253, 0.4); }
    .materio-btn-primary:active { transform: translateY(0); }

    /* Badges */
    .status-badge {
        padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .bg-success { background: #E8F5E9; color: #2E7D32; }
    .bg-danger { background: #FFEBEE; color: #C62828; }
    .bg-warning { background: #FFF3E0; color: #EF6C00; }
    .bg-info { background: #E3F2FD; color: #1565C0; }

    /* WhatsApp Connection Card */
    .wa-connection {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 25px; display: flex; align-items: center; gap: 20px; margin-bottom: 30px;
    }
    .wa-connection.inactive { background: #fff1f2; border-color: #fecaca; }
    .wa-icon-box { width: 60px; height: 60px; border-radius: 50%; background: #22c55e; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); }
    .wa-connection.inactive .wa-icon-box { background: #f43f5e; box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3); }
</style>

<div class="settings-page">
    <div style="margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 900; color: var(--materio-text-main); margin: 0;">Configurações</h1>
            <p style="color: var(--materio-text-muted); margin-top: 5px; font-size: 1.05rem;">Gerencie seu perfil, segurança e integrações comerciais.</p>
        </div>
        <div style="background: white; padding: 10px 20px; border-radius: 12px; border: 1px solid var(--materio-border); box-shadow: var(--materio-shadow);">
            <span style="font-size: 0.8rem; color: var(--materio-text-muted); font-weight: 700; text-transform: uppercase;">Acesso:</span>
            <span style="font-size: 0.9rem; font-weight: 800; color: var(--materio-primary); margin-left: 5px;">GESTOR COMERCIAL</span>
        </div>
    </div>

    @if(session('success'))
        <div class="materio-card" style="padding: 20px; border-left: 6px solid var(--materio-success); background: #f6ffed; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-check-circle" style="font-size: 1.5rem; color: #389e0d;"></i>
            <span style="color: #389e0d; font-weight: 700;">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="materio-card" style="padding: 20px; border-left: 6px solid var(--materio-error); background: #fff1f0; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-times-circle" style="font-size: 1.5rem; color: #cf1322;"></i>
            <span style="color: #cf1322; font-weight: 700;">{{ session('error') }}</span>
        </div>
    @endif

    <div class="materio-tabs">
        <button class="materio-tab-btn {{ $tab === 'geral' ? 'active' : '' }}" onclick="window.location.href='?tab=geral'">
            <i class="fas fa-user-circle"></i> Perfil
        </button>
        <button class="materio-tab-btn {{ $tab === 'seguranca' ? 'active' : '' }}" onclick="window.location.href='?tab=seguranca'">
            <i class="fas fa-shield-halved"></i> Segurança
        </button>
        <button class="materio-tab-btn {{ $tab === 'whatsapp' ? 'active' : '' }}" onclick="window.location.href='?tab=whatsapp'">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </button>
        <button class="materio-tab-btn {{ $tab === 'split' ? 'active' : '' }}" onclick="window.location.href='?tab=split'">
            <i class="fas fa-wallet"></i> Split
        </button>
        <button class="materio-tab-btn {{ $tab === 'aprovacoes' ? 'active' : '' }}" onclick="window.location.href='?tab=aprovacoes'">
            <i class="fas fa-comment-check"></i> Aprovações
            @if(isset($pendentes) && $pendentes->count() > 0)
                <span style="background: var(--materio-error); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 900; margin-left: 8px; box-shadow: 0 2px 6px rgba(255, 76, 81, 0.4);">{{ $pendentes->count() }}</span>
            @endif
        </button>
        <button class="materio-tab-btn {{ $tab === 'termos' ? 'active' : '' }}" onclick="window.location.href='?tab=termos'">
            <i class="fas fa-file-contract"></i> Termos
        </button>
    </div>

    <div class="tab-content">
        {{-- TAB: GERAL --}}
        @if($tab === 'geral')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-user-edit"></i> Informações do Perfil</h4>
            </div>

            <form method="POST" action="{{ route('gestor.configuracoes.perfil.update') }}">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                    <div class="materio-form-group">
                        <label class="materio-label">Nome Completo</label>
                        <input type="text" name="name" class="materio-input" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="materio-form-group">
                        <label class="materio-label">E-mail Corporativo</label>
                        <input type="email" name="email" class="materio-input" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit" class="materio-btn-primary">Atualizar Perfil</button>
                </div>
            </form>

            <div class="section-header" style="margin-top: 50px;">
                <h4><i class="fas fa-lock"></i> Alterar Senha</h4>
            </div>

            <form method="POST" action="{{ route('gestor.configuracoes.senha.update') }}">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="materio-form-group">
                        <label class="materio-label">Senha Atual</label>
                        <input type="password" name="current_password" class="materio-input" required>
                    </div>
                    <div class="materio-form-group">
                        <label class="materio-label">Nova Senha</label>
                        <input type="password" name="password" class="materio-input" required>
                    </div>
                    <div class="materio-form-group">
                        <label class="materio-label">Confirmar Nova Senha</label>
                        <input type="password" name="password_confirmation" class="materio-input" required>
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit" class="materio-btn-primary" style="background: #64748b; box-shadow: 0 4px 14px rgba(100, 116, 139, 0.3);">Redefinir Senha</button>
                </div>
            </form>
        </div>
        @endif

        {{-- TAB: SEGURANÇA (2FA) --}}
        @if($tab === 'seguranca')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-shield-check"></i> Autenticação de Dois Fatores (2FA)</h4>
                @if($user->two_factor_enabled)
                    <span class="status-badge bg-success">Protegido</span>
                @else
                    <span class="status-badge bg-warning">Recomendado</span>
                @endif
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px;">
                <div>
                    <p style="color: var(--materio-text-muted); line-height: 1.6; margin-bottom: 25px;">
                        A autenticação de dois fatores adiciona uma camada extra de segurança à sua conta. Para fazer login, você precisará fornecer sua senha e um código de verificação do seu dispositivo móvel.
                    </p>

                    @if(!$user->two_factor_enabled)
                        <div style="background: #f8f7ff; border: 1px solid #e0e0e8; border-radius: 12px; padding: 25px;">
                            <h5 style="font-weight: 700; margin-bottom: 15px; color: var(--materio-primary);">Configurar novo dispositivo</h5>
                            <ol style="padding-left: 20px; color: var(--materio-text-main); font-size: 0.9rem;">
                                <li style="margin-bottom: 10px;">Instale um app autenticador (Google Authenticator, Authy, etc).</li>
                                <li style="margin-bottom: 10px;">Escaneie o QR Code ao lado ou insira a chave manualmente.</li>
                                <li style="margin-bottom: 10px;">Digite o código de 6 dígitos gerado para confirmar.</li>
                            </ol>

                            <form action="{{ route('gestor.configuracoes.2fa.enable') }}" method="POST" style="margin-top: 25px;">
                                @csrf
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" name="code" class="materio-input" placeholder="000 000" style="text-align: center; letter-spacing: 5px; font-size: 1.2rem; font-weight: 700; max-width: 180px;" maxlength="6" required>
                                    <button type="submit" class="materio-btn-primary">Ativar Agora</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
                            <div style="display: flex; align-items: center; gap: 15px; color: #166534;">
                                <i class="fas fa-check-double" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h5 style="font-weight: 700; margin: 0;">2FA está Ativo e Protegendo sua Conta</h5>
                                    <p style="font-size: 0.85rem; margin: 5px 0 0;">Configurado em {{ count($devices ?? []) }} dispositivo(s).</p>
                                </div>
                            </div>
                        </div>

                        <h5 style="font-weight: 700; margin-bottom: 15px;">Dispositivos Conectados</h5>
                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px;">
                            @foreach($devices ?? [] as $dev)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #fff; border: 1px solid var(--materio-border); border-radius: 10px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <i class="fas fa-mobile-screen" style="color: var(--materio-primary);"></i>
                                        <span style="font-weight: 600;">{{ $dev['name'] }}</span>
                                        <code style="font-size: 0.75rem; background: #eee; padding: 2px 6px; border-radius: 4px;">{{ $dev['mask'] }}</code>
                                    </div>
                                    <span class="status-badge bg-success" style="font-size: 0.65rem;">Ativo</span>
                                </div>
                            @endforeach
                        </div>

                        <form action="{{ route('gestor.configuracoes.2fa.disable') }}" method="POST" onsubmit="return confirm('ATENÇÃO: Desativar o 2FA reduzirá significativamente a segurança da sua conta. Confirmar?')">
                            @csrf
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" name="code" class="materio-input" placeholder="Digite o código para desativar" style="max-width: 250px;" maxlength="6" required>
                                <button type="submit" class="materio-btn-primary" style="background: var(--materio-error); box-shadow: 0 4px 14px rgba(255, 76, 81, 0.3);">Desativar 2FA</button>
                            </div>
                        </form>
                    @endif
                </div>

                <div style="text-align: center;">
                    @if(isset($qrCode))
                        <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid var(--materio-border); display: inline-block; box-shadow: var(--materio-shadow);">
                            {!! $qrCode !!}
                        </div>
                        <p style="margin-top: 15px; font-weight: 700; color: var(--materio-text-main);">QR Code de Configuração</p>
                        <code style="display: block; margin-top: 10px; color: var(--materio-primary); font-size: 0.9rem;">{{ $user->two_factor_secret }}</code>
                    @endif

                    @if($user->two_factor_enabled && isset($recoveryCodes))
                        <div style="margin-top: 40px; text-align: left; background: #fffbe6; border: 1px solid #ffe58f; padding: 20px; border-radius: 12px;">
                            <h5 style="font-weight: 700; color: #856404; margin-bottom: 10px;"><i class="fas fa- lifeboat"></i> Códigos de Recuperação</h5>
                            <p style="font-size: 0.8rem; color: #856404; margin-bottom: 15px;">Guarde estes códigos em local seguro. Eles permitem o acesso se você perder seu celular.</p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                @foreach($recoveryCodes as $code)
                                    <div style="font-family: monospace; font-size: 0.9rem; background: rgba(255,255,255,0.5); padding: 5px; text-align: center; border-radius: 4px;">{{ $code }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- TAB: WHATSAPP --}}
        @if($tab === 'whatsapp')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fab fa-whatsapp"></i> Integração WhatsApp Business</h4>
                <span class="status-badge {{ $whatsappConfig->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $whatsappConfig->is_active ? 'Conectado' : 'Desconectado' }}
                </span>
            </div>

            <div class="wa-connection {{ !$whatsappConfig->is_active ? 'inactive' : '' }}">
                <div class="wa-icon-box">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div style="flex: 1;">
                    <h5 style="font-weight: 800; margin: 0; font-size: 1.1rem; color: var(--materio-text-main);">
                        {{ $whatsappConfig->is_active ? 'Sua API está ativa!' : 'Aguardando configuração da API' }}
                    </h5>
                    <p style="color: var(--materio-text-muted); margin: 5px 0 0;">
                        {{ $whatsappConfig->is_active ? 'As mensagens estão sendo distribuídas normalmente entre os vendedores.' : 'Configure as credenciais abaixo para habilitar o chatbot e a distribuição de leads.' }}
                    </p>
                </div>
                @if($whatsappConfig->is_active)
                    <div class="status-badge bg-success" style="padding: 10px 20px;">
                        <i class="fas fa-check-circle"></i> OPERACIONAL
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('gestor.configuracoes.whatsapp.update') }}">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                    <div class="materio-form-group">
                        <label class="materio-label">Número de Telefone (com DDI)</label>
                        <input type="text" name="numero_telefone" class="materio-input" value="{{ old('numero_telefone', $whatsappConfig->numero_telefone) }}" placeholder="Ex: 5511999999999" required>
                    </div>
                    <div class="materio-form-group">
                        <label class="materio-label">Provedor de API</label>
                        <select name="provider" class="materio-select" required>
                            <option value="Take" {{ $whatsappConfig->provider === 'Take' ? 'selected' : '' }}>Take Blip</option>
                            <option value="meta" {{ $whatsappConfig->provider === 'meta' ? 'selected' : '' }}>Meta (Oficial)</option>
                            <option value="WppConnect" {{ $whatsappConfig->provider === 'WppConnect' ? 'selected' : '' }}>WppConnect</option>
                            <option value="Evolution" {{ $whatsappConfig->provider === 'Evolution' ? 'selected' : '' }}>Evolution API</option>
                        </select>
                    </div>
                </div>

                <div class="materio-form-group">
                    <label class="materio-label">API Token / Access Token</label>
                    <input type="password" name="api_token" class="materio-input" value="{{ $whatsappConfig->api_token }}" placeholder="Insira o token de autenticação fornecido pelo provedor" required>
                </div>

                <div class="materio-form-group">
                    <label class="materio-label">Webhook Verify Token (Opcional)</label>
                    <input type="text" name="webhook_verify_token" class="materio-input" value="{{ $whatsappConfig->webhook_verify_token }}" placeholder="Token para validação de segurança do Webhook">
                </div>

                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px dashed var(--materio-border);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="checkbox" name="is_active" id="is_active" {{ $whatsappConfig->is_active ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: var(--materio-primary);">
                        <label for="is_active" style="font-weight: 700; color: var(--materio-text-main); cursor: pointer;">Habilitar processamento automático e distribuição de mensagens</label>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="materio-btn-primary">Salvar Configurações</button>
                    <button type="button" class="materio-btn-primary" style="background: #16B1FF; box-shadow: 0 4px 14px rgba(22, 177, 255, 0.3);">Testar Conexão</button>
                </div>
            </form>
        </div>
        @endif

        {{-- TAB: SPLIT --}}
        @if($tab === 'split')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-wallet"></i> Configurações de Recebimento (Split)</h4>
                @if(isset($vendedor) && $vendedor->wallet_status === 'validado')
                    <span class="status-badge bg-success">Validado</span>
                @else
                    <span class="status-badge bg-warning">Pendente</span>
                @endif
            </div>

            <div style="background: #F9FAFB; border: 1px solid var(--materio-border); border-radius: 12px; padding: 25px; margin-bottom: 30px;">
                <h5 style="font-weight: 800; color: var(--materio-text-main); margin-bottom: 10px;">Como funciona o Split Asaas?</h5>
                <p style="color: var(--materio-text-muted); font-size: 0.95rem; line-height: 1.6; margin: 0;">
                    Para receber suas comissões automaticamente, você precisa vincular sua **Wallet ID** do Asaas. Uma vez vinculada, o sistema dividirá automaticamente os valores das vendas entre a plataforma e sua conta, sem necessidade de transferências manuais.
                </p>
            </div>

            <form method="POST" action="{{ route('gestor.configuracoes.split.update') }}">
                @csrf
                @method('PUT')
                <div class="materio-form-group">
                    <label class="materio-label">Seu Asaas Wallet ID</label>
                    <input type="text" name="asaas_wallet_id" class="materio-input" 
                           value="{{ old('asaas_wallet_id', $vendedor->asaas_wallet_id ?? '') }}" 
                           {{ (isset($vendedor) && $vendedor->wallet_status === 'validado') ? 'readonly' : '' }} 
                           placeholder="Digite o ID da sua subconta Asaas" required>
                    @if(isset($vendedor) && $vendedor->wallet_status === 'validado')
                        <div style="color: var(--materio-success); font-size: 0.8rem; margin-top: 8px; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Sua carteira já foi validada e está pronta para receber.
                        </div>
                    @else
                        <div style="color: var(--materio-text-muted); font-size: 0.8rem; margin-top: 8px;">
                            Você encontra este ID nas configurações da sua conta no painel do Asaas.
                        </div>
                    @endif
                </div>

                @if(!isset($vendedor) || $vendedor->wallet_status !== 'validado')
                    <div style="margin-top: 25px;">
                        <button type="submit" class="materio-btn-primary">Vincular Carteira</button>
                    </div>
                @endif
            </form>
        </div>
        @endif

        {{-- TAB: APROVAÇÕES --}}
        @if($tab === 'aprovacoes')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-comment-check"></i> Revisão de Primeiras Mensagens</h4>
                <p style="color: var(--materio-text-muted); font-size: 0.9rem; margin: 0;">Aprove ou rejeite as abordagens iniciais sugeridas pelos vendedores.</p>
            </div>

            @forelse($pendentes ?? [] as $msg)
                <div style="border: 1px solid var(--materio-border); border-radius: 12px; padding: 25px; margin-bottom: 20px; background: white; transition: all 0.2s; position: relative;" onmouseover="this.style.borderColor='var(--materio-primary)'" onmouseout="this.style.borderColor='var(--materio-border)'">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 45px; height: 45px; background: var(--materio-primary-light); color: var(--materio-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800;">
                                {{ substr($msg->usuario->name, 0, 1) }}
                            </div>
                            <div>
                                <h5 style="font-weight: 800; margin: 0; color: var(--materio-text-main);">{{ $msg->usuario->name }}</h5>
                                <span style="font-size: 0.75rem; color: var(--materio-text-muted); text-transform: uppercase; font-weight: 700;">Vendedor</span>
                            </div>
                        </div>
                        <div class="status-badge bg-warning">Pendente</div>
                    </div>

                    <div style="background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                        <p style="margin: 0; font-size: 1rem; color: var(--materio-text-main); line-height: 1.6; white-space: pre-wrap;">{{ $msg->mensagem }}</p>
                    </div>

                    <div style="display: flex; gap: 15px; justify-content: flex-end;">
                        <form action="{{ route('gestor.aprovar-mensagem.aprovar', $msg) }}" method="POST">
                            @csrf
                            <button type="submit" class="materio-btn-primary" style="background: var(--materio-success); box-shadow: 0 4px 12px rgba(86, 202, 0, 0.3);">Aprovar Mensagem</button>
                        </form>
                        <button class="materio-btn-primary" style="background: #cf1322; box-shadow: 0 4px 12px rgba(207, 19, 34, 0.3);" onclick="document.getElementById('rejeitar-form-{{ $msg->id }}').style.display = 'block'; this.style.display='none'">Rejeitar</button>
                    </div>

                    <div id="rejeitar-form-{{ $msg->id }}" style="display: none; margin-top: 25px; padding-top: 25px; border-top: 1px dashed #ffa39e;">
                        <form action="{{ route('gestor.aprovar-mensagem.rejeitar', $msg) }}" method="POST">
                            @csrf
                            <label class="materio-label" style="color: #cf1322;">Motivo da Rejeição</label>
                            <textarea name="motivo" class="materio-textarea" rows="3" placeholder="Explique ao vendedor o que precisa ser alterado..." required style="border-color: #ffa39e;"></textarea>
                            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                                <button type="button" class="materio-btn-primary" style="background: #64748b; box-shadow: none;" onclick="location.reload()">Cancelar</button>
                                <button type="submit" class="materio-btn-primary" style="background: #cf1322; box-shadow: 0 4px 12px rgba(207, 19, 34, 0.3);">Confirmar Rejeição</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 80px 20px;">
                    <div style="width: 100px; height: 100px; background: #E8F5E9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; box-shadow: 0 10px 25px rgba(232, 245, 233, 1);">
                        <i class="fas fa-thumbs-up" style="font-size: 3rem; color: #2E7D32;"></i>
                    </div>
                    <h5 style="font-weight: 900; color: var(--materio-text-main); font-size: 1.4rem;">Tudo em Dia!</h5>
                    <p style="color: var(--materio-text-muted); font-size: 1.1rem;">Não há mensagens pendentes de revisão no momento.</p>
                </div>
            @endforelse
        </div>
        @endif

        {{-- TAB: TERMOS --}}
        @if($tab === 'termos')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-file-contract"></i> Termos e Políticas</h4>
                <p style="color: var(--materio-text-muted); font-size: 0.9rem; margin: 0;">Consulte os termos de uso e políticas da plataforma.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 40px;">
                {{-- Termos de Uso --}}
                <div style="border-bottom: 1px solid var(--materio-border); padding-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h5 style="font-weight: 800; color: var(--materio-text-main); margin: 0; font-size: 1.1rem;">1. Termos de Uso</h5>
                        @if(isset($termoUso))
                            <span class="status-badge bg-info">Versão {{ $termoUso->versao }}</span>
                        @endif
                    </div>
                    
                    <div style="background: #F9FAFB; border: 1px solid var(--materio-border); border-radius: 12px; padding: 25px; max-height: 400px; overflow-y: auto; color: var(--materio-text-main); line-height: 1.6;">
                        @if(isset($termoUso))
                            {!! $termoUso->conteudo_html !!}
                        @else
                            <p style="text-align: center; color: var(--materio-text-muted); padding: 20px;">Nenhum termo de uso ativo no momento.</p>
                        @endif
                    </div>

                    @if(isset($termoUso))
                    <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                        <a href="{{ route('gestor.configuracoes.termos.pdf', $termoUso) }}" class="materio-btn-primary" style="background: #64748b; box-shadow: none;">
                            <i class="fas fa-file-pdf"></i> Baixar Termos em PDF
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Política de Privacidade --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h5 style="font-weight: 800; color: var(--materio-text-main); margin: 0; font-size: 1.1rem;">2. Política de Privacidade</h5>
                        @if(isset($termoPrivacidade))
                            <span class="status-badge bg-info">Versão {{ $termoPrivacidade->versao }}</span>
                        @endif
                    </div>
                    
                    <div style="background: #F9FAFB; border: 1px solid var(--materio-border); border-radius: 12px; padding: 25px; max-height: 400px; overflow-y: auto; color: var(--materio-text-main); line-height: 1.6;">
                        @if(isset($termoPrivacidade))
                            {!! $termoPrivacidade->conteudo_html !!}
                        @else
                            <p style="text-align: center; color: var(--materio-text-muted); padding: 20px;">Nenhuma política de privacidade ativa no momento.</p>
                        @endif
                    </div>

                    @if(isset($termoPrivacidade))
                    <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                        <a href="{{ route('gestor.configuracoes.termos.pdf', $termoPrivacidade) }}" class="materio-btn-primary" style="background: #64748b; box-shadow: none;">
                            <i class="fas fa-file-pdf"></i> Baixar Política em PDF
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</script>
@endsection
