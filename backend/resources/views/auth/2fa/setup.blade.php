@extends('layouts.app')
@section('title', 'Configurar 2FA')

@section('content')
<style>
    .twofa-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 99999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .twofa-overlay.hidden { display: none; }
    .twofa-modal { background: white; border-radius: 20px; padding: 40px; max-width: 480px; width: 90%; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.3); animation: modalIn 0.3s ease-out; }
    @keyframes modalIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .twofa-modal .icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #fef3c7, #fde68a); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; }
    .twofa-modal h2 { font-size: 1.4rem; font-weight: 800; color: #1e1b4b; margin-bottom: 12px; }
    .twofa-modal p { color: #6b7280; font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px; }
    .twofa-modal .btn-entendi { background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; padding: 14px 40px; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.2s; }
    .twofa-modal .btn-entendi:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(76,29,149,0.3); }

    .twofa-form { display: none; max-width: 500px; margin: 0 auto; }
    .twofa-form.show { display: block; }
    .twofa-form-card { background: white; border-radius: 14px; border: 1px solid #ededf2; padding: 32px; box-shadow: 0 2px 4px rgba(50,50,71,0.04); }
    .twofa-form-card h3 { font-size: 1.1rem; font-weight: 700; color: #3b3b5c; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .twofa-form-card h3 i { color: #4C1D95; }
    .twofa-form-card .desc { color: #a1a1b5; font-size: 0.85rem; margin-bottom: 24px; }
    .twofa-step { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px; }
    .twofa-step .num { width: 24px; height: 24px; border-radius: 50%; background: #4C1D95; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0; margin-top: 2px; }
    .twofa-step .text { font-size: 0.85rem; color: #3b3b5c; }
    .twofa-step .text strong { color: #4C1D95; }
    .secret-box { background: white; border: 2px solid #4C1D95; border-radius: 10px; padding: 14px; font-family: monospace; font-size: 1.1rem; font-weight: 700; letter-spacing: 3px; text-align: center; color: #4C1D95; margin: 12px 0; word-break: break-all; }
    .twofa-input { width: 100%; max-width: 200px; padding: 14px 16px; border: 2px solid #e0e0e8; border-radius: 10px; font-size: 1.5rem; font-weight: 700; text-align: center; letter-spacing: 8px; outline: none; transition: 0.2s; box-sizing: border-box; }
    .twofa-input:focus { border-color: #4C1D95; box-shadow: 0 0 0 3px rgba(76,29,149,0.15); }
    .btn-activate { width: 100%; padding: 14px; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.2s; }
    .btn-activate:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(76,29,149,0.3); }
</style>

{{-- OVERLAY DE BLOQUEIO --}}
<div class="twofa-overlay" id="twofaOverlay">
    <div class="twofa-modal">
        <div class="icon">🔒</div>
        <h2>Acesso Bloqueado</h2>
        <p>Para proteger sua conta e os dados do sistema, é <strong>obrigatório</strong> configurar a autenticação em duas etapas (2FA). Sem isso, você não poderá acessar nenhuma funcionalidade.</p>
        <p style="font-size: 0.85rem; color: #92400e; background: #fef3c7; padding: 12px; border-radius: 8px; margin-bottom: 24px;">
            <i class="fas fa-info-circle"></i> Você precisará de um app autenticador no celular (Google Authenticator, Authy, etc.)
        </p>
        <button class="btn-entendi" onclick="document.getElementById('twofaOverlay').classList.add('hidden'); document.getElementById('twofaForm').classList.add('show');">
            <i class="fas fa-check"></i> Entendi, vou configurar agora
        </button>
    </div>
</div>

{{-- FORMULÁRIO DE SETUP --}}
<div class="twofa-form" id="twofaForm">
    <x-page-hero title="Configurar 2FA" subtitle="Proteja sua conta com verificação em duas etapas" icon="fas fa-shield-halved" />

    <div class="twofa-form-card">
        <h3><i class="fas fa-shield-halved"></i> Configurar Autenticador</h3>
        <p class="desc">Siga os passos abaixo para ativar a autenticação em duas etapas</p>

        <div class="twofa-step">
            <span class="num">1</span>
            <div class="text"><strong>Instale um app autenticador</strong><br><span style="color: #a1a1b5;">Google Authenticator, Authy, Microsoft Authenticator, etc.</span></div>
        </div>

        <div class="twofa-step">
            <span class="num">2</span>
            <div class="text"><strong>Adicione a chave manualmente no app</strong>
                <div class="secret-box">{{ $user->two_factor_secret }}</div>
                <span style="color: #a1a1b5; font-size: 0.78rem;">Copie esta chave e cole no app como "entrada manual"</span>
            </div>
        </div>

        <div class="twofa-step">
            <span class="num">3</span>
            <div class="text"><strong>Digite o código de 6 dígitos gerado pelo app</strong></div>
        </div>

        <form method="POST" action="{{ $enableRoute }}">
            @csrf
            <div style="margin-bottom: 16px;">
                <input type="text" name="code" class="twofa-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus placeholder="000000" required>
            </div>
            @error('code')
            <div style="color: #ef4444; font-size: 0.8rem; margin-bottom: 12px;">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn-activate"><i class="fas fa-check"></i> Ativar 2FA</button>
        </form>
    </div>
</div>
@endsection
