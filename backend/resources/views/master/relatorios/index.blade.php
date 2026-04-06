@extends('layouts.app')
@section('title', 'Relatórios Gerenciais')

@section('content')
<style>
    /* Grid & Layout */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .insight-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; margin-bottom: 24px; }
    
    /* KPI Cards */
    .kpi-card { background: var(--surface); padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); display: flex; flex-direction: column; gap: 8px; position: relative; overflow: hidden; transition: all 0.2s; box-shadow: var(--shadow-sm); }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--primary-light); }
    .kpi-card.highlight { background: linear-gradient(135deg, #faf5ff, #f3e8ff); border-color: #e9d5ff; }
    .kpi-card .kpi-icon { position: absolute; top: 20px; right: 20px; font-size: 1.5rem; color: var(--primary); opacity: 0.2; transform: rotate(-10deg); }
    .kpi-card .label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-card .value { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-top: 4px; }

    /* Section Cards */
    .section-card { background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border-light); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 24px; }
    .section-card-header { padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 12px; }
    .section-card-header i { color: var(--primary); font-size: 1.1rem; }
    .section-card-header h3 { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin: 0; }
    .section-card-body { padding: 24px; }

    /* Tables */
    .rpt-table { width: 100%; border-collapse: collapse; }
    .rpt-table th { text-align: left; padding: 12px 16px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-light); letter-spacing: 0.5px; }
    .rpt-table td { padding: 14px 16px; font-size: 0.85rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    
    /* Progress Bars */
    .progress { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-bottom: 4px; width: 100%; }
    .progress-bar { height: 100%; border-radius: 4px; transition: width 0.3s; }
    .progress-bar.success { background: var(--success); }
    .progress-bar.warning { background: var(--warning); }
    .progress-bar.danger { background: var(--danger); }
    .progress-bar.primary { background: var(--primary); }

    /* Insight Rows */
    .insight-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
    .insight-row:hover { background: #f8fafc; }
    .insight-row .ilabel { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: var(--text-secondary); }
    .insight-row .ivalue { font-weight: 700; font-size: 0.95rem; color: var(--text-primary); }

    /* Badges Formas */
    .badge-forma { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
    .badge-forma.pix { background: #ecfdf5; color: #059669; }
    .badge-forma.boleto { background: #fefce8; color: #ca8a04; }
    .badge-forma.cartao { background: #eff6ff; color: #2563eb; }

    @media (max-width: 1200px) {
        .kpi-grid { grid-template-columns: repeat(3, 1fr); }
        .insight-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 1024px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .kpi-grid { grid-template-columns: 1fr; }
        .filters-bar { flex-direction: column; }
    }
    
    /* Override select styling para evitar conflitos */
    .filters-bar select.form-control {
        appearance: auto !important;
        -webkit-appearance: auto !important;
        background-image: none !important;
        padding-right: 12px !important;
    }
</style>

<x-page-hero 
    title="Relatórios Gerenciais" 
    subtitle="Análise consolidada da operação comercial e financeira" 
    icon="fas fa-chart-bar"
    :exports="[
        ['type' => 'excel', 'url' => route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'excel'])), 'icon' => 'fas fa-file-excel', 'label' => 'Excel'],
        ['type' => 'pdf', 'url' => route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'pdf'])), 'icon' => 'fas fa-file-pdf', 'label' => 'PDF'],
        ['type' => 'csv', 'url' => route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'csv'])), 'icon' => 'fas fa-file-csv', 'label' => 'CSV'],
    ]"
/>

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
    <div class="kpi-card highlight animate-up" style="animation-delay: 0.3s;">
        <i class="fas fa-coins kpi-icon"></i>
        <span class="label">Total de Vendas</span>
        <div class="value">{{ $resumo['totalVendas'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.35s;">
        <i class="fas fa-chart-line kpi-icon"></i>
        <span class="label">Valor Vendido</span>
        <div class="value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.4s;">
        <i class="fas fa-circle-check kpi-icon" style="color: var(--success);"></i>
        <span class="label">Valor Recebido</span>
        <div class="value" style="color: var(--success);">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.45s;">
        <i class="fas fa-hand-holding-dollar kpi-icon" style="color: var(--warning);"></i>
        <span class="label">Comissões Geradas</span>
        <div class="value" style="color: var(--warning);">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.5s;">
        <i class="fas fa-users kpi-icon"></i>
        <span class="label">Clientes Ativos</span>
        <div class="value">{{ $resumo['clientesAtivos'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.55s;">
        <i class="fas fa-arrows-rotate kpi-icon" style="color: var(--success);"></i>
        <span class="label">Renovações</span>
        <div class="value">{{ $resumo['renovacoes'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.6s;">
        <i class="fas fa-arrow-trend-down kpi-icon" style="color: var(--danger);"></i>
        <span class="label">Churn</span>
        <div class="value" style="color: var(--danger);">{{ $resumo['churn'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.65s;">
        <i class="fas fa-ban kpi-icon" style="color: var(--text-muted);"></i>
        <span class="label">Desistências</span>
        <div class="value">{{ $resumo['desistencia'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.7s;">
        <i class="fas fa-bullseye kpi-icon"></i>
        <span class="label">Ticket Médio</span>
        <div class="value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
    </div>
</div>

{{-- ===== Vendas por Vendedor ===== --}}
<div class="section-card animate-up" style="animation-delay: 0.75s;">
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
    <div class="section-card animate-up" style="animation-delay: 0.8s;">
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

    <div class="section-card animate-up" style="animation-delay: 0.85s;">
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
    <div class="section-card animate-up" style="animation-delay: 0.9s;">
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

    <div class="section-card animate-up" style="animation-delay: 0.95s;">
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
