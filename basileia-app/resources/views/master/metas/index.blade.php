@extends('layouts.app')
@section('title', 'Metas Comerciais')

@section('content')
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }
    .animate-in:nth-child(5) { animation-delay: 0.15s; }
    .animate-in:nth-child(6) { animation-delay: 0.18s; }
    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .card.highlight { background: var(--primary); border-color: var(--primary); }
    .card.highlight .value, .card.highlight .label, .card.highlight .icon { color: white !important; }
    .progress-bar-bg { background: var(--bg); height: 8px; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 4px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
    .progress-info { display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 6px; font-weight: 700; }
    
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
    .badge-forma { display: inline-flex; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }
    .badge-forma.recorrente { background: #d1fae5; color: #065f46; }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
</style>

<div class="page-header animate-in">
    <div>
        <h2><i class="fas fa-flag" style="margin-right: 8px;"></i>Metas da Operação</h2>
        <p>Acompanhamento de objetivos e performance por vendedor</p>
    </div>
    <button class="btn btn-primary" onclick="openMetaModal('create')">
        <i class="fas fa-plus"></i> Nova Meta
    </button>
</div>

<!-- Filters -->
<div class="filters-bar animate-in">
    <form action="{{ route('master.metas') }}" method="GET" style="display: contents;">
        <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
            <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Mês de Referência</label>
            <input type="month" name="mes" value="{{ $mes }}" class="form-control" onchange="this.form.submit()">
        </div>
        <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
            <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);">Vendedor</label>
            <select name="vendedor_id" class="form-control" onchange="this.form.submit()">
                <option value="">Todos os Vendedores</option>
                @foreach($vendedores as $v)
                    <option value="{{ $v->id }}" {{ $vendedorId == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="align-self: flex-end;">Filtrar</button>
        <a href="{{ route('master.metas') }}" class="btn btn-ghost btn-sm" style="align-self: flex-end;">Limpar</a>
    </form>
</div>

<!-- Summary Cards -->
<div class="summary-grid">
    <div class="stat-card animate-in">
        <div class="stat-icon primary"><i class="fas fa-crosshairs"></i></div>
        <div class="stat-value">{{ $resumo['total_metas'] }}</div>
        <div class="stat-label">Total de Metas</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon success"><i class="fas fa-trophy"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ $resumo['metas_batidas'] }}</div>
        <div class="stat-label">Metas Batidas</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon danger"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $resumo['metas_abaixo'] }}</div>
        <div class="stat-label">Abaixo da Meta</div>
    </div>
    <div class="card animate-in highlight">
        <span class="icon">💰</span>
        <span class="value">R$ {{ number_format($resumo['valor_total_meta'], 0, ',', '.') }}</span>
        <span class="label">Volume Esperado</span>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon success"><i class="fas fa-check"></i></div>
        <div class="stat-value">R$ {{ number_format($resumo['valor_total_realizado'], 0, ',', '.') }}</div>
        <div class="stat-label">Volume Realizado</div>
    </div>
    <div class="stat-card animate-in">
        <div class="stat-icon info"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-value">{{ $resumo['percentual_medio'] }}%</div>
        <div class="stat-label">Atingimento Médio</div>
    </div>
</div>

<!-- Table -->
<div class="table-container animate-in">
    @if($metas->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>Mês Ref.</th>
                <th>Meta</th>
                <th>Vendido</th>
                <th>Recebido</th>
                <th>Clientes</th>
                <th>Atingimento</th>
                <th>Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($metas as $m)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $m->vendedor->user->name ?? 'N/A' }}</div>
                </td>
                <td style="font-weight: 600;">{{ \Carbon\Carbon::parse($m->mes_referencia)->translatedFormat('M/Y') }}</td>
                <td style="font-weight: 700;">R$ {{ number_format($m->valor_meta, 2, ',', '.') }}</td>
                <td style="color: var(--text-secondary);">R$ {{ number_format($m->valor_vendido, 2, ',', '.') }}</td>
                <td style="font-weight: 700; color: var(--primary);">R$ {{ number_format($m->valor_recebido, 2, ',', '.') }}</td>
                <td style="text-align: center; font-weight: 700;">{{ $m->clientes_ativos }}</td>
                <td style="width: 180px;">
                    <div class="progress-info">
                        <span>{{ $m->percentual }}%</span>
                        <span style="font-size: 0.65rem;">{{ $m->percentual >= 100 ? 'META BATIDA' : 'EM CURSO' }}</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill {{ $m->percentual >= 100 ? 'bg-success' : ($m->percentual >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ min($m->percentual, 100) }}%; background: {{ $m->percentual >= 100 ? 'var(--success)' : ($m->percentual >= 50 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-{{ $m->percentual >= 100 ? 'success' : ($m->percentual >= 50 ? 'warning' : 'danger') }}">{{ $m->status }}</span>
                </td>
                <td style="text-align: right;">
                    <button class="btn btn-ghost btn-sm" onclick="openMetaModal('edit', {{ json_encode($m) }})"><i class="fas fa-pen"></i></button>
                    <form action="{{ route('master.metas.destroy', $m->id) }}" method="POST" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Excluir Meta', message: 'Deseja realmente excluir esta meta?', type: 'danger', confirmText: 'Excluir', onConfirm: () => this.submit()});">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
        <h3>Nenhuma meta encontrada</h3>
        <p>Não há metas cadastradas para os critérios selecionados.</p>
    </div>
    @endif
</div>

<!-- SEÇÃO: Vendas por Vendedor -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3>👤 Vendas por Vendedor</h3>
    </div>
    <div class="section-body">
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
                    <th class="text-right">Meta</th>
                    <th>% Meta</th>
                </tr>
            </thead>
            <tbody>
                @php
                $vendasPorVendedor = \App\Models\Venda::whereHas('vendedor', function($q) use ($vendedorId) {
                    if($vendedorId) $q->where('id', $vendedorId);
                })
                ->whereNotNull('vendedor_id')
                ->whereMonth('created_at', '=', \Carbon\Carbon::parse($mes)->month)
                ->whereYear('created_at', '=', \Carbon\Carbon::parse($mes)->year)
                ->get()
                ->groupBy('vendedor_id');
                @endphp
                @foreach($vendasPorVendedor as $vendedorId => $vendas)
                @php
                $vendedor = \App\Models\Vendedor::with('user')->find($vendedorId);
                $valorVendido = $vendas->filter(function($v) {
                    $s = strtoupper($v->getStatusEfetivo());
                    return !in_array($s, ['ESTORNADO', 'CANCELADO', 'EXPIRADO', 'VENCIDO']);
                })->sum('valor');
                $valorRecebido = $vendas->filter(function($v) {
                    $s = strtoupper($v->getStatusEfetivo());
                    return in_array($s, ['PAGO', 'RECEIVED', 'CONFIRMED']);
                })->sum('valor');
                $meta = \App\Models\Meta::where('vendedor_id', $vendedorId)
                    ->where('mes_referencia', $mes)
                    ->first();
                $metaValor = $meta ? $meta->valor_meta : 0;
                $percentualMeta = $metaValor > 0 ? round(($valorVendido / $metaValor) * 100) : 0;
                @endphp
                <tr>
                    <td class="font-bold">{{ $vendedor->user->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $vendas->count() }}</td>
                    <td class="text-right">R$ {{ number_format($valorVendido, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($valorRecebido, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($vendas->sum('comissao'), 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vendas->where('status', 'Pago')->count() }}</td>
                    <td class="text-center">0</td>
                    <td class="text-right">R$ {{ number_format($metaValor, 2, ',', '.') }}</td>
                    <td style="min-width: 120px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="progress-bar" style="flex: 1;">
                                <div class="fill {{ $percentualMeta >= 100 ? 'green' : ($percentualMeta >= 50 ? 'yellow' : 'red') }}" style="width: {{ min($percentualMeta, 100) }}%;"></div>
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 700; color: {{ $percentualMeta >= 100 ? '#16a34a' : ($percentualMeta >= 50 ? '#ca8a04' : '#dc2626') }};">{{ $percentualMeta }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- SEÇÃO: Formas de Pagamento -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3>💳 Formas de Pagamento</h3>
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
            <tbody>
                @php
                $vendasQuery = \App\Models\Venda::whereNotNull('vendedor_id')
                    ->whereMonth('created_at', '=', \Carbon\Carbon::parse($mes)->month)
                    ->whereYear('created_at', '=', \Carbon\Carbon::parse($mes)->year);
                $vendasAll = $vendasQuery->get()
                    ->filter(function($v) {
                        $s = strtoupper($v->getStatusEfetivo());
                        return !in_array($s, ['ESTORNADO', 'CANCELADO', 'EXPIRADO', 'VENCIDO']);
                    });
                    
                $pixCount = $vendasAll->where('forma_pagamento', 'pix')->count();
                $pixValor = $vendasAll->where('forma_pagamento', 'pix')->sum('valor');
                $boletoCount = $vendasAll->where('forma_pagamento', 'boleto')->count();
                $boletoValor = $vendasAll->where('forma_pagamento', 'boleto')->sum('valor');
                $cartaoCount = $vendasAll->where('forma_pagamento', 'cartao')->count();
                $cartaoValor = $vendasAll->where('forma_pagamento', 'cartao')->sum('valor');
                $recorrenteCount = $vendasAll->where('forma_pagamento', 'recorrente')->count();
                $recorrenteValor = $vendasAll->where('forma_pagamento', 'recorrente')->sum('valor');
                $totalCount = $vendasAll->count();
                @endphp
                @if($pixCount > 0)
                <tr>
                    <td><span class="badge-forma pix">⚡ PIX</span></td>
                    <td class="text-center font-bold">{{ $pixCount }}</td>
                    <td class="text-right">R$ {{ number_format($pixValor, 2, ',', '.') }}</td>
                </tr>
                @endif
                @if($boletoCount > 0)
                <tr>
                    <td><span class="badge-forma boleto">📄 Boleto</span></td>
                    <td class="text-center font-bold">{{ $boletoCount }}</td>
                    <td class="text-right">R$ {{ number_format($boletoValor, 2, ',', '.') }}</td>
                </tr>
                @endif
                @if($cartaoCount > 0)
                <tr>
                    <td><span class="badge-forma cartao">💳 Cartão</span></td>
                    <td class="text-center font-bold">{{ $cartaoCount }}</td>
                    <td class="text-right">R$ {{ number_format($cartaoValor, 2, ',', '.') }}</td>
                </tr>
                @endif
                @if($recorrenteCount > 0)
                <tr>
                    <td><span class="badge-forma recorrente">🔄 Recorrente</span></td>
                    <td class="text-center font-bold">{{ $recorrenteCount }}</td>
                    <td class="text-right">R$ {{ number_format($recorrenteValor, 2, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="metaModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="metaModalTitle"><i class="fas fa-flag" style="margin-right: 8px;"></i>Cadastrar Meta</h2>
            <button class="modal-close" onclick="BasileiaModal.close('metaModal')">&times;</button>
        </div>
        <form id="metaForm" method="POST" class="modal-body">
            @csrf
            <input type="hidden" name="_method" id="metaFormMethod" value="POST">
            <div class="form-group" id="metaVendedorGroup">
                <label>Vendedor <span class="required">*</span></label>
                <select name="vendedor_id" id="metaFormVendedor" class="form-control" required>
                    <option value="">Selecione...</option>
                    @foreach($vendedores as $v)
                        <option value="{{ $v->id }}">{{ $v->user->name ?? 'N/A' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group" id="metaMesGroup">
                    <label>Mês de Referência <span class="required">*</span></label>
                    <input type="month" name="mes_referencia" id="metaFormMes" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Valor da Meta (R$) <span class="required">*</span></label>
                    <input type="number" step="0.01" name="valor_meta" id="metaFormValor" class="form-control" required placeholder="0,00">
                </div>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="metaFormStatus" class="form-control">
                    <option value="não iniciada">Não iniciada</option>
                    <option value="em andamento">Em andamento</option>
                    <option value="atingida">Atingida</option>
                    <option value="não atingida">Não atingida</option>
                    <option value="superada">Superada</option>
                </select>
            </div>
            <div class="form-group">
                <label>Observação</label>
                <textarea name="observacao" id="metaFormObs" class="form-control" rows="3" placeholder="Informações adicionais..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('metaModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Salvar Meta</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openMetaModal(mode, data = null) {
        const form = document.getElementById('metaForm');
        if (mode === 'create') {
            document.getElementById('metaModalTitle').innerHTML = '<i class="fas fa-flag" style="margin-right: 8px;"></i>Cadastrar Meta';
            document.getElementById('metaFormMethod').value = 'POST';
            form.action = "{{ route('master.metas.store') }}";
            form.reset();
            document.getElementById('metaVendedorGroup').style.display = 'block';
            document.getElementById('metaMesGroup').style.display = 'block';
        } else {
            document.getElementById('metaModalTitle').innerHTML = '<i class="fas fa-pen" style="margin-right: 8px;"></i>Editar Meta';
            document.getElementById('metaFormMethod').value = 'PUT';
            form.action = '/master/metas/' + data.id;
            document.getElementById('metaFormVendedor').value = data.vendedor_id;
            document.getElementById('metaFormMes').value = data.mes_referencia;
            document.getElementById('metaFormValor').value = data.valor_meta;
            document.getElementById('metaFormStatus').value = data.status;
            document.getElementById('metaFormObs').value = data.observacao || '';
            document.getElementById('metaVendedorGroup').style.display = 'none';
            document.getElementById('metaMesGroup').style.display = 'none';
        }
        BasileiaModal.open('metaModal');
    }
</script>
@endsection
