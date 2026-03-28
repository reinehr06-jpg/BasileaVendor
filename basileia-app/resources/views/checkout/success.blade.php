@extends('layouts.checkout')

@section('title', 'Pagamento Confirmado - Basileia Vendas')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0 rounded-lg">

                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="font-weight-bold mb-0">Basileia Vendas</h3>
                    <p class="mb-0 opacity-75">Pedido Recebido</p>
                </div>

                <div class="card-body p-4">

                    {{-- Cabeçalho --}}
                    <div class="text-center mb-4">
                        @if($venda->tipo_pagamento === 'cartao')
                            <div class="mb-3">
                                <span style="font-size:3rem; color:#28a745;">&#10003;</span>
                            </div>
                            <h4 class="text-success font-weight-bold">Pagamento Aprovado!</h4>
                            <p class="text-muted">Seu pagamento foi processado com sucesso.</p>
                        @else
                            <div class="mb-3">
                                <span style="font-size:3rem; color:#ffc107;">&#9203;</span>
                            </div>
                            <h4 class="text-warning font-weight-bold">Aguardando Pagamento</h4>
                            <p class="text-muted">Finalize o pagamento usando as instruções abaixo.</p>
                        @endif

                        <p class="badge badge-secondary px-3 py-2">Venda #{{ $venda->id }}</p>
                    </div>

                    {{-- ============================================ --}}
                    {{-- SEÇÃO PIX: QR Code + Copia e Cola           --}}
                    {{-- ============================================ --}}
                    @if($venda->tipo_pagamento === 'pix')
                    <div class="pix-section text-center">

                        <h5 class="font-weight-bold mb-3">Pague com PIX</h5>

                        {{-- QR Code --}}
                        @if($venda->pix_qrcode_base64)
                        <div class="mb-3">
                            <img src="data:image/png;base64,{{ $venda->pix_qrcode_base64 }}"
                                 alt="QR Code PIX"
                                 style="width:200px;height:200px;border:2px solid #dee2e6;border-radius:8px;padding:8px;">
                        </div>
                        @endif

                        {{-- Código Copia e Cola --}}
                        @if($venda->pix_copia_cola)
                        <p class="text-muted small mb-2">Ou copie o código PIX abaixo:</p>
                        <div class="input-group mb-3">
                            <input type="text"
                                   id="pix-code"
                                   class="form-control form-control-sm text-center"
                                   value="{{ $venda->pix_copia_cola }}"
                                   readonly
                                   style="font-size:11px; background:#f8f9fa;">
                            <div class="input-group-append">
                                <button class="btn btn-success btn-sm" type="button" onclick="copiarPix()" id="btn-copiar">
                                    <i class="fas fa-copy mr-1"></i> Copiar
                                </button>
                            </div>
                        </div>
                        @endif

                        <div class="alert alert-light border py-2 mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Abra o app do seu banco &rarr; PIX &rarr; Pagar com QR Code ou Cole o código acima.
                                <br>O pagamento é confirmado em instantes.
                            </small>
                        </div>
                    </div>
                    @endif

                    {{-- ============================================ --}}
                    {{-- SEÇÃO BOLETO                                --}}
                    {{-- ============================================ --}}
                    @if($venda->tipo_pagamento === 'boleto')
                    <div class="boleto-section text-center">

                        <h5 class="font-weight-bold mb-3">Seu Boleto está Pronto</h5>

                        @if($venda->bank_slip_url)
                        <a href="{{ $venda->bank_slip_url }}"
                           target="_blank"
                           class="btn btn-danger btn-lg btn-block shadow-sm mb-3">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Baixar Boleto em PDF
                        </a>

                        <button onclick="copiarTexto('{{ $venda->bank_slip_url }}')" class="btn btn-outline-secondary btn-block btn-sm mb-3">
                            <i class="fas fa-copy mr-1"></i> Copiar link do boleto
                        </button>
                        @endif

                        <div class="alert alert-warning py-2">
                            <small>
                                <i class="fas fa-clock mr-1"></i>
                                Vencimento em 3 dias úteis. Após o pagamento, a compensação pode levar até 3 dias.
                            </small>
                        </div>
                    </div>
                    @endif

                    {{-- ============================================ --}}
                    {{-- SEÇÃO CARTÃO — Confirmação                  --}}
                    {{-- ============================================ --}}
                    @if($venda->tipo_pagamento === 'cartao')
                    <div class="cartao-section">
                        <div class="alert alert-success text-center">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Pagamento aprovado e confirmado.
                        </div>

                        @if($venda->cartao_bandeira && $venda->cartao_final)
                        <div class="d-flex align-items-center justify-content-center mb-3" style="gap:8px">
                            <span class="badge badge-light border px-3 py-2" style="font-size:14px">
                                {{ $venda->cartao_bandeira }} **** {{ $venda->cartao_final }}
                            </span>
                        </div>
                        @endif

                        @if($venda->tipo_plano === 'anual_12x')
                        <div class="alert alert-info py-2 text-center">
                            <small>
                                <i class="fas fa-sync-alt mr-1"></i>
                                Parcelado em 12x — as parcelas aparecerão na fatura do seu cartão.
                                <br>Renovação automática em {{ \Carbon\Carbon::parse($venda->data_renovacao)->format('d/m/Y') }}.
                            </small>
                        </div>
                        @elseif($venda->data_renovacao)
                        <div class="alert alert-info py-2 text-center">
                            <small>
                                <i class="fas fa-sync-alt mr-1"></i>
                                Próxima cobrança automática em <strong>{{ \Carbon\Carbon::parse($venda->data_renovacao)->format('d/m/Y') }}</strong>.
                            </small>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Resumo final --}}
                    <hr class="my-4">
                    <div class="row text-center text-muted small">
                        <div class="col-6">
                            <strong>Valor</strong><br>
                            R$ {{ number_format($venda->valor, 2, ',', '.') }}
                        </div>
                        <div class="col-6">
                            <strong>Plano</strong><br>
                            {{ match($venda->tipo_plano ?? 'mensal') {
                                'mensal'       => 'Mensal',
                                'anual_avista' => 'Anual à Vista',
                                'anual_12x'    => 'Anual 12x',
                                default        => '-'
                            } }}
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-lock mr-1"></i> Ambiente Seguro e Criptografado
                    </small>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function copiarPix() {
    const codigo = document.getElementById('pix-code').value;
    navigator.clipboard.writeText(codigo).then(() => {
        const btn = document.getElementById('btn-copiar');
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copiado!';
        btn.classList.replace('btn-success', 'btn-secondary');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy mr-1"></i> Copiar';
            btn.classList.replace('btn-secondary', 'btn-success');
        }, 2500);
    });
}

function copiarTexto(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        alert('Link copiado!');
    });
}
</script>
@endsection
