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
</style>

<x-page-hero 
    title="Controle de Pagamentos" 
    subtitle="Visão global de todas as cobranças e recebimentos." 
    icon="fas fa-dollar-sign"
    :exports="[
        ['type' => 'excel', 'url' => '?formato=excel', 'icon' => 'fas fa-file-excel', 'label' => 'Excel'],
        ['type' => 'pdf', 'url' => '?formato=pdf', 'icon' => 'fas fa-file-pdf', 'label' => 'PDF'],
        ['type' => 'csv', 'url' => '?formato=csv', 'icon' => 'fas fa-file-csv', 'label' => 'CSV'],
    ]"
/>

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
                                <a href="{{ $internalLink }}" target="_blank" class="action-btn-sm action-btn-boleto"
                                   style="{{ $methodParam === 'pix' ? 'background: #008080; border-color: #008080;' : '' }}">
                                    @if($methodParam === 'pix')
                                        <i class="fas fa-qrcode"></i> Pix
                                    @elseif($methodParam === 'boleto')
                                        <i class="fas fa-barcode"></i> Boleto
                                    @else
                                        <i class="fas fa-credit-card"></i> Cartão
                                    @endif
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

<script>
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
