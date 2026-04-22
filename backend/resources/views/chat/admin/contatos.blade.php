@extends('layouts.app')
@section('title', 'Contatos do Chat')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-lead { background: #dcfce7; color: #15803d; }
    .badge-conversa { background: #fef9c3; color: #854d0e; }
    
    .action-btn { background: white; border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; transition: 0.2s; }
    .action-btn:hover { border-color: var(--primary); background: #f8fafc; }
</style>

<x-page-hero 
    title="Contatos do Chat" 
    subtitle="Lista completa de leads e contatos que interagiram via WhatsApp/Chat" 
    icon="fab fa-whatsapp"
/>

<!-- Filters -->
<form method="GET" action="{{ route('admin.chat.contatos') }}">
<div class="filters-bar">
    <div style="flex-grow: 1; position: relative;">
        <i class="fas fa-magnifying-glass" style="position: absolute; left: 14px; top: 11px; color: var(--text-muted);"></i>
        <input type="text" name="q" class="search-input" style="padding-left: 40px;" value="{{ request('q') }}" placeholder="Buscar por nome, telefone ou email...">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
    <a href="{{ route('admin.chat.contatos') }}" class="btn btn-ghost btn-sm">Limpar</a>
</div>
</form>

<!-- Table -->
<div class="table-container">
    @if($contatos->count() > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-user" style="margin-right: 4px;"></i> Contato</th>
                <th><i class="fas fa-phone" style="margin-right: 4px;"></i> Telefone</th>
                <th><i class="fas fa-envelope" style="margin-right: 4px;"></i> E-mail</th>
                <th><i class="fas fa-tag" style="margin-right: 4px;"></i> Origem</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contatos as $contact)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $contact->nome ?? 'Sem nome' }}</div>
                    @if($contact->documento) <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $contact->documento }}</div> @endif
                </td>
                <td>{{ $contact->telefone ?? '—' }}</td>
                <td>{{ $contact->email ?? '—' }}</td>
                <td>
                    @if($contact->source)
                        <span class="badge bg-light text-dark">{{ $contact->source }}</span>
                    @else
                        <span class="badge bg-light text-muted">Orgânico</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <a href="{{ route('chat.admin.index', ['contact_id' => $contact->id]) }}" class="action-btn">
                        <i class="fab fa-whatsapp"></i> Abrir Chat
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.85rem; color: var(--text-muted);">Mostrando {{ $contatos->firstItem() ?? 0 }} a {{ $contatos->lastItem() ?? 0 }} de {{ $contatos->total() }}</span>
        <div>{{ $contatos->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-address-book"></i></div>
        <h3>Nenhum contato encontrado</h3>
        <p>Os contatos que enviarem mensagens aparecerão aqui.</p>
    </div>
    @endif
</div>
@endsection