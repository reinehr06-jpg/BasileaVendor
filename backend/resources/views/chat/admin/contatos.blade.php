@extends('chat.layout')

@section('chat-content')
<div class="chat-sidebar">
    <div class="chat-header">
        <h4><i class="fab fa-whatsapp me-2"></i>Contatos</h4>
        <small style="opacity: 0.8">Lista completa de contatos</small>
    </div>

    <div class="chat-search" style="padding: 12px; border-bottom: 1px solid var(--border-color);">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" placeholder="Buscar por nome, telefone ou email..." value="{{ $busca }}" style="flex:1;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="chat-list">
        @forelse($contatos as $contact)
        <div class="chat-item" style="padding: 16px;">
            <div class="chat-item-avatar">
                {{ strtoupper(substr($contact->nome ?? 'C', 0, 1)) }}
            </div>
            <div class="chat-item-info">
                <div class="chat-item-name">
                    <span>{{ $contact->nome ?? 'Sem nome' }}</span>
                </div>
                <div class="chat-item-preview">
                    <small class="text-muted">
                        <i class="fas fa-phone"></i> {{ $contact->telefone ?? '' }}<br>
                        <i class="fas fa-envelope"></i> {{ $contact->email ?? '' }}
                    </small>
                    @if($contact->source)
                    <br><span class="badge bg-secondary">{{ $contact->source }}</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding: 40px;">
            <i class="fas fa-address-book fa-3x"></i>
            <p>Nenhum contato encontrado</p>
        </div>
        @endforelse
    </div>

    <div class="pagination-container" style="padding: 12px;">
        {{ $contatos->appends(['q' => $busca, 'tag' => $tag])->links() }}
    </div>
</div>

<div class="chat-main">
    <div class="empty-state">
        <i class="fas fa-address-book fa-4x"></i>
        <h4>Lista de Contatos</h4>
        <p>Todos os leads que entraram no chat aparecem aqui</p>
    </div>
</div>
@endsection