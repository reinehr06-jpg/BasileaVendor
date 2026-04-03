@extends('layouts.app')
@section('title', 'Relatórios Gerenciais')

@section('content')

<div class="page-header">
    <div>
        <h2><i class="fas fa-chart-bar" style="margin-right: 8px;"></i>Relatórios Gerenciais</h2>
        <p>Análise da operação comercial e financeira</p>
    </div>
    <div>
        <a href="{{ route('master.relatorios.exportar', array_merge(request()->query(), ['formato' => 'csv'])) }}" class="btn btn-outline btn-sm">
            <i class="fas fa-file-csv"></i> Exportar CSV
        </a>
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

{{-- ===== Resumo ===== --}}
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-coins"></i></div>
        <div class="stat-value">{{ $resumo['totalVendas'] }}</div>
        <div class="stat-label">Total Vendas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
        <div class="stat-label">Valor Vendido</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value" style="color: var(--success);">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
        <div class="stat-label">Valor Recebido</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-hand-holding-dollar"></i></div>
        <div class="stat-value" style="color: var(--warning);">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
        <div class="stat-label">Comissões</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ $resumo['clientesAtivos'] }}</div>
        <div class="stat-label">Clientes Ativos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-arrows-rotate"></i></div>
        <div class="stat-value">{{ $resumo['renovacoes'] }}</div>
        <div class="stat-label">Renovações</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-arrow-trend-down"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $resumo['churn'] }}</div>
        <div class="stat-label">Churn</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--bg); color: var(--text-muted);"><i class="fas fa-ban"></i></div>
        <div class="stat-value">{{ $resumo['desistencia'] }}</div>
        <div class="stat-label">Desistências</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-bullseye"></i></div>
        <div class="stat-value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
        <div class="stat-label">Ticket Médio</div>
    </div>
</div>

{{-- ===== Vendas por Vendedor ===== --}}
<div class="table-container">
    <div class="card-header"><i class="fas fa-chart-bar"></i> Vendas por Vendedor</div>
    @if(count($vendasPorVendedor) > 0)
    <div class="table-responsive">
    <table>
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
                <td style="font-weight: 600; color: var(--text-primary);">{{ $vv['vendedor_nome'] }}</td>
                <td class="text-center">{{ $vv['total_vendas'] }}</td>
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

{{-- ===== Metas por Equipe ===== --}}
<div class="table-container">
    <div class="card-header"><i class="fas fa-users-cog"></i> Metas por Equipe</div>
    @if(count($metasPorEquipe) > 0)
    <div class="table-responsive">
    <table>
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
                <td style="font-weight: 600; color: var(--text-primary);">{{ $eq['equipe_nome'] }}</td>
                <td style="color: var(--text-secondary);">{{ $eq['gestor_nome'] }}</td>
                <td class="text-center">{{ $eq['total_vendedores'] }}</td>
                <td class="text-center">{{ $eq['total_vendas'] }}</td>
                <td class="text-right">R$ {{ number_format($eq['valor_vendido'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($eq['valor_recebido'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($eq['meta'], 2, ',', '.') }}</td>
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
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-users"></i></div>
        <h3>Nenhuma equipe cadastrada</h3>
        <p>Crie equipes na aba Equipes para visualizar os dados.</p>
    </div>
    @endif
</div>

{{-- ===== Recebimentos no Período ===== --}}
<div class="table-container">
    <div class="card-header"><i class="fas fa-money-bill-wave"></i> Recebimentos no Período</div>
    <div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th class="text-right">Quantidade / Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total de Cobranças</td>
                <td class="text-right" style="font-weight: 700;">{{ $pagamentosPeriodo['total_pagamentos'] }}</td>
            </tr>
            <tr>
                <td style="color: var(--success);">Total Pago</td>
                <td class="text-right" style="font-weight: 700; color: var(--success);">R$ {{ number_format($pagamentosPeriodo['total_pago'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="color: var(--warning);">Total Pendente</td>
                <td class="text-right" style="font-weight: 700; color: var(--warning);">R$ {{ number_format($pagamentosPeriodo['total_pendente'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="color: var(--danger);">Total Vencido</td>
                <td class="text-right" style="font-weight: 700; color: var(--danger);">R$ {{ number_format($pagamentosPeriodo['total_vencido'], 2, ',', '.') }}</td>
            </tr>
            <tr style="background: var(--bg);">
                <td style="font-weight: 700; color: var(--primary);">Valor Total Recebido</td>
                <td class="text-right" style="font-weight: 700; font-size: 1.1rem; color: var(--primary);">R$ {{ number_format($pagamentosPeriodo['valor_recebido'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

{{-- ===== Renovações e Churn ===== --}}
<div class="table-container">
    <div class="card-header"><i class="fas fa-arrows-rotate"></i> Renovações e Churn</div>
    <div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Clientes Renovados / Pagos</td>
                <td class="text-right" style="font-weight: 700; color: var(--success);">{{ $churnRenovacoes['renovados'] }}</td>
            </tr>
            <tr>
                <td>Churn (Pós-pagamento)</td>
                <td class="text-right" style="font-weight: 700; color: var(--danger);">{{ $churnRenovacoes['churn'] }}</td>
            </tr>
            <tr>
                <td>Desistência (Pré-pagamento)</td>
                <td class="text-right" style="font-weight: 700; color: var(--text-secondary);">{{ $churnRenovacoes['desistencias'] }}</td>
            </tr>
            <tr>
                <td>Taxa de Churn (%)</td>
                <td class="text-right" style="font-weight: 700; color: {{ $churnRenovacoes['churn_percentual'] > 20 ? 'var(--danger)' : ($churnRenovacoes['churn_percentual'] > 10 ? 'var(--warning)' : 'var(--success)') }};">{{ $churnRenovacoes['churn_percentual'] }}%</td>
            </tr>
            <tr>
                <td>Recorrência Ativa</td>
                <td class="text-right" style="font-weight: 700;">{{ $churnRenovacoes['ativos'] }}</td>
            </tr>
            <tr>
                <td>Recorrência Inativa</td>
                <td class="text-right" style="font-weight: 700;">{{ $churnRenovacoes['inativos'] }}</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

{{-- ===== Formas de Pagamento ===== --}}
<div class="table-container">
    <div class="card-header"><i class="fas fa-credit-card"></i> Formas de Pagamento</div>
    <div class="table-responsive">
    <table>
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
                    <span class="badge badge-{{ $fp['forma'] == 'pix' ? 'info' : ($fp['forma'] == 'boleto' ? 'warning' : 'primary') }}">
                        {{ ucfirst($fp['forma']) }}
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
</div>

@endif

@endsection
