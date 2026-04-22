@extends('chat.layout')

@section('chat-content')
<div class="chat-sidebar">
    <div class="chat-header">
        <h4><i class="fab fa-whatsapp"></i> Chat Hub</h4>
        <div style="font-size: 0.75rem; opacity: 0.8; font-weight: 500;">Monitoramento Global de Mensagens</div>
    </div>

    <div class="chat-tabs">
        <button class="chat-tab @if($filtro === 'nao_atendidos') active @endif" 
                onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'nao_atendidos']) }}'">
            Pendentes
            @if($contagem['nao_atendidos'] > 0)
                <span class="badge">{{ $contagem['nao_atendidos'] }}</span>
            @endif
        </button>
        <button class="chat-tab @if($filtro === 'atendidos') active @endif" 
                onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'atendidos']) }}'">
            Em Atendimento
            <span class="badge" style="background: rgba(var(--primary-rgb), 0.2); color: var(--primary);">{{ $contagem['atendidos'] }}</span>
        </button>
    </div>

    <div class="chat-search">
        <form method="GET">
            <input type="text" name="q" placeholder="Procurar por nome ou telefone..." value="{{ $busca }}">
        </form>
    </div>

    <div class="chat-list">
        @forelse($conversas as $conversa)
        <a href="{{ route('admin.chat.conversa', $conversa->id) }}" 
           class="chat-item @if($conversa->pinned) pinned @endif {{ request()->route('id') == $conversa->id ? 'active' : '' }}">
            <div class="chat-item-avatar">
                {{ strtoupper(substr($conversa->contact->nome ?? 'C', 0, 1)) }}
            </div>
            <div class="chat-item-info">
                <div class="chat-item-name">
                    <span style="overflow: hidden; text-overflow: ellipsis;">{{ $conversa->contact->nome ?? 'Cliente' }}</span>
                    <span class="chat-item-time">
                        @if($conversa->last_message_at)
                        {{ $conversa->last_message_at->format('H:i') }}
                        @endif
                    </span>
                </div>
                <div class="chat-item-preview">
                    <span style="font-weight: 700; color: var(--primary); font-size: 0.7rem;">
                        {{ $conversa->vendedor->nome ?? 'Fila Global' }}
                    </span>
                    <br>
                    {{ $conversa->ultimoMensagem->conteudo ?? 'Iniciando conversa...' }}
                </div>
            </div>
            @if($conversa->unread_count > 0)
            <div class="chat-item-badge">{{ $conversa->unread_count }}</div>
            @endif
        </a>
        @empty
        <div class="empty-state">
            <i class="fas fa-comment-slash"></i>
            <h4>Silêncio total</h4>
            <p>Nenhuma conversa ativa encontrada com estes filtros.</p>
        </div>
        @endforelse
    </div>

    <div class="pagination-container" style="padding: 12px;">
        {{ $conversas->appends(['aba' => $filtro, 'q' => $busca, 'gestor' => $gestorId, 'vendedor' => $vendedorId])->links() }}
    </div>
</div>

<div class="chat-main">
    <div class="empty-state">
        <i class="fas fa-comments fa-4x"></i>
        <h4>Selecione uma conversa</h4>
        <p>Clique em uma conversa na lista para visualizar as mensagens</p>
    </div>
</div>
@endsection