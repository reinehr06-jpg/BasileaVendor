@extends('layouts.app')

@section('title', 'Clientes Asaas')

@section('content')
<style>
    .asaas-kpi { background: white; border-radius: 12px; padding: 20px; border: 1px solid #ededf2; text-align: center; box-shadow: 0 2px 4px rgba(50,50,71,0.06); transition: all 0.2s; }
    .asaas-kpi:hover { box-shadow: 0 4px 12px rgba(50,50,71,0.1); transform: translateY(-2px); }
    .asaas-kpi .num { font-size: 2rem; font-weight: 900; line-height: 1; margin-bottom: 6px; }
    .asaas-kpi .lbl { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #a1a1b5; }
    .asaas-kpi.clickable { cursor: pointer; }
    .asaas-kpi.green { background: linear-gradient(135deg,#f0fdf4,#dcfce7); border-color: #86efac; }
    .asaas-kpi.green .num { color: #166534; }
    .asaas-kpi.green .lbl { color: #166534; }
    .asaas-kpi.orange { background: linear-gradient(135deg,#fff7ed,#ffedd5); border-color: #fed7aa; }
    .asaas-kpi.orange .num { color: #c2410c; }
    .asaas-kpi.orange .lbl { color: #c2410c; }
    .asaas-kpi.red { background: linear-gradient(135deg,#fef2f2,#fee2e2); border-color: #fca5a5; }
    .asaas-kpi.red .num { color: #991b1b; }
    .asaas-kpi.red .lbl { color: #991b1b; }
    .asaas-kpi.purple { background: linear-gradient(135deg,#f5f3ff,#ede9fe); border-color: #c4b5fd; }
    .asaas-kpi.purple .num { color: #7c3aed; }
    .asaas-kpi.purple .lbl { color: #7c3aed; }
    .asaas-kpi.money { background: linear-gradient(135deg,#ecfdf5,#d1fae5); border-color: #6ee7b7; }
    .asaas-kpi.money .num { color: #065f46; font-size: 1.3rem; }
    .asaas-kpi.money .lbl { color: #065f46; }

    .asaas-tabs { display: flex; border-bottom: 2px solid #ededf2; background: #f4f5fa; overflow-x: auto; }
    .asaas-tab { padding: 14px 20px; white-space: nowrap; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 6px; color: #a1a1b5; border-bottom: 3px solid transparent; transition: 0.2s; }
    .asaas-tab:hover { color: #4C1D95; background: white; }
    .asaas-tab.active { border-bottom-color: #4C1D95; color: #4C1D95; background: white; }
    .asaas-tab .count { padding: 1px 7px; border-radius: 20px; font-size: 0.68rem; }
    .asaas-tab.active .count { background: #4C1D95; color: white; }
    .asaas-tab:not(.active) .count { background: #ededf2; color: #a1a1b5; }

    .asaas-filters { padding: 16px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; border-bottom: 1px solid #ededf2; }
    .asaas-filters label { font-size: 0.68rem; font-weight: 700; color: #a1a1b5; display: block; margin-bottom: 4px; text-transform: uppercase; }
    .asaas-filters input, .asaas-filters select { padding: 8px 12px; border: 1.5px solid #e0e0e8; border-radius: 8px; font-size: 0.82rem; background: #fafafa; transition: 0.2s; }
    .asaas-filters input:focus, .asaas-filters select:focus { border-color: #4C1D95; background: white; box-shadow: 0 0 0 3px rgba(76,29,149,0.1); }
    .asaas-filters .btn-filter { padding: 8px 16px; background: #4C1D95; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; }
    .asaas-filters .btn-filter:hover { background: #3B0764; }
    .asaas-filters .btn-clear { padding: 8px 16px; border: 1px solid #e0e0e8; border-radius: 8px; font-size: 0.82rem; color: #a1a1b5; text-decoration: none; background: white; }
    .asaas-filters .btn-clear:hover { border-color: #4C1D95; color: #4C1D95; }

    .asaas-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .asaas-table th { background: #f4f5fa; padding: 12px 16px; text-align: left; font-size: 0.7rem; font-weight: 700; color: #a1a1b5; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1px solid #ededf2; white-space: nowrap; }
    .asaas-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #4a4a6a; vertical-align: middle; }
    .asaas-table tr:last-child td { border-bottom: none; }
    .asaas-table tbody tr { transition: background 0.15s; }
    .asaas-table tbody tr:hover { background: #f8f7ff; }

    .badge-status { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.64rem; font-weight: 800; white-space: nowrap; }
    .badge-status.ativo { background: #dcfce7; color: #166534; }
    .badge-status.churn { background: #ffedd5; color: #c2410c; }
    .badge-status.cancelado { background: #fee2e2; color: #991b1b; }
    .badge-status.pendente { background: #f1f5f9; color: #64748b; }

    .btn-detalhes { display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; background: #4C1D95; color: white; border-radius: 8px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: 0.2s; }
    .btn-detalhes:hover { background: #3B0764; }

    .sync-box { background: #fff8f3; border: 2px solid #f97316; border-radius: 14px; padding: 20px; margin-bottom: 20px; }
    .sync-box .spinner { color: #f97316; font-size: 1.6rem; }
    .sync-box .title { font-weight: 800; color: #c2410c; font-size: 1rem; }
    .sync-box .desc { font-size: 0.8rem; color: #9a3412; }
    .sync-bar-bg { background: #fed7aa; border-radius: 8px; height: 8px; overflow: hidden; }
    .sync-bar-fill { background: linear-gradient(90deg,#f97316,#ea580c); height: 100%; width: 0%; transition: width 0.5s ease; border-radius: 8px; }

    @media (max-width: 768px) {
        .asaas-filters { flex-direction: column; }
        .asaas-filters > div, .asaas-filters > form { width: 100%; }
    }
</style>

<x-page-hero 
    title="Clientes Asaas" 
    subtitle="Sincronize, classifique e atribua comissões aos clientes pré-existentes no Asaas." 
    icon="fas fa-cloud-arrow-down"
>
    <button id="btn-sincronizar" onclick="sincronizarAsaas()" class="btn btn-primary" style="font-weight:700;">
        <i class="fas fa-rotate"></i> Sincronizar com Asaas
    </button>
</x-page-hero>

{{-- PROGRESSO DE SINCRONIZAÇÃO --}}
<div id="sync-progress" style="display:none; margin-bottom:20px;">
    <div class="sync-box">
        <div style="display:flex; align-items:center; gap:14px; margin-bottom:12px;">
            <i class="fas fa-spinner fa-spin spinner"></i>
            <div>
                <div class="title">Sincronizando com o Asaas...</div>
                <div class="desc" id="sync-status-msg">Isso pode levar alguns minutos. Não feche esta aba.</div>
            </div>
        </div>
        <div class="sync-bar-bg">
            <div id="sync-progress-bar" class="sync-bar-fill"></div>
        </div>
    </div>
</div>

{{-- KPI CARDS --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px,1fr)); gap:14px; margin-bottom:24px;">
    <div class="asaas-kpi">
        <div class="num" style="color:#4C1D95;">{{ number_format($totais->total ?? 0) }}</div>
        <div class="lbl">Total</div>
    </div>
    <div class="asaas-kpi green clickable" onclick="filtrarAba('ativos')">
        <div class="num">{{ number_format($totais->ativos ?? 0) }}</div>
        <div class="lbl">Ativos</div>
    </div>
    <div class="asaas-kpi orange clickable" onclick="filtrarAba('churn')">
        <div class="num">{{ number_format($totais->churn ?? 0) }}</div>
        <div class="lbl">Churn</div>
    </div>
    <div class="asaas-kpi red clickable" onclick="filtrarAba('cancelados')">
        <div class="num">{{ number_format($totais->cancelados ?? 0) }}</div>
        <div class="lbl">Cancelados</div>
    </div>
    <div class="asaas-kpi purple clickable" onclick="filtrarAba('sem_vendedor')">
        <div class="num">{{ number_format($totais->sem_vendedor ?? 0) }}</div>
        <div class="lbl">Sem Vendedor</div>
    </div>
    <div class="asaas-kpi money">
        <div class="num">R$ {{ number_format($totais->total_comissao_vendedor ?? 0, 2, ',', '.') }}</div>
        <div class="lbl">Comissão Total</div>
    </div>
</div>

{{-- FILTROS + ABAS --}}
<div style="background:white; border-radius:12px; border:1px solid #ededf2; margin-bottom:16px; overflow:hidden; box-shadow:0 2px 4px rgba(50,50,71,0.06);">
    {{-- Abas --}}
    <div class="asaas-tabs">
        @php
            $abas = [
                ''             => ['label' => 'Todos', 'icon' => 'fa-list', 'count' => $totais->total ?? 0],
                'ativos'       => ['label' => 'Ativos', 'icon' => 'fa-circle-check', 'count' => $totais->ativos ?? 0],
                'churn'        => ['label' => 'Churn', 'icon' => 'fa-triangle-exclamation', 'count' => $totais->churn ?? 0],
                'cancelados'   => ['label' => 'Cancelados', 'icon' => 'fa-circle-xmark', 'count' => $totais->cancelados ?? 0],
                'sem_vendedor' => ['label' => 'Sem Vendedor', 'icon' => 'fa-user-slash', 'count' => $totais->sem_vendedor ?? 0],
            ];
        @endphp
        @foreach($abas as $key => $info)
        @php $isActive = ($aba === $key || ($key === '' && $aba === 'todos')); @endphp
        <a href="{{ route('master.clientes-asaas.index', array_merge(request()->except('aba','page'), $key ? ['aba' => $key] : [])) }}"
           class="asaas-tab {{ $isActive ? 'active' : '' }}">
            <i class="fas {{ $info['icon'] }}"></i>
            {{ $info['label'] }}
            <span class="count">{{ $info['count'] }}</span>
        </a>
        @endforeach
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('master.clientes-asaas.index') }}" class="asaas-filters" onsubmit="saveSelectionToUrl(); return true;">
        @if($aba && $aba !== 'todos') <input type="hidden" name="aba" value="{{ $aba }}"> @endif
        <div>
            <label>Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, CPF, email..." style="width:200px;">
        </div>
        <div>
            <label>Vendedor</label>
            <select name="vendedor_id" style="min-width:160px;">
                <option value="">Todos</option>
                <option value="sem_vendedor" {{ request('vendedor_id') === 'sem_vendedor' ? 'selected' : '' }}>— Sem vendedor —</option>
                @php
                    $listaG = $vendedores->where('is_gestor', true);
                    $listaV = $vendedores->where('is_gestor', false);
                @endphp
                @if($listaG->count() > 0)
                <optgroup label="Gestores">
                    @foreach($listaG as $v)
                        <option value="{{ $v->id }}" {{ request('vendedor_id') == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                    @endforeach
                </optgroup>
                @endif
                @if($listaV->count() > 0)
                <optgroup label="Vendedores">
                    @foreach($listaV as $v)
                        <option value="{{ $v->id }}" {{ request('vendedor_id') == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                    @endforeach
                </optgroup>
                @endif
            </select>
        </div>
        <div>
            <label>Tipo Cobrança</label>
            <select name="tipo_cobranca">
                <option value="">Todos</option>
                <option value="subscription" {{ request('tipo_cobranca') === 'subscription' ? 'selected' : '' }}>Assinatura</option>
                <option value="installment"  {{ request('tipo_cobranca') === 'installment' ? 'selected' : '' }}>Parcelamento</option>
                <option value="avulso"       {{ request('tipo_cobranca') === 'avulso' ? 'selected' : '' }}>Avulso</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; align-items:flex-end;">
            <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filtrar</button>
            <a href="{{ route('master.clientes-asaas.index') }}" class="btn-clear">Limpar</a>
        </div>
    </form>
</div>

{{-- TABELA PRINCIPAL --}}
<div style="background:white; border-radius:12px; border:1px solid #ededf2; overflow:hidden; box-shadow:0 2px 4px rgba(50,50,71,0.06);">
    {{-- BARRA DE ATRIBUIÇÃO EM MASSA --}}
    <div id="bulk_assign_bar" style="display:block; background:linear-gradient(135deg, #4C1D95 0%, #7c3aed 100%); padding:16px 20px; color:white;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <input type="checkbox" id="select_all_checkbox" onchange="toggleAllCheckboxes()" style="width:18px; height:18px; cursor:pointer;">
                <span style="font-weight:700; font-size:0.95rem;" id="selected_count">0 selecionados</span>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <select id="bulk_vendedor_select" style="padding:10px 14px; border-radius:8px; border:2px solid rgba(255,255,255,0.3); background:rgba(255,255,255,0.15); color:white; font-weight:600; min-width:200px;">
                    <option value="">— Selecionar Vendedor —</option>
                    @php
                        $listaG = $vendedores->where('is_gestor', true);
                        $listaV = $vendedores->where('is_gestor', false);
                    @endphp
                    @if($listaG->count() > 0)
                    <optgroup label="Gestores" style="color:#1e1e1e;">
                        @foreach($listaG as $v)
                        <option value="{{ $v->id }}" style="color:#1e1e1e;">{{ $v->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </optgroup>
                    @endif
                    @if($listaV->count() > 0)
                    <optgroup label="Vendedores" style="color:#1e1e1e;">
                        @foreach($listaV as $v)
                        <option value="{{ $v->id }}" style="color:#1e1e1e;">{{ $v->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
                <button type="button" onclick="atribuirEmMassa()" class="btn-bulk-assign" style="padding:10px 20px; background:#22c55e; border:none; border-radius:8px; color:white; font-weight:800; cursor:pointer; font-size:0.9rem;">
                    <i class="fas fa-user-plus"></i> Atribuir
                </button>
            </div>
        </div>
    </div>

    @if($clientes->isEmpty())
        <div style="text-align:center; padding:80px 20px; color:#a1a1b5;">
            <i class="fas fa-cloud-arrow-down" style="font-size:3rem; opacity:0.15; display:block; margin-bottom:16px; color:#4C1D95;"></i>
            <h3 style="margin:0 0 8px; font-size:1.1rem; color:#3b3b5c;">
                @if($aba === 'ativos') Nenhum cliente ativo encontrado
                @elseif($aba === 'churn') Nenhum cliente em churn — ótimo!
                @elseif($aba === 'cancelados') Nenhum cliente cancelado
                @elseif($aba === 'sem_vendedor') Todos os clientes têm vendedor atribuído
                @else Nenhum cliente sincronizado ainda
                @endif
            </h3>
            @if(!$aba || $aba === 'todos')
            <p>Clique em <strong>"Sincronizar com Asaas"</strong> para importar os clientes.</p>
            @endif
        </div>
    @else
    <div style="overflow-x:auto;">
        <table class="asaas-table">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="select_all_top" onchange="toggleAllCheckboxes()" style="width:16px; height:16px; cursor:pointer;">
                    </th>
                    <th>Cliente</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">1º Pagamento</th>
                    <th class="text-center">Últ. Confirmado</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $c)
                @php
                    $isDup = in_array($c->documento, $dupCpfs) && !empty($c->documento);
                    $diag  = $c->diagnostico_status ?? 'PENDENTE';
                    $diagClass = match($diag) {
                        'ATIVO'     => 'ativo',
                        'CHURN'     => 'churn',
                        'CANCELADO' => 'cancelado',
                        default     => 'pendente',
                    };
                    $diagLabel = match($diag) {
                        'ATIVO'     => 'Ativo',
                        'CHURN'     => 'Churn',
                        'CANCELADO' => 'Cancelado',
                        default     => 'Pendente',
                    };
                    $tipoLabel = match($c->tipo_cobranca ?? '') {
                        'subscription' => 'Assinatura',
                        'installment'  => 'Parcelamento',
                        'avulso'       => 'Avulso',
                        default        => '—',
                    };
                    $jaConfirmado = !is_null($c->local_cliente_id);
                @endphp
                <tr>
                    <td style="text-align:center;">
                        <input type="checkbox" class="row-checkbox" value="{{ $c->id }}" onchange="updateSelectedCount()" style="width:16px; height:16px; cursor:pointer;">
                    </td>
                    <td style="max-width:220px;">
                        <div style="font-weight:700; color:#3b3b5c; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $c->nome }}">
                            {{ $c->nome ?? '—' }}
                            @if($isDup)
                            <span style="font-size:0.55rem; background:#fbbf24; color:#78350f; padding:1px 5px; border-radius:8px; font-weight:800; vertical-align:middle;">DUP</span>
                            @endif
                            @if($jaConfirmado)
                            <span style="font-size:0.55rem; background:#22c55e; color:white; padding:1px 5px; border-radius:8px; font-weight:800; vertical-align:middle;">✓ SISTEMA</span>
                            @endif
                        </div>
                        <div style="font-size:0.7rem; color:#a1a1b5;">
                            {{ $c->documento ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $c->documento) : '—' }}
                        </div>
                        @if($c->email)
                        <div style="font-size:0.68rem; color:#a1a1b5; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $c->email }}">
                            {{ $c->email }}
                        </div>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge-status {{ $diagClass }}">{{ $diagLabel }}</span>
                        @if($diag === 'CHURN' && ($c->dias_sem_pagar ?? 0) > 0)
                        <div style="font-size:0.62rem; color:#c2410c; margin-top:3px; font-weight:600;">{{ $c->dias_sem_pagar }}d sem pagar</div>
                        @endif
                    </td>
                    <td class="text-center" style="white-space:nowrap; color:#a1a1b5; font-size:0.75rem;">{{ $tipoLabel }}</td>
                    <td class="text-center" style="white-space:nowrap;">
                        @if($c->primeiro_pagamento_at)
                            <span style="font-size:0.8rem; font-weight:600; color:#3b3b5c;">
                                {{ \Carbon\Carbon::parse($c->primeiro_pagamento_at)->format('d/m/Y') }}
                            </span>
                        @else
                            <span style="color:#a1a1b5; font-size:0.75rem;">—</span>
                        @endif
                    </td>
                    <td class="text-center" style="white-space:nowrap;">
                        @if($c->ultimo_pagamento_confirmado_at)
                            <span style="font-size:0.78rem;">{{ \Carbon\Carbon::parse($c->ultimo_pagamento_confirmado_at)->format('d/m/Y') }}</span>
                        @else
                            <span style="color:#ef4444; font-size:0.72rem;">Nunca pagou</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('master.clientes-asaas.show', $c->id) }}" class="btn-detalhes">
                            <i class="fas fa-eye"></i> Detalhes
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PAGINAÇÃO --}}
    <div style="padding:16px; border-top:1px solid #ededf2;">
        {{ $clientes->links() }}
    </div>
    @endif
</div>

{{-- LEGENDA --}}
<div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:16px; padding:14px; background:white; border-radius:12px; border:1px solid #ededf2; box-shadow:0 2px 4px rgba(50,50,71,0.06);">
    <div style="font-size:0.72rem; color:#a1a1b5; font-weight:700;">LEGENDA:</div>
    <div style="font-size:0.7rem; color:#166534; font-weight:600;">Ativo — pagamento em dia</div>
    <div style="font-size:0.7rem; color:#c2410c; font-weight:600;">Churn — já pagou, mas tem cobrança vencida</div>
    <div style="font-size:0.7rem; color:#991b1b; font-weight:600;">Cancelado — nunca pagou ou subscription cancelada</div>
    <div style="font-size:0.7rem; color:#fbbf24; background:#78350f; padding:1px 6px; border-radius:6px; font-weight:800;">DUP</div>
    <div style="font-size:0.7rem; color:#a1a1b5;">= mesmo CPF 2+ vezes no Asaas</div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';

function filtrarAba(aba) {
    window.location.href = '{{ route("master.clientes-asaas.index") }}?aba=' + aba;
}

function getSelectedFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('selected') ? params.get('selected').split(',') : [];
}

function saveSelectionToUrl() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    const url = new URL(window.location);
    if (ids.length > 0) {
        url.searchParams.set('selected', ids.join(','));
    } else {
        url.searchParams.delete('selected');
    }
    window.history.replaceState({}, '', url);
}

function restoreSelection() {
    const saved = getSelectedFromUrl();
    if (saved.length > 0) {
        saved.forEach(id => {
            const cb = document.querySelector(`.row-checkbox[value="${id}"]`);
            if (cb) cb.checked = true;
        });
        updateSelectedCount();
    }
}

function filtrarComSelecao() {
    saveSelectionToUrl();
    const form = document.querySelector('.asaas-filters');
    form.submit();
}

async function sincronizarAsaas() {
    const btn      = document.getElementById('btn-sincronizar');
    const progress = document.getElementById('sync-progress');
    const msg      = document.getElementById('sync-status-msg');
    const bar      = document.getElementById('sync-progress-bar');

    btn.disabled     = true;
    btn.innerHTML    = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
    progress.style.display = 'block';

    let pct = 0;
    const ticker = setInterval(() => {
        pct = Math.min(pct + Math.random() * 3, 90);
        bar.style.width = pct + '%';
    }, 800);

    try {
        const resp = await fetch('{{ route("master.clientes-asaas.sincronizar") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await resp.json();

        clearInterval(ticker);
        bar.style.width = '100%';

        if (data.success) {
            msg.textContent = data.message;
            msg.style.color = '#166534';
            setTimeout(() => window.location.reload(), 2000);
        } else {
            msg.textContent = data.message;
            msg.style.color = '#991b1b';
        }
    } catch (e) {
        clearInterval(ticker);
        msg.textContent = 'Erro de conexão: ' + e.message;
        msg.style.color = '#991b1b';
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-rotate"></i> Sincronizar com Asaas';
    }
}

function toggleAllCheckboxes() {
    const master = document.getElementById('select_all_top');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const bar = document.getElementById('bulk_assign_bar');
    const countEl = document.getElementById('selected_count');
    const count = checkboxes.length;
    countEl.textContent = count + ' selecionado' + (count !== 1 ? 's' : '');
    bar.style.display = count > 0 ? 'block' : 'none';
    
    // Salvar seleção na URL
    saveSelectionToUrl();
}

async function atribuirEmMassa() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const vendedorId = document.getElementById('bulk_vendedor_select').value;
    
    if (checkboxes.length === 0) {
        alert('Selecione pelo menos um cliente.');
        return;
    }
    
    if (!vendedorId) {
        alert('Selecione um vendedor.');
        return;
    }
    
    const clienteIds = Array.from(checkboxes).map(cb => cb.value);
    const vendedorNome = document.getElementById('bulk_vendedor_select').options[document.getElementById('bulk_vendedor_select').selectedIndex].text;
    
    const modalHtml = `
        <div id="confirm-modal" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;">
            <div style="background:white;border-radius:16px;padding:24px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <h3 style="margin:0 0 20px;color:#1e293b;font-size:1.2rem;font-weight:800;">
                    <i class="fas fa-user-plus" style="color:#4C1D95;"></i> Confirmar Atribuição
                </h3>
                
                <div style="background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:16px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Clientes</div>
                            <div style="font-size:1.5rem;font-weight:800;color:#4C1D95;">${clienteIds.length}</div>
                        </div>
                        <div>
                            <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Vendedor</div>
                            <div style="font-size:1rem;font-weight:700;color:#1e293b;">${vendedorNome}</div>
                        </div>
                    </div>
                    
                    <div style="padding-top:16px;border-top:1px solid #e2e8f0;">
                        <button id="btn-calcular" onclick="calcularComissaoPreview('${vendedorId}', '${JSON.stringify(clienteIds)}')" 
                            style="width:100%;padding:12px;background:#4C1D95;border:none;border-radius:8px;color:white;font-weight:700;cursor:pointer;">
                            <i class="fas fa-calculator"></i> Calcular Comissões
                        </button>
                    </div>
                    
                    <div id="preview-result" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid #e2e8f0;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Comissão Vendedor</div>
                                <div id="preview-comissao-vendedor" style="font-size:1.2rem;font-weight:800;color:#166534;">—</div>
                            </div>
                            <div>
                                <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Comissão Gestor</div>
                                <div id="preview-comissao-gestor" style="font-size:1.2rem;font-weight:800;color:#2563eb;">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="display:flex;gap:12px;">
                    <button onclick="closeConfirmModal()" style="flex:1;padding:12px;border:2px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:700;cursor:pointer;">Cancelar</button>
                    <button id="btn-confirm-assign" onclick="executarAtribuicao('${vendedorId}', '${JSON.stringify(clienteIds)}')" disabled style="flex:1;padding:12px;border:none;border-radius:8px;background:#9ca3af;color:white;font-weight:700;cursor:not-allowed;opacity:0.6;">
                        <i class="fas fa-check"></i> Confirmar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    const existing = document.getElementById('confirm-modal');
    if (existing) existing.remove();
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (modal) modal.remove();
}

async function calcularComissaoPreview(vendedorId, clienteIds) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const btn = document.getElementById('btn-calcular');
    let parsedIds;
    
    console.log('DEBUG: vendedorId =', vendedorId);
    console.log('DEBUG: clienteIds =', clienteIds);
    
    try {
        parsedIds = typeof clienteIds === 'string' ? JSON.parse(clienteIds) : clienteIds;
    } catch(e) {
        console.log('DEBUG: erro parse', e);
        parsedIds = clienteIds;
    }
    
    console.log('DEBUG: parsedIds =', parsedIds);
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculando...';
    
    try {
        const routeUrl = '/master/clientes-asaas/preview-assign';
        console.log('DEBUG: Fetching', routeUrl);
        
        const resp = await fetch(routeUrl, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken, 
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ customer_ids: parsedIds, vendedor_id: parseInt(vendedorId) })
        });
        
        console.log('DEBUG: resp status', resp.status);
        
        if (!resp.ok) {
            const errText = await resp.text();
            alert('Erro do servidor: ' + resp.status + ' - ' + errText);
            return;
        }
        
        const data = await resp.json();
        console.log('DEBUG: data', data);
        
        if (data.success) {
            document.getElementById('preview-result').style.display = 'block';
            document.getElementById('preview-comissao-vendedor').textContent = data.comissao_vendedor;
            document.getElementById('preview-comissao-gestor').textContent = data.comissao_gestor;
            
            const confirmBtn = document.getElementById('btn-confirm-assign');
            confirmBtn.disabled = false;
            confirmBtn.style.background = '#22c55e';
            confirmBtn.style.cursor = 'pointer';
            confirmBtn.style.opacity = '1';
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível calcular.'));
        }
    } catch (e) {
        alert('Erro de conexão: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calculator"></i> Calcular Comissões';
    }
}

async function executarAtribuicao(vendedorId, clienteIds) {
    closeConfirmModal();
    
    const btn = document.querySelector('.btn-bulk-assign');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const parsedIds = JSON.parse(clienteIds);
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    
    const bulkAssignUrl = '/master/clientes-asaas/bulk-assign';
    
    try {
        const resp = await fetch(bulkAssignUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ customer_ids: parsedIds, vendedor_id: parseInt(vendedorId) })
        });
        const data = await resp.json();
        
        if (data.success) {
            alert(data.message + '\n\nVendedor: R$ ' + data.comissao_vendedor + '\nGestor: R$ ' + data.comissao_gestor);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível atribuir.'));
        }
    } catch (e) {
        alert('Erro de conexão: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus"></i> Atribuir';
    }
}

// Restaurar seleção ao carregar página
document.addEventListener('DOMContentLoaded', restoreSelection);
</script>
@endsection
