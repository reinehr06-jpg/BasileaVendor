@extends('layouts.app')
@section('title', 'Comissões Legadas')

@section('content')
<style>
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px;
        text-align: center;
    }
    .stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .stat-card .label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
            <h3 class="page-title">Comissões Legadas</h3>
        </div>
        <div class="col-auto">
            <form method="POST" action="{{ route('master.legados.generateRecurring') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Gerar comissões recorrentes do mês atual para todos os clientes legados ativos?')">
                    <i class="fas fa-magic"></i> Gerar Recorrências
                </button>
            </form>
            <a href="{{ route('master.legados.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="number text-warning">R$ {{ number_format($summary['pending_amount'], 2, ',', '.') }}</div>
            <div class="label">Pendente</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="number text-success">R$ {{ number_format($summary['paid_this_month'], 2, ',', '.') }}</div>
            <div class="label">Pago este Mês</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="number">{{ $summary['total_pending_commissions'] }}</div>
            <div class="label">Total Pendente</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="number">{{ $summary['total_vendedores_with_pending'] }}</div>
            <div class="label">Vendedores com Pendência</div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('master.legados.markMultiplePaid') }}" id="commissionsForm">
    @csrf
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title">Lista de Comissões ({{ $commissions->total() }})</h5>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-success btn-sm" onclick="markSelectedAsPaid()" {{ $commissions->total() == 0 ? 'disabled' : '' }}>
                        <i class="fas fa-check"></i> Marcar Selecionadas como Pagas
                    </button>
                </div>
            </div>
        </div>
        <div class="card-header">
            <div class="row g-3">
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos Status</option>
                        <option value="GENERATED" {{ request('status') == 'GENERATED' ? 'selected' : '' }}>Pendente</option>
                        <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Pago</option>
                        <option value="BLOCKED" {{ request('status') == 'BLOCKED' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="vendedor_id" class="form-select form-select-sm">
                        <option value="">Todos Vendedores</option>
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}" {{ request('vendedor_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->user->name ?? 'Vendedor #' . $v->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="commission_type" class="form-select form-select-sm">
                        <option value="">Todos Tipos</option>
                        <option value="OLD_SALE" {{ request('commission_type') == 'OLD_SALE' ? 'selected' : '' }}>Venda Antiga</option>
                        <option value="RECURRING" {{ request('commission_type') == 'RECURRING' ? 'selected' : '' }}>Recorrente</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="month" name="reference_month" class="form-control form-control-sm" value="{{ request('reference_month') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                        </th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Tipo</th>
                        <th>Competência</th>
                        <th>Base</th>
                        <th>Comissão Vendedor</th>
                        <th>Comissão Gerente</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                    <tr>
                        <td>
                            @if($commission->status === 'GENERATED')
                            <input type="checkbox" name="commission_ids[]" value="{{ $commission->id }}" class="commission-checkbox">
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $commission->cliente->nome ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $commission->cliente->documento ?? '' }}</div>
                        </td>
                        <td>
                            {{ $commission->vendedor->user->name ?? 'Vendedor #' . $commission->vendedor_id }}
                            @if($commission->gestor)
                                <div class="small text-muted">Ger: {{ $commission->gestor->name }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $commission->commission_type === 'OLD_SALE' ? 'warning' : 'info' }}">
                                {{ $commission->commission_type === 'OLD_SALE' ? 'Venda Antiga' : 'Recorrente' }}
                            </span>
                        </td>
                        <td>{{ $commission->reference_month }}</td>
                        <td>R$ {{ number_format($commission->base_amount, 2, ',', '.') }}</td>
                        <td class="text-success">R$ {{ number_format($commission->seller_commission_amount, 2, ',', '.') }}</td>
                        <td class="text-info">R$ {{ number_format($commission->gestor_commission_amount ?? 0, 2, ',', '.') }}</td>
                        <td>
                            <span class="status-badge {{ $commission->status_color }}">
                                {{ $commission->status }}
                            </span>
                        </td>
                        <td>
                            @if($commission->status === 'GENERATED')
                            <form method="POST" action="{{ route('master.legados.commission.paid', $commission->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" title="Marcar como pago">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('master.legados.commission.block', $commission->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" title="Bloquear">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">Nenhuma comissão encontrada</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $commissions->links() }}
        </div>
    </div>
</form>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.commission-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function markSelectedAsPaid() {
    const checked = document.querySelectorAll('.commission-checkbox:checked');
    if (checked.length === 0) {
        alert('Selecione pelo menos uma comissão');
        return;
    }
    if (confirm(`Marcar ${checked.length} comissão(es) como paga(s)?`)) {
        document.getElementById('commissionsForm').submit();
    }
}
</script>
@endsection
