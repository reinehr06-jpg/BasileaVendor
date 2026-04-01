@extends('layouts.master')

@section('title', 'Assinaturas')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">
        <i class="fas fa-sync-alt me-2"></i>Assinaturas e Cartões Salvos
    </h1>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.assinaturas') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar cliente</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nome ou e-mail...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativa</option>
                        <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Pausada</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" name="billing_type">
                        <option value="">Todos</option>
                        <option value="monthly" {{ request('billing_type') == 'monthly' ? 'selected' : '' }}>Mensal</option>
                        <option value="yearly" {{ request('billing_type') == 'yearly' ? 'selected' : '' }}>Anual</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Assinaturas ({{ $subscriptions->total() }} registros)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Plano</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Próximo Pagamento</th>
                            <th>Cartão</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                        <tr>
                            <td>
                                <strong>{{ $sub->lead->name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $sub->lead->email ?? '' }}</small>
                            </td>
                            <td>{{ $sub->offer->name ?? 'N/A' }}</td>
                            <td>
                                @if($sub->isYearly())
                                    <span class="badge bg-info">
                                        Anual<br>
                                        <small>Restam {{ $sub->remaining_months }} meses</small>
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Mensal</span>
                                @endif
                            </td>
                            <td>
                                @if($sub->offer && $sub->offer->currency_symbol)
                                    {{ $sub->offer->currency_symbol }} {{ number_format($sub->amount, 2, ',', '.') }}
                                @else
                                    R$ {{ number_format($sub->amount, 2, ',', '.') }}
                                @endif
                            </td>
                            <td>
                                @if($sub->next_billing_date)
                                    <strong>{{ $sub->next_billing_date->format('d/m/Y') }}</strong><br>
                                    @if($sub->isYearly())
                                        <small class="text-muted">
                                            @php
                                                $now = now();
                                                $diff = $now->diffInDays($sub->next_billing_date, false);
                                            @endphp
                                            @if($diff > 0)
                                                em {{ $diff }} dias
                                            @elseif($diff === 0)
                                                <span class="text-warning">Hoje!</span>
                                            @else
                                                <span class="text-danger">{{ abs($diff) }} dias atrasado</span>
                                            @endif
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sub->card)
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="{{ $sub->card->brand_icon }}" style="color: {{ $sub->card->brand_color }}; font-size: 1.5rem;"></i>
                                        <div>
                                            <strong>•••• {{ $sub->card->last4 }}</strong><br>
                                            <small class="text-muted">{{ $sub->card->expiry_month }}/{{ $sub->card->expiry_year }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Sem cartão</span>
                                @endif
                            </td>
                            <td>
                                @if($sub->status === 'active')
                                    <span class="badge bg-success"><i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>Ativa</span>
                                @elseif($sub->status === 'paused')
                                    <span class="badge bg-warning"><i class="fas fa-pause me-1"></i>Pausada</span>
                                @elseif($sub->status === 'cancelled')
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Cancelada</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Expirada</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCard({{ $sub->id }})" title="Ver cartão">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($sub->status === 'active')
                                        <form action="{{ route('master.assinaturas.pause', $sub->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Pausar">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('master.assinaturas.cancel', $sub->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja cancelar esta assinatura?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @elseif($sub->status === 'paused')
                                        <form action="{{ route('master.assinaturas.resume', $sub->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Retomar">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhuma assinatura encontrada.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $subscriptions->links() }}
        </div>
    </div>
</div>

<!-- Modal de Detalhes do Cartão -->
<div class="modal fade" id="cardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Dados do Cartão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="card-modal-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function viewCard(subscriptionId) {
    const modal = new bootstrap.Modal(document.getElementById('cardModal'));
    const body = document.getElementById('card-modal-body');

    body.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
    `;

    modal.show();

    fetch(`/master/assinaturas/${subscriptionId}/card`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                body.innerHTML = `<p class="text-danger text-center">${data.error}</p>`;
                return;
            }

            body.innerHTML = `
                <div class="row">
                    <div class="col-12 text-center mb-4">
                        <i class="${data.brand_icon}" style="font-size: 4rem; color: ${data.brand_color};"></i>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label text-muted small">BANDEIRA</label>
                        <div><strong>${data.brand.toUpperCase()}</strong></div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label text-muted small">FINAL</label>
                        <div><strong>•••• •••• •••• ${data.last4}</strong></div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted small">TITULAR</label>
                        <div><strong>${data.holder_name}</strong></div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label text-muted small">VALIDADE</label>
                        <div><strong>${data.expiry_month}/${data.expiry_year}</strong></div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label text-muted small">STATUS</label>
                        <div>
                            ${data.status === 'active'
                                ? '<span class="badge bg-success">● Ativo</span>'
                                : '<span class="badge bg-secondary">Inativo</span>'}
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted small">TOKEN (CRIPTOGRAFADO)</label>
                        <div class="p-2 bg-light rounded font-monospace small">
                            ${data.token_masked}
                        </div>
                        <small class="text-muted">O token completo é criptografado e nunca é exposto em texto puro.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">ID ASAAS</label>
                        <div class="font-monospace small">${data.asaas_card_id}</div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            body.innerHTML = `<p class="text-danger text-center">Erro ao carregar dados do cartão.</p>`;
        });
}
</script>
@endsection
