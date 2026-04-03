@extends('layouts.app')
@section('title', 'Vendas Canceladas / Expiradas')

@section('content')
<style>
    .status-cancelado { background: #fee2e2; color: #991b1b; }
    .status-expirado { background: #fef3c7; color: #92400e; }
    .status-estornado { background: #fce7f3; color: #9d174d; }
</style>

<x-page-hero title="Vendas Canceladas" subtitle="Histórico de vendas canceladas ou estornadas" icon="fas fa-ban" />

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-circle-xmark"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $vendasCanceladas->count() }}</div>
        <div class="stat-label">Canceladas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">{{ $vendasExpiradas->count() }}</div>
        <div class="stat-label">Expiradas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-box-archive"></i></div>
        <div class="stat-value">{{ $vendasCanceladas->count() + $vendasExpiradas->count() }}</div>
        <div class="stat-label">Total no Histórico</div>
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <div style="flex-grow: 1; position: relative;">
        <i class="fas fa-magnifying-glass" style="position: absolute; left: 14px; top: 11px; color: var(--text-muted);"></i>
        <input type="text" class="search-input" id="searchInput" style="padding-left: 40px;" placeholder="Buscar por igreja, plano..." oninput="filterTable()">
    </div>
    <select class="filter-select" id="statusFilter" onchange="filterTable()">
        <option value="">Todos os Status</option>
        <option value="cancelado">Canceladas</option>
        <option value="expirado">Expiradas</option>
    </select>
</div>

<!-- Table -->
@if($vendasCanceladas->count() > 0 || $vendasExpiradas->count() > 0)
<div class="table-container">
    <table id="vendasTable">
        <thead>
            <tr>
                <th><i class="fas fa-building"></i> Igreja / Cliente</th>
                <th>Plano</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendasCanceladas as $v)
            <tr class="venda-row"
                data-search="{{ strtolower(($v->cliente->nome_igreja ?? '') . ' ' . ($v->plano ?? '')) }}"
                data-status="cancelado">
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—' }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $v->cliente->nome_pastor ?? '' }}</div>
                </td>
                <td><span class="badge badge-primary">{{ $v->plano ?? 'N/A' }}</span></td>
                <td style="font-weight: 700;">R$ {{ number_format($v->valor, 2, ',', '.') }}</td>
                <td style="font-size: 0.85rem; color: var(--text-secondary);">{{ ucfirst($v->tipo_negociacao ?? 'mensal') }}</td>
                <td><span class="badge status-cancelado"><i class="fas fa-circle-xmark"></i> Cancelado</span></td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $v->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
            @foreach($vendasExpiradas as $v)
            <tr class="venda-row"
                data-search="{{ strtolower(($v->cliente->nome_igreja ?? '') . ' ' . ($v->plano ?? '')) }}"
                data-status="expirado">
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—' }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $v->cliente->nome_pastor ?? '' }}</div>
                </td>
                <td><span class="badge badge-primary">{{ $v->plano ?? 'N/A' }}</span></td>
                <td style="font-weight: 700;">R$ {{ number_format($v->valor, 2, ',', '.') }}</td>
                <td style="font-size: 0.85rem; color: var(--text-secondary);">{{ ucfirst($v->tipo_negociacao ?? 'mensal') }}</td>
                <td><span class="badge status-expirado"><i class="fas fa-clock"></i> Expirado</span></td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $v->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="empty-state">
    <div class="empty-icon"><i class="fas fa-circle-check"></i></div>
    <h3>Nenhuma venda cancelada ou expirada</h3>
    <p>Todas as suas vendas estão ativas ou concluídas com sucesso!</p>
    <a href="{{ route('vendedor.vendas') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Ver Vendas Ativas</a>
</div>
@endif

<script>
function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    document.querySelectorAll('.venda-row').forEach(row => {
        const matchSearch = !search || row.dataset.search.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}
</script>
@endsection
