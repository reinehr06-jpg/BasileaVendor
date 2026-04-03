@extends('layouts.app')
@section('title', 'Relatórios Gerenciais')

@section('content')
<style>
    .report-hero {
        margin-bottom: 28px;
        padding: 28px 32px;
        background: linear-gradient(135deg, #3B0764 0%, #4C1D95 100%);
        border-radius: 16px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 25px -5px rgba(59, 7, 100, 0.2);
    }
    .report-hero h2 { color: white; margin-bottom: 6px; font-size: 1.5rem; letter-spacing: -0.5px; }
    .report-hero p { opacity: 0.8; font-size: 0.9rem; }
    .export-dropdown { position: relative; display: inline-block; }
    .export-btn { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }
    .export-btn:hover { background: rgba(255,255,255,0.25); }
    .export-menu { display: none; position: absolute; right: 0; top: calc(100% + 6px); background: white; min-width: 180px; border: 1px solid #e0e0e8; border-radius: 10px; box-shadow: 0 12px 32px rgba(0,0,0,0.15); z-index: 100; overflow: hidden; }
    .export-menu.show { display: block; }
    .export-menu a { display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: #3b3b5c; text-decoration: none; font-size: 0.85rem; font-weight: 500; }
    .export-menu a:hover { background: #faf5ff; color: #4C1D95; }
    .export-menu a i { width: 18px; text-align: center; }

    .kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 28px; }
    .kpi-card { padding: 22px; background: white; border-radius: 12px; border: 1px solid #ededf2; box-shadow: 0 2px 4px rgba(50, 50, 71, 0.08); transition: all 0.3s ease; }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(50, 50, 71, 0.12); }
    .kpi-icon { width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 14px; }
    .kpi-icon.purple { background: rgba(76, 29, 149, 0.1); color: #4C1D95; }
    .kpi-icon.blue { background: #dbeafe; color: #2563eb; }
    .kpi-icon.green { background: #dcfce7; color: #16a34a; }
    .kpi-icon.yellow { background: #fef3c7; color: #f59e0b; }
    .kpi-icon.red { background: #fee2e2; color: #ef4444; }
    .kpi-icon.gray { background: #f4f5fa; color: #a1a1b5; }
    .kpi-label { font-size: 0.72rem; font-weight: 700; color: #a1a1b5; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
    .kpi-value { font-size: 1.5rem; font-weight: 800; color: #3b3b5c; }
    .kpi-card.highlight { background: linear-gradient(135deg, #3B0764, #4C1D95); border: none; }
    .kpi-card.highlight .kpi-label { color: rgba(255,255,255,0.7); }
    .kpi-card.highlight .kpi-value { color: white; }
    .kpi-card.highlight .kpi-icon { background: rgba(255,255,255,0.15); color: white; }
    .kpi-card.highlight:hover { box-shadow: 0 12px 28px rgba(76, 29, 149, 0.3); }

    .section-card { background: white; border: 1px solid #ededf2; border-radius: 12px; margin-bottom: 24px; overflow: hidden; box-shadow: 0 2px 4px rgba(50, 50, 71, 0.08); }
    .section-header { padding: 18px 24px; border-bottom: 1px solid #ededf2; display: flex; align-items: center; gap: 10px; background: #f4f5fa; }
    .section-header i { color: #4C1D95; font-size: 1.1rem; }
    .section-header h3 { font-size: 1rem; font-weight: 700; color: #3b3b5c; }
    .section-body { padding: 0; }

    .rpt-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .rpt-table th { background: #f4f5fa; padding: 12px 16px; text-align: left; font-weight: 600; color: #a1a1b5; text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.72rem; border-bottom: 1px solid #ededf2; white-space: nowrap; }
    .rpt-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #4a4a6a; vertical-align: middle; }
    .rpt-table tr:last-child td { border-bottom: none; }
    .rpt-table tbody tr:hover { background: #f8f7ff; }
    .rpt-table .text-right { text-align: right; }
    .rpt-table .text-center { text-align: center; }
    .rpt-table .fw-bold { font-weight: 700; color: #3b3b5c; }

    .pbar { background: #f4f5fa; height: 8px; border-radius: 4px; overflow: hidden; }
    .pbar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
    .pbar-fill.green { background: #16a34a; }
    .pbar-fill.yellow { background: #f59e0b; }
    .pbar-fill.red { background: #ef4444; }
    .pbar-fill.purple { background: #4C1D95; }

    .badge-forma { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }
    .badge-forma.recorrente { background: #d1fae5; color: #065f46; }

    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 24px; border-bottom: 1px solid #f1f5f9; }
    .summary-row:last-child { border-bottom: none; }
    .summary-row.highlight { background: linear-gradient(135deg, #faf5ff, #f3e8ff); }
    .summary-row .slabel { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: #4a4a6a; }
    .summary-row .slabel i { color: #4C1D95; width: 18px; text-align: center; }
    .summary-row .svalue { font-weight: 700; font-size: 0.95rem; }

    .filter-wrap { width: 100%; }

    @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .report-hero { flex-direction: column; gap: 16px; text-align: center; }
        .filters-bar { flex-direction: column; }
        .filters-bar > div { width: 100%; }
    }
</style>

<!-- ===== Hero Header ===== -->
<div class="report-hero">
    <div>
        <h2><i class="fas fa-chart-bar" style="margin-right: 10px;"></i>Relatórios Gerenciais</h2>
        <p>Análise consolidada da operação comercial e financeira</p>
    </div>
    <div class="export-dropdown">
        <button type="button" id="exportBtn" class="export-btn">
            <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="font-size: 0.65rem;"></i>
        </button>
        <div id="exportMenu" class="export-menu">
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}">
                <i class="fas fa-file-excel" style="color: #16a34a;"></i> Excel
            </a>
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'pdf'])) }}">
                <i class="fas fa-file-pdf" style="color: #dc2626;"></i> PDF
            </a>
            <a href="{{ route('master.relatorios.exportar', request()->query()) }}">
                <i class="fas fa-file-csv" style="color: #2563eb;"></i> CSV
            </a>
        </div>
    </div>
</div>

{{-- ===== Filtros ===== --}}
<form method="GET" action="{{ route('master.relatorios') }}">
<div class="filters-bar">
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Data Início</label>
        <input type="date" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] }}">
    </div>
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Data Fim</label>
        <input type="date" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] }}">
    </div>
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Vendedor</label>
        <select name="vendedor_id" class="form-control">
            <option value="">Todos</option>
            @foreach($vendedores as $v)
                <option value="{{ $v->id }}" {{ $filtros['vendedor_id'] == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Status</label>
        <select name="status" class="form-control">
            <option value="">Todos</option>
            <option value="Aguardando pagamento" {{ $filtros['status'] == 'Aguardando pagamento' ? 'selected' : '' }}>Aguardando</option>
            <option value="Pago" {{ $filtros['status'] == 'Pago' ? 'selected' : '' }}>Pago</option>
            <option value="Cancelado" {{ $filtros['status'] == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
            <option value="Expirado" {{ $filtros['status'] == 'Expirado' ? 'selected' : '' }}>Expirado</option>
            <option value="Vencido" {{ $filtros['status'] == 'Vencido' ? 'selected' : '' }}>Vencido</option>
        </select>
    </div>
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Forma Pagamento</label>
        <select name="forma_pagamento" class="form-control">
            <option value="">Todas</option>
            <option value="pix" {{ $filtros['forma_pagamento'] == 'pix' ? 'selected' : '' }}>PIX</option>
            <option value="boleto" {{ $filtros['forma_pagamento'] == 'boleto' ? 'selected' : '' }}>Boleto</option>
            <option value="cartao" {{ $filtros['forma_pagamento'] == 'cartao' ? 'selected' : '' }}>Cartão</option>
        </select>
    </div>
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Negociação</label>
        <select name="tipo_negociacao" class="form-control">
            <option value="">Todos</option>
            <option value="mensal" {{ $filtros['tipo_negociacao'] == 'mensal' ? 'selected' : '' }}>Mensal</option>
            <option value="anual" {{ $filtros['tipo_negociacao'] == 'anual' ? 'selected' : '' }}>Anual</option>
        </select>
    </div>
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Cliente</label>
        <select name="cliente_id" class="form-control">
            <option value="">Todos</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ $filtros['cliente_id'] == $c->id ? 'selected' : '' }}>{{ $c->nome_igreja ?? $c->nome ?? 'Cliente #'.$c->id }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-wrap">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Recorrência</label>
        <select name="recorrencia" class="form-control">
            <option value="">Todas</option>
            <option value="ativa" {{ $filtros['recorrencia'] == 'ativa' ? 'selected' : '' }}>Ativa</option>
            <option value="inativa" {{ $filtros['recorrencia'] == 'inativa' ? 'selected' : '' }}>Inativa</option>
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
<div class="table-container">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-chart-pie"></i></div>
        <h3>Nenhum dado disponível</h3>
        <p>Os relatórios serão exibidos assim que houver movimentações no sistema.</p>
    </div>
</div>
@elseif(!$filtrosRetornaramDados)
<div class="table-container">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-search"></i></div>
        <h3>Nenhum resultado encontrado</h3>
        <p>Tente alterar os filtros para visualizar os dados.</p>
    </div>
</div>
@else

{{-- ===== KPIs ===== --}}
<div class="kpi-grid">
    <div class="kpi-card highlight">
        <div class="kpi-icon"><i class="fas fa-coins"></i></div>
        <span class="kpi-label">Total de Vendas</span>
        <div class="kpi-value">{{ $resumo['totalVendas'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon blue"><i class="fas fa-chart-line"></i></div>
        <span class="kpi-label">Valor Vendido</span>
        <div class="kpi-value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon green"><i class="fas fa-circle-check"></i></div>
        <span class="kpi-label">Valor Recebido</span>
        <div class="kpi-value" style="color: #16a34a;">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon yellow"><i class="fas fa-hand-holding-dollar"></i></div>
        <span class="kpi-label">Comissões Geradas</span>
        <div class="kpi-value" style="color: #f59e0b;">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon purple"><i class="fas fa-users"></i></div>
        <span class="kpi-label">Clientes Ativos</span>
        <div class="kpi-value">{{ $resumo['clientesAtivos'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon green"><i class="fas fa-arrows-rotate"></i></div>
        <span class="kpi-label">Renovações</span>
        <div class="kpi-value">{{ $resumo['renovacoes'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon red"><i class="fas fa-arrow-trend-down"></i></div>
        <span class="kpi-label">Churn</span>
        <div class="kpi-value" style="color: #ef4444;">{{ $resumo['churn'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon gray"><i class="fas fa-ban"></i></div>
        <span class="kpi-label">Desistências</span>
        <div class="kpi-value">{{ $resumo['desistencia'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon blue"><i class="fas fa-bullseye"></i></div>
        <span class="kpi-label">Ticket Médio</span>
        <div class="kpi-value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
    </div>
</div>

{{-- ===== Vendas por Vendedor ===== --}}
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-chart-bar"></i>
        <h3>Vendas por Vendedor</h3>
    </div>
    <div class="section-body">
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
                    <td class="fw-bold">{{ $vv['vendedor_nome'] }}</td>
                    <td class="text-center">{{ $vv['total_vendas'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_vendido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_recebido'], 2, ',', '.') }}</td>
                    <td class="text-right" style="color: #f59e0b; font-weight: 600;">R$ {{ number_format($vv['comissao'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vv['clientes_ativos'] }}</td>
                    <td class="text-center" style="color: {{ $vv['churn'] > 0 ? '#ef4444' : '#a1a1b5' }};">{{ $vv['churn'] }}</td>
                    <td class="text-center" style="color: {{ $vv['desistencia'] > 0 ? '#6e6b8b' : '#a1a1b5' }};">{{ $vv['desistencia'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['meta'], 2, ',', '.') }}</td>
                    <td>
                        <div class="pbar">
                            <div class="pbar-fill {{ $vv['percentual_meta'] >= 100 ? 'green' : ($vv['percentual_meta'] >= 50 ? 'yellow' : 'red') }}" style="width: {{ min($vv['percentual_meta'], 100) }}%;"></div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: {{ $vv['percentual_meta'] >= 100 ? '#16a34a' : ($vv['percentual_meta'] >= 50 ? '#f59e0b' : '#ef4444') }};">{{ $vv['percentual_meta'] }}%</span>
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

{{-- ===== Metas por Equipe ===== --}}
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-users-cog"></i>
        <h3>Metas por Equipe</h3>
    </div>
    <div class="section-body">
        @if(count($metasPorEquipe) > 0)
        <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Equipe</th>
                    <th>Gestor</th>
                    <th class="text-center">Vendedores</th>
                    <th class="text-center">Vendas</th>
                    <th class="text-right">Valor Vendido</th>
                    <th class="text-right">Valor Recebido</th>
                    <th class="text-right">Meta</th>
                    <th style="min-width: 120px;">% Meta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($metasPorEquipe as $eq)
                <tr>
                    <td class="fw-bold">{{ $eq['equipe_nome'] }}</td>
                    <td style="color: #6e6b8b;">{{ $eq['gestor_nome'] }}</td>
                    <td class="text-center">{{ $eq['total_vendedores'] }}</td>
                    <td class="text-center">{{ $eq['total_vendas'] }}</td>
                    <td class="text-right">R$ {{ number_format($eq['valor_vendido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($eq['valor_recebido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($eq['meta'], 2, ',', '.') }}</td>
                    <td>
                        <div class="pbar">
                            <div class="pbar-fill {{ $eq['percentual_meta'] >= 100 ? 'green' : ($eq['percentual_meta'] >= 50 ? 'yellow' : 'red') }}" style="width: {{ min($eq['percentual_meta'], 100) }}%;"></div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: {{ $eq['percentual_meta'] >= 100 ? '#16a34a' : ($eq['percentual_meta'] >= 50 ? '#f59e0b' : '#ef4444') }};">{{ $eq['percentual_meta'] }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-users"></i></div>
            <h3>Nenhuma equipe cadastrada</h3>
            <p>Crie equipes na aba Equipes para visualizar os dados.</p>
        </div>
        @endif
    </div>
</div>

{{-- ===== Recebimentos no Período ===== --}}
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-money-bill-wave"></i>
        <h3>Recebimentos no Período</h3>
    </div>
    <div class="section-body">
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-list-check"></i> Total de Cobranças</span>
            <span class="svalue fw-bold">{{ $pagamentosPeriodo['total_pagamentos'] }}</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-circle-check" style="color: #16a34a;"></i> Total Pago</span>
            <span class="svalue" style="color: #16a34a;">R$ {{ number_format($pagamentosPeriodo['total_pago'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-clock" style="color: #f59e0b;"></i> Total Pendente</span>
            <span class="svalue" style="color: #f59e0b;">R$ {{ number_format($pagamentosPeriodo['total_pendente'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-circle-xmark" style="color: #ef4444;"></i> Total Vencido</span>
            <span class="svalue" style="color: #ef4444;">R$ {{ number_format($pagamentosPeriodo['total_vencido'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-row highlight">
            <span class="slabel"><i class="fas fa-coins" style="color: #4C1D95;"></i> <strong>Valor Total Recebido</strong></span>
            <span class="svalue" style="font-size: 1.2rem; color: #4C1D95;">R$ {{ number_format($pagamentosPeriodo['valor_recebido'], 2, ',', '.') }}</span>
        </div>
    </div>
</div>

{{-- ===== Renovações e Churn ===== --}}
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-arrows-rotate"></i>
        <h3>Renovações e Churn</h3>
    </div>
    <div class="section-body">
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-circle-check" style="color: #16a34a;"></i> Clientes Renovados / Pagos</span>
            <span class="svalue" style="color: #16a34a;">{{ $churnRenovacoes['renovados'] }}</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-arrow-trend-down" style="color: #ef4444;"></i> Churn (Pós-pagamento)</span>
            <span class="svalue" style="color: #ef4444;">{{ $churnRenovacoes['churn'] }}</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-ban" style="color: #6e6b8b;"></i> Desistência (Pré-pagamento)</span>
            <span class="svalue" style="color: #6e6b8b;">{{ $churnRenovacoes['desistencias'] }}</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-percent" style="color: #4C1D95;"></i> Taxa de Churn</span>
            <span class="svalue" style="color: {{ $churnRenovacoes['churn_percentual'] > 20 ? '#ef4444' : ($churnRenovacoes['churn_percentual'] > 10 ? '#f59e0b' : '#16a34a') }};">{{ $churnRenovacoes['churn_percentual'] }}%</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-circle" style="color: #16a34a;"></i> Recorrência Ativa</span>
            <span class="svalue fw-bold">{{ $churnRenovacoes['ativos'] }}</span>
        </div>
        <div class="summary-row">
            <span class="slabel"><i class="fas fa-circle" style="color: #ef4444;"></i> Recorrência Inativa</span>
            <span class="svalue fw-bold">{{ $churnRenovacoes['inativos'] }}</span>
        </div>
    </div>
</div>

{{-- ===== Formas de Pagamento ===== --}}
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-credit-card"></i>
        <h3>Formas de Pagamento</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Forma</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-right">Valor Total</th>
                    <th style="min-width: 140px;">% de Uso</th>
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
                    <td class="text-center fw-bold">{{ $fp['quantidade'] }}</td>
                    <td class="text-right">R$ {{ number_format($fp['valor_total'], 2, ',', '.') }}</td>
                    <td>
                        <div class="pbar">
                            <div class="pbar-fill purple" style="width: {{ $fp['percentual'] }}%;"></div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 600;">{{ $fp['percentual'] }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

@endif

@section('scripts')
<script>
(function() {
    var btn = document.getElementById('exportBtn');
    var menu = document.getElementById('exportMenu');
    if (!btn || !menu) return;

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.classList.toggle('show');
    });

    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && !btn.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
})();
</script>
@endsection
