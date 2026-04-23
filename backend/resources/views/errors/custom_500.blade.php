@extends('layouts.app')
@section('title', 'Erro Interno')

@section('content')
<div class="container d-flex align-center justify-center" style="min-height: 80vh;">
    <div class="glass-card text-center" style="max-width: 600px; padding: 40px; border: 1px solid #fee2e2; background: rgba(254, 226, 226, 0.05);">
        <div style="width: 80px; height: 80px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-center; margin: 0 auto 20px; font-size: 2rem;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 style="font-weight: 800; color: var(--text-primary); margin-bottom: 10px;">Ops! Algo deu errado.</h2>
        <p style="color: var(--text-secondary); margin-bottom: 25px;">
            Ocorreu um erro ao carregar o seu Dashboard. Nossa equipe técnica já foi notificada.
        </p>
        
        <div style="background: #fafafa; border: 1px solid var(--border-light); border-radius: 12px; padding: 15px; text-align: left; margin-bottom: 25px;">
            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 5px;">Detalhes do Erro (Debug):</div>
            <code style="font-size: 0.85rem; color: #dc2626; word-break: break-all;">{{ $message }}</code>
        </div>

        <div class="d-flex gap-3 justify-center">
            <a href="{{ route('dashboard') }}" class="btn-primary" style="text-decoration: none;">Tentar Novamente</a>
            <a href="javascript:history.back()" style="padding: 10px 20px; color: var(--text-secondary); font-weight: 600; text-decoration: none;">Voltar</a>
        </div>
    </div>
</div>
@endsection
