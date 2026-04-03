@extends('layouts.app')
@section('title', 'Configurar 2FA')

@section('content')
<style>
    .twofa-container { max-width: 500px; margin: 40px auto; }
    .twofa-card { background: white; border-radius: 16px; padding: 40px; border: 1px solid #ededf2; box-shadow: 0 4px 16px rgba(50,50,71,0.08); }
    .twofa-card h2 { font-size: 1.3rem; font-weight: 800; color: #3b3b5c; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
    .twofa-card h2 i { color: #4C1D95; }
    .twofa-card .step { margin-bottom: 24px; }
    .twofa-card .step-num { width: 28px; height: 28px; border-radius: 50%; background: #4C1D95; color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; margin-right: 8px; }
    .twofa-card .step-title { font-weight: 700; color: #3b3b5c; font-size: 0.95rem; margin-bottom: 8px; }
    .twofa-card .step-desc { color: #a1a1b5; font-size: 0.85rem; margin-bottom: 12px; }
    .qr-box { background: white; border: 2px solid #e0e0e8; border-radius: 12px; padding: 20px; display: inline-block; margin: 0 auto; }
    .qr-box svg { display: block; }
    .secret-code { background: #f4f5fa; border: 1px solid #e0e0e8; border-radius: 8px; padding: 10px 14px; font-family: monospace; font-size: 1rem; font-weight: 700; letter-spacing: 2px; text-align: center; color: #4C1D95; margin-top: 12px; }
    .twofa-input { width: 100%; padding: 14px 16px; border: 2px solid #e0e0e8; border-radius: 10px; font-size: 1.5rem; font-weight: 700; text-align: center; letter-spacing: 8px; outline: none; transition: 0.2s; }
    .twofa-input:focus { border-color: #4C1D95; box-shadow: 0 0 0 3px rgba(76,29,149,0.15); }
    .twofa-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 16px; transition: 0.2s; }
    .twofa-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(76,29,149,0.3); }
</style>

<div class="twofa-container">
    <div class="twofa-card">
        <h2><i class="fas fa-shield-halved"></i> Configurar Autenticação 2FA</h2>
        <p style="color: #a1a1b5; font-size: 0.9rem; margin-bottom: 24px;">Proteja sua conta com verificação em duas etapas</p>

        <div class="step">
            <div class="step-title"><span class="step-num">1</span> Instale um app autenticador</div>
            <div class="step-desc">Google Authenticator, Authy, Microsoft Authenticator, etc.</div>
        </div>

        <div class="step">
            <div class="step-title"><span class="step-num">2</span> Escaneie o QR Code</div>
            <div class="step-desc">Abra o app e escaneie o código abaixo:</div>
            <div style="text-align: center;">
                <div class="qr-box">
                    {!! $qrCode !!}
                </div>
            </div>
            <div class="step-desc" style="margin-top: 12px;">Ou use o código manual:</div>
            <div class="secret-code">{{ Auth::user()->two_factor_secret }}</div>
        </div>

        <div class="step">
            <div class="step-title"><span class="step-num">3</span> Verifique o código</div>
            <div class="step-desc">Digite o código de 6 dígitos gerado pelo app:</div>
            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf
                <input type="text" name="code" class="twofa-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus placeholder="000000" required>
                @error('code')
                <div style="color: #ef4444; font-size: 0.8rem; margin-top: 8px;">{{ $message }}</div>
                @enderror
                <button type="submit" class="twofa-btn"><i class="fas fa-check"></i> Ativar 2FA</button>
            </form>
        </div>
    </div>
</div>
@endsection
