@extends('chat.layout')

@section('chat-content')
<div class="chat-sidebar">
    <div class="chat-header">
        <h4><i class="fab fa-whatsapp me-2"></i>Chat Global</h4>
        <small style="opacity: 0.8">Todas as conversas</small>
    </div>

    <div class="chat-tabs">
        <button class="chat-tab @if($filtro === 'nao_atendidos') active @endif" 
                onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'nao_atendidos']) }}'">
            Não Atendidos
            <span class="badge">{{ $contagem['nao_atendidos'] }}</span>
        </button>
        <button class="chat-tab @if($filtro === 'atendidos') active @endif" 
                onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'atendidos']) }}'">
            Atendidos
            <span class="badge">{{ $contagem['atendidos'] }}</span>
        </button>
    </div>

    <div class="chat-search">
        <form method="GET">
            <input type="text" name="q" placeholder="Buscar conversa..." value="{{ $busca }}">
        </form>
    </div>

    <div class="chat-list">
        @forelse($conversas as $conversa)
        <a href="{{ route('admin.chat.conversa', $conversa->id) }}" 
           class="chat-item @if($conversa->pinned) pinned @endif">
            <div class="chat-item-avatar">
                {{ strtoupper(substr($conversa->contact->nome ?? 'C', 0, 1)) }}
            </div>
            <div class="chat-item-info">
                <div class="chat-item-name">
                    @if($conversa->pinned)
                    <i class="fas fa-star pin-icon"></i>
                    @endif
                    <span>{{ $conversa->contact->nome ?? 'Cliente' }}</span>
                    <span class="chat-item-time">
                        @if($conversa->last_message_at)
                        {{ $conversa->last_message_at->format('H:i') }}
                        @endif
                    </span>
                </div>
                <div class="chat-item-preview">
                    <small class="text-muted">
                        {{ $conversa->gestor->name ?? '' }} / {{ $conversa->vendedor->nome ?? 'Sem vendedor' }}
                    </small>
                    <br>
                    {{ $conversa->ultimoMensagem->conteudo ?? 'Sem mensagens' }}
                </div>
            </div>
            @if($conversa->unread_count > 0)
            <div class="chat-item-badge">{{ $conversa->unread_count }}</div>
            @endif
        </a>
        @empty
        <div class="empty-state" style="padding: 40px;">
            <i class="fas fa-comments fa-3x"></i>
            <p>Nenhuma conversa encontrada</p>
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