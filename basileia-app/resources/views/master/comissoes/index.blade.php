@extends('layouts.app')
@section('title', 'Comissões')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-success { background: #dcfce7; color: #15803d; }
    .badge-warning { background: #fef9c3; color: #854d0e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: var(--surface); cursor: pointer; transition: all 0.2s; font-size: 0.95rem; color: var(--text-secondary); margin-left: 4px; }
    .action-btn:hover { background: var(--bg); color: var(--primary); border-color: var(--primary); }
    .vendedor-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; flex-shrink: 0; }
    .export-dropdown { position: relative; display: inline-block; }
    .export-dropdown-content { display: none; position: absolute; right: 0; background: var(--surface); min-width: 180px; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100; margin-top: 4px; }
    .export-dropdown:hover .export-dropdown-content { display: block; }
    .export-item { display: block; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 0.875rem; transition: 0.15s; }
    .export-item:hover { background: var(--bg); color: var(--primary); }
    .export-item:first-child { border-radius: 8px 8px 0 0; }
    .export-item:last-child { border-radius: 0 0 8px 8px; }
    .export-item i { margin-right: 8px; width: 16px; }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-hand-holding-dollar" style="margin-right: 8px;"></i>Comissões</h2>
        <p>Resumo de comissões por vendedor com histórico detalhado</p>
    </div>
    <div class="export-dropdown">
        <button class="btn btn-outline">
            <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="margin-left: 6px; font-size: 0.7rem;"></i>
        </button>
        <div class="export-dropdown-content">
            <a href="{{ route('master.comissoes.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="export-item">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <a href="{{ route('master.comissoes.exportar', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="export-item">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('master.comissoes.exportar', array_merge(request()->query(), ['formato' => 'csv'])) }}" class="export-item">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ $resumo['total_vendedores'] }}</div>
        <div class="stat-label">Vendedores</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-sack-dollar"></i></div>
        <div class="stat-value" style="color: var(--success);">R$ {{ number_format($resumo['total_comissao'], 2, ',', '.') }}</div>
        <div class="stat-label">Total Comissão</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-value">{{ $resumo['total_vendas'] }}</div>
        <div class="stat-label">Total Vendas</div>
    </div>
    <div class="stat-card" style="background: var(--primary); border-color: var(--primary);">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value" style="color: white;">R$ {{ number_format($resumo['ticket_medio'], 2, ',', '.') }}</div>
        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Ticket Médio</div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('master.comissoes') }}">
<div class="filters-bar">
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-calendar"></i> Mês</label>
        <input type="month" name="mes" class="form-control" value="{{ $mes }}" onchange="this.form.submit()">
    </div>
    <div style="display: flex; gap: 8px; align-items: flex-end;">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="{{ route('master.comissoes') }}" class="btn btn-ghost btn-sm">Limpar</a>
    </div>
</div>
</form>

<!-- Table -->
<div class="table-container">
    @if(count($vendedoresComissao) > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-user" style="margin-right: 4px;"></i> Vendedor</th>
                <th class="text-center"><i class="fas fa-shopping-bag" style="margin-right: 4px;"></i> Vendas</th>
                <th class="text-right"><i class="fas fa-dollar-sign" style="margin-right: 4px;"></i> Comissão Total</th>
                <th class="text-center"><i class="fas fa-percentage" style="margin-right: 4px;"></i> % Meta</th>
                <th class="text-center"><i class="fas fa-file-invoice" style="margin-right: 4px;"></i> Notas Fiscais</th>
                <th style="text-align: right;"><i class="fas fa-cog" style="margin-right: 4px;"></i> Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendedoresComissao as $vc)
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="vendedor-avatar">{{ strtoupper(substr($vc['nome'], 0, 1)) }}</div>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $vc['nome'] }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $vc['email'] }}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center" style="font-weight: 700; font-size: 1.1rem;">{{ $vc['total_vendas'] }}</td>
                <td class="text-right">
                    <span style="font-weight: 700; color: var(--success); font-size: 1.1rem;">R$ {{ number_format($vc['comissao_total'], 2, ',', '.') }}</span>
                </td>
                <td class="text-center">
                    @php
                    $percentualMeta = $vc['meta'] > 0 ? round(($vc['vendido'] / $vc['meta']) * 100) : 0;
                    @endphp
                    <span class="badge {{ $percentualMeta >= 100 ? 'badge-success' : ($percentualMeta >= 50 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $percentualMeta }}%
                    </span>
                </td>
                <td class="text-center">
                    @if($vc['notas_fiscais_count'] > 0)
                    <span class="badge badge-success">{{ $vc['notas_fiscais_count'] }} NF(s)</span>
                    @else
                    <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                    @endif
                </td>
                <td style="text-align: right; white-space: nowrap;">
                    @if(Auth::user()->perfil === 'master')
                    <a href="{{ route('master.comissoes.historico', $vc['id']) }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-eye"></i> Ver Histórico
                    </a>
                    @else
                    <a href="{{ route('master.comissoes.historico', $vc['id']) }}" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-eye"></i> Ver Histórico
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-hand-holding-dollar"></i></div>
        <h3>Nenhuma comissão encontrada</h3>
        <p>As comissões aparecerão aqui quando os pagamentos forem confirmados.</p>
    </div>
    @endif
</div>

@endsection
