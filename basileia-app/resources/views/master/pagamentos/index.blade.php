@extends('layouts.app')
@section('title', 'Controle de Pagamentos')

@section('content')
<style>
    .forma-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; background: rgba(76,29,149,0.08); color: var(--primary); }
    .status-pendente { background: #fef9c3; color: #854d0e; }
    .status-pago, .status-received { background: #dcfce7; color: #166534; }
    .status-vencido { background: #fee2e2; color: #991b1b; }
    .status-cancelado { background: #f1f5f9; color: #64748b; }
    .status-estornado { background: #fce7f3; color: #9d174d; }
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
    .action-btn-copy { background: white; color: var(--primary); border-color: var(--border); }
    .action-btn-copy:hover { border-color: var(--primary); }
    
    .tabs-container { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
    .tab-btn { padding: 10px 20px; background: transparent; border: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; color: var(--text-muted); transition: all 0.2s; }
    .tab-btn:hover { color: var(--primary); }
    .tab-btn.active { background: var(--primary); color: white; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    .repasse-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .repasse-vendedor { background: #dbeafe; color: #1d4ed8; }
    .repasse-gestor { background: #f3e8ff; color: #7e22ce; }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Controle de Pagamentos</h2>
        <p>Visão global de todas as cobranças, recebimentos e repasses.</p>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-container">
    <button class="tab-btn active" onclick="switchTab('tab-cobrancas', this)">
        <i class="fas fa-credit-card" style="margin-right: 6px;"></i> Cobranças Recebidas
    </button>
    <button class="tab-btn" onclick="switchTab('tab-repasses', this)">
        <i class="fas fa-share" style="margin-right: 6px;"></i> Repasses a Vendedores
    </button>
</div>

<!-- Tab: Cobranças Recebidas -->
<div id="tab-cobrancas" class="tab-content active">

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
        <input type="text" class="search-input" id="searchPag" style="padding-left: 40px;" placeholder="Buscar por igreja, vendedor..." oninput="filterPag()">
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
                <th><i class="fas fa-building"></i> Igreja</th>
                <th><i class="fas fa-user"></i> Vendedor</th>
                <th><i class="fas fa-dollar-sign"></i> Valor</th>
                <th><i class="fas fa-credit-card"></i> Forma</th>
                <th><i class="fas fa-circle-check"></i> Status</th>
                <th><i class="fas fa-calendar-check"></i> Pagamento</th>
                <th><i class="fas fa-bolt"></i> Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($todosPagamentos as $pag)
            <tr class="pag-row"
                data-igreja="{{ strtolower($pag->igreja) }}"
                data-vendedor="{{ strtolower($pag->vendedor ?? '') }}"
                data-status="{{ $pag->status }}"
                data-forma="{{ strtolower($pag->forma) }}">
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $pag->igreja }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $pag->pastor ?? '' }}</div>
                </td>
                <td style="font-size: 0.85rem;">{{ $pag->vendedor ?? '—' }}</td>
                <td style="font-weight: 700;">R$ {{ number_format($pag->valor, 2, ',', '.') }}</td>
                <td><span class="forma-badge">{{ strtoupper($pag->forma) }}</span></td>
                <td><span class="badge status-{{ $pag->status }}">{{ ucfirst($pag->status) }}</span></td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $pag->pagamento_data ? \Carbon\Carbon::parse($pag->pagamento_data)->format('d/m/Y') : '—' }}</td>
                <td>
                    @php
                        $checkoutUrl = $pag->checkout_hash ? url('/checkout/' . $pag->checkout_hash) : null;
                        $formaClean = strtolower($pag->forma);
                        $methodParam = match($formaClean) {
                            'pix' => 'pix',
                            'boleto' => 'boleto',
                            default => 'cartao'
                        };
                        $internalLink = $checkoutUrl ? $checkoutUrl . '?method=' . $methodParam : null;
                    @endphp

                    @if($pag->status === 'pendente' || $pag->status === 'vencido')
                        @if($internalLink)
                            <div class="d-flex flex-column gap-1">
                                <a href="{{ $internalLink }}" target="_blank" class="action-btn-sm action-btn-boleto">
                                    <i class="fas fa-barcode"></i> Checkout
                                </a>
                                <button onclick="navigator.clipboard.writeText('{{ $checkoutUrl }}').then(() => alert('Link copiado!'))" class="action-btn-sm action-btn-copy">
                                    <i class="fas fa-copy"></i> Copiar
                                </button>
                            </div>
                        @elseif($pag->link)
                            <a href="{{ $pag->link }}" target="_blank" class="action-btn-sm action-btn-link">
                                <i class="fas fa-external-link-alt"></i> External
                            </a>
                        @endif
                    @elseif($pag->link)
                         <a href="{{ $pag->link }}" target="_blank" class="action-btn-sm action-btn-link">
                            <i class="fas fa-file-pdf"></i> Comprovante
                        </a>
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
        <p>Os pagamentos aparecerão aqui conforme forem processados.</p>
    </div>
    @endif
</div>

<!-- Tab: Repasses a Vendedores -->
<div id="tab-repasses" class="tab-content">
    <!-- Stats -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ isset($repasses) ? $repasses->count() : 0 }}</div>
            <div class="stat-label">Total Repasses</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
            <div class="stat-value" style="color: var(--success);">R$ {{ number_format(isset($repasses) ? $repasses->where('status', 'pago')->sum('valor') : 0, 2, ',', '.') }}</div>
            <div class="stat-label">Total Pago</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-value" style="color: var(--warning);">R$ {{ number_format(isset($repasses) ? $repasses->where('status', 'pendente')->sum('valor') : 0, 2, ',', '.') }}</div>
            <div class="stat-label">Pendente</div>
        </div>
    </div>

    @php
    $repasses = \App\Models\Comissao::whereNotNull('vendedor_id')
        ->where('competencia', now()->format('Y-m'))
        ->with(['vendedor.user'])
        ->orderByDesc('created_at')
        ->get()
        ->map(function($c) {
            return (object)[
                'nome' => $c->vendedor->user->name ?? 'N/A',
                'tipo' => $c->vendedor->user->perfil === 'gestor' ? 'gestor' : 'vendedor',
                'valor' => $c->valor_comissao,
                'status' => $c->status,
                'data' => $c->data_pagamento ?? $c->created_at,
            ];
        });
    @endphp

    <div class="table-container">
        @if($repasses->count() > 0)
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> Destinatário</th>
                    <th><i class="fas fa-tag"></i> Tipo</th>
                    <th><i class="fas fa-dollar-sign"></i> Valor</th>
                    <th><i class="fas fa-circle-check"></i> Status</th>
                    <th><i class="fas fa-calendar-check"></i> Data Pagamento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repasses as $r)
                <tr>
                    <td style="font-weight: 600; color: var(--text-primary);">{{ $r->nome }}</td>
                    <td>
                        @if($r->tipo === 'gestor')
                        <span class="repasse-badge repasse-gestor"><i class="fas fa-user-tie"></i> Gestor</span>
                        @else
                        <span class="repasse-badge repasse-vendedor"><i class="fas fa-user"></i> Vendedor</span>
                        @endif
                    </td>
                    <td style="font-weight: 700;">R$ {{ number_format($r->valor, 2, ',', '.') }}</td>
                    <td><span class="badge status-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $r->data ? \Carbon\Carbon::parse($r->data)->format('d/m/Y') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-share"></i></div>
            <h3>Nenhum repasse registrado</h3>
            <p>Os repasses aparecerão aqui quando houverem comissões a pagar.</p>
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
    const search = document.getElementById('searchPag') ? document.getElementById('searchPag').value.toLowerCase() : '';
    const status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';
    const forma = document.getElementById('formaFilter') ? document.getElementById('formaFilter').value : '';
    document.querySelectorAll('.pag-row').forEach(row => {
        const matchSearch = !search || row.dataset.igreja.includes(search) || row.dataset.vendedor.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        const matchForma = !forma || row.dataset.forma.includes(forma);
        row.style.display = (matchSearch && matchStatus && matchForma) ? '' : 'none';
    });
}
</script>
@endsection
