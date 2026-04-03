@extends('layouts.app')
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

    .qr-box { background: white; border: 2px solid #e0e0e8; border-radius: 12px; padding: 20px; display: inline-block; }
    .secret-code { background: #f4f5fa; border: 1px solid #e0e0e8; border-radius: 8px; padding: 10px 14px; font-family: monospace; font-size: 1rem; font-weight: 700; letter-spacing: 2px; text-align: center; color: #4C1D95; margin-top: 12px; }
    .twofa-input { width: 100%; max-width: 200px; padding: 14px 16px; border: 2px solid #e0e0e8; border-radius: 10px; font-size: 1.5rem; font-weight: 700; text-align: center; letter-spacing: 8px; outline: none; transition: 0.2s; }
    .twofa-input:focus { border-color: #4C1D95; box-shadow: 0 0 0 3px rgba(76,29,149,0.15); }

    .btn-danger { padding: 10px 24px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.2s; }
    .btn-danger:hover { background: #dc2626; }
    .btn-success { padding: 10px 24px; background: #16a34a; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.2s; }
    .btn-success:hover { background: #15803d; }
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

<div class="settings-card">
    <h3><i class="fas fa-lock"></i> Alterar Senha</h3>
    <p class="desc">Sua senha deve ter no mínimo 8 caracteres, com letras maiúsculas, minúsculas, números e símbolos</p>

    <form method="POST" action="{{ route('vendedor.configuracoes.senha.update') }}">
        @csrf
        <div class="form-group">
            <label>Senha Atual</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>Nova Senha</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirmar Nova Senha</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn-save"><i class="fas fa-key"></i> Alterar Senha</button>
    </form>
</div>

{{-- SEGURANCA --}}
@elseif($tab === 'seguranca')
<div class="settings-card">
    <h3><i class="fas fa-shield-halved"></i> Autenticação em Duas Etapas (2FA)</h3>
    <p class="desc">Proteja sua conta com um código adicional gerado pelo app autenticador</p>

    @if($user->two_factor_enabled)
    <div class="twofa-status active">
        <div class="icon"><i class="fas fa-check"></i></div>
        <div class="info">
            <h4>2FA Ativado</h4>
            <p>Sua conta está protegida com autenticação em duas etapas</p>
        </div>
    </div>

    <form method="POST" action="{{ route('vendedor.configuracoes.2fa.disable') }}" style="margin-top: 16px;">
        @csrf
        <div class="form-group">
            <label>Digite o código atual do app para desativar</label>
            <input type="text" name="code" class="twofa-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="000000" required>
        </div>
        @error('code')
        <div style="color: #ef4444; font-size: 0.8rem; margin-bottom: 12px;">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn-danger"><i class="fas fa-times"></i> Desativar 2FA</button>
    </form>
    @else
    <div class="twofa-status inactive">
        <div class="icon"><i class="fas fa-exclamation"></i></div>
        <div class="info">
            <h4>2FA Não Ativado</h4>
            <p>Recomendamos ativar para maior segurança da sua conta</p>
        </div>
    </div>

    <a href="{{ route('vendedor.configuracoes.2fa.setup') }}" class="btn-success" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
        <i class="fas fa-qrcode"></i> Configurar 2FA
    </a>
    @endif
</div>

<div class="settings-card">
    <h3><i class="fas fa-clock"></i> Sessão do Sistema</h3>
    <p class="desc">Sua sessão expira automaticamente após 2 horas de inatividade</p>
    <div style="background: #f4f5fa; border-radius: 8px; padding: 14px; font-size: 0.85rem; color: #6b7280;">
        <i class="fas fa-info-circle" style="color: #4C1D95; margin-right: 6px;"></i>
        Último login: {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '—' }}
        @if($user->login_ip)
        <br><i class="fas fa-globe" style="color: #4C1D95; margin-right: 6px;"></i> IP: {{ $user->login_ip }}
        @endif
    </div>
</div>

{{-- SPLIT --}}
@elseif($tab === 'split')
<div class="settings-card">
    <h3><i class="fas fa-wallet"></i> Configuração de Split</h3>
    <p class="desc">Configure sua carteira Asaas para recebimento automático de comissões</p>

    <form method="POST" action="{{ route('vendedor.configuracoes.split.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Wallet ID Asaas</label>
            <input type="text" name="asaas_wallet_id" value="{{ old('asaas_wallet_id', $vendedor->asaas_wallet_id ?? '') }}" {{ ($vendedor->wallet_validada ?? false) ? 'disabled' : '' }} placeholder="Digite o ID da sua carteira Asaas">
            @if($vendedor->wallet_validada ?? false)
            <div style="color: #16a34a; font-size: 0.8rem; margin-top: 4px;"><i class="fas fa-check-circle"></i> Wallet validada — não pode ser alterada</div>
            @endif
        </div>

        <div class="form-group">
            <label>Tipo de Split</label>
            <select name="tipo_split" {{ ($vendedor->wallet_validada ?? false) ? 'disabled' : '' }}>
                <option value="percentual" {{ ($vendedor->tipo_split ?? 'percentual') === 'percentual' ? 'selected' : '' }}>Percentual (%)</option>
                <option value="fixo" {{ ($vendedor->tipo_split ?? '') === 'fixo' ? 'selected' : '' }}>Valor Fixo (R$)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Valor Split Primeira Venda</label>
            <input type="number" step="0.01" name="valor_split_inicial" value="{{ old('valor_split_inicial', $vendedor->valor_split_inicial ?? 0) }}" {{ ($vendedor->wallet_validada ?? false) ? 'disabled' : '' }}>
        </div>

        <div class="form-group">
            <label>Valor Split Recorrência</label>
            <input type="number" step="0.01" name="valor_split_recorrencia" value="{{ old('valor_split_recorrencia', $vendedor->valor_split_recorrencia ?? 0) }}" {{ ($vendedor->wallet_validada ?? false) ? 'disabled' : '' }}>
        </div>

        @if(!($vendedor->wallet_validada ?? false))
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Salvar Configurações</button>
        @endif
    </form>
</div>
@endif

@endsection
