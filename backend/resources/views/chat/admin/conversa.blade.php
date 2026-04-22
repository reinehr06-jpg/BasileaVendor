@extends('chat.layout')

@section('chat-sidebar')
    <div class="chat-sidebar-header">
        <h4><i class="fab fa-whatsapp"></i> Chat Hub</h4>
        <div style="font-size: 0.75rem; opacity: 0.8; font-weight: 500;">Monitoramento Global de Mensagens</div>
    </div>

    <div class="chat-tabs">
        <button class="chat-tab-btn" onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'nao_atendidos']) }}'">
            <i class="fas fa-clock"></i> Pendentes
        </button>
        <button class="chat-tab-btn" onclick="window.location.href='{{ route('admin.chat.index', ['aba' => 'atendidos']) }}'">
            <i class="fas fa-user-check"></i> Atendimento
        </button>
    </div>

    <div class="chat-search-box">
        <form method="GET" action="{{ route('admin.chat.index') }}">
            <input type="text" name="q" class="chat-search-input" placeholder="Procurar por nome ou telefone...">
        </form>
    </div>

    <div class="chat-list" id="chatList">
        {{-- Aqui seriam listadas as outras conversas via AJAX ou similar --}}
        <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 0.8rem;">
            Volte para a lista para ver todas as conversas.
        </div>
    </div>
@endsection

@section('chat-content')
    <div class="chat-main-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="chat-item-avatar">
                {{ strtoupper(substr($conversa->contact->nome ?? 'C', 0, 1)) }}
            </div>
            <div>
                <div style="font-weight: 800; color: #1e293b; font-size: 1.1rem;">{{ $conversa->contact->nome ?? 'Cliente' }}</div>
                <div style="font-size: 0.75rem; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <span class="status-badge" style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 10px; font-weight: 700;">{{ strtoupper($conversa->status) }}</span>
                    @if($conversa->vendedor)
                        <span style="color: var(--primary); font-weight: 600;"><i class="fas fa-user-tie"></i> {{ $conversa->vendedor->user->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">{{ $conversa->contact->telefone }}</div>
            <div style="font-size: 0.7rem; color: #94a3b8;">{{ $conversa->contact->email ?? 'Sem e-mail' }}</div>
        </div>
    </div>

    <div class="chat-messages" id="messagesContainer">
        @forelse($conversa->mensagens as $mensagem)
            <div style="display: flex; flex-direction: column; align-items: {{ $mensagem->direction === 'outbound' ? 'flex-end' : 'flex-start' }}; margin-bottom: 10px;">
                <div style="
                    max-width: 75%;
                    padding: 12px 16px;
                    border-radius: 18px;
                    font-size: 0.95rem;
                    line-height: 1.4;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                    background: {{ $mensagem->direction === 'outbound' ? 'var(--primary)' : 'white' }};
                    color: {{ $mensagem->direction === 'outbound' ? 'white' : '#1e293b' }};
                    border-bottom-{{ $mensagem->direction === 'outbound' ? 'right' : 'left' }}-radius: 4px;
                ">
                    {{ $mensagem->conteudo }}
                    <div style="font-size: 0.65rem; margin-top: 4px; opacity: 0.7; text-align: right;">
                        {{ $mensagem->created_at->format('H:i') }}
                        @if($mensagem->direction === 'outbound')
                            <i class="fas fa-{{ $mensagem->delivery_status === 'read' ? 'check-double' : 'check' }}" style="margin-left: 4px;"></i>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="chat-empty-state">
                <i class="fas fa-comment-dots"></i>
                <h3>Início da conversa</h3>
                <p>Nenhuma mensagem enviada ou recebida ainda.</p>
            </div>
        @endforelse
    </div>

    <div style="padding: 20px 28px; background: white; border-top: 1px solid var(--chat-border);">
        <form action="{{ route('admin.chat.conversa', $conversa->id) }}" method="POST" id="msgForm" style="display: flex; gap: 15px; align-items: center;">
            @csrf
            <button type="button" class="btn btn-ghost" style="padding: 10px; color: #94a3b8;"><i class="fas fa-paperclip fa-lg"></i></button>
            <input type="text" name="mensagem" class="chat-search-input" style="flex: 1; background: #f1f5f9;" placeholder="Digite sua resposta aqui...">
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px; border-radius: 14px; font-weight: 800;"><i class="fas fa-paper-plane me-2"></i> ENVIAR</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('messagesContainer');
            if (container) container.scrollTop = container.scrollHeight;
        });
    </script>
@endsection
