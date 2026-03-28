@extends('layouts.checkout')

@section('title', 'Finalizar Pagamento - Basileia Vendas')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0 rounded-lg">

                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="font-weight-bold mb-0">Basileia Vendas</h3>
                    <p class="mb-0 opacity-75">Checkout Seguro</p>
                </div>

                <div class="card-body p-4">

                    {{-- Erros --}}
                    @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first('error') }}
                    </div>
                    @endif

                    {{-- Resumo --}}
                    <div class="text-center mb-4">
                        <h5 class="text-muted">Resumo do Pedido</h5>
                        <h2 class="display-4 text-primary font-weight-bold">
                            R$ {{ number_format($venda->valor, 2, ',', '.') }}
                        </h2>
                        <span class="badge badge-info">Venda #{{ $venda->id }}</span>

                        @if(isset($venda->tipo_plano))
                        <div class="mt-2">
                            <span class="badge badge-secondary px-3 py-1">
                                {{ match($venda->tipo_plano) {
                                    'mensal'       => 'Plano Mensal',
                                    'anual_avista' => 'Plano Anual à Vista',
                                    'anual_12x'    => 'Plano Anual - 12x no cartão',
                                    default        => 'Plano'
                                } }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
                        @csrf

                        {{-- Seleção do método de pagamento --}}
                        <div class="form-group mb-4">
                            <label class="font-weight-bold d-block mb-2">Forma de Pagamento</label>
                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                <label class="btn btn-outline-primary active flex-fill" id="btn-cartao">
                                    <input type="radio" name="payment_method" value="credit_card" checked
                                        onchange="mostrarSecao('cartao')">
                                    <i class="fas fa-credit-card mr-1"></i> Cartão
                                </label>
                                <label class="btn btn-outline-primary flex-fill" id="btn-pix">
                                    <input type="radio" name="payment_method" value="pix"
                                        onchange="mostrarSecao('pix')">
                                    <i class="fas fa-qrcode mr-1"></i> PIX
                                </label>
                                <label class="btn btn-outline-primary flex-fill" id="btn-boleto">
                                    <input type="radio" name="payment_method" value="boleto"
                                        onchange="mostrarSecao('boleto')">
                                    <i class="fas fa-barcode mr-1"></i> Boleto
                                </label>
                            </div>
                        </div>

                        {{-- Seção: Cartão de Crédito --}}
                        <div id="secao-cartao">
                            @if(($venda->tipo_plano ?? '') === 'anual_12x')
                            <div class="alert alert-info py-2 mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Plano anual: será cobrado em <strong>12x de R$ {{ number_format($venda->valor / 12, 2, ',', '.') }}</strong> no cartão.
                            </div>
                            @endif

                            <div class="form-group">
                                <label>Nome no Cartão</label>
                                <input type="text" name="nome_cartao" class="form-control"
                                    placeholder="Como escrito no cartão"
                                    value="{{ old('nome_cartao') }}">
                            </div>
                            <div class="form-group">
                                <label>Número do Cartão</label>
                                <input type="text" name="numero_cartao" class="form-control"
                                    placeholder="0000 0000 0000 0000"
                                    maxlength="19"
                                    oninput="formatarCartao(this)"
                                    value="{{ old('numero_cartao') }}">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Validade</label>
                                        <input type="text" name="expiry" class="form-control"
                                            placeholder="MM/AA" maxlength="5"
                                            oninput="formatarValidade(this)"
                                            value="{{ old('expiry') }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>CVV</label>
                                        <input type="text" name="cvv" class="form-control"
                                            placeholder="123" maxlength="4"
                                            oninput="this.value=this.value.replace(/\D/g,'')"
                                            value="{{ old('cvv') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: PIX --}}
                        <div id="secao-pix" style="display:none">
                            <div class="alert alert-success py-3 text-center">
                                <i class="fas fa-bolt mr-1"></i>
                                O QR Code e o código Copia e Cola serão gerados após confirmar.
                                <br><small class="text-muted">Aprovação instantânea após o pagamento.</small>
                            </div>
                        </div>

                        {{-- Seção: Boleto --}}
                        <div id="secao-boleto" style="display:none">
                            <div class="alert alert-warning py-3 text-center">
                                <i class="fas fa-clock mr-1"></i>
                                O boleto será gerado após confirmar.
                                <br><small class="text-muted">Prazo de compensação: até 3 dias úteis.</small>
                            </div>
                        </div>

                        {{-- CPF do pagador --}}
                        <div class="form-group mt-3">
                            <label>CPF / CNPJ do Pagador</label>
                            <input type="text" name="cpf_titular" class="form-control"
                                placeholder="000.000.000-00"
                                oninput="formatarCpf(this)"
                                value="{{ old('cpf_titular') }}"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-4 shadow-sm" id="btn-pagar">
                            <i class="fas fa-lock mr-2"></i>
                            Pagar R$ {{ number_format($venda->valor, 2, ',', '.') }}
                        </button>
                    </form>
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
function mostrarSecao(secao) {
    document.getElementById('secao-cartao').style.display = secao === 'cartao' ? 'block' : 'none';
    document.getElementById('secao-pix').style.display    = secao === 'pix'    ? 'block' : 'none';
    document.getElementById('secao-boleto').style.display = secao === 'boleto' ? 'block' : 'none';

    const btn = document.getElementById('btn-pagar');
    const valor = 'R$ {{ number_format($venda->valor, 2, ',', '.') }}';
    if (secao === 'pix')    btn.innerHTML = '<i class="fas fa-qrcode mr-2"></i>Gerar QR Code PIX ' + valor;
    if (secao === 'boleto') btn.innerHTML = '<i class="fas fa-barcode mr-2"></i>Gerar Boleto ' + valor;
    if (secao === 'cartao') btn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pagar ' + valor;
}

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

// Previne duplo clique no submit
document.getElementById('checkout-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-pagar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Processando...';
});
</script>
@endsection
