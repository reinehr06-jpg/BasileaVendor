@extends('layouts.app')
@section('title', 'Detalhes Cliente Legado')

@section('content')
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    .info-item {
        padding: 12px;
        background: var(--bg);
        border-radius: var(--radius-sm);
    }
    .info-item label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
    }
    .info-item .value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-badge.success { background: rgba(34,197,94,0.1); color: #16a34a; }
    .status-badge.warning { background: rgba(234,179,8,0.1); color: #ca8a04; }
    .status-badge.danger { background: rgba(239,68,68,0.1); color: #dc2626; }
    .status-badge.info { background: rgba(59,130,246,0.1); color: #2563eb; }
    .status-badge.secondary { background: rgba(107,114,128,0.1); color: #6b7280; }
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">{{ $legado->nome ?? 'Cliente Legado' }}</h3>
        </div>
        <div class="col-auto">
            <a href="{{ route('master.legados.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <form method="POST" action="{{ route('master.legados.sync', $legado->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-sync"></i> Sincronizar
                </button>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Dados do Cliente</h5>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nome</label>
                        <div class="value">{{ $legado->nome ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <label>CPF/CNPJ</label>
                        <div class="value">{{ $legado->documento ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <div class="value">{{ $legado->email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <label>Telefone</label>
                        <div class="value">{{ $legado->telefone ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <label>Status no Asaas</label>
                        <div class="value">
                            <span class="status-badge {{ $legado->customer_status_color }}">
                                {{ $legado->customer_status ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Recorrência</label>
                        <div class="value">
                            <span class="status-badge {{ $legado->subscription_status === 'ACTIVE' ? 'success' : 'secondary' }}">
                                {{ $legado->subscription_status ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Status Importação</label>
                        <div class="value">
                            <span class="status-badge {{ $legado->status_color }}">
                                {{ $legado->import_status }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>ID Asaas</label>
                        <div class="value">{{ $legado->asaas_customer_id ?? 'N/A' }}</div>
                    </div>
                </div>

                @if($legado->payments()->where('total_installments', '>', 1)->exists())
                    @php 
                        $inst = $legado->payments()->where('total_installments', '>', 1)->orderByDesc('installment_number')->first();
                        $paidCount = $legado->payments()->whereIn('status', ['RECEIVED', 'CONFIRMED'])->count();
                        $totalInst = ($inst && $inst->total_installments > 0) ? $inst->total_installments : 1;
                        $percent = ($paidCount / $totalInst) * 100;
                    @endphp
                    <div class="mt-4 p-3 border rounded bg-light">
                        <label class="small fw-bold text-uppercase text-muted d-block mb-2">Progresso do Parcelamento ({{ $totalInst }}x)</label>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1" style="height: 12px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="ms-3 fw-bold">{{ $paidCount }}/{{ $totalInst }}</span>
                        </div>
                        <div class="mt-1 small text-muted">
                            Total já pago: <strong>R$ {{ number_format($legado->payments()->whereIn('status', ['RECEIVED', 'CONFIRMED'])->sum('value'), 2, ',', '.') }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Vínculo Comercial</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('master.legados.update', $legado->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Vendedor</label>
                            <select name="vendedor_id" class="form-select form-select-sm">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\Vendedor::where('status', 'ativo')->with('usuario')->get() as $v)
                                    <option value="{{ $v->id }}" {{ $legado->vendedor_id == $v->id ? 'selected' : '' }}>
                                        {{ $v->user->name ?? 'Vendedor #' . $v->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="info-item">
                            <label>Gerente</label>
                            <select name="gestor_id" class="form-select form-select-sm">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\Vendedor::where('is_gestor', true)->with('usuario')->get() as $g)
                                    <option value="{{ $g->user_id }}" {{ $legado->gestor_id == $g->user_id ? 'selected' : '' }}>
                                        {{ $g->user->name ?? 'Gestor #' . $g->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="info-item">
                            <label>Plano</label>
                            <select name="plano_id" class="form-select form-select-sm">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\Plano::all() as $p)
                                    <option value="{{ $p->id }}" {{ $legado->plano_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="info-item">
                            <label>Valor Recorrente</label>
                            <input type="number" name="plano_valor_recorrente" class="form-control form-control-sm" step="0.01" value="{{ $legado->plano_valor_recorrente }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Cobranças ({{ $legado->payments->count() }})</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Parcela</th>
                            <th>Método</th>
                            <th>Status</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($legado->payments as $payment)
                        <tr>
                            <td>R$ {{ number_format($payment->value, 2, ',', '.') }}</td>
                            <td>{{ $payment->due_date?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td>{{ $payment->paid_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($payment->installment_number)
                                    <span class="badge bg-light text-dark">{{ $payment->installment_number }}/{{ $payment->total_installments }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td><small>{{ $payment->billing_type }} {{ $payment->payment_method ? "($payment->payment_method)" : "" }}</small></td>
                            <td>
                                <span class="status-badge {{ $payment->status_color }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>{{ $payment->is_recurring ? 'Recorrente' : 'Avulsa' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhuma cobrança importada</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Comissões</h5>
            </div>
            <div class="card-body">
                @php 
                    $marchComm = $legado->commissions()->where('reference_month', '2026-03')->first();
                @endphp
                
                @if($marchComm)
                <div class="alert alert-info border-info mb-4">
                    <h6 class="alert-heading fw-bold mb-1"><i class="fas fa-calendar-check"></i> Comissão de Março/2026</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $marchComm->status === 'PAID' ? 'success' : 'primary' }} mb-1">
                                {{ $marchComm->status === 'PAID' ? 'PAGO' : 'GERADA' }}
                            </span>
                            <div class="fs-4 fw-bold">R$ {{ number_format($marchComm->seller_commission_amount, 2, ',', '.') }}</div>
                        </div>
                        <i class="fas fa-hand-holding-usd fa-2x opacity-25"></i>
                    </div>
                </div>
                @endif

                <h6 class="small fw-bold text-muted text-uppercase mb-3">Histórico de Comissões</h6>
                @forelse($legado->commissions as $commission)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-{{ $commission->status === 'PAID' ? 'success' : ($commission->status === 'GENERATED' ? 'primary' : 'warning') }}">
                            {{ $commission->commission_type }}
                        </span>
                        <span class="text-muted small">{{ $commission->reference_month }}</span>
                    </div>
                    <div class="mt-2">
                        <div class="small">Vendedor: <strong>R$ {{ number_format($commission->seller_commission_amount, 2, ',', '.') }}</strong></div>
                        @if($commission->gestor_commission_amount > 0)
                            <div class="small">Gerente: <strong>R$ {{ number_format($commission->gestor_commission_amount, 2, ',', '.') }}</strong></div>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Nenhuma comissão gerada</p>
                @endforelse
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Logs</h5>
            </div>
            <div class="card-body">
                <div class="small">
                    <div><strong>Criado em:</strong> {{ $legado->created_at?->format('d/m/Y H:i') }}</div>
                    <div><strong>Última sync:</strong> {{ $legado->last_sync_at?->format('d/m/Y H:i') ?? 'Nunca' }}</div>
                    <div><strong>Notas:</strong> {{ $legado->notes ?? 'Sem observações' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
