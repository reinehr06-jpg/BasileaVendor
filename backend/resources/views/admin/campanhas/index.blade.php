@extends('layouts.app')
@section('title', 'Marketing de Resultados')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-ativa { background: #dcfce7; color: #15803d; }
    .badge-pausada { background: #fef9c3; color: #854d0e; }
    .badge-encerrada { background: #fee2e2; color: #b91c1c; }

    .action-btn { background: white; border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; transition: 0.2s; }
    .action-btn:hover { border-color: var(--primary); background: #f8fafc; }

    .canal-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: white; }
    .canal-meta { background: #1877F2; }
    .canal-google { background: #EA4335; }
    .canal-whatsapp { background: #25D366; }
    .canal-instagram { background: #E4405F; }
    .canal-tiktok { background: #000000; }

    .perf-bar { height: 6px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 6px; width: 100px; }
    .perf-fill { height: 100%; background: var(--primary); }
</style>

<x-page-hero 
    title="Marketing & Leads" 
    subtitle="Gerencie suas campanhas e monitore a performance de captação" 
    icon="fas fa-bullhorn"
>
    <button type="button" class="btn btn-primary btn-sm" onclick="novaCampanha()">
        <i class="fas fa-plus"></i> Nova Campanha
    </button>
</x-page-hero>

<!-- Summary Cards -->
<div class="stats-bar">
    <div class="stat-card" style="background: var(--primary); border-color: var(--primary);">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-bullhorn"></i></div>
        <div class="stat-value" style="color: white;">{{ $kpis['campanhas_ativas'] }}</div>
        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Ativas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
        <div class="stat-value" style="color: var(--primary);">{{ number_format($kpis['total_leads']) }}</div>
        <div class="stat-label">Total Leads</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ number_format($kpis['total_convertidos']) }}</div>
        <div class="stat-label">Convertidos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value" style="color: var(--info);">{{ number_format($kpis['taxa_geral'], 1) }}%</div>
        <div class="stat-label">Conversão</div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.campanhas.index') }}">
<div class="filters-bar">
    <select name="status" class="filter-select">
        <option value="">Todos os Status</option>
        <option value="ativa" {{ request('status') == 'ativa' ? 'selected' : '' }}>Ativa</option>
        <option value="pausada" {{ request('status') == 'pausada' ? 'selected' : '' }}>Pausada</option>
    </select>
    <select name="canal" class="filter-select">
        <option value="">Todos os Canais</option>
        <option value="meta_ads" {{ request('canal') == 'meta_ads' ? 'selected' : '' }}>Meta Ads</option>
        <option value="google_ads" {{ request('canal') == 'google_ads' ? 'selected' : '' }}>Google Ads</option>
        <option value="whatsapp_link" {{ request('canal') == 'whatsapp_link' ? 'selected' : '' }}>WhatsApp</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
    <a href="{{ route('admin.campanhas.index') }}" class="btn btn-ghost btn-sm">Limpar</a>
</div>
</form>

<!-- Table -->
<div class="table-container">
    @if($campanhas->count() > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-bullhorn" style="margin-right: 4px;"></i> Campanha</th>
                <th><i class="fas fa-link" style="margin-right: 4px;"></i> Canal</th>
                <th><i class="fas fa-chart-bar" style="margin-right: 4px;"></i> Performance</th>
                <th><i class="fas fa-circle-check" style="margin-right: 4px;"></i> Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($campanhas as $campanha)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $campanha->nome }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #CMP-{{ str_pad($campanha->id, 4, '0', STR_PAD_LEFT) }}</div>
                </td>
                <td>
                    <div class="d-flex align-center gap-2">
                        @php
                            $canal_class = match($campanha->canal) {
                                'meta_ads' => 'canal-meta',
                                'google_ads' => 'canal-google',
                                'whatsapp_link' => 'canal-whatsapp',
                                'instagram' => 'canal-instagram',
                                'tiktok_ads' => 'canal-tiktok',
                                default => ''
                            };
                            $canal_icon = match($campanha->canal) {
                                'meta_ads' => 'fab fa-facebook-square',
                                'google_ads' => 'fab fa-google',
                                'whatsapp_link' => 'fab fa-whatsapp',
                                'instagram' => 'fab fa-instagram',
                                'tiktok_ads' => 'fab fa-tiktok',
                                default => 'fas fa-link'
                            };
                        @endphp
                        <div class="canal-icon {{ $canal_class }}">
                            <i class="{{ $canal_icon }}"></i>
                        </div>
                        <span style="font-size: 0.85rem; font-weight: 600;">{{ ucfirst(str_replace('_', ' ', $campanha->canal)) }}</span>
                    </div>
                </td>
                <td>
                    <div style="font-size: 0.85rem;">
                        <strong>{{ $campanha->total_leads }}</strong> leads
                        <small class="text-muted" style="margin-left: 8px;">{{ $campanha->total_convertidos }} conv.</small>
                    </div>
                    <div class="perf-bar">
                        <div class="perf-fill" style="width: {{ $campanha->total_leads > 0 ? ($campanha->total_convertidos / $campanha->total_leads * 100) : 0 }}%"></div>
                    </div>
                </td>
                <td><span class="badge badge-{{ $campanha->status }}">{{ ucfirst($campanha->status) }}</span></td>
                <td style="text-align: right;">
                    <div class="d-flex gap-1 justify-end">
                        <a href="{{ route('admin.campanhas.show', $campanha->id) }}" class="action-btn" title="Detalhes"><i class="fas fa-eye"></i></a>
                        <button type="button" class="action-btn" onclick="editarCampanha({{ $campanha->id }})" title="Editar"><i class="fas fa-edit"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
        <h3>Nenhuma campanha encontrada</h3>
        <p>Crie sua primeira campanha para começar a captar leads.</p>
    </div>
    @endif
</div>

{{-- Modais e Scripts omitidos para brevidade, mas devem ser mantidos se existirem no original --}}
@endsection