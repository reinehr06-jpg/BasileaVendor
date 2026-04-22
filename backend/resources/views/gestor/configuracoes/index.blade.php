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

    .materio-input, .materio-select, .materio-textarea {
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
    </div>

    <div class="tab-content">
        {{-- TAB: GERAL --}}
        @if($tab === 'geral')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-id-card"></i> Dados do Perfil</h4>
            </div>
            <form action="{{ route('gestor.configuracoes.perfil.update') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                    <div class="materio-form-group">
                        <label class="materio-label">Nome Completo</label>
                        <input type="text" name="name" class="materio-input" value="{{ $user->name }}" required>
                    </div>
                    <div class="materio-form-group">
                        <label class="materio-label">E-mail de Acesso</label>
                        <input type="email" name="email" class="materio-input" value="{{ $user->email }}" required>
                    </div>
                </div>
                <button type="submit" class="materio-btn-primary"><i class="fas fa-save"></i> Atualizar Dados</button>
            </form>
        </div>
        @endif

        {{-- TAB: SEGURANÇA --}}
        @if($tab === 'seguranca')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-key"></i> Alterar Senha</h4>
            </div>
            <form action="{{ route('vendedor.configuracoes.senha.update') }}" method="POST">
                @csrf
                <div class="materio-form-group">
                    <label class="materio-label">Senha Atual</label>
                    <input type="password" name="current_password" class="materio-input" required placeholder="••••••••">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                    <div class="materio-form-group">
                        <label class="materio-label">Nova Senha</label>
                        <input type="password" name="password" class="materio-input" required placeholder="Mínimo 8 caracteres">
                    </div>
                    <div class="materio-form-group">
                        <label class="materio-label">Confirmar Nova Senha</label>
                        <input type="password" name="password_confirmation" class="materio-input" required placeholder="Repita a nova senha">
                    </div>
                </div>
                <button type="submit" class="materio-btn-primary"><i class="fas fa-lock"></i> Atualizar Senha</button>
            </form>
        </div>

        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-shield-halved"></i> Autenticação 2FA</h4>
                <span class="status-badge {{ $user->two_factor_enabled ? 'bg-success' : 'bg-warning' }}">
                    {{ $user->two_factor_enabled ? 'Ativado' : 'Não Ativado' }}
                </span>
            </div>
            
            @if($user->two_factor_enabled)
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                    <div>
                        <h5 style="font-weight: 800; color: var(--materio-text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-mobile-screen-button" style="color: var(--materio-primary);"></i> Meus Dispositivos
                        </h5>
                        @foreach($devices as $device)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #fafafa; border: 1px solid var(--materio-border); border-radius: 12px; margin-bottom: 12px; transition: 0.2s hover:border-primary;">
                                <div>
                                    <strong style="color: var(--materio-text-main);">{{ $device['name'] }}</strong><br>
                                    <small style="color: var(--materio-text-muted);">{{ $device['mask'] }}</small>
                                </div>
                                <i class="fas fa-circle-check" style="color: var(--materio-success);"></i>
                            </div>
                        @endforeach
                        <form action="{{ route('vendedor.configuracoes.2fa.add-device') }}" method="POST" style="margin-top: 20px;">
                            @csrf
                            <div class="materio-form-group">
                                <input type="text" name="device_name" class="materio-input" placeholder="Ex: Meu iPhone 15" required>
                            </div>
                            <button type="submit" class="materio-btn-primary" style="width: 100%; justify-content: center;"><i class="fas fa-plus"></i> Adicionar Celular</button>
                        </form>
                    </div>
                    <div>
                        <h5 style="font-weight: 800; color: var(--materio-text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-file-shield" style="color: var(--materio-primary);"></i> Códigos de Recuperação
                        </h5>
                        <p style="font-size: 0.85rem; color: var(--materio-text-muted); margin-bottom: 15px;">Imprima ou salve estes códigos em um local seguro.</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 25px;">
                            @if($recoveryCodes)
                                @foreach($recoveryCodes as $code)
                                    <div style="font-family: 'Roboto Mono', monospace; font-size: 0.9rem; background: #F4F5FA; padding: 10px; border-radius: 8px; text-align: center; border: 1px dashed var(--materio-border); color: var(--materio-primary); font-weight: 700;">{{ $code }}</div>
                                @endforeach
                            @endif
                        </div>
                        <form action="{{ route('vendedor.configuracoes.2fa.disable') }}" method="POST" style="padding-top: 25px; border-top: 1px solid var(--materio-border);">
                            @csrf
                            <label class="materio-label">Desativar 2FA (Digite o código do app)</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="code" class="materio-input" placeholder="000 000" required>
                                <button type="submit" class="materio-btn-primary" style="background: var(--materio-error); box-shadow: 0 4px 14px rgba(255, 76, 81, 0.3);">Desativar</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div style="display: flex; gap: 40px; align-items: flex-start; padding: 20px; background: #fefce8; border: 1px solid #fef08a; border-radius: 15px;">
                    <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #fef08a; flex-shrink: 0; box-shadow: 0 8px 25px rgba(254, 240, 138, 0.3);">
                        {!! $qrCode !!}
                    </div>
                    <div style="flex: 1;">
                        <h5 style="font-weight: 800; color: #854d0e; margin-bottom: 15px;">Proteja sua Conta Agora</h5>
                        <p style="font-size: 0.95rem; color: #a16207; line-height: 1.6; margin-bottom: 25px;">
                            1. Instale o Google Authenticator ou Authy.<br>
                            2. Escaneie este QR Code exclusivo.<br>
                            3. Digite o código de 6 dígitos gerado no app.
                        </p>
                        <form action="{{ route('vendedor.configuracoes.2fa.enable') }}" method="POST">
                            @csrf
                            <div class="materio-form-group">
                                <input type="text" name="code" class="materio-input" style="font-size: 1.5rem; text-align: center; letter-spacing: 10px; font-weight: 900;" placeholder="000000" required>
                            </div>
                            <button type="submit" class="materio-btn-primary" style="width: 100%; justify-content: center;"><i class="fas fa-shield-check"></i> Ativar Proteção 2FA</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- TAB: WHATSAPP --}}
        @if($tab === 'whatsapp')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fab fa-whatsapp"></i> Integração WhatsApp</h4>
            </div>
            
            <div class="wa-connection {{ $whatsappConfig->is_active ? '' : 'inactive' }}">
                <div class="wa-icon-box">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div style="flex: 1;">
                    <h5 style="font-weight: 800; color: var(--materio-text-main); margin: 0; font-size: 1.2rem;">
                        {{ $whatsappConfig->is_active ? 'Conexão Ativa' : 'Integração Pausada' }}
                    </h5>
                    <p style="color: var(--materio-text-muted); margin: 5px 0 0; font-size: 0.95rem;">
                        {{ $whatsappConfig->is_active ? 'O sistema está recebendo e enviando mensagens normalmente.' : 'As mensagens não serão sincronizadas enquanto estiver pausado.' }}
                    </p>
                </div>
                <div class="status-badge {{ $whatsappConfig->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $whatsappConfig->is_active ? 'ONLINE' : 'OFFLINE' }}
                </div>
            </div>

            <form action="{{ route('gestor.configuracoes.whatsapp.update') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                    <div class="materio-form-group">
                        <label class="materio-label">Número do WhatsApp</label>
                        <input type="text" name="numero_telefone" class="materio-input" value="{{ $whatsappConfig->numero_telefone }}" placeholder="Ex: 5547999999999" required>
                    </div>
                    <div class="materio-form-group">
                        <label class="materio-label">Provedor de API</label>
                        <select name="provider" class="materio-input" required>
                            <option value="meta" {{ $whatsappConfig->provider === 'meta' ? 'selected' : '' }}>Meta (Oficial)</option>
                            <option value="Evolution" {{ $whatsappConfig->provider === 'Evolution' ? 'selected' : '' }}>Evolution API</option>
                            <option value="WppConnect" {{ $whatsappConfig->provider === 'WppConnect' ? 'selected' : '' }}>WppConnect</option>
                            <option value="Take" {{ $whatsappConfig->provider === 'Take' ? 'selected' : '' }}>Take Blip</option>
                        </select>
                    </div>
                </div>
                <div class="materio-form-group">
                    <label class="materio-label">Token de Acesso / API Key</label>
                    <input type="password" name="api_token" class="materio-input" value="{{ $whatsappConfig->api_token }}" required placeholder="Digite o token da API">
                </div>
                <div class="materio-form-group" style="padding: 20px; background: #f8fafc; border: 1px solid var(--materio-border); border-radius: 12px;">
                    <label class="materio-label" style="color: #64748b;">Verify Token para Webhook</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="webhook_verify_token" class="materio-input" value="{{ $whatsappConfig->webhook_verify_token }}" readonly style="background: #fff; font-family: monospace;">
                        <button type="button" class="materio-btn-primary" style="padding: 10px; background: #64748b; box-shadow: none;" onclick="navigator.clipboard.writeText('{{ $whatsappConfig->webhook_verify_token }}')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small style="color: #94a3b8; display: block; margin-top: 10px;">Copie este token para configurar o recebimento de mensagens no seu provedor.</small>
                </div>
                <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 15px; padding: 15px; border-radius: 12px; background: rgba(145, 85, 253, 0.05);">
                    <input type="checkbox" name="is_active" value="1" {{ $whatsappConfig->is_active ? 'checked' : '' }} style="width: 22px; height: 22px; accent-color: var(--materio-primary);">
                    <span style="font-weight: 800; color: var(--materio-primary);">Manter integração ativa e processando dados</span>
                </div>
                <button type="submit" class="materio-btn-primary"><i class="fas fa-cloud-arrow-up"></i> Salvar Integração</button>
            </form>
        </div>
        @endif

        {{-- TAB: SPLIT --}}
        @if($tab === 'split')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-wallet"></i> Split & Recebimentos</h4>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 40px;">
                <div style="background: linear-gradient(135deg, #F4EFFF 0%, #FFFFFF 100%); border: 1px solid #E9D9FF; padding: 30px; border-radius: 20px; text-align: center; position: relative; overflow: hidden;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: #7c3aed; font-weight: 900; letter-spacing: 1.5px; margin-bottom: 10px;">Comissão Venda Direta</div>
                    <div style="font-size: 2.5rem; font-weight: 900; color: #4C1D95;">
                        {{ ($vendedor->tipo_split ?? 'percentual') === 'percentual' ? ($vendedor->valor_split_inicial ?? 0) . '%' : 'R$ ' . number_format($vendedor->valor_split_inicial ?? 0, 2, ',', '.') }}
                    </div>
                    <i class="fas fa-sack-dollar" style="position: absolute; bottom: -10px; right: -10px; font-size: 5rem; opacity: 0.05; color: #4C1D95;"></i>
                </div>
                <div style="background: linear-gradient(135deg, #F4EFFF 0%, #FFFFFF 100%); border: 1px solid #E9D9FF; padding: 30px; border-radius: 20px; text-align: center; position: relative; overflow: hidden;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: #7c3aed; font-weight: 900; letter-spacing: 1.5px; margin-bottom: 10px;">Recorrência Mensal</div>
                    <div style="font-size: 2.5rem; font-weight: 900; color: #4C1D95;">
                        {{ ($vendedor->tipo_split ?? 'percentual') === 'percentual' ? ($vendedor->valor_split_recorrencia ?? 0) . '%' : 'R$ ' . number_format($vendedor->valor_split_recorrencia ?? 0, 2, ',', '.') }}
                    </div>
                    <i class="fas fa-arrows-rotate" style="position: absolute; bottom: -10px; right: -10px; font-size: 5rem; opacity: 0.05; color: #4C1D95;"></i>
                </div>
            </div>

            <form action="{{ route('gestor.configuracoes.split.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="materio-form-group">
                    <label class="materio-label">ID da Carteira Asaas (Wallet ID)</label>
                    <input type="text" name="asaas_wallet_id" class="materio-input" value="{{ $vendedor->asaas_wallet_id }}" {{ $vendedor->wallet_status === 'validado' ? 'readonly' : '' }} placeholder="Ex: b65c69b2-...">
                    @if($vendedor->wallet_status === 'validado')
                        <div style="margin-top: 12px; padding: 12px; background: #e8f5e9; border-radius: 10px; color: #2e7d32; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-shield-check"></i> Carteira Validada e Operacional
                        </div>
                    @else
                        <div style="margin-top: 12px; padding: 12px; background: #fff8e1; border-radius: 10px; color: #f57f17; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-clock"></i> Pendente de Validação ou Configuração
                        </div>
                        <button type="submit" class="materio-btn-primary" style="margin-top: 20px;"><i class="fas fa-save"></i> Salvar Wallet ID</button>
                    @endif
                </div>
            </form>
        </div>
        @endif

        {{-- TAB: APROVAÇÕES --}}
        @if($tab === 'aprovacoes')
        <div class="materio-card">
            <div class="section-header">
                <h4><i class="fas fa-clipboard-list"></i> Aprovação de Mensagens</h4>
            </div>
            
            @forelse($pendentes as $p)
                <div style="border: 1px solid var(--materio-border); border-radius: 15px; padding: 30px; margin-bottom: 25px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: 0.3s hover:shadow-md;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--materio-border);">
                        <div>
                            <h5 style="font-size: 1.2rem; font-weight: 900; color: var(--materio-text-main); margin: 0;">{{ $p->titulo }}</h5>
                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                                <span class="status-badge bg-info" style="font-size: 0.65rem;">Solicitação</span>
                                <span style="font-size: 0.85rem; color: var(--materio-primary); font-weight: 800;">De: {{ $p->usuario->name }}</span>
                                <span style="font-size: 0.8rem; color: var(--materio-text-muted); margin-left: 10px;"><i class="far fa-clock"></i> {{ $p->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <form action="{{ route('gestor.aprovar-mensagem.aprovar', $p->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="materio-btn-primary" style="background: var(--materio-success); padding: 10px 20px; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(86, 202, 0, 0.3);">
                                    <i class="fas fa-check"></i> Aprovar
                                </button>
                            </form>
                            <button type="button" class="materio-btn-primary" style="background: var(--materio-error); padding: 10px 20px; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(255, 76, 81, 0.3);" 
                                    onclick="document.getElementById('rejeitar-form-{{ $p->id }}').style.display='block'; this.style.display='none'">
                                <i class="fas fa-times"></i> Rejeitar
                            </button>
                        </div>
                    </div>
                    
                    <div style="background: #F4F5FA; padding: 25px; border-radius: 12px; border-left: 5px solid var(--materio-primary); position: relative;">
                        <i class="fas fa-quote-left" style="position: absolute; top: 10px; left: 10px; font-size: 1.5rem; opacity: 0.1; color: var(--materio-primary);"></i>
                        <p style="margin: 0; font-size: 1rem; line-height: 1.7; color: #3b4252; white-space: pre-line; font-weight: 500;">{{ $p->mensagem }}</p>
                    </div>

                    <div id="rejeitar-form-{{ $p->id }}" style="display: none; margin-top: 25px; padding: 20px; border-radius: 12px; background: #fff1f0; border: 1px solid #ffa39e; animation: slideDown 0.3s ease-out;">
                        <form action="{{ route('gestor.aprovar-mensagem.rejeitar', $p->id) }}" method="POST">
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
    </div>
</div>

<script>
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</script>
@endsection
