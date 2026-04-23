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
        <input type="text" class="chat-search-input" placeholder="Buscar por nome ou telefone..." id="searchChat">
    </div>

    <div class="chat-list">
        @forelse($conversas as $conversa)
        <a href="{{ route('vendedor.chat.conversa', $conversa->id) }}" 
           class="chat-item">
            <div class="chat-item-avatar">
                {{ strtoupper(substr($conversa->contact->nome ?? 'C', 0, 1)) }}
            </div>
            <div class="chat-item-info">
                <div class="chat-item-name">
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
            <div style="background: #7c3aed; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">
                {{ $conversa->unread_count }}
            </div>
            @endif
        </a>
        @empty
        <div class="empty-state">
            <i class="fas fa-comments fa-3x" style="margin-bottom: 15px; opacity: 0.3;"></i>
            <p>Nenhuma conversa encontrada nesta aba.</p>
        </div>
        @endforelse
    </div>

    <div style="padding: 15px; border-top: 1px solid var(--chat-border);">
        {{ $conversas->appends(['aba' => $filtro])->links() }}
    </div>
</div>

<div class="chat-main">
    <div class="empty-state">
        <div style="width: 100px; height: 100px; background: #f3e8ff; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin-bottom: 25px; color: #7c3aed; font-size: 2.5rem;">
            <i class="fab fa-whatsapp"></i>
        </div>
        <h3 style="color: #1e293b; font-weight: 800;">Selecione uma conversa</h3>
        <p style="max-width: 280px; margin: 0 auto; color: #64748b;">Inicie um atendimento clicando em um dos contatos na lista ao lado.</p>
    </div>
</div>
@endsection