@extends('chat.layout')

@section('chat-content')
<div class="chat-sidebar">
    <div class="chat-header">
        <h4><i class="fab fa-whatsapp me-2"></i>Chat da Equipe</h4>
        <small style="opacity: 0.8">Todas as conversas da equipe</small>
    </div>

    <div class="chat-tabs">
        <button class="chat-tab @if($filtro === 'nao_atendidos') active @endif" 
                onclick="window.location.href='{{ route('gestor.chat.index', ['aba' => 'nao_atendidos']) }}'">
            Não Atendidos
            <span class="badge">{{ $contagem['nao_atendidos'] }}</span>
        </button>
        <button class="chat-tab @if($filtro === 'atendidos') active @endif" 
                onclick="window.location.href='{{ route('gestor.chat.index', ['aba' => 'atendidos']) }}'">
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
        <a href="{{ route('gestor.chat.conversa', $conversa->id) }}" 
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
                    <small class="text-muted">{{ $conversa->vendedor->nome ?? 'Sem vendedor' }}</small>
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
        {{ $conversas->appends(['aba' => $filtro, 'q' => $busca])->links() }}
    </div>
</div>

<div class="chat-main">
    <div class="chat-main-header">
        <div class="avatar">
            {{ strtoupper(substr($conversa->contact->nome ?? 'C', 0, 1)) }}
        </div>
        <div class="info">
            <h5>{{ $conversa->contact->nome ?? 'Cliente' }}</h5>
            <p>
                <span class="status-badge status-{{ $conversa->status }}">
                    {{ ucfirst($conversa->status) }}
                </span>
                <small class="text-muted ms-2">{{ $conversa->vendedor->nome ?? '' }}</small>
            </p>
        </div>
        <div class="ms-auto">
            <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('atribuirModal').show()">
                <i class="fas fa-user-plus"></i> Atribuir
            </button>
        </div>
    </div>

    <div class="chat-messages">
        @forelse($conversa->mensagens as $mensagem)
        <div class="message {{ $mensagem->direction }}">
            <div class="message-bubble">
                <div>{{ $mensagem->conteudo }}</div>
                <div class="message-time">
                    {{ $mensagem->created_at->format('H:i') }}
                    @if($mensagem->direction === 'outbound')
                    <span class="message-status">
                        @if($mensagem->delivery_status === 'read')
                        <i class="fas fa-check-double"></i>
                        @else
                        <i class="fas fa-check"></i>
                        @endif
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-comments fa-3x"></i>
            <p>Nenhuma mensagem ainda</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Atribuir -->
<div id="atribuirModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="position:relative; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:8px; max-width:400px;">
        <h5>Atribuir Conversa</h5>
        <form action="{{ route('gestor.chat.atribuir', $conversa->id) }}" method="POST">
            @csrf
            <select name="vendedor_id" class="form-control mb-3" required>
                <option value="">Selecione um vendedor</option>
                @foreach($vendedores as $v)
                <option value="{{ $v->id }}">{{ $v->nome }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Atribuir</button>
            <button type="button" onclick="document.getElementById('atribuirModal').style.display='none'" class="btn btn-secondary">Cancelar</button>
        </form>
    </div>
</div>
@endsection