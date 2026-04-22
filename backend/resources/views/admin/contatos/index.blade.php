@extends('layouts.app')
@section('title', 'Gestão de Leads')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-lead { background: #dcfce7; color: #15803d; }
    .badge-convertido { background: #fef9c3; color: #854d0e; }
    .badge-perdido { background: #fee2e2; color: #b91c1c; }
    .badge-lead_ruim { background: #f1f5f9; color: #64748b; }
    
    .action-btn { background: white; border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; transition: 0.2s; }
    .action-btn:hover { border-color: var(--primary); background: #f8fafc; }

    .canal-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: white; }
    .canal-meta { background: #1877F2; }
    .canal-google { background: #EA4335; }
    .canal-whatsapp { background: #25D366; }
    .canal-instagram { background: #E4405F; }
    .canal-tiktok { background: #000000; }
</style>

<x-page-hero 
    title="Gestão de Leads" 
    subtitle="Monitore e gerencie o fluxo de contatos vindos de todas as suas fontes de marketing" 
    icon="fas fa-address-book"
    :exports="[
        ['type' => 'excel', 'url' => '?formato=excel', 'icon' => 'fas fa-file-excel', 'label' => 'Excel'],
    ]"
>
    <button type="button" class="btn btn-primary btn-sm" onclick="alert('Funcionalidade em desenvolvimento')">
        <i class="fas fa-plus"></i> Novo Lead
    </button>
</x-page-hero>

<!-- Summary Cards -->
<div class="stats-bar">
    <div class="stat-card" style="background: var(--primary); border-color: var(--primary);">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-users"></i></div>
        <div class="stat-value" style="color: white;">{{ number_format($contatos->total()) }}</div>
        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ $contatos->where('status', 'convertido')->count() }}</div>
        <div class="stat-label">Convertidos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-user-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">{{ $contatos->where('status', 'lead')->count() }}</div>
        <div class="stat-label">Leads Abertos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-user-times"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $contatos->whereIn('status', ['perdido', 'lead_ruim'])->count() }}</div>
        <div class="stat-label">Perdidos</div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.contatos.index') }}">
<div class="filters-bar">
    <div style="flex-grow: 1; position: relative;">
        <i class="fas fa-magnifying-glass" style="position: absolute; left: 14px; top: 11px; color: var(--text-muted);"></i>
        <input type="text" name="busca" class="search-input" style="padding-left: 40px;" value="{{ request('busca') }}" placeholder="Nome, telefone, documento...">
    </div>
    <select name="status" class="filter-select">
        <option value="">Status</option>
        <option value="lead" {{ request('status') == 'lead' ? 'selected' : '' }}>Lead</option>
        <option value="convertido" {{ request('status') == 'convertido' ? 'selected' : '' }}>Convertido</option>
        <option value="perdido" {{ request('status') == 'perdido' ? 'selected' : '' }}>Perdido</option>
        <option value="lead_ruim" {{ request('status') == 'lead_ruim' ? 'selected' : '' }}>Lead Ruim</option>
    </select>
    <select name="canal" class="filter-select">
        <option value="">Canal</option>
        <option value="meta_ads" {{ request('canal') == 'meta_ads' ? 'selected' : '' }}>Meta Ads</option>
        <option value="google_ads" {{ request('canal') == 'google_ads' ? 'selected' : '' }}>Google Ads</option>
        <option value="whatsapp_link" {{ request('canal') == 'whatsapp_link' ? 'selected' : '' }}>WhatsApp</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
    <a href="{{ route('admin.contatos.index') }}" class="btn btn-ghost btn-sm">Limpar</a>
</div>
</form>

<!-- Table -->
<div class="table-container">
    @if($contatos->count() > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-user" style="margin-right: 4px;"></i> Contato</th>
                <th><i class="fas fa-link" style="margin-right: 4px;"></i> Canal / Origem</th>
                <th><i class="fas fa-calendar-alt" style="margin-right: 4px;"></i> Cadastro</th>
                <th><i class="fas fa-circle-check" style="margin-right: 4px;"></i> Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contatos as $c)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $c->nome }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        <i class="fas fa-phone"></i> {{ $c->whatsapp ?? $c->telefone ?? '—' }}
                        @if($c->email) <span style="margin-left: 8px;"><i class="fas fa-envelope"></i> {{ $c->email }}</span> @endif
                    </div>
                </td>
                <td>
                    <div class="d-flex align-center gap-2">
                        @php
                            $canal_class = match($c->canal) {
                                'meta_ads' => 'canal-meta',
                                'google_ads' => 'canal-google',
                                'whatsapp_link' => 'canal-whatsapp',
                                'instagram' => 'canal-instagram',
                                'tiktok_ads' => 'canal-tiktok',
                                default => ''
                            };
                            $canal_icon = match($c->canal) {
                                'meta_ads' => 'fab fa-facebook-square',
                                'google_ads' => 'fab fa-google',
                                'whatsapp_link' => 'fab fa-whatsapp',
                                'instagram' => 'fab fa-instagram',
                                'tiktok_ads' => 'fab fa-tiktok',
                                default => 'fas fa-link'
                            };
                        @endphp
                        <div class="canal-icon {{ $canal_class }}" title="{{ ucfirst(str_replace('_', ' ', $c->canal)) }}">
                            <i class="{{ $canal_icon }}"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 600;">{{ $c->campanha?->nome ?? 'Direto / Orgânico' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ ucfirst(str_replace('_', ' ', $c->canal)) }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-size: 0.85rem;">{{ $c->created_at->format('d/m/Y') }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $c->created_at->format('H:i') }}</div>
                </td>
                <td><span class="badge badge-{{ $c->status ?? 'lead' }}">{{ ucfirst($c->status ?? 'Lead') }}</span></td>
                <td style="text-align: right;">
                    <a href="{{ route('admin.contatos.show', $c->id) }}" class="action-btn"><i class="fas fa-eye"></i> Ver Detalhes</a>
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
        <h3>Nenhum lead encontrado</h3>
        <p>Seus leads aparecerão aqui conforme as campanhas gerarem contatos.</p>
    </div>
    @endif
</div>
@endsection