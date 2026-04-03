@extends('layouts.app')
@section('title', 'Teste de Layout')

@section('content')
<div style="padding: 40px; text-align: center; background: white; border-radius: 12px; border: 1px solid var(--border);">
    <h1 style="color: var(--success);"><i class="fas fa-check-circle"></i> LAYOUT CARREGADO COM SUCESSO</h1>
    <p>Se você está vendo isso, o problema **NÃO É O LAYOUT** (app.blade.php), mas sim o conteúdo da tabela de comissões.</p>
    <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--border-light);">
    <div style="text-align: left; font-family: monospace; background: #f8fafc; padding: 15px; border-radius: 8px;">
        Vendedor: {{ Auth::user()->name }}<br>
        Perfil: {{ Auth::user()->perfil }}<br>
        Mes: {{ $mes }}<br>
        Total no Resumo: R$ {{ $resumo['total'] ?? 0 }}
    </div>
    <div style="margin-top: 20px;">
        <a href="{{ route('vendedor.comissoes') }}" class="btn btn-primary">Recarregar Lista</a>
    </div>
</div>
@endsection
