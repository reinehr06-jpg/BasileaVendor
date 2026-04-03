@extends('layouts.app')
@section('title', 'Relatórios Gerenciais')

@section('content')
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }
    .animate-in:nth-child(5) { animation-delay: 0.15s; }
    .animate-in:nth-child(6) { animation-delay: 0.18s; }
    .animate-in:nth-child(7) { animation-delay: 0.21s; }
    .animate-in:nth-child(8) { animation-delay: 0.24s; }
    .animate-in:nth-child(9) { animation-delay: 0.27s; }

    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .summary-grid .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px 24px; box-shadow: var(--shadow-xs); transition: var(--transition); }
    .summary-grid .stat-card:hover { box-shadow: var(--shadow-sm); transform: translateY(-2px); }
    .summary-grid .stat-card .stat-icon { width: 42px; height: 42px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 12px; }
    .summary-grid .stat-card .stat-icon.primary { background: rgba(var(--primary-rgb), 0.1); color: var(--primary); }
    .summary-grid .stat-card .stat-icon.success { background: var(--success-light); color: var(--success); }
    .summary-grid .stat-card .stat-icon.warning { background: var(--warning-light); color: var(--warning); }
    .summary-grid .stat-card .stat-icon.danger { background: var(--danger-light); color: var(--danger); }
    .summary-grid .stat-card .stat-icon.info { background: var(--info-light); color: var(--info); }
    .summary-grid .stat-card .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-bottom: 4px; }
    .summary-grid .stat-card .stat-label { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }

    .stat-card.highlight { background: var(--primary) !important; border-color: var(--primary) !important; }
    .stat-card.highlight .stat-icon { background: rgba(255,255,255,0.2) !important; color: white !important; }
    .stat-card.highlight .stat-value { color: white !important; }
    .stat-card.highlight .stat-label { color: rgba(255,255,255,0.8) !important; }

    .section-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 24px; overflow: hidden; box-shadow: var(--shadow-xs); }
    .section-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg); }
    .section-header h3 { font-size: 1rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
    .section-header h3 i { color: var(--primary); }
    .section-body { padding: 0; }

    .report-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .report-table th { background: var(--bg); padding: 12px 16px; text-align: left; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.75rem; border-bottom: 1px solid var(--border); white-space: nowrap; }
    .report-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: var(--text); vertical-align: middle; }
    .report-table tr:last-child td { border-bottom: none; }
    .report-table tbody tr { transition: background 0.15s; }
    .report-table tbody tr:hover td { background: var(--surface-hover); }
    .report-table .text-right { text-align: right; }
    .report-table .text-center { text-align: center; }
    .report-table .font-bold { font-weight: 700; color: var(--text-primary); }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    .progress-bar-bg { background: var(--bg); height: 8px; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 4px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }

    .progress-bar { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; position: relative; }
    .progress-bar .fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
    .progress-bar .fill.green { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .progress-bar .fill.yellow { background: linear-gradient(90deg, #eab308, #ca8a04); }
    .progress-bar .fill.red { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .progress-bar .fill.purple { background: linear-gradient(90deg, #7c3aed, #a855f7); }

    .badge-forma { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }
    .badge-forma.recorrente { background: #d1fae5; color: #065f46; }

    .vendedor-cell { display: flex; align-items: center; gap: 10px; }
    .vendedor-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.7rem; flex-shrink: 0; }

    .export-dropdown { position: relative; display: inline-block; }
    .export-dropdown-content { display: none; position: absolute; right: 0; background: var(--surface); min-width: 180px; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100; margin-top: 4px; }
    .export-dropdown-content.show { display: block; }
    .export-item { display: block; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 0.875rem; transition: 0.15s; }
    .export-item:hover { background: var(--bg); color: var(--primary); }
    .export-item:first-child { border-radius: 8px 8px 0 0; }
    .export-item:last-child { border-radius: 0 0 8px 8px; }
    .export-item i { margin-right: 8px; width: 16px; }

    @media (max-width: 768px) {
        .summary-grid { grid-template-columns: repeat(2, 1fr); }
        .filters-bar { flex-direction: column; }
        .filters-bar > div, .filters-bar > form { width: 100%; }
    }
</style>

<!-- ===== Page Header ===== -->
<div class="page-header animate-in">
    <div>
        <h2><i class="fas fa-chart-bar" style="margin-right: 8px;"></i>Relatórios Gerenciais</h2>
        <p>Análise consolidada da operação comercial e financeira</p>
    </div>
    <div class="export-dropdown">
        <button type="button" id="exportBtn" class="btn btn-outline">
            <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="margin-left: 6px; font-size: 0.7rem;"></i>
        </button>
        <div id="exportMenu" class="export-dropdown-content">
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="export-item"><i class="fas fa-file-excel" style="color: var(--success);"></i> Excel</a>
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="export-item"><i class="fas fa-file-pdf" style="color: var(--danger);"></i> PDF</a>
            <a href="{{ route('master.relatorios.exportar', request()->query()) }}" class="export-item"><i class="fas fa-file-csv" style="color: var(--info);"></i> CSV</a>
        </div>
    </div>
</div>

<!-- ===== Filtros ===== -->
<form method="GET" action="{{ route('master.relatorios') }}">
<div class="filters-bar animate-in">
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-calendar" style="margin-right: 4px;"></i> Data Início</label>
        <input type="date" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] }}">
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-calendar" style="margin-right: 4px;"></i> Data Fim</label>
        <input type="date" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] }}">
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-user-tie" style="margin-right: 4px;"></i> Vendedor</label>
        <select name="vendedor_id" class="form-control">
            <option value="">Todos</option>
            @foreach($vendedores as $v)
                <option value="{{ $v->id }}" {{ $filtros['vendedor_id'] == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
            @endforeach
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-circle-check" style="margin-right: 4px;"></i> Status</label>
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
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-credit-card" style="margin-right: 4px;"></i> Pagamento</label>
        <select name="forma_pagamento" class="form-control">
            <option value="">Todas</option>
            <option value="pix" {{ $filtros['forma_pagamento'] == 'pix' ? 'selected' : '' }}>PIX</option>
            <option value="boleto" {{ $filtros['forma_pagamento'] == 'boleto' ? 'selected' : '' }}>Boleto</option>
            <option value="cartao" {{ $filtros['forma_pagamento'] == 'cartao' ? 'selected' : '' }}>Cartão</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-arrows-rotate" style="margin-right: 4px;"></i> Negociação</label>
        <select name="tipo_negociacao" class="form-control">
            <option value="">Todos</option>
            <option value="mensal" {{ $filtros['tipo_negociacao'] == 'mensal' ? 'selected' : '' }}>Mensal</option>
            <option value="anual" {{ $filtros['tipo_negociacao'] == 'anual' ? 'selected' : '' }}>Anual</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-building" style="margin-right: 4px;"></i> Cliente</label>
        <select name="cliente_id" class="form-control">
            <option value="">Todos</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ $filtros['cliente_id'] == $c->id ? 'selected' : '' }}>{{ $c->nome_igreja ?? $c->nome ?? 'Cliente #'.$c->id }}</option>
            @endforeach
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-sync-alt" style="margin-right: 4px;"></i> Recorrência</label>
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

<!-- ===== Resumo Geral ===== -->
<div class="summary-grid">
    <div class="stat-card highlight animate-in">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-coins"></i></div>
        <div class="stat-value" style="color: white;">{{ $resumo['totalVendas'] }}</div>
        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total de Vendas</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon info"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
        <div class="stat-label">Valor Vendido</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value" style="color: var(--success);">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
        <div class="stat-label">Valor Recebido</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon warning"><i class="fas fa-hand-holding-dollar"></i></div>
        <div class="stat-value" style="color: var(--warning);">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
        <div class="stat-label">Comissões</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ $resumo['clientesAtivos'] }}</div>
        <div class="stat-label">Clientes Ativos</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon success"><i class="fas fa-arrows-rotate"></i></div>
        <div class="stat-value">{{ $resumo['renovacoes'] }}</div>
        <div class="stat-label">Renovações</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon danger"><i class="fas fa-arrow-trend-down"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $resumo['churn'] }}</div>
        <div class="stat-label">Churn</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon" style="background: var(--bg); color: var(--text-muted);"><i class="fas fa-ban"></i></div>
        <div class="stat-value">{{ $resumo['desistencia'] }}</div>
        <div class="stat-label">Desistências</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon primary"><i class="fas fa-bullseye"></i></div>
        <div class="stat-value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
        <div class="stat-label">Ticket Médio</div>
    </div>
</div>

<!-- ===== Vendas por Vendedor ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-chart-bar"></i> Vendas por Vendedor</h3>
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
                    <th>% Meta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendasPorVendedor as $vv)
                <tr>
                    <td>
                        <div class="vendedor-cell">
                            <div class="vendedor-avatar">{{ strtoupper(substr($vv['vendedor_nome'], 0, 2)) }}</div>
                            <span class="font-bold">{{ $vv['vendedor_nome'] }}</span>
                        </div>
                    </td>
                    <td class="text-center">{{ $vv['total_vendas'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_vendido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_recebido'], 2, ',', '.') }}</td>
                    <td class="text-right" style="color: var(--warning); font-weight: 600;">R$ {{ number_format($vv['comissao'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vv['clientes_ativos'] }}</td>
                    <td class="text-center" style="color: {{ $vv['churn'] > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">{{ $vv['churn'] }}</td>
                    <td class="text-center" style="color: {{ $vv['desistencia'] > 0 ? 'var(--text-secondary)' : 'var(--text-muted)' }};">{{ $vv['desistencia'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['meta'], 2, ',', '.') }}</td>
                    <td style="min-width: 120px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="progress-bar" style="flex: 1;">
                                <div class="fill {{ $vv['percentual_meta'] >= 100 ? 'green' : ($vv['percentual_meta'] >= 50 ? 'yellow' : 'red') }}" style="width: {{ min($vv['percentual_meta'], 100) }}%;"></div>
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 700; color: {{ $vv['percentual_meta'] >= 100 ? 'var(--success)' : ($vv['percentual_meta'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $vv['percentual_meta'] }}%</span>
                        </div>
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

<!-- ===== Metas por Equipe ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-users-cog"></i> Metas por Equipe</h3>
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
                    <th>% Meta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($metasPorEquipe as $eq)
                <tr>
                    <td class="font-bold"><i class="fas fa-people-group" style="margin-right: 6px; color: var(--primary);"></i>{{ $eq['equipe_nome'] }}</td>
                    <td style="color: var(--text-secondary);">{{ $eq['gestor_nome'] }}</td>
                    <td class="text-center">{{ $eq['total_vendedores'] }}</td>
                    <td class="text-center">{{ $eq['total_vendas'] }}</td>
                    <td class="text-right">R$ {{ number_format($eq['valor_vendido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($eq['valor_recebido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($eq['meta'], 2, ',', '.') }}</td>
                    <td style="min-width: 120px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="progress-bar" style="flex: 1;">
                                <div class="fill {{ $eq['percentual_meta'] >= 100 ? 'green' : ($eq['percentual_meta'] >= 50 ? 'yellow' : 'red') }}" style="width: {{ min($eq['percentual_meta'], 100) }}%;"></div>
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 700; color: {{ $eq['percentual_meta'] >= 100 ? 'var(--success)' : ($eq['percentual_meta'] >= 50 ? 'var(--warning)' : 'var(--danger)') }};">{{ $eq['percentual_meta'] }}%</span>
                        </div>
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

<!-- ===== Recebimentos por Período ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-money-bill-wave"></i> Recebimentos no Período</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="text-right">Quantidade / Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><i class="fas fa-list-check" style="margin-right: 8px; color: var(--primary);"></i>Total de Cobranças</td>
                    <td class="text-right font-bold">{{ $pagamentosPeriodo['total_pagamentos'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle-check" style="margin-right: 8px; color: var(--success);"></i>Total Pago</td>
                    <td class="text-right font-bold" style="color: var(--success);">R$ {{ number_format($pagamentosPeriodo['total_pago'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-clock" style="margin-right: 8px; color: var(--warning);"></i>Total Pendente</td>
                    <td class="text-right font-bold" style="color: var(--warning);">R$ {{ number_format($pagamentosPeriodo['total_pendente'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle-xmark" style="margin-right: 8px; color: var(--danger);"></i>Total Vencido</td>
                    <td class="text-right font-bold" style="color: var(--danger);">R$ {{ number_format($pagamentosPeriodo['total_vencido'], 2, ',', '.') }}</td>
                </tr>
                <tr style="background: var(--bg);">
                    <td class="font-bold"><i class="fas fa-coins" style="margin-right: 8px; color: var(--primary);"></i>Valor Total Recebido</td>
                    <td class="text-right font-bold" style="font-size: 1.15rem; color: var(--primary);">R$ {{ number_format($pagamentosPeriodo['valor_recebido'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- ===== Renovações e Churn ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-arrows-rotate"></i> Renovações e Churn</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><i class="fas fa-circle-check" style="margin-right: 8px; color: var(--success);"></i>Clientes Renovados / Pagos</td>
                    <td class="text-right font-bold" style="color: var(--success);">{{ $churnRenovacoes['renovados'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-arrow-trend-down" style="margin-right: 8px; color: var(--danger);"></i>Churn (Pós-pagamento)</td>
                    <td class="text-right font-bold" style="color: var(--danger);">{{ $churnRenovacoes['churn'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-ban" style="margin-right: 8px; color: var(--text-secondary);"></i>Desistência (Pré-pagamento)</td>
                    <td class="text-right font-bold" style="color: var(--text-secondary);">{{ $churnRenovacoes['desistencias'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-percentage" style="margin-right: 8px; color: var(--primary);"></i>Taxa de Churn (%)</td>
                    <td class="text-right font-bold" style="color: {{ $churnRenovacoes['churn_percentual'] > 20 ? 'var(--danger)' : ($churnRenovacoes['churn_percentual'] > 10 ? 'var(--warning)' : 'var(--success)') }};">
                        {{ $churnRenovacoes['churn_percentual'] }}%
                    </td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle" style="margin-right: 8px; color: var(--success);"></i>Recorrência Ativa</td>
                    <td class="text-right font-bold">{{ $churnRenovacoes['ativos'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle" style="margin-right: 8px; color: var(--danger);"></i>Recorrência Inativa</td>
                    <td class="text-right font-bold">{{ $churnRenovacoes['inativos'] }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- ===== Formas de Pagamento ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-credit-card"></i> Formas de Pagamento</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Forma</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-right">Valor Total</th>
                    <th>% de Uso</th>
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
                    <td class="text-center font-bold">{{ $fp['quantidade'] }}</td>
                    <td class="text-right">R$ {{ number_format($fp['valor_total'], 2, ',', '.') }}</td>
                    <td style="min-width: 140px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="progress-bar" style="flex: 1;">
                                <div class="fill purple" style="width: {{ $fp['percentual'] }}%;"></div>
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 600;">{{ $fp['percentual'] }}%</span>
                        </div>
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
