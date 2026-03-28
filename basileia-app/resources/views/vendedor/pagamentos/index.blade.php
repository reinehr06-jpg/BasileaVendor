@extends('layouts.app')
@section('title', 'Pagamentos dos Clientes')

@section('content')
<style>
    .forma-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; background: rgba(76,29,149,0.08); color: var(--primary); }
    .status-pendente { background: #fef9c3; color: #854d0e; }
    .status-pago { background: #dcfce7; color: #166534; }
    .status-vencido { background: #fee2e2; color: #991b1b; }
    .status-cancelado { background: #f1f5f9; color: #64748b; }
    .status-confirmada { background: #dbeafe; color: #1d4ed8; }
    .action-btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-btn-boleto { background: var(--primary); color: white; border-color: var(--primary); }
    .action-btn-boleto:hover { background: var(--primary-dark); }
    .action-btn-link { background: var(--success); color: white; border-color: var(--success); }
    .action-btn-link:hover { background: var(--success-dark); }
    
    .tabs-container { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
    .tab-btn { padding: 10px 20px; background: transparent; border: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; color: var(--text-muted); transition: all 0.2s; }
    .tab-btn:hover { color: var(--primary); }
    .tab-btn.active { background: var(--primary); color: white; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    .repasse-badge { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; }
    .split-icon { color: var(--success); margin-right: 4px; }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Pagamentos</h2>
        <p>Acompanhe as cobranças, pagamentos e repasses da Basileia.</p>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-container">
    <button class="tab-btn active" onclick="switchTab('tab-cobrancas', this)">
        <i class="fas fa-credit-card" style="margin-right: 6px;"></i> Cobranças Clients
    </button>
    <button class="tab-btn" onclick="switchTab('tab-repasses', this)">
        <i class="fas fa-university" style="margin-right: 6px;"></i> Repasses Basileia
    </button>
</div>

<!-- Tab: Cobranças dos Clientes -->
<div id="tab-cobrancas" class="tab-content active">

@php
    $todosPagamentos = collect();
    foreach ($pagamentos as $p) {
        $statusNormalized = strtolower($p->status) === 'received' ? 'pago' : strtolower($p->status);
        $todosPagamentos->push((object)[
            'igreja' => $p->cliente->nome_igreja ?? $p->cliente->nome ?? '—',
            'pastor' => $p->cliente->nome_pastor ?? '',
            'valor' => $p->valor,
            'forma' => $p->forma_pagamento_real ?? $p->forma_pagamento,
            'status' => $statusNormalized,
            'vencimento' => $p->data_vencimento,
            'pagamento_data' => $p->data_pagamento,
            'link' => $p->bank_slip_url ?? $p->link_pagamento ?? null,
            'created_at' => $p->created_at,
        ]);
    }
    foreach ($vendasComCobrancas as $v) {
        foreach ($v->cobrancas as $c) {
            $statusNormalized = strtolower($c->status) === 'received' ? 'pago' : (strtolower($c->status) === 'pending' ? 'pendente' : strtolower($c->status));
            $todosPagamentos->push((object)[
                'igreja' => $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—',
                'pastor' => $v->cliente->nome_pastor ?? '',
                'valor' => $v->valor,
                'forma' => $v->forma_pagamento ?? 'pix',
                'status' => $statusNormalized,
                'vencimento' => null,
                'pagamento_data' => strtolower($c->status) === 'received' ? $c->updated_at : null,
                'link' => $c->link,
                'created_at' => $c->created_at,
            ]);
        }
    }
    $todosPagamentos = $todosPagamentos->sortByDesc('created_at')->unique(fn($p) => ($p->igreja ?? '') . ($p->valor ?? 0) . ($p->status ?? ''));
@endphp

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-list-check"></i></div>
        <div class="stat-value">{{ $todosPagamentos->count() }}</div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ $todosPagamentos->where('status', 'pago')->count() }}</div>
        <div class="stat-label">Pagos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">{{ $todosPagamentos->where('status', 'pendente')->count() }}</div>
        <div class="stat-label">Pendentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value">R$ {{ number_format($todosPagamentos->where('status', 'pago')->sum('valor'), 2, ',', '.') }}</div>
        <div class="stat-label">Recebido</div>
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <div style="flex-grow: 1; position: relative;">
        <i class="fas fa-magnifying-glass" style="position: absolute; left: 14px; top: 11px; color: var(--text-muted);"></i>
        <input type="text" class="search-input" id="searchPag" style="padding-left: 40px;" placeholder="Buscar por igreja ou pastor..." oninput="filterPag()">
    </div>
    <select class="filter-select" id="statusFilter" onchange="filterPag()">
        <option value="">Status: Todos</option>
        <option value="pendente">Pendente</option>
        <option value="pago">Pago</option>
        <option value="vencido">Vencido</option>
        <option value="cancelado">Cancelado</option>
    </select>
    <select class="filter-select" id="formaFilter" onchange="filterPag()">
        <option value="">Forma: Todas</option>
        <option value="pix">PIX</option>
        <option value="boleto">Boleto</option>
        <option value="cartao">Cartão</option>
    </select>
</div>

<div class="table-container">
    @if($todosPagamentos->count() > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-building"></i> Igreja / Pastor</th>
                <th><i class="fas fa-dollar-sign"></i> Valor</th>
                <th><i class="fas fa-credit-card"></i> Forma</th>
                <th><i class="fas fa-circle-check"></i> Status</th>
                <th><i class="fas fa-calendar"></i> Vencimento</th>
                <th><i class="fas fa-calendar-check"></i> Pagamento</th>
                <th><i class="fas fa-bolt"></i> Ações</th>
            </tr>
        </thead>
        <tbody id="pagTableBody">
            @foreach($todosPagamentos as $pag)
            <tr class="pag-row"
                data-igreja="{{ strtolower($pag->igreja) }}"
                data-pastor="{{ strtolower($pag->pastor) }}"
                data-status="{{ $pag->status }}"
                data-forma="{{ strtolower($pag->forma) }}">
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $pag->igreja }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $pag->pastor }}</div>
                </td>
                <td style="font-weight: 700;">R$ {{ number_format($pag->valor, 2, ',', '.') }}</td>
                <td><span class="forma-badge">{{ strtoupper($pag->forma) }}</span></td>
                <td><span class="badge status-{{ $pag->status }}">{{ ucfirst($pag->status) }}</span></td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $pag->vencimento ? \Carbon\Carbon::parse($pag->vencimento)->format('d/m/Y') : '—' }}</td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $pag->pagamento_data ? \Carbon\Carbon::parse($pag->pagamento_data)->format('d/m/Y') : '—' }}</td>
                <td>
                    @if($pag->link)
                        @if(strtolower($pag->forma) === 'boleto')
                            <a href="{{ $pag->link }}" target="_blank" class="action-btn-sm action-btn-boleto">
                                <i class="fas fa-file-lines"></i> Boleto
                            </a>
                        @else
                            <button onclick="copyToClipboard('{{ $pag->link }}')" class="action-btn-sm action-btn-link">
                                <i class="fas fa-link"></i> Copiar Link
                            </button>
                        @endif
                    @else
                        <span style="font-size: 0.8rem; color: var(--text-muted);">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-dollar-sign"></i></div>
        <h3>Nenhum pagamento registrado</h3>
        <p>Os pagamentos aparecerão aqui conforme vendas forem realizadas.</p>
    </div>
    @endif
</div>

<!-- Tab: Repasses da Basileia -->
<div id="tab-repasses" class="tab-content">
    @php
    $repasses = \App\Models\Comissao::where('vendedor_id', $vendedor->id)
        ->orderByDesc('created_at')
        ->limit(50)
        ->get()
        ->map(function($c) {
            return (object)[
                'cliente' => $c->cliente->nome_igreja ?? $c->cliente->nome ?? 'N/A',
                'valor_venda' => $c->valor_venda,
                'valor_comissao' => $c->valor_comissao,
                'tipo' => $c->tipo_comissao,
                'status' => $c->status,
                'data' => $c->data_pagamento ?? $c->created_at,
            ];
        });
    @endphp

    <!-- Stats -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-sack-dollar"></i></div>
            <div class="stat-value">R$ {{ number_format($repasses->sum('valor_comissao'), 2, ',', '.') }}</div>
            <div class="stat-label">Total Recebido</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-value" style="color: var(--warning);">R$ {{ number_format($repasses->where('status', 'pendente')->sum('valor_comissao'), 2, ',', '.') }}</div>
            <div class="stat-label">Pendente</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value" style="color: var(--success);">R$ {{ number_format($repasses->whereIn('status', ['paga', 'confirmada'])->sum('valor_comissao'), 2, ',', '.') }}</div>
            <div class="stat-label">Confirmado</div>
        </div>
    </div>

    <div class="table-container">
        @if($repasses->count() > 0)
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-building"></i> Cliente</th>
                    <th><i class="fas fa-dollar-sign"></i> Valor Venda</th>
                    <th><i class="fas fa-hand-holding-dollar"></i> Repasse</th>
                    <th><i class="fas fa-tag"></i> Tipo</th>
                    <th><i class="fas fa-circle-check"></i> Status</th>
                    <th><i class="fas fa-calendar-check"></i> Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repasses as $r)
                <tr>
                    <td style="font-weight: 600; color: var(--text-primary);">{{ $r->cliente }}</td>
                    <td>R$ {{ number_format($r->valor_venda, 2, ',', '.') }}</td>
                    <td style="font-weight: 700; color: var(--success);">R$ {{ number_format($r->valor_comissao, 2, ',', '.') }}</td>
                    <td>
                        @if($r->tipo === 'recorrencia')
                        <span class="repasse-badge"><i class="fas fa-redo"></i> Recorrência</span>
                        @else
                        <span class="repasse-badge"><i class="fas fa-star"></i> Primeira Venda</span>
                        @endif
                    </td>
                    <td><span class="badge status-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);">{{ \Carbon\Carbon::parse($r->data)->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-university"></i></div>
            <h3>Nenhum repasse registrado</h3>
            <p>Os repasses da Basileia aparecerão aqui quando houverem vendas confirmadas.</p>
        </div>
        @endif
    </div>
</div>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}

function filterPag() {
    const search = document.getElementById('searchPag').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const forma = document.getElementById('formaFilter').value;
    document.querySelectorAll('.pag-row').forEach(row => {
        const matchSearch = !search || row.dataset.igreja.includes(search) || row.dataset.pastor.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        const matchForma = !forma || row.dataset.forma.includes(forma);
        row.style.display = (matchSearch && matchStatus && matchForma) ? '' : 'none';
    });
}
</script>
@endsection
