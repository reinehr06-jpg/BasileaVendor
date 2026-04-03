@extends('layouts.app')
@section('title', 'Relatórios Gerenciais')

@section('content')
<style>
    .report-hero {
        margin-bottom: 30px;
        padding: 30px;
        background: linear-gradient(135deg, var(--primary-dark) 0%, #4C1D95 100%);
        border-radius: var(--radius-xl);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 25px -5px rgba(59, 7, 100, 0.2);
    }
    .report-hero h2 { color: white; margin-bottom: 6px; font-size: 1.6rem; letter-spacing: -0.5px; }
    .report-hero p { opacity: 0.85; font-size: 0.95rem; }
    .export-dropdown { position: relative; display: inline-block; }
    .export-btn { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; backdrop-filter: blur(10px); transition: 0.2s; }
    .export-btn:hover { background: rgba(255,255,255,0.25); }
    .export-dropdown-content { display: none; position: absolute; right: 0; top: calc(100% + 6px); background: var(--surface); min-width: 180px; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999; }
    .export-dropdown:hover .export-dropdown-content { display: block; }
    .export-item { display: block; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 0.875rem; transition: 0.15s; }
    .export-item:hover { background: var(--bg); color: var(--primary); }
    .export-item:first-child { border-radius: 8px 8px 0 0; }
    .export-item:last-child { border-radius: 0 0 8px 8px; }
    .export-item i { margin-right: 8px; width: 16px; }

    .kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
    .kpi-card { padding: 24px; background: white; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); transition: all 0.3s ease; position: relative; overflow: hidden; }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .kpi-card .label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: block; }
    .kpi-card .value { font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
    .kpi-icon { position: absolute; top: 15px; right: 15px; font-size: 1.2rem; opacity: 0.15; color: var(--primary); }
    .kpi-card.highlight { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); border: none; }
    .kpi-card.highlight .label { color: rgba(255,255,255,0.75); }
    .kpi-card.highlight .value { color: white; }
    .kpi-card.highlight .kpi-icon { color: white; opacity: 0.3; }
    .kpi-card.highlight:hover { box-shadow: 0 12px 28px rgba(76, 29, 149, 0.3); }

    .section-card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); margin-bottom: 25px; overflow: hidden; }
    .section-card-header { padding: 20px 24px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px; }
    .section-card-header i { color: var(--primary); font-size: 1.15rem; }
    .section-card-header h3 { font-size: 1.05rem; font-weight: 700; color: var(--text-primary); }
    .section-card-body { padding: 0; }

    .insight-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
    .insight-card { padding: 24px; background: white; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); }
    .insight-card:hover { box-shadow: var(--shadow-md); }
    .insight-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; font-weight: 700; font-size: 0.95rem; color: var(--text-primary); }
    .insight-header i { color: var(--primary); }
    .insight-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border-light); }
    .insight-row:last-child { border-bottom: none; }
    .insight-row .ilabel { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: var(--text); }
    .insight-row .ilabel i { width: 16px; text-align: center; font-size: 0.9rem; }
    .insight-row .ivalue { font-weight: 700; font-size: 0.95rem; }

    .rpt-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
    .rpt-table th { background: var(--bg); padding: 12px 18px; text-align: left; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.72rem; border-bottom: 1px solid var(--border-light); white-space: nowrap; }
    .rpt-table td { padding: 14px 18px; border-bottom: 1px solid var(--border-light); color: var(--text); vertical-align: middle; }
    .rpt-table tr:last-child td { border-bottom: none; }
    .rpt-table tbody tr:hover { background: var(--surface-hover); }
    .rpt-table .text-right { text-align: right; }
    .rpt-table .text-center { text-align: center; }

    .badge-forma { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }
    .badge-forma.recorrente { background: #d1fae5; color: #065f46; }

    .filter-wrap { width: 100%; }

    @media (max-width: 1200px) {
        .kpi-grid { grid-template-columns: repeat(3, 1fr); }
        .insight-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .report-hero { flex-direction: column; gap: 16px; text-align: center; }
        .filters-bar { flex-direction: column; }
        .filters-bar > div { width: 100%; }
    }
</style>

<!-- ===== Hero Banner ===== -->
<div class="animate-up" style="animation-delay: 0.1s;">
    <div class="report-hero" style="overflow: visible;">
        <div>
            <h2><i class="fas fa-chart-bar" style="margin-right: 10px;"></i>Relatórios Gerenciais</h2>
            <p>Análise consolidada da operação comercial e financeira</p>
        </div>
        <div class="export-dropdown">
            <button class="export-btn">
                <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="font-size: 0.65rem;"></i>
            </button>
            <div class="export-dropdown-content">
                <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="export-item">
                    <i class="fas fa-file-excel" style="color: var(--success);"></i> Exportar Excel
                </a>
                <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="export-item">
                    <i class="fas fa-file-pdf" style="color: var(--danger);"></i> Exportar PDF
                </a>
                <a href="{{ route('master.relatorios.exportar', request()->query()) }}" class="export-item">
                    <i class="fas fa-file-csv" style="color: var(--info);"></i> Exportar CSV
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ===== Filtros ===== --}}
<form method="GET" action="{{ route('master.relatorios') }}">
<div class="filters-bar animate-up" style="animation-delay: 0.15s;">
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 130px;">
        <label style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Início</label>
        <input type="date" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] }}">
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 130px;">
        <label style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Fim</label>
        <input type="date" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] }}">
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
        <label style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Vendedor</label>
        <select name="vendedor_id" class="form-control">
            <option value="">Todos</option>
            @foreach($vendedores as $v)
                <option value="{{ $v->id }}" {{ $filtros['vendedor_id'] == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
            @endforeach
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 120px;">
        <label style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Status</label>
        <select name="status" class="form-control">
            <option value="">Todos</option>
            <option value="Pago" {{ $filtros['status'] == 'Pago' ? 'selected' : '' }}>Pago</option>
            <option value="Cancelado" {{ $filtros['status'] == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px; align-items: flex-end;">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="{{ route('master.relatorios') }}" class="btn btn-ghost btn-sm">Limpar</a>
    </div>
</div>
</form>

{{-- ===== Estado vazio ===== --}}
@if(!$temDadosNoSistema)
<div class="table-container animate-up" style="animation-delay: 0.2s;">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-chart-pie"></i></div>
        <h3>Nenhum dado disponível</h3>
        <p>Os relatórios serão exibidos assim que houver movimentações no sistema.</p>
    </div>
</div>
@elseif(!$filtrosRetornaramDados)
<div class="table-container animate-up" style="animation-delay: 0.2s;">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-search"></i></div>
        <h3>Nenhum resultado encontrado</h3>
        <p>Tente alterar os filtros para visualizar os dados.</p>
    </div>
</div>
@else

{{-- ===== KPIs ===== --}}
<div class="kpi-grid">
    <div class="kpi-card highlight animate-up" style="animation-delay: 0.2s;">
        <i class="fas fa-coins kpi-icon"></i>
        <span class="label">Total de Vendas</span>
        <div class="value">{{ $resumo['totalVendas'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.25s;">
        <i class="fas fa-chart-line kpi-icon"></i>
        <span class="label">Valor Vendido</span>
        <div class="value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.3s;">
        <i class="fas fa-circle-check kpi-icon" style="color: var(--success);"></i>
        <span class="label">Valor Recebido</span>
        <div class="value" style="color: var(--success);">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.35s;">
        <i class="fas fa-hand-holding-dollar kpi-icon" style="color: var(--warning);"></i>
        <span class="label">Comissões Geradas</span>
        <div class="value" style="color: var(--warning);">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.4s;">
        <i class="fas fa-users kpi-icon"></i>
        <span class="label">Clientes Ativos</span>
        <div class="value">{{ $resumo['clientesAtivos'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.45s;">
        <i class="fas fa-arrows-rotate kpi-icon" style="color: var(--success);"></i>
        <span class="label">Renovações</span>
        <div class="value">{{ $resumo['renovacoes'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.5s;">
        <i class="fas fa-arrow-trend-down kpi-icon" style="color: var(--danger);"></i>
        <span class="label">Churn</span>
        <div class="value" style="color: var(--danger);">{{ $resumo['churn'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.55s;">
        <i class="fas fa-ban kpi-icon" style="color: var(--text-muted);"></i>
        <span class="label">Desistências</span>
        <div class="value">{{ $resumo['desistencia'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.6s;">
        <i class="fas fa-bullseye kpi-icon"></i>
        <span class="label">Ticket Médio</span>
        <div class="value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
    </div>
</div>

{{-- ===== Vendas por Vendedor ===== --}}
<div class="section-card animate-up" style="animation-delay: 0.65s;">
    <div class="section-card-header">
        <i class="fas fa-chart-bar"></i>
        <h3>Vendas por Vendedor</h3>
    </div>
    <div class="section-card-body">
        @if(count($vendasPorVendedor) > 0)
        <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th class="text-center">Vendas</th>
                    <th class="text-right">Valor Vendido</th>
                    <th class="text-right">Valor Recebido</th>
                    <th class="text-right">Comissão</th>
                    <th class="text-center">Clientes</th>
                    <th class="text-center">Churn</th>
                    <th class="text-center">Desist.</th>
                    <th class="text-right">Meta</th>
                    <th style="min-width: 120px;">% Meta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendasPorVendedor as $vv)
                <tr>
                    <td style="font-weight: 600; color: var(--text-primary);">{{ $vv['vendedor_nome'] }}</td>
                    <td class="text-center" style="font-weight: 700;">{{ $vv['total_vendas'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_vendido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_recebido'], 2, ',', '.') }}</td>
                    <td class="text-right" style="color: var(--warning); font-weight: 600;">R$ {{ number_format($vv['comissao'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vv['clientes_ativos'] }}</td>
                    <td class="text-center" style="color: {{ $vv['churn'] > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">{{ $vv['churn'] }}</td>
                    <td class="text-center" style="color: {{ $vv['desistencia'] > 0 ? 'var(--text-secondary)' : 'var(--text-muted)' }};">{{ $vv['desistencia'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['meta'], 2, ',', '.') }}</td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar {{ $vv['percentual_meta'] >= 100 ? 'success' : ($vv['percentual_meta'] >= 50 ? 'warning' : 'danger') }}" style="width: {{ min($vv['percentual_meta'], 100) }}%;"></div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: {{ $vv['percentual_meta'] >= 100 ? 'var(--success)' : ($vv['percentual_meta'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $vv['percentual_meta'] }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-chart-bar"></i></div>
            <h3>Sem dados de vendas</h3>
            <p>Nenhum vendedor com vendas no período selecionado.</p>
        </div>
        @endif
    </div>
</div>

{{-- ===== Grid: Metas por Equipe + Recebimentos ===== --}}
<div class="insight-grid">
    <div class="section-card animate-up" style="animation-delay: 0.7s;">
        <div class="section-card-header">
            <i class="fas fa-users-cog"></i>
            <h3>Metas por Equipe</h3>
        </div>
        <div class="section-card-body">
            @if(count($metasPorEquipe) > 0)
            <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Equipe</th>
                        <th class="text-center">Vendas</th>
                        <th class="text-right">Recebido</th>
                        <th>% Meta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metasPorEquipe as $eq)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-primary);">{{ $eq['equipe_nome'] }}</td>
                        <td class="text-center">{{ $eq['total_vendas'] }}</td>
                        <td class="text-right">R$ {{ number_format($eq['valor_recebido'], 2, ',', '.') }}</td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar {{ $eq['percentual_meta'] >= 100 ? 'success' : ($eq['percentual_meta'] >= 50 ? 'warning' : 'danger') }}" style="width: {{ min($eq['percentual_meta'], 100) }}%;"></div>
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 700; color: {{ $eq['percentual_meta'] >= 100 ? 'var(--success)' : ($eq['percentual_meta'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $eq['percentual_meta'] }}%</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @else
            <div class="empty-state" style="padding: 30px;">
                <div class="empty-icon" style="width: 50px; height: 50px; font-size: 1.2rem; margin: 0 auto 10px;"><i class="fas fa-users"></i></div>
                <h3 style="font-size: 0.95rem;">Sem equipes</h3>
                <p style="font-size: 0.8rem;">Crie equipes para visualizar.</p>
            </div>
            @endif
        </div>
    </div>

    <div class="section-card animate-up" style="animation-delay: 0.75s;">
        <div class="section-card-header">
            <i class="fas fa-money-bill-wave"></i>
            <h3>Recebimentos no Período</h3>
        </div>
        <div class="section-card-body">
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-list-check" style="color: var(--primary);"></i> Cobranças</span>
                <span class="ivalue">{{ $pagamentosPeriodo['total_pagamentos'] }}</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-circle-check" style="color: var(--success);"></i> Pago</span>
                <span class="ivalue" style="color: var(--success);">R$ {{ number_format($pagamentosPeriodo['total_pago'], 2, ',', '.') }}</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-clock" style="color: var(--warning);"></i> Pendente</span>
                <span class="ivalue" style="color: var(--warning);">R$ {{ number_format($pagamentosPeriodo['total_pendente'], 2, ',', '.') }}</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-circle-xmark" style="color: var(--danger);"></i> Vencido</span>
                <span class="ivalue" style="color: var(--danger);">R$ {{ number_format($pagamentosPeriodo['total_vencido'], 2, ',', '.') }}</span>
            </div>
            <div class="insight-row" style="background: linear-gradient(135deg, #faf5ff, #f3e8ff); padding: 14px 16px; border-radius: 8px; margin: 8px; border: none;">
                <span class="ilabel"><i class="fas fa-coins" style="color: var(--primary);"></i> <strong>Total Recebido</strong></span>
                <span class="ivalue" style="font-size: 1.15rem; color: var(--primary);">R$ {{ number_format($pagamentosPeriodo['valor_recebido'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== Grid: Churn + Formas de Pagamento ===== --}}
<div class="insight-grid">
    <div class="section-card animate-up" style="animation-delay: 0.8s;">
        <div class="section-card-header">
            <i class="fas fa-arrows-rotate"></i>
            <h3>Renovações e Churn</h3>
        </div>
        <div class="section-card-body">
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-circle-check" style="color: var(--success);"></i> Renovados / Pagos</span>
                <span class="ivalue" style="color: var(--success);">{{ $churnRenovacoes['renovados'] }}</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-arrow-trend-down" style="color: var(--danger);"></i> Churn</span>
                <span class="ivalue" style="color: var(--danger);">{{ $churnRenovacoes['churn'] }}</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-ban" style="color: var(--text-secondary);"></i> Desistências</span>
                <span class="ivalue" style="color: var(--text-secondary);">{{ $churnRenovacoes['desistencias'] }}</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-percent" style="color: var(--primary);"></i> Taxa de Churn</span>
                <span class="ivalue" style="color: {{ $churnRenovacoes['churn_percentual'] > 20 ? 'var(--danger)' : ($churnRenovacoes['churn_percentual'] > 10 ? 'var(--warning)' : 'var(--success)') }};">{{ $churnRenovacoes['churn_percentual'] }}%</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-circle" style="color: var(--success);"></i> Recorrência Ativa</span>
                <span class="ivalue" style="font-weight: 700;">{{ $churnRenovacoes['ativos'] }}</span>
            </div>
            <div class="insight-row">
                <span class="ilabel"><i class="fas fa-circle" style="color: var(--danger);"></i> Recorrência Inativa</span>
                <span class="ivalue" style="font-weight: 700;">{{ $churnRenovacoes['inativos'] }}</span>
            </div>
        </div>
    </div>

    <div class="section-card animate-up" style="animation-delay: 0.85s;">
        <div class="section-card-header">
            <i class="fas fa-credit-card"></i>
            <h3>Formas de Pagamento</h3>
        </div>
        <div class="section-card-body">
            @if(count($formasPagamento) > 0)
            <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Forma</th>
                        <th class="text-center">Qtd</th>
                        <th class="text-right">Valor</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formasPagamento as $fp)
                    <tr>
                        <td>
                            <span class="badge-forma {{ $fp['forma'] }}">
                                @if($fp['forma'] == 'pix') <i class="fas fa-bolt"></i> PIX
                                @elseif($fp['forma'] == 'boleto') <i class="fas fa-barcode"></i> Boleto
                                @elseif($fp['forma'] == 'cartao') <i class="fas fa-credit-card"></i> Cartão
                                @else <i class="fas fa-arrows-rotate"></i> Recorrente
                                @endif
                            </span>
                        </td>
                        <td class="text-center" style="font-weight: 700;">{{ $fp['quantidade'] }}</td>
                        <td class="text-right">R$ {{ number_format($fp['valor_total'], 2, ',', '.') }}</td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar primary" style="width: {{ $fp['percentual'] }}%;"></div>
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 600;">{{ $fp['percentual'] }}%</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @else
            <div class="empty-state" style="padding: 30px;">
                <div class="empty-icon" style="width: 50px; height: 50px; font-size: 1.2rem; margin: 0 auto 10px;"><i class="fas fa-credit-card"></i></div>
                <h3 style="font-size: 0.95rem;">Sem pagamentos</h3>
                <p style="font-size: 0.8rem;">Nenhum pagamento no período.</p>
            </div>
            @endif
        </div>
    </div>
</div>

@endif

@endsection
