@extends('layouts.app')
@section('title', 'Verificação 2FA')

@section('content')
<style>
    .twofa-container { max-width: 420px; margin: 60px auto; }
    .twofa-card { background: white; border-radius: 16px; padding: 40px; border: 1px solid #ededf2; box-shadow: 0 4px 16px rgba(50,50,71,0.08); text-align: center; }
    .twofa-icon { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 20px; }
    .twofa-card h2 { font-size: 1.3rem; font-weight: 800; color: #3b3b5c; margin-bottom: 8px; }
    .twofa-card p { color: #a1a1b5; font-size: 0.9rem; margin-bottom: 24px; }
    .twofa-input { width: 100%; padding: 14px 16px; border: 2px solid #e0e0e8; border-radius: 10px; font-size: 1.5rem; font-weight: 700; text-align: center; letter-spacing: 8px; outline: none; transition: 0.2s; }
    .twofa-input:focus { border-color: #4C1D95; box-shadow: 0 0 0 3px rgba(76,29,149,0.15); }
    .twofa-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 16px; transition: 0.2s; }
    .twofa-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(76,29,149,0.3); }
</style>

<div class="twofa-container">
    <div class="twofa-card">
        <div class="twofa-icon"><i class="fas fa-shield-halved"></i></div>
        <h2>Verificação em Duas Etapas</h2>
        <p>Digite o código de 6 dígitos do seu aplicativo autenticador</p>

        <form method="POST" action="{{ route('2fa.verify.post') }}">
            @csrf
            <input type="text" name="code" class="twofa-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus placeholder="000000" required>
            @error('code')
            <div style="color: #ef4444; font-size: 0.8rem; margin-top: 8px;">{{ $message }}</div>
            @enderror
            <button type="submit" class="twofa-btn"><i class="fas fa-check"></i> Verificar</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 16px;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #a1a1b5; font-size: 0.85rem; cursor: pointer; text-decoration: underline;">
                <i class="fas fa-right-from-bracket"></i> Sair
            </button>
        </form>
    </div>
</div>
@endsection
