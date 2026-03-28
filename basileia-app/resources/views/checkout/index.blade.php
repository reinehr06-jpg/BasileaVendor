@extends('layouts.checkout')

@section('title', 'Finalizar Pagamento - Basileia Vendas')

@section('content')
<div class="card-checkout">
    <div class="card-header-premium">
        <h5 class="text-muted text-uppercase small font-weight-bold mb-0">Total a Pagar</h5>
        <div class="price-tag">
            R$ {{ number_format($venda->valor, 2, ',', '.') }}
        </div>
        <span class="badge badge-primary px-3 py-1">Venda #{{ $venda->id }}</span>
        
        @php
            $tipoPlano = $venda->tipo_negociacao ?? ($venda->plano ?? 'mensal');
        @endphp

        <div class="mt-2 text-muted small">
            <i class="fas fa-box-open mr-1"></i>
            {{ match($tipoPlano) {
                'mensal'       => 'Plano Mensal',
                'anual'        => 'Plano Anual',
                'anual_avista' => 'Plano Anual à Vista',
                'anual_12x'    => 'Plano Anual - 12x no cartão',
                default        => 'Assinatura'
            } }}
        </div>
    </div>

    <div class="card-body p-4 p-md-5">

        @if($errors->any())
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ $errors->first('error') }}
        </div>
        @endif

        <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
            @csrf

            {{-- Gestão de Abas: Só exibe se não houver restrição ou se for o método selecionado --}}
            @php
                $metodoAtual = old('payment_method', $restritoMetodo ?? 'credit_card');
                $exibirAbas = !isset($restritoMetodo);
            @endphp

            @if($exibirAbas)
            <div class="form-group mb-4">
                <label class="font-weight-bold text-dark small text-uppercase">Forma de Pagamento</label>
                <div class="nav nav-pills nav-pills-premium text-center" role="tablist">
                    <a class="nav-link flex-fill {{ $metodoAtual === 'credit_card' ? 'active' : '' }}" 
                       href="javascript:void(0)" onclick="mostrarSecao('cartao', this)">
                        <i class="fas fa-credit-card mr-1"></i> Cartão
                    </a>
                    <a class="nav-link flex-fill {{ $metodoAtual === 'pix' ? 'active' : '' }}" 
                       href="javascript:void(0)" onclick="mostrarSecao('pix', this)">
                        <i class="fas fa-bolt mr-1"></i> PIX
                    </a>
                    <a class="nav-link flex-fill {{ $metodoAtual === 'boleto' ? 'active' : '' }}" 
                       href="javascript:void(0)" onclick="mostrarSecao('boleto', this)">
                        <i class="fas fa-barcode mr-1"></i> Boleto
                    </a>
                </div>
                <input type="radio" name="payment_method" value="credit_card" style="display:none" {{ $metodoAtual === 'credit_card' ? 'checked' : '' }}>
                <input type="radio" name="payment_method" value="pix" style="display:none" {{ $metodoAtual === 'pix' ? 'checked' : '' }}>
                <input type="radio" name="payment_method" value="boleto" style="display:none" {{ $metodoAtual === 'boleto' ? 'checked' : '' }}>
            </div>
            @else
                <input type="hidden" name="payment_method" value="{{ $restritoMetodo }}">
                <div class="alert alert-light border mb-4 text-center">
                    <span class="text-uppercase small font-weight-bold text-muted d-block">Pagando via</span>
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-{{ $restritoMetodo === 'credit_card' ? 'credit-card' : ($restritoMetodo === 'pix' ? 'bolt' : 'barcode') }} mr-2"></i>
                        {{ $restritoMetodo === 'credit_card' ? 'Cartão de Crédito' : ($restritoMetodo === 'pix' ? 'PIX' : 'Boleto Bancário') }}
                    </h5>
                </div>
            @endif

            {{-- Seção: Cartão de Crédito --}}
            <div id="secao-cartao" style="{{ $metodoAtual === 'credit_card' ? 'display:block' : 'display:none' }}">
                @if($tipoPlano === 'anual_12x')
                <div class="alert alert-info py-2 mb-3 small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Cobrança recorrente de <strong>12x de R$ {{ number_format($venda->valor / 12, 2, ',', '.') }}</strong>.
                </div>
                @endif

                <div class="form-group">
                    <label class="small font-weight-bold">Nome no Cartão</label>
                    <input type="text" name="nome_cartao" class="form-control form-control-premium"
                        placeholder="COMO ESCRITO NO CARTÃO"
                        value="{{ old('nome_cartao') }}">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Número do Cartão</label>
                    <div class="position-relative">
                        <input type="text" name="numero_cartao" class="form-control form-control-premium"
                            placeholder="0000 0000 0000 0000"
                            maxlength="19"
                            oninput="formatarCartao(this)"
                            value="{{ old('numero_cartao') }}">
                        <i class="fas fa-credit-card position-absolute" style="right: 15px; top: 15px; color: #ccc;"></i>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7">
                        <div class="form-group">
                            <label class="small font-weight-bold">Validade</label>
                            <input type="text" name="expiry" class="form-control form-control-premium"
                                placeholder="MM/AA" maxlength="5"
                                oninput="formatarValidade(this)"
                                value="{{ old('expiry') }}">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label class="small font-weight-bold">CVV</label>
                            <input type="text" name="cvv" class="form-control form-control-premium"
                                placeholder="123" maxlength="4"
                                oninput="this.value=this.value.replace(/\D/g,'')"
                                value="{{ old('cvv') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seção: PIX --}}
            <div id="secao-pix" style="{{ $metodoAtual === 'pix' ? 'display:block' : 'display:none' }}">
                <div class="text-center py-4 px-3 bg-light rounded mb-3">
                    <i class="fas fa-bolt text-warning mb-2" style="font-size: 2rem;"></i>
                    <h6 class="font-weight-bold">Pagamento Instantâneo</h6>
                    <p class="text-muted small mb-0">O QR Code será gerado após clicar no botão abaixo. A liberação é imediata.</p>
                </div>
            </div>

            {{-- Seção: Boleto --}}
            <div id="secao-boleto" style="{{ $metodoAtual === 'boleto' ? 'display:block' : 'display:none' }}">
                <div class="text-center py-4 px-3 bg-light rounded mb-3">
                    <i class="fas fa-barcode text-primary mb-2" style="font-size: 2rem;"></i>
                    <h6 class="font-weight-bold">Boleto Bancário</h6>
                    <p class="text-muted small mb-0">O PDF do boleto será gerado ao confirmar. Compensação em até 48h úteis.</p>
                </div>
            </div>

            {{-- CPF do pagador --}}
            <div class="form-group mt-3">
                <label class="small font-weight-bold text-dark">CPF / CNPJ do Pagador</label>
                <input type="text" name="cpf_titular" class="form-control form-control-premium"
                    placeholder="000.000.000-00"
                    oninput="formatarCpf(this)"
                    value="{{ old('cpf_titular') }}"
                    required>
            </div>

            <button type="submit" class="btn btn-pay btn-block mt-4" id="btn-pagar">
                <i class="fas fa-lock mr-2"></i>
                Pagar Agora R$ {{ number_format($venda->valor, 2, ',', '.') }}
            </button>
        </form>
    </div>

    <div class="card-footer bg-white border-top-0 py-4 text-center">
        <div class="d-flex justify-content-center align-items-center gap-3 opacity-75">
            <i class="fab fa-cc-visa fa-2x mx-2"></i>
            <i class="fab fa-cc-mastercard fa-2x mx-2"></i>
            <i class="fas fa-barcode fa-2x mx-2"></i>
            <i class="fas fa-bolt fa-2x mx-2"></i>
        </div>
    </div>
</div>

<script>
function mostrarSecao(secao, element) {
    // Altera interface visual das abas
    if (element) {
        document.querySelectorAll('.nav-pills-premium .nav-link').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
    }

    // Altera visibilidade das seções
    document.getElementById('secao-cartao').style.display = secao === 'cartao' ? 'block' : 'none';
    document.getElementById('secao-pix').style.display    = secao === 'pix'    ? 'block' : 'none';
    document.getElementById('secao-boleto').style.display = secao === 'boleto' ? 'block' : 'none';

    // Sincroniza o rádio oculto
    const value = secao === 'cartao' ? 'credit_card' : secao;
    document.querySelector('input[name="payment_method"][value="' + value + '"]').checked = true;

    // Altera texto do botão
    const btn = document.getElementById('btn-pagar');
    const valorStr = 'R$ {{ number_format($venda->valor, 2, ',', '.') }}';
    if (secao === 'pix')    btn.innerHTML = '<i class="fas fa-bolt mr-2"></i>Gerar QR Code PIX ' + valorStr;
    if (secao === 'boleto') btn.innerHTML = '<i class="fas fa-barcode mr-2"></i>Gerar Boleto ' + valorStr;
    if (secao === 'cartao') btn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pagar Agora ' + valorStr;
}

// Inicializa o botão conforme o método atual
document.addEventListener('DOMContentLoaded', function() {
    const metodoInício = '{{ $metodoAtual }}' === 'credit_card' ? 'cartao' : '{{ $metodoAtual }}';
    mostrarSecao(metodoInício, document.querySelector('.nav-link.active'));
});

function formatarCartao(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}

function formatarValidade(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length > 2) v = v.substring(0,2) + '/' + v.substring(2);
    input.value = v;
}

function formatarCpf(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) {
        v = v.replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    }
    input.value = v;
}

document.getElementById('checkout-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-pagar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';
});
</script>
@endsection
