@extends('layouts.app')
@section('title', 'Histórico do Vendedor')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-success { background: #dcfce7; color: #15803d; }
    .badge-warning { background: #fef9c3; color: #854d0e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1d4ed8; }
    .badge-forma { display: inline-flex; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }
    .badge-forma.recorrente { background: #d1fae5; color: #065f46; }
    .section-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 24px; overflow: hidden; }
    .section-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .section-header h3 { font-size: 1rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px; }
    .section-body { padding: 0; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .report-table th { background: #f8fafc; padding: 12px 16px; text-align: left; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.75rem; border-bottom: 1px solid var(--border); white-space: nowrap; }
    .report-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: var(--text-main); }
    .report-table tr:last-child td { border-bottom: none; }
    .report-table tr:hover td { background: #f8fafc; }
    .report-table .text-right { text-align: right; }
    .report-table .text-center { text-align: center; }
    .report-table .font-bold { font-weight: 700; }
    .progress-bar { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; position: relative; }
    .progress-bar .fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
    .progress-bar .fill.green { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .progress-bar .fill.yellow { background: linear-gradient(90deg, #eab308, #ca8a04); }
    .progress-bar .fill.red { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .vendedor-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.5rem; flex-shrink: 0; }
    .nf-link { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; background: #dbeafe; color: #1d4ed8; font-size: 0.8rem; font-weight: 600; text-decoration: none; }
    .nf-link:hover { background: #bfdbfe; }
</style>

<x-page-hero title="Histórico de Comissões" subtitle="Carregando informações do vendedor..." icon="fas fa-history" :exports="[
    ['type' => 'excel', 'url' => route('master.comissoes.exportar-historico', ['vendedorId' => $vendedorId, 'mes' => $mes, 'formato' => 'excel']), 'icon' => 'fas fa-file-excel', 'label' => 'Excel'],
    ['type' => 'pdf', 'url' => route('master.comissoes.exportar-historico', ['vendedorId' => $vendedorId, 'mes' => $mes, 'formato' => 'pdf']), 'icon' => 'fas fa-file-pdf', 'label' => 'PDF'],
    ['type' => 'csv', 'url' => route('master.comissoes.exportar-historico', ['vendedorId' => $vendedorId, 'mes' => $mes, 'formato' => 'csv']), 'icon' => 'fas fa-file-csv', 'label' => 'CSV'],
]" />

<!-- Filtros -->
<form method="GET" action="{{ route('master.comissoes.historico', $vendedorId) }}">
<div class="filters-bar">
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-calendar"></i> Mês</label>
        <input type="month" name="mes" class="form-control" value="{{ $mes }}" onchange="this.form.submit()">
    </div>
</div>
</form>

<!-- Perfil do Vendedor -->
<div class="section-card" style="margin-top: 24px;">
    <div style="padding: 24px; display: flex; align-items: center; gap: 20px;">
        <div id="vendedorAvatar" class="vendedor-avatar">?</div>
        <div style="flex: 1;">
            <h2 id="vendedorNome" style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">...</h2>
            <div id="vendedorEmail" style="color: var(--text-muted); font-size: 0.9rem;">...</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;">Competência</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">{{ Carbon\Carbon::parse($mes.'-01')->translatedFormat('F / Y') }}</div>
        </div>
    </div>
</div>

<!-- Resumo -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-bullseye"></i></div>
        <div class="stat-value" id="metaValor">R$ 0,00</div>
        <div class="stat-label">Meta</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value" id="vendidoValor" style="color: var(--success);">R$ 0,00</div>
        <div class="stat-label">Vendido</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-percentage"></i></div>
        <div class="stat-value" id="metaPercentual">0%</div>
        <div class="stat-label">% Meta</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-sack-dollar"></i></div>
        <div class="stat-value" id="comissaoTotal">R$ 0,00</div>
        <div class="stat-label">Comissão Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2); color: white;"><i class="fas fa-receipt"></i></div>
        <div class="stat-value" id="ticketMedio" style="color: #0891b2;">R$ 0,00</div>
        <div class="stat-label">Ticket Médio</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white;"><i class="fas fa-users"></i></div>
        <div class="stat-value" id="clientesAtivosCard" style="color: #7c3aed;">0</div>
        <div class="stat-label">Clientes Ativos</div>
    </div>
</div>

<!-- SEÇÃO: Vendas -->
<div class="section-card">
    <div class="section-header">
        <h3>📊 Performance de Vendas</h3>
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
                    <td>Total de Vendas</td>
                    <td class="text-right font-bold" id="totalVendas">0</td>
                </tr>
                <tr>
                    <td>Valor Total Vendido</td>
                    <td class="text-right font-bold" id="valorVendido">R$ 0,00</td>
                </tr>
                <tr>
                    <td>Valor Recebido</td>
                    <td class="text-right font-bold" id="valorRecebido" style="color: var(--success);">R$ 0,00</td>
                </tr>
                <tr>
                    <td>Clientes Ativos</td>
                    <td class="text-right font-bold" id="clientesAtivos">0</td>
                </tr>
                <tr>
                    <td style="color: var(--danger);">Cancelamentos</td>
                    <td class="text-right font-bold" id="cancelamentos" style="color: var(--danger);">0</td>
                </tr>
                <tr>
                    <td style="color: var(--danger);">Valor Cancelado</td>
                    <td class="text-right font-bold" id="valorCancelado" style="color: var(--danger);">R$ 0,00</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- SEÇÃO: Por Forma de Pagamento -->
<div class="section-card">
    <div class="section-header">
        <h3>💳 Vendas por Forma de Pagamento</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Forma</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-right">Valor Total</th>
                </tr>
            </thead>
            <tbody id="formaPagamentoBody">
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- SEÇÃO: Por Tipo de Negociação -->
<div class="section-card">
    <div class="section-header">
        <h3>📅 Vendas por Tipo de Negociação</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-right">Valor Total</th>
                </tr>
            </thead>
            <tbody id="tipoNegociacaoBody">
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- SEÇÃO: Detalhamento de Comissões -->
<div class="section-card">
    <div class="section-header">
        <h3>💰 Detalhamento de Comissões</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th class="text-center">Venda</th>
                    <th class="text-right">Valor Venda</th>
                    <th class="text-right">% Com.</th>
                    <th class="text-right">Comissão</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Pagamento</th>
                </tr>
            </thead>
            <tbody id="comissoesBody">
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- SEÇÃO: Notas Fiscais (só ADM) -->
@if(Auth::user()->perfil === 'master')
<div class="section-card" id="notasFiscaisSection" style="display: none;">
    <div class="section-header">
        <h3>📄 Notas Fiscais</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="text-right">Valor</th>
                    <th>Data</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody id="notasFiscaisBody">
            </tbody>
        </table>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
let vendedorId = {{ $vendedorId }};
let mesAtual = '{{ $mes }}';

function formatMoney(value) {
    return 'R$ ' + parseFloat(value || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function carregarHistorico() {
    fetch(`/master/comissoes/${vendedorId}/historico?mes=${mesAtual}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.json())
        .then(data => {
            // Dados do vendedor
            document.getElementById('vendedorNome').textContent = data.vendedor.nome;
            document.getElementById('vendedorEmail').textContent = data.vendedor.email;
            document.getElementById('vendedorAvatar').textContent = data.vendedor.nome.charAt(0).toUpperCase();
            
            // Meta
            document.getElementById('metaValor').textContent = formatMoney(data.meta.valor);
            document.getElementById('vendidoValor').textContent = formatMoney(data.meta.valor_vendido);
            document.getElementById('metaPercentual').textContent = data.meta.percentual + '%';
            document.getElementById('metaPercentual').style.color = data.meta.percentual >= 100 ? '#16a34a' : (data.meta.percentual >= 50 ? '#ca8a04' : '#dc2626');
            
            // Vendas
            document.getElementById('totalVendas').textContent = data.vendas.total;
            document.getElementById('valorVendido').textContent = formatMoney(data.vendas.valor_total);
            document.getElementById('valorRecebido').textContent = formatMoney(data.vendas.valor_recebido);
            document.getElementById('clientesAtivos').textContent = data.vendas.clientes_ativos;
            document.getElementById('cancelamentos').textContent = data.vendas.cancelamentos;
            document.getElementById('valorCancelado').textContent = formatMoney(data.vendas.valor_cancelado);
            
            // Comissão
            document.getElementById('comissaoTotal').textContent = formatMoney(data.comissoes.total);
            
            // Ticket Médio e Clientes Ativos
            document.getElementById('ticketMedio').textContent = formatMoney(data.vendas.ticket_medio || 0);
            document.getElementById('clientesAtivosCard').textContent = data.vendas.clientes_ativos || 0;
            
            // Forma de pagamento
            const formaBody = document.getElementById('formaPagamentoBody');
            formaBody.innerHTML = '';
            if (data.vendas.por_forma_pagamento && data.vendas.por_forma_pagamento.length > 0) {
                data.vendas.por_forma_pagamento.forEach(fp => {
                    const badgeClass = fp.forma.toLowerCase();
                    formaBody.innerHTML += `
                        <tr>
                            <td><span class="badge-forma ${badgeClass}">${fp.forma === 'pix' ? '⚡ PIX' : (fp.forma === 'boleto' ? '📄 Boleto' : (fp.forma === 'cartao' ? '💳 Cartão' : '🔄 Recorrente'))}</span></td>
                            <td class="text-center font-bold">${fp.quantidade}</td>
                            <td class="text-right">${formatMoney(fp.valor)}</td>
                        </tr>
                    `;
                });
            } else {
                formaBody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: var(--text-muted);">Nenhuma venda encontrada</td></tr>';
            }
            
            // Tipo negociação
            const tipoBody = document.getElementById('tipoNegociacaoBody');
            tipoBody.innerHTML = '';
            if (data.vendas.por_tipo_negociacao && data.vendas.por_tipo_negociacao.length > 0) {
                data.vendas.por_tipo_negociacao.forEach(tn => {
                    const label = tn.tipo === 'anual' ? '📅 Anual' : (tn.tipo === 'mensal' ? '📆 Mensal' : 'Não definido');
                    tipoBody.innerHTML += `
                        <tr>
                            <td>${label}</td>
                            <td class="text-center font-bold">${tn.quantidade}</td>
                            <td class="text-right">${formatMoney(tn.valor)}</td>
                        </tr>
                    `;
                });
            } else {
                tipoBody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: var(--text-muted);">Nenhuma venda encontrada</td></tr>';
            }
            
            // Comissões detalhadas
            const comissoesBody = document.getElementById('comissoesBody');
            comissoesBody.innerHTML = '';
            if (data.comissoes.detalhes && data.comissoes.detalhes.length > 0) {
                data.comissoes.detalhes.forEach(c => {
                    const badgeClass = c.tipo === 'recorrencia' ? 'badge-success' : 'badge-info';
                    const statusClass = c.status === 'paga' || c.status === 'confirmada' ? 'badge-success' : (c.status === 'pendente' ? 'badge-warning' : 'badge-danger');
                    const legacyBadge = c.is_legacy ? '<span style="font-size:0.6rem; background:#fbbf24; color:#78350f; padding:1px 6px; border-radius:8px; font-weight:800; margin-left:4px;">LEGADO</span>' : '';
                    const tipoLabel = c.tipo === 'inicial_antecipada' ? 'Antecipada' : (c.tipo === 'recorrencia' ? 'Recorrência' : 'Inicial');
                    comissoesBody.innerHTML += `
                        <tr>
                            <td class="font-bold">${c.cliente}${legacyBadge}</td>
                            <td class="text-center">#${c.venda_id}</td>
                            <td class="text-right">${formatMoney(c.valor_venda)}</td>
                            <td class="text-center">${c.percentual}%</td>
                            <td class="text-right" style="color: var(--success); font-weight: 700;">${formatMoney(c.valor_comissao)}</td>
                            <td><span class="badge ${badgeClass}">${tipoLabel}</span></td>
                            <td><span class="badge ${statusClass}">${c.status}</span></td>
                            <td>${c.data_pagamento || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                comissoesBody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Nenhuma comissão encontrada</td></tr>';
            }
            
            // Notas fiscais (só ADM)
            @if(Auth::user()->perfil === 'master')
            if (data.is_admin && data.notas_fiscais && data.notas_fiscais.length > 0) {
                document.getElementById('notasFiscaisSection').style.display = 'block';
                const notasBody = document.getElementById('notasFiscaisBody');
                notasBody.innerHTML = '';
                data.notas_fiscais.forEach(nf => {
                    notasBody.innerHTML += `
                        <tr>
                            <td class="font-bold">${nf.descricao}</td>
                            <td class="text-right">${formatMoney(nf.valor)}</td>
                            <td>${nf.data}</td>
                            <td>
                                <a href="/master/comissoes/nota-fiscal/${nf.id}/download" class="nf-link">
                                    <i class="fas fa-download"></i> Baixar
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }
            @endif
        })
        .catch(error => {
            console.error('Erro ao carregar histórico:', error);
        });
}

function exportarHistorico(formato) {
    window.location.href = `/master/comissoes/${vendedorId}/exportar-historico?mes=${mesAtual}&formato=${formato}`;
}

document.addEventListener('DOMContentLoaded', carregarHistorico);
</script>
@endsection
