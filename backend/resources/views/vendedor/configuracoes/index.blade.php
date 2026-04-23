@extends('layouts.app')
@php $hide_banner = true; @endphp
@section('title', 'Configurações')

@section('content')
<style>
    .settings-tabs { display: flex; gap: 4px; margin-bottom: 24px; background: white; border-radius: 12px; padding: 6px; border: 1px solid #ededf2; }
    .settings-tab { padding: 10px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; color: #a1a1b5; transition: 0.2s; display: flex; align-items: center; gap: 6px; }
    .settings-tab:hover { background: #f4f5fa; color: #3b3b5c; }
    .settings-tab.active { background: #4C1D95; color: white; }
    .settings-tab.active i { color: white; }
    .settings-tab i { font-size: 1rem; }

    .settings-card { background: white; border-radius: 14px; border: 1px solid #ededf2; padding: 28px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(50,50,71,0.04); }
    .settings-card h3 { font-size: 1.1rem; font-weight: 700; color: #3b3b5c; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
    .settings-card h3 i { color: #4C1D95; }
    .settings-card .desc { color: #a1a1b5; font-size: 0.85rem; margin-bottom: 20px; }

    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 0.78rem; font-weight: 600; color: #3b3b5c; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px; }
    .form-group input, .form-group select { width: 100%; padding: 10px 14px; border: 1.5px solid #e0e0e8; border-radius: 8px; font-size: 0.9rem; background: #fafafa; transition: 0.2s; box-sizing: border-box; }
    .form-group input:focus, .form-group select:focus { border-color: #4C1D95; background: white; box-shadow: 0 0 0 3px rgba(76,29,149,0.1); }

    .btn-save { padding: 10px 24px; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.2s; }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(76,29,149,0.3); }

    .twofa-status { display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 10px; margin-bottom: 16px; }
    .twofa-status.active { background: #dcfce7; border: 1px solid #86efac; }
    .twofa-status.inactive { background: #fef3c7; border: 1px solid #fbbf24; }
    .twofa-status .icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .twofa-status.active .icon { background: #166534; color: white; }
    .twofa-status.inactive .icon { background: #f59e0b; color: white; }
    .twofa-status .info h4 { font-size: 0.9rem; font-weight: 700; margin-bottom: 2px; }
    .twofa-status .info p { font-size: 0.8rem; color: #6b7280; }

    .twofa-setup { background: #f8f7ff; border: 1px solid #e0e0e8; border-radius: 12px; padding: 24px; margin-top: 16px; }
    .twofa-setup .step { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px; }
    .twofa-setup .step-num { width: 24px; height: 24px; border-radius: 50%; background: #4C1D95; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0; margin-top: 2px; }
    .twofa-setup .step-text { font-size: 0.85rem; color: #3b3b5c; }
    .twofa-setup .step-text strong { color: #4C1D95; }
    .twofa-setup .secret-box { background: white; border: 2px solid #4C1D95; border-radius: 10px; padding: 14px; font-family: monospace; font-size: 1.1rem; font-weight: 700; letter-spacing: 3px; text-align: center; color: #4C1D95; margin: 12px 0; word-break: break-all; }
    .twofa-input { width: 100%; max-width: 200px; padding: 14px 16px; border: 2px solid #e0e0e8; border-radius: 10px; font-size: 1.5rem; font-weight: 700; text-align: center; letter-spacing: 8px; outline: none; transition: 0.2s; box-sizing: border-box; }
    .twofa-input:focus { border-color: #4C1D95; box-shadow: 0 0 0 3px rgba(76,29,149,0.15); }

    .btn-danger { padding: 10px 24px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.2s; }
    .btn-danger:hover { background: #dc2626; }
    .btn-success { padding: 10px 24px; background: #16a34a; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.2s; }
    .btn-success:hover { background: #15803d; }

    .recovery-codes { background: #f4f5fa; border: 1px solid #e0e0e8; border-radius: 8px; padding: 14px; margin-top: 16px; }
    .recovery-codes h4 { font-size: 0.85rem; font-weight: 700; color: #3b3b5c; margin-bottom: 8px; }
    .recovery-codes .codes { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .recovery-codes .code { font-family: monospace; font-size: 0.85rem; color: #4C1D95; font-weight: 600; background: white; padding: 6px 10px; border-radius: 6px; text-align: center; }
</style>

<x-page-hero title="Configurações" subtitle="Gerencie seu perfil, segurança e preferências" icon="fas fa-gear" />

{{-- Tabs --}}
<div class="settings-tabs">
    <a href="{{ route('vendedor.configuracoes', ['tab' => 'perfil']) }}" class="settings-tab {{ $tab === 'perfil' ? 'active' : '' }}">
        <i class="fas fa-user"></i> Perfil
    </a>
    <a href="{{ route('vendedor.configuracoes', ['tab' => 'seguranca']) }}" class="settings-tab {{ $tab === 'seguranca' ? 'active' : '' }}">
        <i class="fas fa-shield-halved"></i> Segurança
    </a>
    <a href="{{ route('vendedor.configuracoes', ['tab' => 'split']) }}" class="settings-tab {{ $tab === 'split' ? 'active' : '' }}">
        <i class="fas fa-wallet"></i> Split
    </a>
    <a href="{{ route('vendedor.configuracoes', ['tab' => 'termos']) }}" class="settings-tab {{ $tab === 'termos' ? 'active' : '' }}">
        <i class="fas fa-file-contract"></i> Termos
    </a>
</div>

{{-- PERFIL --}}
@if($tab === 'perfil')
<div class="settings-card">
    <h3><i class="fas fa-user"></i> Dados do Perfil</h3>
    <p class="desc">Atualize suas informações pessoais</p>

    <form method="POST" action="{{ route('vendedor.configuracoes.perfil.update') }}">
        @csrf
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Salvar Alterações</button>
    </form>
</div>

{{-- SEGURANCA --}}
@elseif($tab === 'seguranca')

{{-- Alterar Senha --}}
<div class="settings-card">
    <h3><i class="fas fa-key"></i> Alterar Senha</h3>
    <p class="desc">Sua senha deve ter no mínimo 8 caracteres, com letras maiúsculas, minúsculas, números e símbolos</p>

    <form method="POST" action="{{ route('vendedor.configuracoes.senha.update') }}">
        @csrf
        <div class="form-group">
            <label>Senha Atual</label>
            <input type="password" name="current_password" required>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>Nova Senha</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirmar Nova Senha</label>
                <input type="password" name="password_confirmation" required>
            </div>
        </div>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Atualizar Senha</button>
    </form>
</div>

{{-- 2FA --}}
<div class="settings-card">
    <h3><i class="fas fa-shield-halved"></i> Autenticação de Dois Fatores (2FA)</h3>
    <p class="desc">Proteja sua conta com uma camada extra de segurança</p>

    @if($user->two_factor_enabled)
    <div class="twofa-status active">
        <div class="icon"><i class="fas fa-check"></i></div>
        <div class="info">
            <h4>2FA Ativado</h4>
            <p>Sua conta está protegida por autenticação em dois fatores.</p>
        </div>
    </div>
    
    <div style="margin-top: 24px;">
        <h4 style="font-size: 0.85rem; font-weight: 700; color: #3b3b5c; margin-bottom: 12px; text-transform: uppercase;">Ações de Segurança</h4>
        <div style="display: flex; gap: 10px;">
            <form method="POST" action="{{ route('vendedor.configuracoes.2fa.rotate') }}">
                @csrf
                <button type="submit" class="btn-save" style="background: #6366f1;"><i class="fas fa-sync"></i> Rotacionar Chave</button>
            </form>
            <button onclick="document.getElementById('disable-2fa-form').style.display='block'" class="btn-danger"><i class="fas fa-power-off"></i> Desativar</button>
        </div>

        <div id="disable-2fa-form" style="display: none; margin-top: 20px; padding: 20px; background: #fee2e2; border-radius: 12px; border: 1px solid #fecaca;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #991b1b; margin-bottom: 12px;">Confirmar Desativação</h4>
            <form method="POST" action="{{ route('vendedor.configuracoes.2fa.disable') }}">
                @csrf
                <div class="form-group">
                    <label style="color: #991b1b;">Código do Autenticador</label>
                    <input type="text" name="code" class="twofa-input" placeholder="000000" maxlength="6" required>
                </div>
                <button type="submit" class="btn-danger">Confirmar Desativação Permanente</button>
            </form>
        </div>

        @if(isset($recoveryCodes))
        <div class="recovery-codes">
            <h4>Códigos de Recuperação</h4>
            <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 12px;">Guarde estes códigos em local seguro. Se perder o acesso ao app, use um deles para entrar.</p>
            <div class="codes">
                @foreach(json_decode($user->recovery_codes, true) as $code)
                    <div class="code">{{ $code }}</div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="twofa-status inactive">
        <div class="icon"><i class="fas fa-exclamation"></i></div>
        <div class="info">
            <h4>2FA Desativado</h4>
            <p>Recomendamos fortemente a ativação para maior segurança.</p>
        </div>
    </div>

    <div class="twofa-setup">
        <div class="step">
            <div class="step-num">1</div>
            <div class="step-text">Instale um aplicativo autenticador como <strong>Google Authenticator</strong> ou <strong>Authy</strong>.</div>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <div class="step-text">Escaneie o QR Code abaixo ou insira a chave manual no aplicativo.</div>
        </div>

        <div style="display: flex; flex-direction: column; align-items: center; margin: 24px 0;">
            @if(isset($qrCode))
                <div style="background: white; padding: 12px; border-radius: 12px; border: 1px solid #e0e0e8; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
                    {!! $qrCode !!}
                </div>
            @endif
            <div class="secret-box">{{ $user->two_factor_secret }}</div>
            <p style="font-size: 0.75rem; color: #a1a1b5;">Chave manual de configuração</p>
        </div>

        <div class="step">
            <div class="step-num">3</div>
            <div class="step-text">Digite o código de 6 dígitos gerado pelo aplicativo para confirmar.</div>
        </div>

        <form method="POST" action="{{ route('vendedor.configuracoes.2fa.enable') }}" style="display: flex; flex-direction: column; align-items: center; gap: 16px; margin-top: 10px;">
            @csrf
            <input type="text" name="code" class="twofa-input" placeholder="000000" maxlength="6" required>
            <button type="submit" class="btn-save" style="width: 100%; max-width: 200px;">Ativar Proteção 2FA</button>
        </form>
    </div>
    @endif
</div>

{{-- SPLIT --}}
@elseif($tab === 'split')
<div class="settings-card">
    <h3><i class="fas fa-wallet"></i> Configurações de Split (Asaas)</h3>
    <p class="desc">Gerencie seus recebimentos automáticos</p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
        <div class="rate-box" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; text-align: center;">
            <div class="rate-label" style="font-size: 0.72rem; color: #166534; text-transform: uppercase;">Vendas Diretas</div>
            <div class="rate-value" style="font-size: 1.4rem; font-weight: 800; color: #15803d; margin: 4px 0;">
                {{ ($vendedor->tipo_split ?? 'percentual') === 'percentual' ? ($vendedor->valor_split ?? 0) . '%' : 'R$ ' . number_format($vendedor->valor_split ?? 0, 2, ',', '.') }}
            </div>
            <div class="rate-type" style="font-size: 0.75rem; color: #a1a1b5;">Comissão Atribuída</div>
        </div>
        <div class="rate-box" style="background: #f8f7ff; border: 1px solid #e0e0e8; border-radius: 12px; padding: 16px; text-align: center;">
            <div class="rate-label" style="font-size: 0.72rem; color: #6b7280; text-transform: uppercase;">Renovações</div>
            <div class="rate-value" style="font-size: 1.4rem; font-weight: 800; color: #4C1D95; margin: 4px 0;">
                {{ ($vendedor->tipo_split ?? 'percentual') === 'percentual' ? ($vendedor->valor_split_recorrencia ?? 0) . '%' : 'R$ ' . number_format($vendedor->valor_split_recorrencia ?? 0, 2, ',', '.') }}
            </div>
            <div class="rate-type" style="font-size: 0.75rem; color: #a1a1b5;">Comissão Atribuída</div>
        </div>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

    <form method="POST" action="{{ route('vendedor.configuracoes.split.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Wallet ID Asaas</label>
            <input type="text" name="asaas_wallet_id" value="{{ old('asaas_wallet_id', $vendedor->asaas_wallet_id ?? '') }}" {{ (($vendedor->wallet_status ?? '') === 'validado') ? 'readonly' : '' }} placeholder="Digite o ID da sua carteira Asaas">
            @if(($vendedor->wallet_status ?? '') === 'validado')
            <div style="color: #16a34a; font-size: 0.8rem; margin-top: 4px;"><i class="fas fa-check-circle"></i> Wallet validada — não pode ser alterada</div>
            @else
            <div style="color: #a1a1b5; font-size: 0.75rem; margin-top: 4px;">Utilize o Wallet ID gerada no Asaas para receber as comissões via split.</div>
            @endif
        </div>

        @if(($vendedor->wallet_status ?? '') !== 'validado')
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Salvar Wallet ID</button>
        @endif
    </form>
</div>

{{-- TERMOS --}}
@elseif($tab === 'termos')
<div class="settings-card">
    <h3><i class="fas fa-file-contract"></i> Termos e Políticas</h3>
    <p class="desc">Consulte os documentos legais da plataforma</p>

    <div style="display: flex; flex-direction: column; gap: 40px; margin-top: 20px;">
        {{-- Termos de Uso --}}
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="font-size: 1rem; font-weight: 700; color: #3b3b5c; margin: 0;">1. Termos de Uso</h4>
                @if(isset($termoUso))
                    <span style="padding: 4px 10px; background: #E3F2FD; color: #1565C0; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">VERSÃO {{ $termoUso->versao }}</span>
                @endif
            </div>
            
            <div style="background: #F9FAFB; border: 1px solid #e0e0e8; border-radius: 12px; padding: 20px; max-height: 400px; overflow-y: auto; color: #4b5563; line-height: 1.6; font-size: 0.9rem;">
                @if(isset($termoUso))
                    {!! $termoUso->conteudo_html !!}
                @else
                    <p style="text-align: center; color: #9ca3af; padding: 20px;">Nenhum termo de uso ativo.</p>
                @endif
            </div>

            @if(isset($termoUso))
            <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
                <a href="{{ route('vendedor.configuracoes.termos.pdf', $termoUso) }}" class="btn-save" style="background: #6b7280; text-decoration: none;">
                    <i class="fas fa-file-pdf"></i> Baixar PDF
                </a>
            </div>
            @endif
        </div>

        {{-- Política de Privacidade --}}
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="font-size: 1rem; font-weight: 700; color: #3b3b5c; margin: 0;">2. Política de Privacidade</h4>
                @if(isset($termoPrivacidade))
                    <span style="padding: 4px 10px; background: #E3F2FD; color: #1565C0; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">VERSÃO {{ $termoPrivacidade->versao }}</span>
                @endif
            </div>
            
            <div style="background: #F9FAFB; border: 1px solid #e0e0e8; border-radius: 12px; padding: 20px; max-height: 400px; overflow-y: auto; color: #4b5563; line-height: 1.6; font-size: 0.9rem;">
                @if(isset($termoPrivacidade))
                    {!! $termoPrivacidade->conteudo_html !!}
                @else
                    <p style="text-align: center; color: #9ca3af; padding: 20px;">Nenhuma política ativa.</p>
                @endif
            </div>

            @if(isset($termoPrivacidade))
            <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
                <a href="{{ route('vendedor.configuracoes.termos.pdf', $termoPrivacidade) }}" class="btn-save" style="background: #6b7280; text-decoration: none;">
                    <i class="fas fa-file-pdf"></i> Baixar PDF
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@endsection
