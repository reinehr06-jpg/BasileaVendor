@extends('layouts.app')
@section('title', 'Vendas Globais')

@section('content')
<style>
    .action-btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-btn-boleto { background: var(--primary); color: white; border-color: var(--primary); }
    .action-btn-link { background: var(--success); color: white; border-color: var(--success); }
    .forma-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; background: rgba(76,29,149,0.08); color: var(--primary); }
</style>

<x-page-hero 
    title="Vendas Globais" 
    subtitle="Todas as vendas da operação." 
    icon="fas fa-shopping-bag"
    :exports="[
        ['type' => 'excel', 'url' => route('master.vendas.exportar', array_merge(request()->query(), ['formato' => 'excel'])), 'icon' => 'fas fa-file-excel', 'label' => 'Excel'],
        ['type' => 'pdf', 'url' => route('master.vendas.exportar', array_merge(request()->query(), ['formato' => 'pdf'])), 'icon' => 'fas fa-file-pdf', 'label' => 'PDF'],
        ['type' => 'csv', 'url' => route('master.vendas.exportar', array_merge(request()->query(), ['formato' => 'csv'])), 'icon' => 'fas fa-file-csv', 'label' => 'CSV'],
    ]"
/>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-value">
            {{ $vendas->filter(function($v) {
                $s = strtoupper($v->getStatusEfetivo());
                return !in_array($s, ['ESTORNADO', 'CANCELADO', 'EXPIRADO', 'VENCIDO']);
            })->count() }}
        </div>
        <div class="stat-label">Ativas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value">
            R$ {{ number_format($vendas->filter(function($v) {
                $s = strtoupper($v->getStatusEfetivo());
                return in_array($s, ['PAGO', 'RECEIVED', 'CONFIRMED']);
            })->sum('valor'), 2, ',', '.') }}
        </div>
        <div class="stat-label">Valor Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value" style="color: var(--success);">
            {{ $vendas->filter(function($v) {
                $s = strtoupper($v->getStatusEfetivo());
                return in_array($s, ['PAGO', 'RECEIVED', 'CONFIRMED']);
            })->count() }}
        </div>
        <div class="stat-label">Pagas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">
            {{ $vendas->filter(function($v) {
                $s = strtoupper($v->getStatusEfetivo());
                return in_array($s, ['AGUARDANDO PAGAMENTO', 'PENDING', 'AGUARDANDO APROVAÇÃO']);
            })->count() }}
        </div>
        <div class="stat-label">Aguardando</div>
    </div>
</div>

<!-- Table -->
<div class="table-container">
    @if($vendas->count() > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-building"></i> Cliente</th>
                <th><i class="fas fa-user"></i> Vendedor</th>
                <th><i class="fas fa-tag"></i> Plano</th>
                <th><i class="fas fa-dollar-sign"></i> Valor</th>
                <th><i class="fas fa-circle-check"></i> Status</th>
                <th><i class="fas fa-bolt"></i> Pagamento</th>
                <th><i class="fas fa-calendar"></i> Data</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $venda)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $venda->cliente->nome_igreja ?? '—' }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $venda->cliente->nome_pastor ?? '' }}</div>
                </td>
                <td style="font-size: 0.85rem;">{{ $venda->vendedor->user->name ?? '—' }}</td>
                <td><span class="badge badge-primary">{{ $venda->plano ?? 'N/A' }}</span></td>
                <td style="font-weight: 700;">R$ {{ number_format($venda->valor, 2, ',', '.') }}</td>
                <td>
                    @php
                        $status = $venda->getStatusEfetivo();
                        $cleanStatus = trim(strtoupper($status));
                        $statusClass = match(true) {
                            in_array($cleanStatus, ['PAGO', 'RECEIVED', 'CONFIRMED']) => 'status-pago',
                            in_array($cleanStatus, ['AGUARDANDO PAGAMENTO', 'PENDING', 'AGUARDANDO APROVAÇÃO']) => 'status-aguardando',
                            in_array($cleanStatus, ['VENCIDO', 'OVERDUE']) => 'status-vencido',
                            in_array($cleanStatus, ['CANCELADO', 'CANCELED']) => 'status-cancelado',
                            $cleanStatus === 'ESTORNADO' => 'status-estornado',
                            $cleanStatus === 'EXPIRADO' => 'status-expirado',
                            default => 'status-aguardando'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $status }}</span>
                    @if($venda->isPagamentoParcelado() && $venda->getParcelaAtual() > 0)
                        <div style="font-size: 0.7rem; color: var(--success); margin-top: 2px;">{{ $venda->getProgressoParcelas() }}</div>
                    @endif
                </td>
                <td>
                    @php
                        $pagamento = $venda->pagamentos->first();
                        $cobranca = $venda->cobrancas->first();
                        $formaUpper = strtoupper($venda->forma_pagamento ?? '');
                    @endphp
                    @if(!in_array(strtoupper($venda->getStatusEfetivo()), ['PAGO', 'CANCELADO', 'EXPIRADO', 'ESTORNADO']))
                        <div class="d-flex flex-column gap-1">
                            <div style="display: flex; gap: 4px;">
                                @if($formaUpper === 'BOLETO' || empty($formaUpper))
                                    <button onclick="openCheckoutLink({{ $venda->id }}, 'boleto')" class="action-btn-sm action-btn-boleto" style="flex: 1;" title="Abrir Checkout Boleto">
                                        <i class="fas fa-barcode"></i> Boleto
                                    </button>
                                @endif

                                @if($formaUpper === 'PIX' || empty($formaUpper))
                                    <button onclick="openCheckoutLink({{ $venda->id }}, 'pix')" class="action-btn-sm" style="background: #008080; color: white; flex: 1;" title="Abrir Checkout PIX">
                                        <i class="fas fa-qrcode"></i> Pix
                                    </button>
                                @endif

                                @if($formaUpper === 'CREDIT_CARD' || empty($formaUpper))
                                    <button onclick="openCheckoutLink({{ $venda->id }}, 'credit_card')" class="action-btn-sm" style="background: var(--primary); color: white; flex: 1;" title="Abrir Checkout Cartão">
                                        <i class="fas fa-credit-card"></i> Cartão
                                    </button>
                                @endif
                            </div>

                            <button onclick="copyCheckoutLink({{ $venda->id }})" class="action-btn-sm" style="background: var(--success); color: white;" title="Copiar Link de Checkout">
                                <i class="fas fa-copy"></i> Copiar Link
                            </button>
                        </div>
                    @else
                        @if($linkBoleto)
                             <a href="{{ $linkBoleto }}" target="_blank" class="action-btn-sm action-btn-boleto" title="Baixar Boleto">
                                 <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        @else
                            <span style="font-size: 0.8rem; color: var(--text-muted);">—</span>
                        @endif
                    @endif
                </td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $venda->created_at->format('d/m/Y') }}</td>
                <td style="text-align: right;">
                    @if(strtoupper($venda->status) === 'PAGO')
                    <button class="btn btn-ghost btn-sm" style="color: var(--warning);" onclick="openRefundModal({{ $venda->id }}, '{{ $venda->cliente->nome_igreja ?? 'N/A' }}', {{ $venda->valor }}, '{{ $venda->modo_cobranca_asaas ?? 'PAYMENT' }}', {{ $venda->parcelas ?? 1 }})" title="Estornar">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                    @elseif(!in_array(strtoupper($venda->status), ['CANCELADO', 'EXPIRADO', 'ESTORNADO']))
                    <form method="POST" action="{{ route('master.vendas.cancelar', $venda->id) }}" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Cancelar Venda', message: 'Cancelar venda #' + {{ $venda->id }} + '?', type: 'danger', confirmText: 'Cancelar', onConfirm: () => this.submit()});">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-shopping-bag"></i></div>
        <h3>Nenhuma venda ativa</h3>
        <p>Não há vendas cadastradas no momento.</p>
    </div>
    @endif
</div>

<!-- Modal de Estorno Inteligente -->
<div class="modal-overlay" id="refundModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-rotate-left" style="margin-right: 8px; color: var(--warning);"></i>Estornar Venda</h2>
            <button class="modal-close" onclick="BasileiaModal.close('refundModal')">&times;</button>
        </div>
        <form id="refundForm" method="POST" class="modal-body">
            @csrf
            
            <div class="alert alert-warning" style="margin-bottom: 16px;">
                <i class="fas fa-triangle-exclamation"></i>
                <span id="refundWarning">Selecione as opções abaixo.</span>
            </div>

            <div style="background: #f5f3ff; border: 1px solid rgba(var(--primary-rgb), 0.2); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div class="info-row">
                    <span class="info-label">Venda #</span>
                    <span class="info-value" id="refundVendaId">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cliente</span>
                    <span class="info-value" id="refundCliente">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Valor Total</span>
                    <span class="info-value text-primary" id="refundValor">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Modo</span>
                    <span class="info-value" id="refundModo">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Parcelas</span>
                    <span class="info-value" id="refundParcelas">-</span>
                </div>
            </div>

            <div class="form-group">
                <label>Motivo do Estorno <span class="required">*</span></label>
                <textarea name="motivo" class="form-control" rows="3" required placeholder="Descreva o motivo do estorno..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('refundModal')">Cancelar</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-rotate-left"></i> Confirmar Estorno</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openRefundModal(vendaId, cliente, valor, modo, parcelas) {
    document.getElementById('refundVendaId').textContent = '#' + vendaId;
    document.getElementById('refundCliente').textContent = cliente;
    document.getElementById('refundValor').textContent = 'R$ ' + valor.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('refundModo').textContent = modo;
    document.getElementById('refundParcelas').textContent = parcelas + 'x';
    document.getElementById('refundForm').action = '/master/vendas/' + vendaId + '/estornar';

    // Ajustar aviso baseado no modo
    let warning = '';
    if (modo === 'INSTALLMENT' && parcelas > 1) {
        warning = 'Venda parcelada em ' + parcelas + 'x. O sistema cancelará todas as parcelas pendentes no Asaas e tentará estornar as já pagas (apenas PIX/Cartão).';
    } else if (modo === 'SUBSCRIPTION') {
        warning = 'Assinatura recorrente. O sistema cancelará a assinatura no Asaas para parar cobranças futuras e tentará estornar o último pagamento.';
    } else {
        warning = 'Cobrança avulsa. O sistema tentará estornar no Asaas. Nota: Boleto já pago não permite estorno automático.';
    }
    document.getElementById('refundWarning').textContent = warning;

    BasileiaModal.open('refundModal');
}

async function copyCheckoutLink(vendaId, method = null) {
    try {
        const urlParams = method ? `?method=${method}` : '';
        const response = await fetch(`/master/vendas/${vendaId}/checkout-link${urlParams}`);
        const data = await response.json();
        
        if (data.success) {
            await navigator.clipboard.writeText(data.url);
            alert('✅ Link de checkout copiado!\n\n' + data.url);
        } else {
            alert('❌ ' + (data.error || 'Erro ao gerar link'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao copiar link. Tente novamente.');
    }
}

async function openCheckoutLink(vendaId, method = null) {
    try {
        const urlParams = method ? `?method=${method}` : '';
        const response = await fetch(`/master/vendas/${vendaId}/checkout-link${urlParams}`);
        const data = await response.json();
        
        if (data.success) {
            window.open(data.url, '_blank');
        } else {
            alert('❌ ' + (data.error || 'Erro ao gerar link'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao abrir checkout.');
    }
}
</script>
@endsection
