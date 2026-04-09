@extends('layouts.app')

@push('css')
<style>
.audit-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); border: 1px solid var(--materio-border); margin-bottom: 24px; }
.audit-month { font-size: 1.15rem; font-weight: 800; color: var(--materio-text-main); margin-bottom: 12px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; display: flex; justify-content: space-between; }
.audit-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.audit-table th, .audit-table td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
.audit-table th { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; background: #f8fafc; }
.audit-table td { font-size: 0.9rem; color: #334155; }
.audit-table tr:hover td { background: #f8fafc; }
.val-sys { font-weight: 800; color: #166534; }
.val-input { width: 120px; padding: 6px 10px; border: 2px solid #e2e8f0; border-radius: 6px; font-weight: 700; text-align: right; }
.val-input:focus { outline: none; border-color: #4C1D95; }
.diff-val { font-weight: 800; }
.diff-zero { color: #94a3b8; }
.diff-pos { color: #166534; }
.diff-neg { color: #dc2626; }
.badge { padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.badge-ini { background: #dbeafe; color: #1e3a8a; }
.badge-rec { background: #f3e8ff; color: #581c87; }
</style>
@endpush

@section('content')
<div class="materio-container">
    <x-page-hero title="Auditoria Retroativa" subtitle="Confronte as comissões calculadas contra sua planilha antiga" icon="fas fa-file-invoice-dollar" />

    <div class="audit-card">
        <form method="GET" action="{{ route('master.clientes-asaas.auditoria') }}" style="display: flex; gap: 16px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Filtrar Vendedor</label>
                <select name="vendedor_id" class="form-control" style="padding: 12px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 0.95rem; width: 100%;" onchange="this.form.submit()">
                    <option value="">— Selecione um Vendedor —</option>
                    @foreach($vendedores as $v)
                    <option value="{{ $v->id }}" {{ $vendedorId == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'Sem Nome' }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('master.clientes-asaas.index') }}" class="btn btn-outline" style="padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700;">Voltar</a>
        </form>
    </div>

    @if($vendedorId)
        @if(empty($dadosTabela))
            <div class="audit-card" style="text-align: center; padding: 40px;">
                <i class="fas fa-box-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                <h3 style="color: #64748b; font-weight: 700;">Nenhum rendimento retroativo</h3>
                <p style="color: #94a3b8;">Não há comissões calculadas nos meses passados para este vendedor.</p>
            </div>
        @else
            @foreach($dadosTabela as $mes => $clientes)
            <div class="audit-card">
                <div class="audit-month">
                    <span>Mês Ref: {{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->translatedFormat('F / Y') }}</span>
                    <span style="font-size:0.9rem; color:#64748b; background:#f1f5f9; padding:4px 12px; border-radius:20px;">
                        Total Sistema: R$ <span id="total_sys_{{ str_replace('-', '', $mes) }}">0,00</span>
                    </span>
                </div>
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Início</th>
                            <th>Tipo</th>
                            <th style="text-align: right;">Cálculo Sistema</th>
                            <th style="text-align: right;">Valor Planilha</th>
                            <th style="text-align: right;">Diferença</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalSys = 0; @endphp
                        @foreach($clientes as $idx => $c)
                        @php $totalSys += $c['comissao_calculada']; @endphp
                        <tr class="audit-row">
                            <td>
                                <strong>{{ $c['cliente_nome'] }}</strong>
                                <div style="font-size: 0.75rem; color: #94a3b8;">{{ $c['cliente_doc'] }}</div>
                            </td>
                            <td>{{ $c['data_inicio'] }}</td>
                            <td>
                                @if($c['tipo'] === 'inicial_antecipada')
                                    <span class="badge badge-ini">Antecipada 100%</span>
                                @elseif($c['tipo'] === 'inicial')
                                    <span class="badge {{ $c['parcela_numero'] == 1 ? 'badge-ini' : 'badge-rec' }}">{{ $c['parcela_numero'] == 1 ? '1ª Mensalidade' : 'Recorrência' }}</span>
                                @else
                                    <span class="badge badge-rec">Recorrência</span>
                                @endif
                                <div style="font-size:0.7rem; color:#94a3b8; margin-top:4px;">Parcela #{{ $c['parcela_numero'] }}</div>
                            </td>
                            <td style="text-align: right;" class="val-sys" data-sys="{{ $c['comissao_calculada'] }}">
                                R$ {{ number_format($c['comissao_calculada'], 2, ',', '.') }}
                            </td>
                            <td style="text-align: right;">
                                <input type="number" step="0.01" class="val-input plan-input" value="" placeholder="0.00" oninput="calcularDif(this)">
                            </td>
                            <td style="text-align: right;" class="diff-val diff-zero">
                                Aguardando...
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <script>
                    document.getElementById('total_sys_{{ str_replace('-', '', $mes) }}').textContent = '{{ number_format($totalSys, 2, ',', '.') }}';
                </script>
            </div>
            @endforeach
        @endif
    @else
        <div class="audit-card" style="text-align: center; padding: 40px;">
            <i class="fas fa-hand-pointer" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 16px;"></i>
            <h3 style="color: #64748b; font-weight: 700;">Selecione um Vendedor</h3>
            <p style="color: #94a3b8;">Para visualizar o histórico e o comparativo.</p>
        </div>
    @endif
</div>

@push('js')
<script>
function calcularDif(inputEl) {
    const row = inputEl.closest('tr');
    const sysVal = parseFloat(row.querySelector('.val-sys').dataset.sys) || 0;
    const planVal = parseFloat(inputEl.value);
    
    const difEl = row.querySelector('.diff-val');
    
    if (isNaN(planVal)) {
        difEl.textContent = 'Aguardando...';
        difEl.className = 'diff-val diff-zero';
        return;
    }
    
    const diferenca = sysVal - planVal;
    
    // Se diferença for 0, bateu perfeito
    if (Math.abs(diferenca) < 0.01) {
        difEl.innerHTML = '<i class="fas fa-check-circle"></i> Exato';
        difEl.className = 'diff-val diff-pos';
    } else if (diferenca > 0) {
        // Sistema pagou a mais
        difEl.textContent = 'Sis +R$ ' + diferenca.toLocaleString('pt-BR', {minimumFractionDigits:2});
        difEl.className = 'diff-val diff-neg';
    } else {
        // Planilha maior que o sistema
        difEl.textContent = 'Plan +R$ ' + Math.abs(diferenca).toLocaleString('pt-BR', {minimumFractionDigits:2});
        difEl.className = 'diff-val diff-neg'; // Ambas diferenças sao alertas
    }
}
</script>
@endpush
@endsection
