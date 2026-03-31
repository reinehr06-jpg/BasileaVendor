@extends('layouts.app')

@section('title', 'Clientes Asaas — Importação & Comissões Março/2026')

@section('content')
<div style="padding: 0 0 40px;">

    {{-- HEADER --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:1.5rem; font-weight:900; color:var(--materio-text-main); margin:0;">
                <i class="fas fa-cloud-arrow-down" style="color:#f97316;"></i>
                Clientes Asaas
                <span style="font-size:0.75rem; background:#f97316; color:white; padding:3px 10px; border-radius:20px; vertical-align:middle; margin-left:8px; font-weight:700;">
                    MARÇO/2026
                </span>
            </h1>
            <p style="color:var(--materio-text-muted); font-size:0.85rem; margin:4px 0 0;">
                Sincronize clientes do Asaas, atribua vendedores e calcule comissões.
                @if($ultimaSincronizacao)
                    Última sync: <strong>{{ \Carbon\Carbon::parse($ultimaSincronizacao)->format('d/m/Y H:i') }}</strong>
                @else
                    <strong>Nunca sincronizado</strong>
                @endif
            </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button id="btn-sincronizar" onclick="sincronizarAsaas()" class="materio-btn-primary" style="background: linear-gradient(135deg, #f97316, #ea580c); border:none;">
                <i class="fas fa-rotate"></i> Sincronizar com Asaas
            </button>
        </div>
    </div>

    {{-- BARRA DE PROGRESSO DA SINCRONIZAÇÃO --}}
    <div id="sync-progress" style="display:none; margin-bottom:20px;">
        <div style="background:#fff8f3; border:1px solid #f97316; border-radius:12px; padding:16px; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-spinner fa-spin" style="color:#f97316; font-size:1.3rem;"></i>
            <div>
                <div style="font-weight:700; color:#c2410c;">Sincronizando com o Asaas...</div>
                <div style="font-size:0.82rem; color:#9a3412;" id="sync-status-msg">Isso pode levar alguns minutos dependendo da quantidade de clientes.</div>
            </div>
        </div>
    </div>

    {{-- CARDS DE TOTAIS --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:24px;">
        <div style="background:var(--materio-surface); border-radius:12px; padding:16px; border:1px solid var(--materio-border); text-align:center;">
            <div style="font-size:1.8rem; font-weight:900; color:var(--materio-primary);">{{ number_format($totais->total ?? 0) }}</div>
            <div style="font-size:0.75rem; color:var(--materio-text-muted); font-weight:600;">TOTAL CLIENTES</div>
        </div>
        <div style="background:var(--materio-surface); border-radius:12px; padding:16px; border:1px solid var(--materio-border); text-align:center;">
            <div style="font-size:1.8rem; font-weight:900; color:#22c55e;">{{ number_format($totais->ativos ?? 0) }}</div>
            <div style="font-size:0.75rem; color:var(--materio-text-muted); font-weight:600;">ATIVOS</div>
        </div>
        <div style="background:var(--materio-surface); border-radius:12px; padding:16px; border:1px solid var(--materio-border); text-align:center;">
            <div style="font-size:1.8rem; font-weight:900; color:#ef4444;">{{ number_format($totais->vencidos ?? 0) }}</div>
            <div style="font-size:0.75rem; color:var(--materio-text-muted); font-weight:600;">VENCIDOS</div>
        </div>
        <div style="background:var(--materio-surface); border-radius:12px; padding:16px; border:1px solid var(--materio-border); text-align:center;">
            <div style="font-size:1.8rem; font-weight:900; color:#8b5cf6;">{{ number_format($totais->parcelados ?? 0) }}</div>
            <div style="font-size:0.75rem; color:var(--materio-text-muted); font-weight:600;">PARCELADOS</div>
        </div>
        <div style="background:var(--materio-surface); border-radius:12px; padding:16px; border:1px solid var(--materio-border); text-align:center;">
            <div style="font-size:1.4rem; font-weight:900; color:#f97316;">{{ number_format($totais->sem_vendedor ?? 0) }}</div>
            <div style="font-size:0.75rem; color:var(--materio-text-muted); font-weight:600;">SEM VENDEDOR</div>
        </div>
        <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-radius:12px; padding:16px; border:1px solid #86efac; text-align:center;">
            <div style="font-size:1.1rem; font-weight:900; color:#166534;">R$ {{ number_format($totais->total_comissao_vendedor ?? 0, 2, ',', '.') }}</div>
            <div style="font-size:0.75rem; color:#166534; font-weight:700;">TOTAL COMISSÃO</div>
        </div>
    </div>

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('master.clientes-asaas.index') }}" style="margin-bottom:16px;">
        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; background:var(--materio-surface); padding:14px; border-radius:12px; border:1px solid var(--materio-border);">
            <div>
                <label style="font-size:0.72rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:4px;">BUSCAR</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, CPF, email..." class="materio-input" style="width:200px; padding:8px 12px;">
            </div>
            <div>
                <label style="font-size:0.72rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:4px;">VENDEDOR</label>
                <select name="vendedor_id" class="materio-select" style="padding:8px 12px;">
                    <option value="">Todos</option>
                    <option value="sem_vendedor" {{ request('vendedor_id') === 'sem_vendedor' ? 'selected' : '' }}>— Sem vendedor —</option>
                    @foreach($vendedores as $v)
                        <option value="{{ $v->id }}" {{ request('vendedor_id') == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:4px;">STATUS</label>
                <select name="status" class="materio-select" style="padding:8px 12px;">
                    <option value="">Todos</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>✅ Ativo</option>
                    <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>⚠️ Vencido</option>
                    <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>⛔ Inativo</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>❌ Cancelado</option>
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:4px;">TIPO COMISSÃO</label>
                <select name="tipo_comissao" class="materio-select" style="padding:8px 12px;">
                    <option value="">Todos</option>
                    <option value="inicial" {{ request('tipo_comissao') === 'inicial' ? 'selected' : '' }}>🆕 1ª Venda</option>
                    <option value="inicial_antecipada" {{ request('tipo_comissao') === 'inicial_antecipada' ? 'selected' : '' }}>💳 Parcelado (Antecipado)</option>
                    <option value="recorrencia" {{ request('tipo_comissao') === 'recorrencia' ? 'selected' : '' }}>🔄 Recorrência</option>
                </select>
            </div>
            <div>
                <label style="font-size:0.72rem; font-weight:700; color:var(--materio-text-muted); display:block; margin-bottom:4px;">TIPO COBRANÇA</label>
                <select name="tipo_cobranca" class="materio-select" style="padding:8px 12px;">
                    <option value="">Todos</option>
                    <option value="subscription" {{ request('tipo_cobranca') === 'subscription' ? 'selected' : '' }}>📋 Assinatura</option>
                    <option value="installment" {{ request('tipo_cobranca') === 'installment' ? 'selected' : '' }}>💳 Parcelamento</option>
                    <option value="avulso" {{ request('tipo_cobranca') === 'avulso' ? 'selected' : '' }}>🔖 Avulso</option>
                </select>
            </div>
            <button type="submit" class="materio-btn-primary" style="padding:8px 16px;">Filtrar</button>
            <a href="{{ route('master.clientes-asaas.index') }}" class="materio-btn-outline" style="padding:8px 16px;">Limpar</a>
        </div>
    </form>

    {{-- TABELA PRINCIPAL --}}
    <div style="background:var(--materio-surface); border-radius:14px; border:1px solid var(--materio-border); overflow:hidden;">
        @if($clientes->isEmpty())
            <div style="text-align:center; padding:60px 20px; color:var(--materio-text-muted);">
                <i class="fas fa-cloud-arrow-down" style="font-size:3rem; opacity:0.2; display:block; margin-bottom:16px;"></i>
                <h3 style="font-size:1.1rem; margin:0 0 8px;">Nenhum cliente sincronizado ainda</h3>
                <p>Clique em <strong>"Sincronizar com Asaas"</strong> para importar os clientes.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.82rem;">
                    <thead>
                        <tr style="background:var(--materio-bg); border-bottom:2px solid var(--materio-border);">
                            <th style="padding:12px 16px; text-align:left; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted); white-space:nowrap;">CLIENTE / DOC</th>
                            <th style="padding:12px 8px; text-align:center; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted);">STATUS</th>
                            <th style="padding:12px 8px; text-align:center; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted);">TIPO</th>
                            <th style="padding:12px 8px; text-align:center; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted);">PARCELAS</th>
                            <th style="padding:12px 8px; text-align:center; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted);">1º PAGAMENTO</th>
                            <th style="padding:12px 8px; text-align:right; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted);">VLR. MARÇO</th>
                            <th style="padding:12px 8px; text-align:center; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted);">TIPO COMISSÃO</th>
                            <th style="padding:12px 8px; text-align:right; font-size:0.7rem; font-weight:800; color:#166534;">COMISSÃO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:0.7rem; font-weight:800; color:var(--materio-text-muted); min-width:200px;">VENDEDOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                        @php
                            $isDuplicate = in_array($cliente->documento, $dupCpfs) && !empty($cliente->documento);
                            $statusColor = match($cliente->subscription_status ?? 'NONE') {
                                'ACTIVE'    => ['bg' => '#dcfce7', 'color' => '#166534', 'label' => 'ATIVA'],
                                'INACTIVE'  => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => 'INATIVA'],
                                'CANCELLED' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'CANCELADA'],
                                'OVERDUE'   => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'VENCIDA'],
                                default     => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => 'N/D'],
                            };
                            $comissaoTipoLabel = match($cliente->comissao_tipo ?? '') {
                                'inicial'           => ['label' => '🆕 1ª Venda', 'color' => '#7c3aed'],
                                'inicial_antecipada'=> ['label' => '💳 Antecipado', 'color' => '#dc2626'],
                                'recorrencia'       => ['label' => '🔄 Recorrência', 'color' => '#0284c7'],
                                default             => ['label' => '— sem pagto —', 'color' => '#9ca3af'],
                            };
                            $tipoCobrancaLabel = match($cliente->tipo_cobranca ?? '') {
                                'subscription' => '📋 Assinatura',
                                'installment'  => '💳 Parcelamento',
                                'avulso'       => '🔖 Avulso',
                                default        => '—',
                            };
                            $parcelasRestantes = max(0, ($cliente->parcelas_total ?? 1) - ($cliente->parcelas_pagas ?? 0));
                        @endphp
                        <tr style="border-bottom:1px solid var(--materio-border); transition:background 0.15s;" onmouseover="this.style.background='var(--materio-bg)'" onmouseout="this.style.background=''">
                            {{-- CLIENTE --}}
                            <td style="padding:12px 16px; max-width:220px;">
                                <div style="font-weight:700; color:var(--materio-text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $cliente->nome }}">
                                    {{ $cliente->nome ?? '—' }}
                                    @if($isDuplicate)
                                    <span style="font-size:0.55rem; background:#fbbf24; color:#78350f; padding:1px 5px; border-radius:8px; vertical-align:middle; font-weight:800;">DUP</span>
                                    @endif
                                </div>
                                <div style="font-size:0.72rem; color:var(--materio-text-muted);">
                                    {{ $cliente->documento ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cliente->documento) : '—' }}
                                </div>
                                @if($cliente->email)
                                <div style="font-size:0.7rem; color:var(--materio-text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $cliente->email }}">
                                    {{ $cliente->email }}
                                </div>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td style="padding:12px 8px; text-align:center;">
                                <span style="display:inline-block; padding:3px 8px; border-radius:20px; font-size:0.65rem; font-weight:800; background:{{ $statusColor['bg'] }}; color:{{ $statusColor['color'] }};">
                                    {{ $statusColor['label'] }}
                                </span>
                            </td>

                            {{-- TIPO COBRANÇA --}}
                            <td style="padding:12px 8px; text-align:center; white-space:nowrap; color:var(--materio-text-muted); font-size:0.75rem;">
                                {{ $tipoCobrancaLabel }}
                            </td>

                            {{-- PARCELAS --}}
                            <td style="padding:12px 8px; text-align:center;">
                                @if(($cliente->tipo_cobranca ?? '') === 'installment')
                                    <div style="font-weight:700; color:var(--materio-text-main);">
                                        {{ $cliente->parcelas_pagas }}/{{ $cliente->parcelas_total }}
                                    </div>
                                    <div style="font-size:0.68rem; color:{{ $parcelasRestantes > 0 ? '#f97316' : '#22c55e' }}; font-weight:600;">
                                        {{ $parcelasRestantes > 0 ? "{$parcelasRestantes} restantes" : 'quitado' }}
                                    </div>
                                @elseif(($cliente->tipo_cobranca ?? '') === 'subscription')
                                    <span style="font-size:0.72rem; color:var(--materio-text-muted);">Recorrente</span>
                                @else
                                    <span style="color:var(--materio-text-muted);">—</span>
                                @endif
                            </td>

                            {{-- 1º PAGAMENTO --}}
                            <td style="padding:12px 8px; text-align:center; white-space:nowrap;">
                                @if($cliente->primeiro_pagamento_at)
                                    <span style="font-size:0.8rem; font-weight:600; color:{{ str_starts_with($cliente->primeiro_pagamento_at, '2026-03') ? '#7c3aed' : 'var(--materio-text-main)' }};">
                                        {{ \Carbon\Carbon::parse($cliente->primeiro_pagamento_at)->format('d/m/Y') }}
                                    </span>
                                    @if(str_starts_with($cliente->primeiro_pagamento_at, '2026-03'))
                                    <div style="font-size:0.6rem; color:#7c3aed; font-weight:700;">🆕 NOVO CLIENTE</div>
                                    @endif
                                @else
                                    <span style="color:var(--materio-text-muted);">—</span>
                                @endif
                            </td>

                            {{-- VALOR MARÇO --}}
                            <td style="padding:12px 8px; text-align:right; white-space:nowrap; font-weight:700; color:{{ $cliente->valor_marco_pago > 0 ? '#166534' : 'var(--materio-text-muted)' }};">
                                @if($cliente->valor_marco_pago > 0)
                                    R$ {{ number_format($cliente->valor_marco_pago, 2, ',', '.') }}
                                @else
                                    <span style="font-size:0.75rem;">sem pagto</span>
                                @endif
                            </td>

                            {{-- TIPO COMISSÃO --}}
                            <td style="padding:12px 8px; text-align:center;">
                                <span style="font-size:0.72rem; font-weight:700; color:{{ $comissaoTipoLabel['color'] }}; white-space:nowrap;">
                                    {{ $comissaoTipoLabel['label'] }}
                                </span>
                            </td>

                            {{-- COMISSÃO CALCULADA --}}
                            <td style="padding:12px 8px; text-align:right;">
                                <div id="comissao-{{ $cliente->id }}" style="font-weight:800; font-size:0.9rem; color:{{ ($cliente->comissao_vendedor_calculada ?? 0) > 0 ? '#166534' : 'var(--materio-text-muted)' }};">
                                    @if(($cliente->comissao_vendedor_calculada ?? 0) > 0)
                                        R$ {{ number_format($cliente->comissao_vendedor_calculada, 2, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </div>
                                @if(($cliente->comissao_gestor_calculada ?? 0) > 0)
                                <div style="font-size:0.68rem; color:#2563eb;">Gestor: R$ {{ number_format($cliente->comissao_gestor_calculada, 2, ',', '.') }}</div>
                                @endif
                            </td>

                            {{-- VENDEDOR (DROPDOWN INLINE) --}}
                            <td style="padding:10px 16px; min-width:200px;">
                                <select
                                    class="materio-select vendedor-select"
                                    data-id="{{ $cliente->id }}"
                                    onchange="atribuirVendedor(this)"
                                    style="width:100%; padding:6px 10px; font-size:0.78rem;"
                                >
                                    <option value="">— Atribuir vendedor —</option>
                                    @foreach($vendedores as $v)
                                        <option value="{{ $v->id }}" {{ $cliente->vendedor_id == $v->id ? 'selected' : '' }}>
                                            {{ $v->user->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(($cliente->comissao_tipo ?? '') === 'inicial_antecipada' && !$cliente->vendedor_id)
                                <div style="font-size:0.62rem; color:#dc2626; margin-top:3px;">⚠️ Comissão antecipada — atribua o vendedor</div>
                                @endif
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
    <div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:16px; padding:14px; background:var(--materio-surface); border-radius:10px; border:1px solid var(--materio-border);">
        <div style="font-size:0.75rem; color:var(--materio-text-muted); font-weight:600;">LEGENDA:</div>
        <div style="font-size:0.72rem; color:var(--materio-text-muted);">
            <span style="background:#fbbf24; color:#78350f; padding:1px 5px; border-radius:8px; font-weight:800; font-size:0.65rem;">DUP</span> = mesmo CPF cadastrado 2+ vezes no Asaas
        </div>
        <div style="font-size:0.72rem; color:#7c3aed; font-weight:600;">🆕 1ª Venda = 1º pagamento em Março → comissão_inicial</div>
        <div style="font-size:0.72rem; color:#dc2626; font-weight:600;">💳 Antecipado = parcelado em Março → inicial + todas recorrências antecipadas</div>
        <div style="font-size:0.72rem; color:#0284c7; font-weight:600;">🔄 Recorrência = já pagava antes de Março → comissao_recorrencia</div>
        <div style="font-size:0.72rem; color:var(--materio-text-muted);">
            ⚡ Comissões são zeradas automaticamente no <strong>dia 2 de cada mês</strong>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';

async function sincronizarAsaas() {
    const btn = document.getElementById('btn-sincronizar');
    const progress = document.getElementById('sync-progress');
    const statusMsg = document.getElementById('sync-status-msg');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
    progress.style.display = 'block';

    try {
        const response = await fetch('{{ route("master.clientes-asaas.sincronizar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            statusMsg.textContent = data.message;
            statusMsg.style.color = '#166534';
            setTimeout(() => window.location.reload(), 2000);
        } else {
            statusMsg.textContent = data.message;
            statusMsg.style.color = '#991b1b';
        }
    } catch (e) {
        statusMsg.textContent = 'Erro de conexão: ' + e.message;
        statusMsg.style.color = '#991b1b';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-rotate"></i> Sincronizar com Asaas';
    }
}

async function atribuirVendedor(select) {
    const id = select.getAttribute('data-id');
    const vendedorId = select.value;
    const row = select.closest('tr');

    // Feedback visual
    select.style.borderColor = '#f97316';
    select.disabled = true;

    try {
        const response = await fetch(`{{ url('master/clientes-asaas') }}/${id}/vendedor`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ vendedor_id: vendedorId || null }),
        });

        const data = await response.json();

        if (data.success) {
            // Atualizar comissão na mesma linha
            const comissaoEl = document.getElementById('comissao-' + id);
            if (comissaoEl) {
                if (vendedorId && data.comissao_vendedor !== 'R$ 0,00') {
                    comissaoEl.innerHTML = `<strong style="color:#166534;">${data.comissao_vendedor}</strong>`;
                } else {
                    comissaoEl.innerHTML = '<span style="color:var(--materio-text-muted);">—</span>';
                }
            }
            select.style.borderColor = '#22c55e';
            setTimeout(() => { select.style.borderColor = ''; }, 2000);
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível atribuir o vendedor.'));
            select.style.borderColor = '#ef4444';
        }
    } catch (e) {
        alert('Erro de conexão: ' + e.message);
    } finally {
        select.disabled = false;
    }
}
</script>
@endsection
