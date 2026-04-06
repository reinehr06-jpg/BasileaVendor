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
    <form method="GET" action="{{ route('master.clientes-asaas.index') }}" class="asaas-filters">
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
                            <span style="font-size:0.8rem; font-weight:600; color:{{ str_starts_with($c->primeiro_pagamento_at, '2026-03') ? '#4C1D95' : '#3b3b5c' }};">
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
</script>
@endsection
