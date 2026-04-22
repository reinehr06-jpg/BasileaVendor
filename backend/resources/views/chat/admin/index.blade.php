@extends('chat.layout')

@section('chat-sidebar')
    <div class="chat-sidebar-header">
        <h4><i class="fab fa-whatsapp"></i> Chat Hub</h4>
        <div style="font-size: 0.75rem; opacity: 0.8; font-weight: 500;">Monitoramento Global de Mensagens</div>
    </div>

    <div class="chat-tabs">
        <button class="chat-tab-btn @if($filtro === 'nao_atendidos') active @endif" 
                onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'nao_atendidos']) }}'">
            <i class="fas fa-clock"></i> Pendentes
            @if($contagem['nao_atendidos'] > 0)
                <span class="badge bg-white text-primary" style="font-size: 0.65rem; padding: 2px 6px;">{{ $contagem['nao_atendidos'] }}</span>
            @endif
        </button>
        <button class="chat-tab-btn @if($filtro === 'atendidos') active @endif" 
                onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'atendidos']) }}'">
            <i class="fas fa-user-check"></i> Atendimento
        </button>
    </div>

    <div class="chat-search-box">
        <form method="GET">
            <input type="text" name="q" class="chat-search-input" placeholder="Procurar por nome ou telefone..." value="{{ $busca }}">
        </form>
    </div>

    <div class="chat-list">
        @forelse($conversas as $conversa)
        <a href="{{ route('admin.chat.conversa', $conversa->id) }}" 
           class="chat-item {{ request()->route('id') == $conversa->id ? 'active' : '' }}">
            <div class="chat-item-avatar">
                {{ strtoupper(substr($conversa->contact->nome ?? 'C', 0, 1)) }}
            </div>
            <div class="chat-item-info">
                <div class="chat-item-header">
                    <span class="chat-item-name">{{ $conversa->contact->nome ?? 'Cliente' }}</span>
                    <span class="chat-item-time">
                        @if($conversa->last_message_at)
                        {{ $conversa->last_message_at->format('H:i') }}
                        @endif
                    </span>
                </div>
                <div class="chat-item-preview">
                    <span style="font-weight: 800; color: var(--primary); font-size: 0.7rem; text-transform: uppercase;">
                        {{ $conversa->vendedor->user->name ?? 'Fila Global' }}
                    </span>
                    <span style="margin-left: 4px;">{{ $conversa->ultimoMensagem->conteudo ?? 'Iniciando conversa...' }}</span>
                </div>
            </div>
            @if($conversa->unread_count > 0)
            <div style="background: var(--primary); color: white; border-radius: 20px; min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; padding: 0 6px;">
                {{ $conversa->unread_count }}
            </div>
            @endif
        </a>
        @empty
        <div class="chat-empty-state" style="padding-top: 60px;">
            <i class="fas fa-comment-slash"></i>
            <h3>Silêncio total</h3>
            <p>Nenhuma conversa ativa encontrada com estes filtros.</p>
        </div>
        @endforelse
    </div>

    <div style="padding: 12px; border-top: 1px solid var(--chat-border);">
        {{ $conversas->appends(['aba' => $filtro, 'q' => $busca])->links('pagination::bootstrap-4') }}
    </div>
@endsection

@section('chat-content')
    <div class="chat-empty-state">
        <i class="fas fa-comments"></i>
        <h3>Selecione uma conversa</h3>
        <p>Clique em uma conversa na lista lateral para visualizar o histórico de mensagens e responder ao cliente.</p>
    </div>
@endsection