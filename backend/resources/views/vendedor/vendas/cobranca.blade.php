@extends('layouts.app')
@section('title', 'Detalhes da Cobrança')

@section('content')
<style>
    .cobranca-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media (max-width: 900px) { .cobranca-grid { grid-template-columns: 1fr; } }
    .valor-destaque { font-size: 2rem; font-weight: 800; color: var(--primary); text-align: center; padding: 24px; background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.04), rgba(124,58,237,0.04)); border-radius: var(--radius-md); margin-bottom: 16px; }
    .status-pendente { background: #fef9c3; color: #854d0e; }
    .status-pago { background: #dcfce7; color: #166534; }
    .status-vencido { background: #fee2e2; color: #991b1b; }
    .status-cancelado { background: #f1f5f9; color: #64748b; }
    .actions-grid { display: flex; gap: 12px; flex-wrap: wrap; }
</style>

<x-page-hero title="Gerar Cobrança" subtitle="Venda #{{ $venda->id }} — {{ $venda->cliente->nome_igreja ?? $venda->cliente->nome ?? 'Cliente' }}" icon="fas fa-file-invoice-dollar" />

@php
    $pagamento = $venda->pagamentos->first();
    $cobranca = $venda->cobrancas->first();
    $boletoUrl = null;
    $invoiceUrl = null;
    $linhaDigitavel = null;

    if ($pagamento) {
        $boletoUrl = $pagamento->bank_slip_url ?? null;
        $invoiceUrl = $pagamento->link_pagamento ?? $pagamento->invoice_url ?? null;
        $linhaDigitavel = $pagamento->linha_digitavel ?? null;
    }
    if ($cobranca) {
        if (!$boletoUrl) $boletoUrl = $cobranca->link ?? null;
        if (!$invoiceUrl) $invoiceUrl = $cobranca->link ?? null;
    }
    $statusEfetivo = $venda->getStatusEfetivo();
    $statusClass = match(strtoupper($statusEfetivo)) {
        'PENDENTE', 'PENDING', 'AGUARDANDO PAGAMENTO' => 'status-pendente',
        'PAGO', 'RECEIVED', 'CONFIRMED' => 'status-pago',
        'VENCIDO', 'OVERDUE' => 'status-vencido',
        'CANCELADO', 'CANCELED' => 'status-cancelado',
        default => 'status-pendente',
    };
@endphp

<div class="cobranca-grid">
    <!-- Value Card -->
    <div class="card">
        <div class="card-header"><i class="fas fa-dollar-sign"></i> Valor da Cobrança</div>
        <div class="valor-destaque">R$ {{ number_format($venda->valor, 2, ',', '.') }}</div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="badge {{ $statusClass }}">{{ strtoupper($statusEfetivo) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Forma de Pagamento</span>
            <span class="info-value">{{ $venda->forma_pagamento }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo</span>
            <span class="info-value">{{ ucfirst($venda->tipo_negociacao ?? 'Mensal') }}</span>
        </div>
        @if($venda->isPagamentoParcelado())
        <div class="info-row">
            <span class="info-label">Parcelas</span>
            <span class="info-value text-success">{{ $venda->getProgressoParcelas() }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Plano</span>
            <span class="info-value text-primary">{{ $venda->plano }}</span>
        </div>
    </div>

    <!-- Client Card -->
    <div class="card">
        <div class="card-header"><i class="fas fa-building"></i> Cliente</div>
        <div class="info-row">
            <span class="info-label">Igreja</span>
            <span class="info-value">{{ $venda->cliente->nome_igreja ?? $venda->cliente->nome }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pastor</span>
            <span class="info-value">{{ $venda->cliente->nome_pastor ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Documento</span>
            <span class="info-value">{{ $venda->cliente->documento ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vencimento</span>
            <span class="info-value">{{ $pagamento && $pagamento->data_vencimento ? $pagamento->data_vencimento->format('d/m/Y') : '—' }}</span>
        </div>
        @if($pagamento && $pagamento->data_pagamento)
        <div class="info-row">
            <span class="info-label">Data de Pagamento</span>
            <span class="info-value text-success">{{ $pagamento->data_pagamento->format('d/m/Y') }}</span>
        </div>
        @endif
        @if($linhaDigitavel)
        <div class="info-row">
            <span class="info-label">Linha Digitável</span>
            <span class="info-value" style="font-size: 0.78rem; word-break: break-all; font-family: monospace;">{{ $linhaDigitavel }}</span>
        </div>
        @endif
    </div>

    <!-- Actions Card -->
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header"><i class="fas fa-bolt"></i> Ações Rápidas</div>
        <div class="actions-grid">
            @if($venda->forma_pagamento === 'BOLETO')
                <button type="button" id="btn-download-boleto" onclick="downloadBoleto({{ $venda->id }})" class="btn btn-primary">
                    <i class="fas fa-file-lines"></i> Baixar Boleto
                </button>
            @endif

            @if($venda->checkout_payment_link)
                <button type="button" onclick="copyToClipboard('{{ $venda->checkout_payment_link }}')" class="btn btn-primary" style="background: var(--success); border-color: var(--success); color: #fff; box-shadow: 0 4px 10px rgba(34,197,94,0.3);">
                    <i class="fas fa-external-link-alt"></i> Copiar Link do Checkout
                </button>
            @endif

            @if($invoiceUrl)
                <button type="button" onclick="copyToClipboard('{{ $invoiceUrl }}')" class="btn btn-outline-primary">
                    <i class="fas fa-link"></i> Copiar Link Asaas
                </button>
            @endif

            @if($linhaDigitavel)
                <button onclick="copyToClipboard('{{ $linhaDigitavel }}')" class="btn btn-outline">
                    <i class="fas fa-clipboard"></i> Copiar Linha Digitável
                </button>
            @endif

            @if(!in_array(strtoupper($venda->status), ['PAGO', 'CANCELADO', 'EXPIRADO', 'ESTORNADO']))
                <form method="POST" action="{{ route('vendedor.vendas.cancelar', $venda->id) }}" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Cancelar Venda', message: 'Deseja cancelar esta venda? O registro será mantido no histórico.', type: 'warning', confirmText: 'Cancelar Venda', onConfirm: () => this.submit()});">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash"></i> Cancelar Venda
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
async function downloadBoleto(vendaId) {
    const btn = document.getElementById('btn-download-boleto');
    setButtonLoading(btn, true);
    const newWindow = window.open('about:blank', '_blank');
    if (newWindow) newWindow.document.write('<body style="font-family:sans-serif; text-align:center; padding-top:50px;"><h2>Carregando boleto...</h2></body>');
    try {
        const response = await fetch('/vendedor/vendas/' + vendaId + '/boleto', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await response.json();
        if (!response.ok || !data.success) { if (newWindow) newWindow.close(); throw new Error(data.message || 'Falha ao buscar boleto.'); }
        if (newWindow) newWindow.location.href = data.url; else window.location.href = data.url;
    } catch (error) {
        if (newWindow) newWindow.close();
        BasileiaToast.error(error.message);
    } finally {
        setButtonLoading(btn, false);
    }
}
</script>
@endsection
