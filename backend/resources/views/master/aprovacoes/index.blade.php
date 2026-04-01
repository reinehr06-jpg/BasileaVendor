@extends('layouts.app')
@section('title', 'Aprovações Comerciais')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-circle-check" style="margin-right: 8px;"></i>Aprovações Comerciais</h2>
        <p>Vendas que precisam de aprovação por desconto ou plano especial.</p>
    </div>
</div>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">{{ $pendentes->count() }}</div>
        <div class="stat-label">Pendentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-check"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ $aprovadas->count() }}</div>
        <div class="stat-label">Aprovadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-xmark"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $rejeitadas->count() }}</div>
        <div class="stat-label">Rejeitadas</div>
    </div>
</div>

<!-- Pending Table -->
@if($pendentes->count() > 0)
<div class="table-container" style="margin-bottom: 28px;">
    <table>
        <thead>
            <tr>
                <th>Venda #</th>
                <th>Vendedor</th>
                <th>Cliente</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Solicitado</th>
                <th>Data</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendentes as $a)
            <tr>
                <td style="font-weight: 700; color: var(--primary);">#{{ str_pad($a->venda_id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $a->venda->vendedor->user->name ?? 'N/A' }}</td>
                <td>{{ $a->venda->cliente->nome_igreja ?? 'N/A' }}</td>
                <td style="font-weight: 700;">R$ {{ number_format($a->venda->valor_final ?? $a->venda->valor, 2, ',', '.') }}</td>
                <td>
                    @if($a->tipo_aprovacao === 'VALOR_PERFORMANCE')
                        <span class="badge badge-info">Performance</span>
                    @else
                        <span class="badge badge-warning">Desconto</span>
                    @endif
                </td>
                <td>
                    @if($a->tipo_aprovacao === 'VALOR_PERFORMANCE')
                        <span style="font-weight: 700; color: var(--primary);">R$ {{ number_format($a->valor_solicitado ?? 0, 2, ',', '.') }}</span>
                    @else
                        <span style="font-weight: 700; color: var(--primary);">{{ $a->percentual_solicitado }}%</span>
                    @endif
                </td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                <td style="text-align: right;">
                    <button class="btn btn-success btn-sm" onclick="openApprovalModal('aprovar', {{ $a->id }})">
                        <i class="fas fa-check"></i> Aprovar
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="openApprovalModal('rejeitar', {{ $a->id }})">
                        <i class="fas fa-xmark"></i> Rejeitar
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card" style="margin-bottom: 28px;">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-circle-check"></i></div>
        <h3>Nenhuma aprovação pendente</h3>
        <p>Todas as vendas estão dentro das regras comerciais.</p>
    </div>
</div>
@endif

<!-- History -->
@if($aprovadas->count() > 0 || $rejeitadas->count() > 0)
<h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">
    <i class="fas fa-clock-rotate-left" style="margin-right: 6px;"></i>Histórico
</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Venda #</th>
                <th>Vendedor</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Valor Solicitado</th>
                <th>Status</th>
                <th>Por</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aprovadas->merge($rejeitadas) as $a)
            <tr>
                <td style="font-weight: 700;">#{{ str_pad($a->venda_id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $a->venda->vendedor->user->name ?? 'N/A' }}</td>
                <td>{{ $a->venda->cliente->nome_igreja ?? 'N/A' }}</td>
                <td>
                    <span class="badge badge-{{ $a->tipo_aprovacao === 'VALOR_PERFORMANCE' ? 'info' : 'warning' }}">
                        {{ $a->tipo_aprovacao === 'VALOR_PERFORMANCE' ? 'Performance' : 'Desconto' }}
                    </span>
                </td>
                <td>
                    @if($a->tipo_aprovacao === 'VALOR_PERFORMANCE')
                        R$ {{ number_format($a->valor_solicitado ?? 0, 2, ',', '.') }}
                    @else
                        {{ $a->percentual_solicitado }}%
                    @endif
                </td>
                <td><span class="badge badge-{{ $a->status === 'APROVADO' ? 'success' : 'danger' }}">{{ ucfirst(strtolower($a->status)) }}</span></td>
                <td>{{ $a->aprovadoPor->name ?? '-' }}</td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $a->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- Approval Modal -->
<div class="modal-overlay" id="approvalModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="approvalModalTitle">Ação</h2>
            <button class="modal-close" onclick="BasileiaModal.close('approvalModal')">&times;</button>
        </div>
        <form id="approvalForm" method="POST" class="modal-body">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label>Observação</label>
                <textarea name="observacao" class="form-control" rows="3" placeholder="Adicione uma observação (opcional)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('approvalModal')">Cancelar</button>
                <button type="submit" id="approvalSubmitBtn" class="btn">Confirmar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openApprovalModal(action, id) {
        const title = document.getElementById('approvalModalTitle');
        const btn = document.getElementById('approvalSubmitBtn');
        const form = document.getElementById('approvalForm');

        if (action === 'aprovar') {
            title.innerHTML = '<i class="fas fa-check-double" style="margin-right: 8px; color: var(--success);"></i>Aprovar Venda';
            btn.textContent = 'Aprovar';
            btn.className = 'btn btn-success';
            form.action = '/master/aprovacoes/' + id + '/aprovar';
        } else {
            title.innerHTML = '<i class="fas fa-circle-xmark" style="margin-right: 8px; color: var(--danger);"></i>Rejeitar Venda';
            btn.textContent = 'Rejeitar';
            btn.className = 'btn btn-danger';
            form.action = '/master/aprovacoes/' + id + '/rejeitar';
        }

        BasileiaModal.open('approvalModal');
    }
</script>
@endsection
