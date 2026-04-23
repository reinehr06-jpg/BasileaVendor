@extends('chat.layout')

@section('chat-sidebar')
    <div class="chat-sidebar-header">
        <h4><i class="fab fa-whatsapp"></i> Chat Hub</h4>
        
        @php
            $user = Auth::user();
            $vendedor = $user->vendedor;
            $status = $vendedor ? ($vendedor->status === 'ativo' ? 'Ativo' : 'Inativo') : 'Ativo';
            $telefone = $vendedor ? $vendedor->telefone : 'Admin/Global';
        @endphp

        <div class="user-status-card">
            <span class="user-status-name">{{ $user->name }}</span>
            <span class="user-status-number"><i class="fas fa-phone-alt" style="font-size: 0.6rem;"></i> {{ $telefone }}</span>
            <div class="status-indicator">
                <div class="status-dot {{ $status === 'Ativo' ? 'active' : 'inactive' }}"></div>
                {{ $status }}
            </div>
        </div>
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
                    @if($conversa->vendedor && $conversa->vendedor->user)
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

        <div class="chat-messages" id="chat-messages" style="flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
            @forelse($conversa->mensagens as $mensagem)
                <div class="message {{ $mensagem->direction }}" style="display: flex; flex-direction: column; max-width: 75%; {{ $mensagem->direction === 'outbound' ? 'align-self: flex-end;' : 'align-self: flex-start;' }}">
                    <div class="message-bubble" style="padding: 12px 18px; border-radius: 18px; font-size: 0.95rem; line-height: 1.5; {{ $mensagem->direction === 'outbound' ? 'background: #7c3aed; color: white; border-bottom-right-radius: 4px;' : 'background: white; border: 1px solid #e2e8f0; color: #1e293b; border-bottom-left-radius: 4px;' }}">
                        {{ $mensagem->conteudo }}
                    </div>
                    <div class="message-time" style="font-size: 0.7rem; margin-top: 5px; color: #94a3b8; font-weight: 600; display: flex; align-items: center; gap: 4px; {{ $mensagem->direction === 'outbound' ? 'justify-content: flex-end;' : '' }}">
                        {{ $mensagem->created_at->format('H:i') }}
                        @if($mensagem->direction === 'outbound')
                            <i class="fas fa-check-double {{ $mensagem->delivery_status === 'read' ? 'text-info' : '' }}" style="font-size: 0.6rem;"></i>
                        @endif
                    </div>
                </div>
            @empty
                <div style="height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #94a3b8; text-align: center;">
                    <i class="fas fa-history" style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.2;"></i>
                    <p style="font-weight: 600;">Sem histórico de mensagens disponível.</p>
                </div>
            @endforelse
        </div>

        <div class="chat-input-area" style="padding: 20px; background: white; border-top: 1px solid var(--chat-border);">
            <form action="{{ route('admin.chat.mensagem', $conversa->id) }}" method="POST" id="msgForm" style="display: flex; gap: 15px; align-items: center;">
                @csrf
                <input type="text" name="mensagem" placeholder="Digite sua mensagem aqui..." required autocomplete="off" style="flex: 1; padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.95rem; outline: none; transition: 0.2s;">
                <button type="submit" style="background: #7c3aed; color: white; border: none; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; cursor: pointer; transition: 0.2s;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('messagesContainer');
            if (container) container.scrollTop = container.scrollHeight;
        });
    </script>
@endsection
