@extends('layouts.app')
@section('title', 'Configurar 2FA')

@section('content')
<x-page-hero title="Configurar 2FA" subtitle="Proteja sua conta com verificação em duas etapas" icon="fas fa-shield-halved" />

<div style="max-width: 500px; margin: 0 auto;">
    <div style="background: white; border-radius: 14px; border: 1px solid #ededf2; padding: 32px; box-shadow: 0 2px 4px rgba(50,50,71,0.04);">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #3b3b5c; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-shield-halved" style="color: #4C1D95;"></i> Configurar Autenticador
        </h3>
        <p style="color: #a1a1b5; font-size: 0.85rem; margin-bottom: 24px;">Siga os passos abaixo para ativar a autenticação em duas etapas</p>

        <div style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span style="width: 24px; height: 24px; border-radius: 50%; background: #4C1D95; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">1</span>
                <span style="font-weight: 600; color: #3b3b5c;">Instale um app autenticador</span>
            </div>
            <p style="color: #a1a1b5; font-size: 0.82rem; margin-left: 32px;">Google Authenticator, Authy, Microsoft Authenticator, etc.</p>
        </div>

        <div style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span style="width: 24px; height: 24px; border-radius: 50%; background: #4C1D95; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">2</span>
                <span style="font-weight: 600; color: #3b3b5c;">Escaneie o QR code ou adicione manualmente</span>
            </div>
            <div style="text-align: center; margin: 16px 0;">
                {!! $qrCode !!}
            </div>
            <div style="background: #f4f5fa; border: 2px solid #4C1D95; border-radius: 10px; padding: 14px; margin: 12px 0 8px 32px; font-family: monospace; font-size: 1.1rem; font-weight: 700; letter-spacing: 3px; text-align: center; color: #4C1D95; word-break: break-all;">
                {{ Auth::user()->two_factor_secret }}
            </div>
            <p style="color: #a1a1b5; font-size: 0.78rem; margin-left: 32px;">Copie esta chave e cole no app autenticador como "entrada manual"</p>
        </div>

        <div style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span style="width: 24px; height: 24px; border-radius: 50%; background: #4C1D95; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">3</span>
                <span style="font-weight: 600; color: #3b3b5c;">Digite o código gerado pelo app</span>
            </div>
        </div>

        <form method="POST" action="{{ route('vendedor.configuracoes.2fa.enable') }}">
            @csrf
            <div style="margin-bottom: 16px;">
                <input type="text" name="code" style="width: 100%; max-width: 200px; padding: 14px 16px; border: 2px solid #e0e0e8; border-radius: 10px; font-size: 1.5rem; font-weight: 700; text-align: center; letter-spacing: 8px; outline: none; transition: 0.2s; box-sizing: border-box;" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus placeholder="000000" required>
            </div>
            @error('code')
            <div style="color: #ef4444; font-size: 0.8rem; margin-bottom: 12px;">{{ $message }}</div>
            @enderror
            <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.2s;">
                <i class="fas fa-check"></i> Ativar 2FA
            </button>
        </form>

        <a href="{{ route('vendedor.configuracoes', ['tab' => 'seguranca']) }}" style="display: block; text-align: center; margin-top: 16px; color: #a1a1b5; font-size: 0.85rem; text-decoration: underline;">
            Voltar para Segurança
        </a>
    </div>
</div>
@endsection
