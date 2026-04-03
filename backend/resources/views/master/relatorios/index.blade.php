@extends('layouts.app')
@section('title', 'Relatórios Gerenciais')

@section('content')
<style>
    .report-hero {
        margin-bottom: 28px;
        padding: 28px 32px;
        background: linear-gradient(135deg, var(--primary-dark) 0%, #4C1D95 100%);
        border-radius: var(--radius-xl);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 25px -5px rgba(59, 7, 100, 0.2);
    }
    .report-hero h2 { color: white; margin-bottom: 6px; font-size: 1.5rem; letter-spacing: -0.5px; }
    .report-hero p { opacity: 0.8; font-size: 0.9rem; }
    .export-dropdown { position: relative; display: inline-block; }
    .export-dropdown .btn { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 0.85rem; backdrop-filter: blur(10px); transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .export-dropdown .btn:hover { background: rgba(255,255,255,0.25); }
    .export-menu { display: none; position: absolute; right: 0; top: calc(100% + 6px); background: white; min-width: 180px; border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 12px 32px rgba(0,0,0,0.15); z-index: 100; overflow: hidden; }
    .export-menu.show { display: block; }
    .export-item { display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: var(--text-primary); text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: 0.15s; border: none; background: none; width: 100%; cursor: pointer; }
    .export-item:hover { background: #faf5ff; color: var(--primary); }
    .export-item i { width: 18px; text-align: center; }

    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .kpi-card { padding: 22px; background: white; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); transition: all 0.3s ease; position: relative; overflow: hidden; }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .kpi-card .kpi-icon { width: 44px; height: 44px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 14px; }
    .kpi-card .kpi-icon.purple { background: rgba(var(--primary-rgb), 0.1); color: var(--primary); }
    .kpi-card .kpi-icon.blue { background: var(--info-light); color: var(--info); }
    .kpi-card .kpi-icon.green { background: var(--success-light); color: var(--success); }
    .kpi-card .kpi-icon.yellow { background: var(--warning-light); color: var(--warning); }
    .kpi-card .kpi-icon.red { background: var(--danger-light); color: var(--danger); }
    .kpi-card .kpi-icon.gray { background: var(--bg); color: var(--text-muted); }
    .kpi-card .label { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
    .kpi-card .value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
    .kpi-card.highlight { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); border: none; }
    .kpi-card.highlight .label { color: rgba(255,255,255,0.7); }
    .kpi-card.highlight .value { color: white; }
    .kpi-card.highlight .kpi-icon { background: rgba(255,255,255,0.15); color: white; }
    .kpi-card.highlight:hover { box-shadow: 0 12px 28px rgba(76, 29, 149, 0.3); }

    .section-card { background: white; border: 1px solid var(--border-light); border-radius: var(--radius-lg); margin-bottom: 24px; overflow: hidden; box-shadow: var(--shadow-sm); }
    .section-header { padding: 18px 24px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px; background: var(--bg); }
    .section-header i { color: var(--primary); font-size: 1.1rem; }
    .section-header h3 { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
    .section-body { padding: 0; }
    .table-responsive { overflow-x: auto; }

    .report-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .report-table th { background: var(--bg); padding: 12px 16px; text-align: left; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.72rem; border-bottom: 1px solid var(--border-light); white-space: nowrap; }
    .report-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: var(--text); vertical-align: middle; }
    .report-table tr:last-child td { border-bottom: none; }
    .report-table tbody tr:hover { background: var(--surface-hover); }
    .report-table .text-right { text-align: right; }
    .report-table .text-center { text-align: center; }
    .report-table .fw-bold { font-weight: 700; color: var(--text-primary); }

    .progress-bar-bg { background: var(--bg); height: 8px; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }

    .badge-forma { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }

    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 24px; border-bottom: 1px solid #f1f5f9; }
    .summary-row:last-child { border-bottom: none; }
    .summary-row.highlight { background: linear-gradient(135deg, #faf5ff, #f3e8ff); }
    .summary-row .label { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: var(--text); }
    .summary-row .label i { color: var(--primary); width: 18px; text-align: center; }
    .summary-row .value { font-weight: 700; font-size: 0.95rem; }

    @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .report-hero { flex-direction: column; gap: 16px; text-align: center; }
        .filters-bar { flex-direction: column; }
        .filters-bar > div { width: 100%; }
    }
</style>

<!-- ===== Hero Header ===== -->
<div class="report-hero animate-up">
    <div>
        <h2><i class="fas fa-chart-bar" style="margin-right: 10px;"></i>Relatórios Gerenciais</h2>
        <p>Análise consolidada da operação comercial e financeira</p>
    </div>
    <div class="export-dropdown">
        <button type="button" id="exportBtn" class="btn">
            <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="font-size: 0.65rem;"></i>
        </button>
        <div id="exportMenu" class="export-menu">
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="export-item">
                <i class="fas fa-file-excel" style="color: #16a34a;"></i> Excel
            </a>
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="export-item">
                <i class="fas fa-file-pdf" style="color: #dc2626;"></i> PDF
            </a>
            <a href="{{ route('master.relatorios.exportar', request()->query()) }}" class="export-item">
                <i class="fas fa-file-csv" style="color: #2563eb;"></i> CSV
            </a>
        </div>
    </div>
</div>

{{-- ===== Filtros ===== --}}
<form method="GET" action="{{ route('master.relatorios') }}">
<div class="filters-bar">
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Data Início</label>
        <input type="date" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] }}">
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Data Fim</label>
        <input type="date" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] }}">
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Vendedor</label>
        <select name="vendedor_id" class="form-control">
            <option value="">Todos</option>
            @foreach($vendedores as $v)
                <option value="{{ $v->id }}" {{ $filtros['vendedor_id'] == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
            @endforeach
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
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
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Forma Pagamento</label>
        <select name="forma_pagamento" class="form-control">
            <option value="">Todas</option>
            <option value="pix" {{ $filtros['forma_pagamento'] == 'pix' ? 'selected' : '' }}>PIX</option>
            <option value="boleto" {{ $filtros['forma_pagamento'] == 'boleto' ? 'selected' : '' }}>Boleto</option>
            <option value="cartao" {{ $filtros['forma_pagamento'] == 'cartao' ? 'selected' : '' }}>Cartão</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Negociação</label>
        <select name="tipo_negociacao" class="form-control">
            <option value="">Todos</option>
            <option value="mensal" {{ $filtros['tipo_negociacao'] == 'mensal' ? 'selected' : '' }}>Mensal</option>
            <option value="anual" {{ $filtros['tipo_negociacao'] == 'anual' ? 'selected' : '' }}>Anual</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Cliente</label>
        <select name="cliente_id" class="form-control">
            <option value="">Todos</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ $filtros['cliente_id'] == $c->id ? 'selected' : '' }}>{{ $c->nome_igreja ?? $c->nome ?? 'Cliente #'.$c->id }}</option>
            @endforeach
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
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
    <div class="kpi-card highlight animate-up" style="animation-delay: 0.1s;">
        <div class="kpi-icon"><i class="fas fa-coins"></i></div>
        <span class="label">Total de Vendas</span>
        <div class="value">{{ $resumo['totalVendas'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.15s;">
        <div class="kpi-icon blue"><i class="fas fa-chart-line"></i></div>
        <span class="label">Valor Vendido</span>
        <div class="value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.2s;">
        <div class="kpi-icon green"><i class="fas fa-circle-check"></i></div>
        <span class="label">Valor Recebido</span>
        <div class="value" style="color: var(--success);">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.25s;">
        <div class="kpi-icon yellow"><i class="fas fa-hand-holding-dollar"></i></div>
        <span class="label">Comissões Geradas</span>
        <div class="value" style="color: var(--warning);">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.3s;">
        <div class="kpi-icon purple"><i class="fas fa-users"></i></div>
        <span class="label">Clientes Ativos</span>
        <div class="value">{{ $resumo['clientesAtivos'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.35s;">
        <div class="kpi-icon green"><i class="fas fa-arrows-rotate"></i></div>
        <span class="label">Renovações</span>
        <div class="value">{{ $resumo['renovacoes'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.4s;">
        <div class="kpi-icon red"><i class="fas fa-arrow-trend-down"></i></div>
        <span class="label">Churn</span>
        <div class="value" style="color: var(--danger);">{{ $resumo['churn'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.45s;">
        <div class="kpi-icon gray"><i class="fas fa-ban"></i></div>
        <span class="label">Desistências</span>
        <div class="value">{{ $resumo['desistencia'] }}</div>
    </div>
    <div class="kpi-card animate-up" style="animation-delay: 0.5s;">
        <div class="kpi-icon blue"><i class="fas fa-bullseye"></i></div>
        <span class="label">Ticket Médio</span>
        <div class="value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
    </div>
</div>

{{-- ===== Vendas por Vendedor ===== --}}
<div class="section-card animate-up" style="animation-delay: 0.55s;">
    <div class="section-header">
        <i class="fas fa-chart-bar"></i>
        <h3>Vendas por Vendedor</h3>
    </div>
    <div class="section-body">
        @if(count($vendasPorVendedor) > 0)
        <div class="table-responsive">
        <table class="report-table">
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
                    <td class="text-right" style="color: var(--warning); font-weight: 600;">R$ {{ number_format($vv['comissao'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vv['clientes_ativos'] }}</td>
                    <td class="text-center" style="color: {{ $vv['churn'] > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">{{ $vv['churn'] }}</td>
                    <td class="text-center" style="color: {{ $vv['desistencia'] > 0 ? 'var(--text-secondary)' : 'var(--text-muted)' }};">{{ $vv['desistencia'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['meta'], 2, ',', '.') }}</td>
                    <td>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: {{ min($vv['percentual_meta'], 100) }}%; background: {{ $vv['percentual_meta'] >= 100 ? 'var(--success)' : ($vv['percentual_meta'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};"></div>
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

{{-- ===== Metas por Equipe ===== --}}
<div class="section-card animate-up" style="animation-delay: 0.6s;">
    <div class="section-header">
        <i class="fas fa-users-cog"></i>
        <h3>Metas por Equipe</h3>
    </div>
    <div class="section-body">
        @if(count($metasPorEquipe) > 0)
        <div class="table-responsive">
        <table class="report-table">
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
                    <td style="color: var(--text-secondary);">{{ $eq['gestor_nome'] }}</td>
                    <td class="text-center">{{ $eq['total_vendedores'] }}</td>
                    <td class="text-center">{{ $eq['total_vendas'] }}</td>
                    <td class="text-right">R$ {{ number_format($eq['valor_vendido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($eq['valor_recebido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($eq['meta'], 2, ',', '.') }}</td>
                    <td>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: {{ min($eq['percentual_meta'], 100) }}%; background: {{ $eq['percentual_meta'] >= 100 ? 'var(--success)' : ($eq['percentual_meta'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: {{ $eq['percentual_meta'] >= 100 ? 'var(--success)' : ($eq['percentual_meta'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $eq['percentual_meta'] }}%</span>
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
<div class="section-card animate-up" style="animation-delay: 0.65s;">
    <div class="section-header">
        <i class="fas fa-money-bill-wave"></i>
        <h3>Recebimentos no Período</h3>
    </div>
    <div class="section-body">
        <div class="summary-row">
            <span class="label"><i class="fas fa-list-check"></i> Total de Cobranças</span>
            <span class="value fw-bold">{{ $pagamentosPeriodo['total_pagamentos'] }}</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-circle-check" style="color: var(--success);"></i> Total Pago</span>
            <span class="value" style="color: var(--success);">R$ {{ number_format($pagamentosPeriodo['total_pago'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-clock" style="color: var(--warning);"></i> Total Pendente</span>
            <span class="value" style="color: var(--warning);">R$ {{ number_format($pagamentosPeriodo['total_pendente'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-circle-xmark" style="color: var(--danger);"></i> Total Vencido</span>
            <span class="value" style="color: var(--danger);">R$ {{ number_format($pagamentosPeriodo['total_vencido'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-row highlight">
            <span class="label"><i class="fas fa-coins" style="color: var(--primary);"></i> <strong>Valor Total Recebido</strong></span>
            <span class="value" style="font-size: 1.2rem; color: var(--primary);">R$ {{ number_format($pagamentosPeriodo['valor_recebido'], 2, ',', '.') }}</span>
        </div>
    </div>
</div>

{{-- ===== Renovações e Churn ===== --}}
<div class="section-card animate-up" style="animation-delay: 0.7s;">
    <div class="section-header">
        <i class="fas fa-arrows-rotate"></i>
        <h3>Renovações e Churn</h3>
    </div>
    <div class="section-body">
        <div class="summary-row">
            <span class="label"><i class="fas fa-circle-check" style="color: var(--success);"></i> Clientes Renovados / Pagos</span>
            <span class="value" style="color: var(--success);">{{ $churnRenovacoes['renovados'] }}</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-arrow-trend-down" style="color: var(--danger);"></i> Churn (Pós-pagamento)</span>
            <span class="value" style="color: var(--danger);">{{ $churnRenovacoes['churn'] }}</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-ban" style="color: var(--text-secondary);"></i> Desistência (Pré-pagamento)</span>
            <span class="value" style="color: var(--text-secondary);">{{ $churnRenovacoes['desistencias'] }}</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-percent" style="color: var(--primary);"></i> Taxa de Churn</span>
            <span class="value" style="color: {{ $churnRenovacoes['churn_percentual'] > 20 ? 'var(--danger)' : ($churnRenovacoes['churn_percentual'] > 10 ? 'var(--warning)' : 'var(--success)') }};">{{ $churnRenovacoes['churn_percentual'] }}%</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-circle" style="color: var(--success);"></i> Recorrência Ativa</span>
            <span class="value fw-bold">{{ $churnRenovacoes['ativos'] }}</span>
        </div>
        <div class="summary-row">
            <span class="label"><i class="fas fa-circle" style="color: var(--danger);"></i> Recorrência Inativa</span>
            <span class="value fw-bold">{{ $churnRenovacoes['inativos'] }}</span>
        </div>
    </div>
</div>

{{-- ===== Formas de Pagamento ===== --}}
<div class="section-card animate-up" style="animation-delay: 0.75s;">
    <div class="section-header">
        <i class="fas fa-credit-card"></i>
        <h3>Formas de Pagamento</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
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
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: {{ $fp['percentual'] }}%; background: var(--primary);"></div>
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
