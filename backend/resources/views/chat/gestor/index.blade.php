@extends('chat.layout')

@section('chat-sidebar')
    <div class="chat-sidebar-header">
        <h4><i class="fab fa-whatsapp"></i> Chat Equipe</h4>
        
        @php
            $user = Auth::user();
            $vendedor = $user->vendedor;
            $status = $vendedor ? ($vendedor->status === 'ativo' ? 'Ativo' : 'Inativo') : 'Ativo';
            $telefone = $vendedor ? $vendedor->telefone : 'Gestor/Global';
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
        <button class="chat-tab-btn @if($filtro === 'nao_atendidos') active @endif" 
                onclick="window.location.href='{{ route('gestor.chat.index', ['aba' => 'nao_atendidos']) }}'">
            <i class="fas fa-clock"></i> Pendentes
            @if($contagem['nao_atendidos'] > 0)
                <span class="badge bg-white text-primary" style="font-size: 0.65rem; padding: 2px 6px;">{{ $contagem['nao_atendidos'] }}</span>
            @endif
        </button>
        <button class="chat-tab-btn @if($filtro === 'atendidos') active @endif" 
                onclick="window.location.href='{{ route('gestor.chat.index', ['aba' => 'atendidos']) }}'">
            <i class="fas fa-user-check"></i> Atendimento
        </button>
    </div>

    <div class="chat-search-box">
        <form method="GET">
            <input type="text" name="q" class="chat-search-input" placeholder="Procurar na equipe..." value="{{ $busca }}">
        </form>
    </div>

    <div class="chat-list">
        @forelse($conversas as $conversa)
        <a href="{{ route('gestor.chat.conversa', $conversa->id) }}" 
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
                    <span style="margin-left: 4px;">{{ $conversa->ultimoMensagem->conteudo ?? 'Mensagem da equipe...' }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="chat-empty-state" style="padding: 20px;">
            <i class="fas fa-comment-slash" style="font-size: 2rem; margin-bottom: 10px;"></i>
            <p style="font-size: 0.85rem;">Nenhuma conversa ativa</p>
        </div>
        @endforelse
    </div>
@endsection

@section('chat-content')
    <div class="chat-empty-state">
        <i class="fab fa-whatsapp"></i>
        <h3>Chat da Equipe</h3>
        <p>Selecione uma conversa ao lado para monitorar o atendimento ou intervir se necessário.</p>
    </div>
@endsection