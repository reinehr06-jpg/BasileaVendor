@extends('chat.layout')

@section('content')
<div class="chat-sidebar">
    <div class="chat-header">
        <h4><i class="fab fa-whatsapp me-2"></i>Chat</h4>
        <small style="opacity: 0.8">Minhas conversas</small>
    </div>

    <div class="chat-tabs">
        <button class="chat-tab {{ $filtro === 'nao_atendidos' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('vendedor.chat', ['aba' => 'nao_atendidos']) }}'">
            Não Atendidos
            <span class="badge">{{ $contagem['nao_atendidos'] }}</span>
        </button>
        <button class="chat-tab {{ $filtro === 'atendidos' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('vendedor.chat', ['aba' => 'atendidos']) }}'">
            Atendidos
            <span class="badge">{{ $contagem['atendidos'] }}</span>
        </button>
    </div>

    <div class="chat-search">
        <input type="text" placeholder="Buscar conversa..." id="searchChat">
    </div>

    <div class="chat-list">
        @forelse($conversas as $conversa)
        <a href="{{ route('vendedor.chat.conversa', $conversa->id) }}" 
           class="chat-item {{ $conversa->pinned ? 'pinned' : '' }}">
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
                        {{ $conversa->last_message_at ? $conversa->last_message_at->format('H:i') : '' }}
                    </span>
                </div>
                <div class="chat-item-preview">
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
            <p>Nenhuma conversa{{ $filtro === 'nao_atendidos' ? ' não atendida' : '' }}</p>
        </div>
        @endforelse
    </div>

    <div class="pagination-container" style="padding: 12px;">
        {{ $conversas->appends(['aba' => $filtro])->links() }}
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
                @if($conversa->is_atendido)
                <span class="atendido-badge ms-2">Atendido</span>
                @else
                <span class="atendido-badge ms-2" style="background: #FEF3C7; color: #B45309;">Aguardando</span>
                @endif
            </p>
        </div>
        <div class="ms-auto">
            <span class="text-muted">
                {{ $conversa->contact->telefone ?? '' }}
            </span>
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

    <div class="chat-input">
        <form id="mensagemForm" action="{{ route('vendedor.chat.mensagem', $conversa->id) }}" method="POST">
            @csrf
            <button type="button" class="btn btn-light">
                <i class="fas fa-paperclip"></i>
            </button>
            <input type="text" name="mensagem" placeholder="Digite uma mensagem..." required>
            <button type="submit">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('mensagemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const input = form.querySelector('input[name="mensagem"]');
    const button = form.querySelector('button[type="submit"]');
    
    if (!input.value.trim()) return;

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ mensagem: input.value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            location.reload();
        } else {
            alert(data.error || 'Erro ao enviar mensagem');
        }
    })
    .catch(err => {
        alert('Erro ao enviar mensagem');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.querySelector('.chat-messages');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
@endpush
@endsection