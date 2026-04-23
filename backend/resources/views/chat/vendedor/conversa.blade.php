@extends('chat.layout')

@section('header_title', 'Atendimento WhatsApp')
@section('header_description', 'Gerencie seus contatos e responda mensagens em tempo real.')

@section('chat-content')
<div class="chat-sidebar">
    <div class="chat-sidebar-header">
        <h4><i class="fab fa-whatsapp me-2"></i>Minhas Conversas</h4>
    </div>

    <div class="chat-tabs">
        <button class="chat-tab-btn {{ $filtro === 'nao_atendidos' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('vendedor.chat', ['aba' => 'nao_atendidos']) }}'">
            <i class="fas fa-clock"></i> Pendentes ({{ $contagem['nao_atendidos'] }})
        </button>
        <button class="chat-tab-btn {{ $filtro === 'atendidos' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('vendedor.chat', ['aba' => 'atendidos']) }}'">
            <i class="fas fa-check-double"></i> Atendidos ({{ $contagem['atendidos'] }})
        </button>
    </div>

    <div class="chat-search-box">
        <input type="text" class="chat-search-input" placeholder="Buscar conversa..." id="searchChat">
    </div>

    <div class="chat-list">
        @foreach($conversas as $c)
        <a href="{{ route('vendedor.chat.conversa', $c->id) }}" 
           class="chat-item {{ $c->id == $conversa->id ? 'active' : '' }}">
            <div class="chat-item-avatar">
                {{ strtoupper(substr($c->contact->nome ?? 'C', 0, 1)) }}
            </div>
            <div class="chat-item-info">
                <div class="chat-item-name">
                    <span>{{ $c->contact->nome ?? 'Cliente' }}</span>
                    <span class="chat-item-time">
                        {{ $c->last_message_at ? $c->last_message_at->format('H:i') : '' }}
                    </span>
                </div>
                <div class="chat-item-preview">
                    {{ $c->ultimoMensagem->conteudo ?? 'Sem mensagens' }}
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>

<div class="chat-main">
    <div class="chat-main-header">
        <div class="chat-item-avatar" style="width: 42px; height: 42px; font-size: 1rem;">
            {{ strtoupper(substr($conversa->contact->nome ?? 'C', 0, 1)) }}
        </div>
        <div>
            <h5 style="margin: 0; font-weight: 800; color: #1e293b;">{{ $conversa->contact->nome ?? 'Cliente' }}</h5>
            <small style="color: #64748b; font-weight: 600;">{{ $conversa->contact->telefone ?? '' }}</small>
        </div>
        <div class="ms-auto d-flex gap-2">
            @if(!$conversa->is_atendido)
            <form action="{{ route('vendedor.chat.atender', $conversa->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: #f3e8ff; color: #6b21a8; font-weight: 700; border-radius: 8px; border: none; padding: 6px 12px;">
                    <i class="fas fa-check me-1"></i> Atender
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        @forelse($conversa->mensagens as $mensagem)
        <div class="message {{ $mensagem->direction === 'outbound' ? 'outbound' : 'inbound' }}">
            <div class="message-bubble">
                {{ $mensagem->conteudo }}
            </div>
            <div class="message-time">
                {{ $mensagem->created_at->format('H:i') }}
                @if($mensagem->direction === 'outbound')
                <i class="fas fa-check-double {{ $mensagem->delivery_status === 'read' ? 'text-info' : '' }}" style="font-size: 0.6rem;"></i>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <p>Inicie a conversa enviando uma mensagem abaixo.</p>
        </div>
        @endforelse
    </div>

    <div class="chat-input-area">
        <form id="mensagemForm" action="{{ route('vendedor.chat.mensagem', $conversa->id) }}" method="POST" class="chat-input-form">
            @csrf
            <input type="text" name="mensagem" placeholder="Escreva sua mensagem aqui..." required autocomplete="off">
            <button type="submit" class="btn-send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messages = document.getElementById('chatMessages');
        messages.scrollTop = messages.scrollHeight;

        const form = document.getElementById('mensagemForm');
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('.btn-send');
            const input = form.querySelector('input');
            
            if (!input.value.trim()) {
                e.preventDefault();
                return;
            }

            // Opcional: Feedback visual de envio
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        });
    });
</script>
@endpush
@endsection