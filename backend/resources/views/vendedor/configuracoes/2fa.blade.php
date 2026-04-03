@extends('layouts.app')
@section('title', 'Configurar 2FA')

@section('content')
<x-page-hero title="Configurar 2FA" subtitle="Proteja sua conta com verificação em duas etapas" icon="fas fa-shield-halved" />

<div style="max-width: 500px; margin: 0 auto;">
    <div style="background: white; border-radius: 14px; border: 1px solid #ededf2; padding: 32px; box-shadow: 0 2px 4px rgba(50,50,71,0.04);">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #3b3b5c; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-qrcode" style="color: #4C1D95;"></i> Configurar Autenticador
        </h3>
        <p style="color: #a1a1b5; font-size: 0.85rem; margin-bottom: 24px;">Escaneie o QR Code com seu app autenticador</p>

        <div style="text-align: center; margin-bottom: 20px;">
            <div style="background: white; border: 2px solid #e0e0e8; border-radius: 12px; padding: 20px; display: inline-block;">
                {!! $qrCode !!}
            </div>
        </div>

        <div style="background: #f4f5fa; border: 1px solid #e0e0e8; border-radius: 8px; padding: 10px 14px; font-family: monospace; font-size: 1rem; font-weight: 700; letter-spacing: 2px; text-align: center; color: #4C1D95; margin-bottom: 24px;">
            {{ Auth::user()->two_factor_secret }}
        </div>

        <form method="POST" action="{{ route('vendedor.configuracoes.2fa.enable') }}">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 600; color: #3b3b5c; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px;">Código do App</label>
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
