@extends('layouts.app')
@section('title', 'Relatórios')

@section('content')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }
    .animate-in:nth-child(5) { animation-delay: 0.15s; }
    .animate-in:nth-child(6) { animation-delay: 0.18s; }
    .animate-in:nth-child(7) { animation-delay: 0.21s; }
    .animate-in:nth-child(8) { animation-delay: 0.24s; }

    .skeleton-block {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 400px 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 8px;
    }

    /* ===== Header ===== */
    .report-hero {
        background: linear-gradient(135deg, #3b0764 0%, #581c87 40%, #7c3aed 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        box-shadow: 0 20px 40px rgba(88, 28, 135, 0.25);
        overflow: visible;
        position: relative;
        z-index: 1;
    }
    .report-hero h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.5px; color: white; }
    .report-hero p { opacity: 0.85; font-size: 0.9rem; color: rgba(255,255,255,0.9); }
    .export-dropdown { position: relative; display: inline-block; z-index: 9999; }
    .export-dropdown-content { display: none; position: fixed; background: white; min-width: 180px; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 12px 32px rgba(0,0,0,0.25); z-index: 999999; margin-top: 8px; }
    .export-dropdown-content.show { display: block; }
    .export-item { display: block; padding: 12px 16px; color: #374151; text-decoration: none; font-size: 0.85rem; transition: 0.15s; font-weight: 500; }
    .export-item:hover { background: #faf5ff; color: #7c3aed; }
    .export-item:first-child { border-radius: 10px 10px 0 0; }
    .export-item:last-child { border-radius: 0 0 10px 10px; }
    .export-item i { margin-right: 8px; width: 16px; }
    .btn-export-hero { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 0.85rem; backdrop-filter: blur(10px); transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-export-hero:hover { background: rgba(255,255,255,0.25); }

    /* ===== Filtros ===== */
    .filters-bar {
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        border: 1px solid #e9d5ff;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-end;
    }
    .filter-group { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 130px; }
    .filter-group label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b21a8; }
    .filter-group input, .filter-group select { padding: 7px 12px; border: 1.5px solid #d8b4fe; border-radius: 8px; font-size: 0.82rem; outline: none; background: white; transition: all 0.2s; }
    .filter-group input:focus, .filter-group select:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }
    .btn-filter { background: linear-gradient(135deg, #7c3aed, #581c87); color: white; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.82rem; transition: 0.2s; white-space: nowrap; box-shadow: 0 2px 8px rgba(124,58,237,0.3); }
    .btn-filter:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(124,58,237,0.4); }
    .btn-clear { background: white; border: 1.5px solid #d8b4fe; padding: 7px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.82rem; color: #6b21a8; text-decoration: none; white-space: nowrap; transition: 0.2s; }
    .btn-clear:hover { background: #faf5ff; border-color: #7c3aed; }

    /* ===== Cards ===== */
    .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .stat-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 22px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #7c3aed, #a855f7);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .stat-card:hover { box-shadow: 0 8px 24px rgba(124,58,237,0.12); transform: translateY(-3px); border-color: #d8b4fe; }
    .stat-card:hover::before { opacity: 1; }
    .stat-card .icon { font-size: 1.4rem; margin-bottom: 10px; color: #7c3aed; }
    .stat-card .value { font-size: 1.5rem; font-weight: 800; color: #1e1b4b; margin-bottom: 4px; }
    .stat-card .label { font-size: 0.72rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .stat-card.highlight {
        background: linear-gradient(135deg, #581c87, #7c3aed);
        border: none;
        color: white;
    }
    .stat-card.highlight::before { background: rgba(255,255,255,0.3); }
    .stat-card.highlight .icon { color: rgba(255,255,255,0.9); }
    .stat-card.highlight .value { color: white; }
    .stat-card.highlight .label { color: rgba(255,255,255,0.8); }
    .stat-card.green { border-left: 4px solid #16a34a; }
    .stat-card.green .icon { color: #16a34a; }
    .stat-card.red { border-left: 4px solid #dc2626; }
    .stat-card.red .icon { color: #dc2626; }
    .stat-card.yellow { border-left: 4px solid #ca8a04; }
    .stat-card.yellow .icon { color: #ca8a04; }
    .stat-card.blue { border-left: 4px solid #2563eb; }
    .stat-card.blue .icon { color: #2563eb; }

    /* ===== Seções ===== */
    .section-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        margin-bottom: 24px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: box-shadow 0.3s;
    }
    .section-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .section-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #faf5ff 0%, #f8fafc 100%);
    }
    .section-header h3 { font-size: 1rem; font-weight: 700; color: #1e1b4b; display: flex; align-items: center; gap: 8px; }
    .section-header h3 i { color: #7c3aed; }
    .section-body { padding: 0; }

    /* ===== Tabelas ===== */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; min-width: 600px; }
    .report-table th { background: #faf5ff; padding: 10px 14px; text-align: left; font-weight: 700; color: #6b21a8; text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.7rem; border-bottom: 2px solid #e9d5ff; white-space: nowrap; }
    .report-table td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #374151; }
    .report-table tr:last-child td { border-bottom: none; }
    .report-table tbody tr { transition: background 0.15s; }
    .report-table tbody tr:hover td { background: #faf5ff; }
    .report-table .text-right { text-align: right; }
    .report-table .text-center { text-align: center; }
    .report-table .font-bold { font-weight: 700; color: #1e1b4b; }
    .report-table .vendedor-cell { display: flex; align-items: center; gap: 10px; }
    .vendedor-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #a855f7); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.7rem; flex-shrink: 0; }

    /* ===== Barra de progresso ===== */
    .progress-bar { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; position: relative; }
    .progress-bar .fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
    .progress-bar .fill.green { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .progress-bar .fill.yellow { background: linear-gradient(90deg, #eab308, #ca8a04); }
    .progress-bar .fill.red { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .progress-bar .fill.purple { background: linear-gradient(90deg, #7c3aed, #a855f7); }

    /* ===== Badge ===== */
    .badge-forma { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }

    /* ===== Empty states ===== */
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state .icon { font-size: 2.5rem; margin-bottom: 12px; }
    .empty-state h3 { color: var(--text-main); font-size: 1.1rem; margin-bottom: 6px; }
    .empty-state-box { background: white; border: 1px solid var(--border); border-radius: 14px; }

    @media (max-width: 768px) {
        .filters-bar { flex-direction: column; }
        .filter-group { min-width: 100%; }
        .cards-grid { grid-template-columns: repeat(2, 1fr); }
        .report-table { font-size: 0.8rem; }
        .report-table th, .report-table td { padding: 8px 10px; }
        .report-hero { flex-direction: column; gap: 16px; text-align: center; }
    }
</style>

<!-- ===== Hero Header ===== -->
<div class="report-hero animate-in">
    <div>
        <h2><i class="fas fa-chart-line" style="margin-right: 10px;"></i>Relatórios Gerenciais</h2>
        <p>Análise consolidada da operação comercial e financeira</p>
    </div>
    <div class="export-dropdown">
        <button type="button" onclick="toggleExportMenu(this)" class="btn-export-hero">
            <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="font-size: 0.65rem;"></i>
        </button>
        <div id="exportMenu" class="export-dropdown-content">
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="export-item"><i class="fas fa-file-excel" style="color: #16a34a;"></i> Excel</a>
            <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="export-item"><i class="fas fa-file-pdf" style="color: #dc2626;"></i> PDF</a>
            <a href="{{ route('master.relatorios.exportar', request()->query()) }}" class="export-item"><i class="fas fa-file-csv" style="color: #2563eb;"></i> CSV</a>
        </div>
    </div>
</div>

<!-- ===== Filtros ===== -->
<form method="GET" action="{{ route('master.relatorios') }}">
<div class="filters-bar animate-in">
    <div class="filter-group">
        <label><i class="fas fa-calendar-start" style="margin-right: 3px;"></i>Período Início</label>
        <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] }}">
    </div>
    <div class="filter-group">
        <label><i class="fas fa-calendar-end" style="margin-right: 3px;"></i>Período Fim</label>
        <input type="date" name="data_fim" value="{{ $filtros['data_fim'] }}">
    </div>
    <div class="filter-group">
        <label><i class="fas fa-user-tie" style="margin-right: 3px;"></i>Vendedor</label>
        <select name="vendedor_id">
            <option value="">Todos</option>
            @foreach($vendedores as $v)
                <option value="{{ $v->id }}" {{ $filtros['vendedor_id'] == $v->id ? 'selected' : '' }}>{{ ($v->user->name ?? 'N/A') }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label><i class="fas fa-circle-check" style="margin-right: 3px;"></i>Status</label>
        <select name="status">
            <option value="">Todos</option>
            <option value="Aguardando pagamento" {{ $filtros['status'] == 'Aguardando pagamento' ? 'selected' : '' }}>Aguardando</option>
            <option value="Pago" {{ $filtros['status'] == 'Pago' ? 'selected' : '' }}>Pago</option>
            <option value="Cancelado" {{ $filtros['status'] == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
            <option value="Expirado" {{ $filtros['status'] == 'Expirado' ? 'selected' : '' }}>Expirado</option>
            <option value="Vencido" {{ $filtros['status'] == 'Vencido' ? 'selected' : '' }}>Vencido</option>
        </select>
    </div>
    <div class="filter-group">
        <label><i class="fas fa-credit-card" style="margin-right: 3px;"></i>Pagamento</label>
        <select name="forma_pagamento">
            <option value="">Todas</option>
            <option value="pix" {{ $filtros['forma_pagamento'] == 'pix' ? 'selected' : '' }}>PIX</option>
            <option value="boleto" {{ $filtros['forma_pagamento'] == 'boleto' ? 'selected' : '' }}>Boleto</option>
            <option value="cartao" {{ $filtros['forma_pagamento'] == 'cartao' ? 'selected' : '' }}>Cartão</option>
        </select>
    </div>
    <div class="filter-group">
        <label><i class="fas fa-repeat" style="margin-right: 3px;"></i>Negociação</label>
        <select name="tipo_negociacao">
            <option value="">Todos</option>
            <option value="mensal" {{ $filtros['tipo_negociacao'] == 'mensal' ? 'selected' : '' }}>Mensal</option>
            <option value="anual" {{ $filtros['tipo_negociacao'] == 'anual' ? 'selected' : '' }}>Anual</option>
        </select>
    </div>
    <div class="filter-group">
        <label><i class="fas fa-building" style="margin-right: 3px;"></i>Cliente</label>
        <select name="cliente_id">
            <option value="">Todos</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ $filtros['cliente_id'] == $c->id ? 'selected' : '' }}>{{ ($c->nome_igreja ?? $c->nome ?? 'Cliente #'.$c->id) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label><i class="fas fa-arrows-rotate" style="margin-right: 3px;"></i>Recorrência</label>
        <select name="recorrencia">
            <option value="">Todas</option>
            <option value="ativa" {{ $filtros['recorrencia'] == 'ativa' ? 'selected' : '' }}>Ativa</option>
            <option value="inativa" {{ $filtros['recorrencia'] == 'inativa' ? 'selected' : '' }}>Inativa</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px; align-items: flex-end;">
        <button type="submit" class="btn-filter"><i class="fas fa-filter" style="margin-right: 4px;"></i>Filtrar</button>
        <a href="{{ route('master.relatorios') }}" class="btn-clear"><i class="fas fa-rotate-left" style="margin-right: 4px;"></i>Limpar</a>
    </div>
</div>
</form>

{{-- ===== Estado vazio global ===== --}}
@if(!$temDadosNoSistema)
<div class="empty-state empty-state-box" style="padding: 80px 20px;">
    <div class="icon"><i class="fas fa-chart-pie" style="color: #d8b4fe;"></i></div>
    <h3 style="color: #1e1b4b;">Nenhum dado disponível</h3>
    <p>Os relatórios serão exibidos assim que houver movimentações no sistema.</p>
</div>

{{-- ===== Dados existem, mas filtros não retornaram nada ===== --}}
@elseif(!$filtrosRetornaramDados)
<div class="empty-state empty-state-box" style="padding: 60px 20px;">
    <div class="icon"><i class="fas fa-search" style="color: #f59e0b;"></i></div>
    <h3 style="color: #1e1b4b;">Nenhum resultado encontrado</h3>
    <p>Tente alterar os filtros para visualizar os dados.</p>
</div>

@else

<!-- ===== SEÇÃO 1: Resumo Geral ===== -->
<div class="cards-grid">
    <div class="stat-card highlight animate-in">
        <div class="icon"><i class="fas fa-coins"></i></div>
        <div class="value">{{ $resumo['totalVendas'] }}</div>
        <div class="label">Total de Vendas</div>
    </div>
    <div class="stat-card blue animate-in">
        <div class="icon"><i class="fas fa-chart-line"></i></div>
        <div class="value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
        <div class="label">Valor Vendido</div>
    </div>
    <div class="stat-card green animate-in">
        <div class="icon"><i class="fas fa-circle-check"></i></div>
        <div class="value">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
        <div class="label">Valor Recebido</div>
    </div>
    <div class="stat-card yellow animate-in">
        <div class="icon"><i class="fas fa-hand-holding-dollar"></i></div>
        <div class="value">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
        <div class="label">Comissão Gerada</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon"><i class="fas fa-users"></i></div>
        <div class="value">{{ $resumo['clientesAtivos'] }}</div>
        <div class="label">Clientes Ativos</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon"><i class="fas fa-arrows-rotate"></i></div>
        <div class="value">{{ $resumo['renovacoes'] }}</div>
        <div class="label">Renovações</div>
    </div>
    <div class="stat-card red animate-in">
        <div class="icon"><i class="fas fa-chart-line-down"></i></div>
        <div class="value">{{ $resumo['churn'] }}</div>
        <div class="label">Churn</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon"><i class="fas fa-ban"></i></div>
        <div class="value">{{ $resumo['desistencia'] }}</div>
        <div class="label">Desistência</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon"><i class="fas fa-bullseye"></i></div>
        <div class="value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
        <div class="label">Ticket Médio</div>
    </div>
</div>

<!-- ===== SEÇÃO 2: Vendas por Vendedor ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-user-chart"></i>Vendas por Vendedor</h3>
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
                    <td class="text-right" style="color: #ca8a04; font-weight: 600;">R$ {{ number_format($vv['comissao'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vv['clientes_ativos'] }}</td>
                    <td class="text-center" style="color: {{ $vv['churn'] > 0 ? '#dc2626' : '#94a3b8' }};">{{ $vv['churn'] }}</td>
                    <td class="text-center" style="color: {{ $vv['desistencia'] > 0 ? '#64748b' : '#94a3b8' }};">{{ $vv['desistencia'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['meta'], 2, ',', '.') }}</td>
                    <td style="min-width: 120px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="progress-bar" style="flex: 1;">
                                <div class="fill {{ $vv['percentual_meta'] >= 100 ? 'green' : ($vv['percentual_meta'] >= 50 ? 'yellow' : 'red') }}" style="width: {{ min($vv['percentual_meta'], 100) }}%;"></div>
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 700; color: {{ $vv['percentual_meta'] >= 100 ? '#16a34a' : ($vv['percentual_meta'] >= 50 ? '#ca8a04' : '#dc2626') }};">{{ $vv['percentual_meta'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div class="empty-state">
            <p>Nenhum relatório encontrado para os filtros aplicados.</p>
        </div>
        @endif
    </div>
</div>

<!-- ===== SEÇÃO 2B: Metas por Equipe ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-users-gear"></i>Metas por Equipe</h3>
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
                    <td class="font-bold"><i class="fas fa-people-group" style="margin-right: 6px; color: #7c3aed;"></i>{{ $eq['equipe_nome'] }}</td>
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
                            <span style="font-size: 0.78rem; font-weight: 700; color: {{ $eq['percentual_meta'] >= 100 ? '#16a34a' : ($eq['percentual_meta'] >= 50 ? '#ca8a04' : '#dc2626') }};">{{ $eq['percentual_meta'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div class="empty-state">
            <p>Nenhuma equipe cadastrada. Crie equipes na aba Equipes para visualizar os dados.</p>
        </div>
        @endif
    </div>
</div>

<!-- ===== SEÇÃO 3: Recebimentos por Período ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-receipt"></i>Recebimentos no Período</h3>
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
                    <td><i class="fas fa-list-check" style="margin-right: 8px; color: #7c3aed;"></i>Total de Cobranças</td>
                    <td class="text-right font-bold">{{ $pagamentosPeriodo['total_pagamentos'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle-check" style="margin-right: 8px; color: #16a34a;"></i>Total Pago</td>
                    <td class="text-right font-bold" style="color: #16a34a;">R$ {{ number_format($pagamentosPeriodo['total_pago'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-clock" style="margin-right: 8px; color: #ca8a04;"></i>Total Pendente</td>
                    <td class="text-right font-bold" style="color: #ca8a04;">R$ {{ number_format($pagamentosPeriodo['total_pendente'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle-xmark" style="margin-right: 8px; color: #dc2626;"></i>Total Vencido</td>
                    <td class="text-right font-bold" style="color: #dc2626;">R$ {{ number_format($pagamentosPeriodo['total_vencido'], 2, ',', '.') }}</td>
                </tr>
                <tr style="background: linear-gradient(135deg, #faf5ff, #f3e8ff);">
                    <td class="font-bold"><i class="fas fa-coins" style="margin-right: 8px; color: #7c3aed;"></i>Valor Total Recebido</td>
                    <td class="text-right font-bold" style="font-size: 1.15rem; color: #581c87;">R$ {{ number_format($pagamentosPeriodo['valor_recebido'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- ===== SEÇÃO 4: Renovações e Churn ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-arrows-rotate"></i>Renovações e Churn</h3>
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
                    <td><i class="fas fa-circle-check" style="margin-right: 8px; color: #16a34a;"></i>Clientes Renovados / Pagos</td>
                    <td class="text-right font-bold" style="color: #16a34a;">{{ $churnRenovacoes['renovados'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-chart-line-down" style="margin-right: 8px; color: #dc2626;"></i>Churn (Pós-pagamento)</td>
                    <td class="text-right font-bold" style="color: #dc2626;">{{ $churnRenovacoes['churn'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-ban" style="margin-right: 8px; color: #64748b;"></i>Desistência (Pré-pagamento)</td>
                    <td class="text-right font-bold" style="color: #64748b;">{{ $churnRenovacoes['desistencias'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-percent" style="margin-right: 8px; color: #7c3aed;"></i>Taxa de Churn (%)</td>
                    <td class="text-right font-bold" style="color: {{ $churnRenovacoes['churn_percentual'] > 20 ? '#dc2626' : ($churnRenovacoes['churn_percentual'] > 10 ? '#ca8a04' : '#16a34a') }};">
                        {{ $churnRenovacoes['churn_percentual'] }}%
                    </td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle" style="margin-right: 8px; color: #16a34a;"></i>Recorrência Ativa</td>
                    <td class="text-right font-bold">{{ $churnRenovacoes['ativos'] }}</td>
                </tr>
                <tr>
                    <td><i class="fas fa-circle" style="margin-right: 8px; color: #dc2626;"></i>Recorrência Inativa</td>
                    <td class="text-right font-bold">{{ $churnRenovacoes['inativos'] }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- ===== SEÇÃO 5: Formas de Pagamento ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3><i class="fas fa-credit-card"></i>Formas de Pagamento</h3>
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

@endsection

@section('scripts')
<script>
function toggleExportMenu(btn) {
    var menu = document.getElementById('exportMenu');
    if (menu.classList.contains('show')) {
        menu.classList.remove('show');
    } else {
        var rect = btn.getBoundingClientRect();
        menu.style.top = (rect.bottom + 4) + 'px';
        menu.style.right = (window.innerWidth - rect.right) + 'px';
        menu.classList.add('show');
    }
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('exportMenu');
    var dropdown = menu ? menu.closest('.export-dropdown') : null;
    if (dropdown && !dropdown.contains(e.target) && menu) {
        menu.classList.remove('show');
    }
});
</script>
@endsection
