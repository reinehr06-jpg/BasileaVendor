@extends('layouts.app')

@section('title', 'Clientes Asaas — Painel de Importação')

@section('content')
<x-page-hero 
    title="Clientes Asaas" 
    subtitle="Sincronize, classifique e atribua comissões aos clientes pré-existentes no Asaas." 
    icon="fas fa-cloud-arrow-down"
>
    <x-slot:actions>
        <button id="btn-sincronizar" onclick="sincronizarAsaas()" class="btn btn-primary">
            <i class="fas fa-rotate"></i> Sincronizar com Asaas
        </button>
    </x-slot:actions>
</x-page-hero>

<div style="padding: 0 0 48px;">

    {{-- PROGRESSO DE SINCRONIZAÇÃO --}}
    <div id="sync-progress" style="display:none; margin-bottom:20px;">
        <div style="background:#fff8f3; border:2px solid #f97316; border-radius:14px; padding:20px;">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:12px;">
                <i class="fas fa-spinner fa-spin" style="color:#f97316; font-size:1.6rem;"></i>
                <div>
                    <div style="font-weight:800; color:#c2410c; font-size:1rem;">Sincronizando com o Asaas...</div>
                    <div style="font-size:0.8rem; color:#9a3412;" id="sync-status-msg">
                        Isso pode levar alguns minutos (≈300ms por cliente). Não feche esta aba.
                    </div>
                </div>
            </div>
            <div style="background:#fed7aa; border-radius:8px; height:8px; overflow:hidden;">
                <div id="sync-progress-bar" style="background:linear-gradient(90deg,#f97316,#ea580c); height:100%; width:0%; transition:width 0.5s ease; border-radius:8px;"></div>
            </div>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px,1fr)); gap:12px; margin-bottom:24px;">
        {{-- Total --}}
        <div style="background:var(--materio-surface); border-radius:14px; padding:18px; border:1px solid var(--materio-border); text-align:center;">
            <div style="font-size:2rem; font-weight:900; color:var(--materio-primary);">{{ number_format($totais->total ?? 0) }}</div>
            <div style="font-size:0.72rem; color:var(--materio-text-muted); font-weight:700;">TOTAL</div>
        </div>
        {{-- Ativos --}}
        <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-radius:14px; padding:18px; border:1px solid #86efac; text-align:center; cursor:pointer;" onclick="filtrarAba('ativos')">
            <div style="font-size:2rem; font-weight:900; color:#166534;">{{ number_format($totais->ativos ?? 0) }}</div>
            <div style="font-size:0.72rem; color:#166534; font-weight:700;">✅ ATIVOS</div>
        </div>
        {{-- Churn --}}
        <div style="background:linear-gradient(135deg,#fff7ed,#ffedd5); border-radius:14px; padding:18px; border:1px solid #fed7aa; text-align:center; cursor:pointer;" onclick="filtrarAba('churn')">
            <div style="font-size:2rem; font-weight:900; color:#c2410c;">{{ number_format($totais->churn ?? 0) }}</div>
            <div style="font-size:0.72rem; color:#c2410c; font-weight:700;">⚠️ CHURN</div>
        </div>
        {{-- Cancelados --}}
        <div style="background:linear-gradient(135deg,#fef2f2,#fee2e2); border-radius:14px; padding:18px; border:1px solid #fca5a5; text-align:center; cursor:pointer;" onclick="filtrarAba('cancelados')">
            <div style="font-size:2rem; font-weight:900; color:#991b1b;">{{ number_format($totais->cancelados ?? 0) }}</div>
            <div style="font-size:0.72rem; color:#991b1b; font-weight:700;">❌ CANCELADOS</div>
        </div>
        {{-- Sem Vendedor --}}
        <div style="background:linear-gradient(135deg,#f5f3ff,#ede9fe); border-radius:14px; padding:18px; border:1px solid #c4b5fd; text-align:center; cursor:pointer;" onclick="filtrarAba('sem_vendedor')">
            <div style="font-size:2rem; font-weight:900; color:#7c3aed;">{{ number_format($totais->sem_vendedor ?? 0) }}</div>
            <div style="font-size:0.72rem; color:#7c3aed; font-weight:700;">👤 SEM VENDEDOR</div>
        </div>
        {{-- Total Comissão --}}
        <div style="background:linear-gradient(135deg,#ecfdf5,#d1fae5); border-radius:14px; padding:18px; border:1px solid #6ee7b7; text-align:center;">
            <div style="font-size:1.2rem; font-weight:900; color:#065f46;">R$ {{ number_format($totais->total_comissao_vendedor ?? 0, 2, ',', '.') }}</div>
            <div style="font-size:0.72rem; color:#065f46; font-weight:700;">💰 COMISSÃO TOTAL</div>
        </div>
    </div>

    {{-- FILTROS + ABAS --}}
    <div style="background:var(--materio-surface); border-radius:14px; border:1px solid var(--materio-border); margin-bottom:16px; overflow:hidden;">
        {{-- Barra de abas --}}
        <div style="display:flex; border-bottom:2px solid var(--materio-border); background:var(--materio-bg); overflow-x:auto;">
            @php
                $abas = [
                    ''            => ['label' => 'Todos', 'icon' => 'fa-list', 'count' => $totais->total ?? 0],
                    'ativos'      => ['label' => 'Ativos', 'icon' => 'fa-circle-check', 'count' => $totais->ativos ?? 0, 'color' => '#166534'],
                    'churn'       => ['label' => 'Churn', 'icon' => 'fa-triangle-exclamation', 'count' => $totais->churn ?? 0, 'color' => '#c2410c'],
                    'cancelados'  => ['label' => 'Cancelados', 'icon' => 'fa-circle-xmark', 'count' => $totais->cancelados ?? 0, 'color' => '#991b1b'],
                    'sem_vendedor'=> ['label' => 'Sem Vendedor', 'icon' => 'fa-user-slash', 'count' => $totais->sem_vendedor ?? 0, 'color' => '#7c3aed'],
                ];
            @endphp
            @foreach($abas as $key => $info)
            @php $isActive = ($aba === $key || ($key === '' && $aba === 'todos')); @endphp
            <a href="{{ route('master.clientes-asaas.index', array_merge(request()->except('aba','page'), $key ? ['aba' => $key] : [])) }}"
               style="padding:14px 20px; white-space:nowrap; font-size:0.8rem; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:6px;
                      {{ $isActive ? 'border-bottom:3px solid #f97316; color:#f97316; background:white;' : 'color:var(--materio-text-muted); border-bottom:3px solid transparent;' }}">
                <i class="fas {{ $info['icon'] }}" style="{{ isset($info['color']) && !$isActive ? 'color:'.$info['color'] : '' }}"></i>
                {{ $info['label'] }}
                <span style="background:{{ $isActive ? '#f97316' : 'var(--materio-border)' }}; color:{{ $isActive ? 'white' : 'var(--materio-text-muted)' }};
                             padding:1px 7px; border-radius:20px; font-size:0.68rem;">
                    {{ $info['count'] }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('master.clientes-asaas.index') }}" style="padding:14px 16px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            @if($aba && $aba !== 'todos') <input type="hidden" name="aba" value="{{ $aba }}"> @endif
            <div>
                <label style="font-size:0.68rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:3px;">BUSCAR</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, CPF, email..."
                    style="padding:8px 12px; border:1px solid var(--materio-border); border-radius:8px; font-size:0.82rem; width:200px;">
            </div>
            <div>
                <label style="font-size:0.68rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:3px;">VENDEDOR</label>
                <select name="vendedor_id" style="padding:8px 12px; border:1px solid var(--materio-border); border-radius:8px; font-size:0.82rem;">
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
                <label style="font-size:0.68rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:3px;">TIPO COBRANÇA</label>
                <select name="tipo_cobranca" style="padding:8px 12px; border:1px solid var(--materio-border); border-radius:8px; font-size:0.82rem;">
                    <option value="">Todos</option>
                    <option value="subscription" {{ request('tipo_cobranca') === 'subscription' ? 'selected' : '' }}>📋 Assinatura</option>
                    <option value="installment"  {{ request('tipo_cobranca') === 'installment' ? 'selected' : '' }}>💳 Parcelamento</option>
                    <option value="avulso"       {{ request('tipo_cobranca') === 'avulso' ? 'selected' : '' }}>🔖 Avulso</option>
                </select>
            </div>
            <button type="submit" style="padding:8px 16px; background:var(--materio-primary); color:white; border:none; border-radius:8px; font-weight:700; font-size:0.82rem; cursor:pointer;">
                Filtrar
            </button>
            <a href="{{ route('master.clientes-asaas.index') }}" style="padding:8px 16px; border:1px solid var(--materio-border); border-radius:8px; font-size:0.82rem; color:var(--materio-text-muted); text-decoration:none;">
                Limpar
            </a>
        </form>
    </div>

    {{-- TABELA PRINCIPAL --}}
    <div style="background:var(--materio-surface); border-radius:14px; border:1px solid var(--materio-border); overflow:hidden;">
        @if($clientes->isEmpty())
            <div style="text-align:center; padding:80px 20px; color:var(--materio-text-muted);">
                <i class="fas fa-cloud-arrow-down" style="font-size:3rem; opacity:0.15; display:block; margin-bottom:16px;"></i>
                <h3 style="margin:0 0 8px; font-size:1.1rem;">
                    @if($aba === 'ativos') Nenhum cliente ativo encontrado
                    @elseif($aba === 'churn') Nenhum cliente em churn — ótimo! 🎉
                    @elseif($aba === 'cancelados') Nenhum cliente cancelado
                    @elseif($aba === 'sem_vendedor') Todos os clientes têm vendedor atribuído ✅
                    @else Nenhum cliente sincronizado ainda
                    @endif
                </h3>
                @if(!$aba || $aba === 'todos')
                <p>Clique em <strong>"Sincronizar com Asaas"</strong> para importar os clientes.</p>
                @endif
            </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.81rem;">
                <thead>
                    <tr style="background:var(--materio-bg); border-bottom:2px solid var(--materio-border);">
                        <th style="padding:12px 16px; text-align:left; font-size:0.68rem; font-weight:800; color:var(--materio-text-muted); white-space:nowrap;">CLIENTE</th>
                        <th style="padding:12px 8px; text-align:center; font-size:0.68rem; font-weight:800; color:var(--materio-text-muted);">STATUS</th>
                        <th style="padding:12px 8px; text-align:center; font-size:0.68rem; font-weight:800; color:var(--materio-text-muted);">TIPO</th>
                        <th style="padding:12px 8px; text-align:center; font-size:0.68rem; font-weight:800; color:var(--materio-text-muted);">1º PAGAMENTO</th>
                        <th style="padding:12px 8px; text-align:center; font-size:0.68rem; font-weight:800; color:var(--materio-text-muted);">ÚLT. CONFIRMADO</th>
                        <th style="padding:12px 8px; text-align:center; font-size:0.68rem; font-weight:800; color:var(--materio-text-muted);">AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $c)
                    @php
                        $isDup = in_array($c->documento, $dupCpfs) && !empty($c->documento);
                        $diag  = $c->diagnostico_status ?? 'PENDENTE';

                        $diagStyle = match($diag) {
                            'ATIVO'     => ['bg'=>'#dcfce7','color'=>'#166534','label'=>'✅ ATIVO'],
                            'CHURN'     => ['bg'=>'#ffedd5','color'=>'#c2410c','label'=>'⚠️ CHURN'],
                            'CANCELADO' => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'❌ CANCELADO'],
                            default     => ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>'⏳ PENDENTE'],
                        };

                        $tipoLabel = match($c->tipo_cobranca ?? '') {
                            'subscription' => '📋 Assinatura',
                            'installment'  => '💳 Parcelamento',
                            'avulso'       => '🔖 Avulso',
                            default        => '—',
                        };

                        $comissaoLabel = match($c->comissao_tipo ?? '') {
                            'inicial'            => ['label'=>'🆕 1ª Venda','color'=>'#7c3aed'],
                            'inicial_antecipada' => ['label'=>'💳 Antecipado','color'=>'#dc2626'],
                            'recorrencia'        => ['label'=>'🔄 Recorrência','color'=>'#0284c7'],
                            default              => ['label'=>'— sem pagto —','color'=>'#9ca3af'],
                        };

                        $parcelasRestantes = max(0, ($c->parcelas_total ?? 1) - ($c->parcelas_pagas ?? 0));
                        $jaConfirmado = !is_null($c->local_cliente_id);
                    @endphp

                    <tr style="border-bottom:1px solid var(--materio-border); transition:background 0.15s;"
                        onmouseover="this.style.background='var(--materio-bg)'"
                        onmouseout="this.style.background=''">

                        {{-- CLIENTE --}}
                        <td style="padding:12px 16px; max-width:220px;">
                            <div style="font-weight:700; color:var(--materio-text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $c->nome }}">
                                {{ $c->nome ?? '—' }}
                                @if($isDup)
                                <span style="font-size:0.55rem; background:#fbbf24; color:#78350f; padding:1px 5px; border-radius:8px; font-weight:800; vertical-align:middle;">DUP</span>
                                @endif
                                @if($jaConfirmado)
                                <span style="font-size:0.55rem; background:#22c55e; color:white; padding:1px 5px; border-radius:8px; font-weight:800; vertical-align:middle;">✓ NO SISTEMA</span>
                                @endif
                            </div>
                            <div style="font-size:0.7rem; color:var(--materio-text-muted);">
                                {{ $c->documento ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $c->documento) : '—' }}
                            </div>
                            @if($c->email)
                            <div style="font-size:0.68rem; color:var(--materio-text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $c->email }}">
                                {{ $c->email }}
                            </div>
                            @endif
                        </td>

                        {{-- STATUS DIAGNÓSTICO --}}
                        <td style="padding:12px 8px; text-align:center;">
                            <span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:0.64rem; font-weight:800; background:{{ $diagStyle['bg'] }}; color:{{ $diagStyle['color'] }}; white-space:nowrap;">
                                {{ $diagStyle['label'] }}
                            </span>
                            @if($diag === 'CHURN' && ($c->dias_sem_pagar ?? 0) > 0)
                            <div style="font-size:0.62rem; color:#c2410c; margin-top:3px; font-weight:600;">
                                {{ $c->dias_sem_pagar }}d sem pagar
                            </div>
                            @endif
                        </td>

                        {{-- TIPO COBRANÇA --}}
                        <td style="padding:12px 8px; text-align:center; white-space:nowrap; color:var(--materio-text-muted); font-size:0.75rem;">
                            {{ $tipoLabel }}
                        </td>

                        {{-- 1º PAGAMENTO --}}
                        <td style="padding:12px 8px; text-align:center; white-space:nowrap;">
                            @if($c->primeiro_pagamento_at)
                                <span style="font-size:0.8rem; font-weight:600; color:{{ str_starts_with($c->primeiro_pagamento_at, '2026-03') ? '#7c3aed' : 'var(--materio-text-main)' }};">
                                    {{ \Carbon\Carbon::parse($c->primeiro_pagamento_at)->format('d/m/Y') }}
                                </span>
                                @if(str_starts_with($c->primeiro_pagamento_at, '2026-03'))
                                <div style="font-size:0.6rem; color:#7c3aed; font-weight:700;">🆕 NOVO</div>
                                @endif
                            @else
                                <span style="color:var(--materio-text-muted); font-size:0.75rem;">—</span>
                            @endif
                        </td>

                        {{-- ÚLTIMO CONFIRMADO --}}
                        <td style="padding:12px 8px; text-align:center; white-space:nowrap;">
                            @if($c->ultimo_pagamento_confirmado_at)
                                <span style="font-size:0.78rem; color:var(--materio-text-main);">
                                    {{ \Carbon\Carbon::parse($c->ultimo_pagamento_confirmado_at)->format('d/m/Y') }}
                                </span>
                            @else
                                <span style="color:#ef4444; font-size:0.72rem;">Nunca pagou</span>
                            @endif
                        </td>

                        {{-- AÇÃO BOTÃO OLHINHO --}}
                        <td style="padding:10px 8px; text-align:center;">
                            <a href="{{ route('master.clientes-asaas.show', $c->id) }}" class="materio-btn-primary" style="padding:6px 12px; font-size:0.8rem; text-decoration:none;">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINAÇÃO --}}
        <div style="padding:16px; border-top:1px solid var(--materio-border);">
            {{ $clientes->links() }}
        </div>
        @endif
    </div>

    {{-- LEGENDA --}}
    <div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:16px; padding:14px; background:var(--materio-surface); border-radius:12px; border:1px solid var(--materio-border);">
        <div style="font-size:0.72rem; color:var(--materio-text-muted); font-weight:700;">LEGENDA:</div>
        <div style="font-size:0.7rem; color:#166534; font-weight:600;">✅ ATIVO — pagamento em dia</div>
        <div style="font-size:0.7rem; color:#c2410c; font-weight:600;">⚠️ CHURN — já pagou, mas tem cobrança vencida</div>
        <div style="font-size:0.7rem; color:#991b1b; font-weight:600;">❌ CANCELADO — nunca pagou ou subscription cancelada</div>
        <div style="font-size:0.7rem; color:#fbbf24; background:#78350f; padding:1px 6px; border-radius:6px; font-weight:800;">DUP</div>
        <div style="font-size:0.7rem; color:var(--materio-text-muted);">= mesmo CPF 2+ vezes no Asaas</div>
        <div style="font-size:0.7rem; color:#7c3aed; font-weight:600;">💳 Antecipado = parcelado em março → comissão total na 1ª parcela</div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';

// Filtrar por aba clicando no KPI card
function filtrarAba(aba) {
    window.location.href = '{{ route("master.clientes-asaas.index") }}?aba=' + aba;
}

// ── Sincronizar com Asaas ──
async function sincronizarAsaas() {
    const btn      = document.getElementById('btn-sincronizar');
    const progress = document.getElementById('sync-progress');
    const msg      = document.getElementById('sync-status-msg');
    const bar      = document.getElementById('sync-progress-bar');

    btn.disabled     = true;
    btn.innerHTML    = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
    progress.style.display = 'block';

    // Animação de progresso indeterminada
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
