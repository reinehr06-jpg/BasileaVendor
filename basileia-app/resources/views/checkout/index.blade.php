@extends('layouts.checkout')

@section('title', 'Check-out Seguro - Basileia Vendas')

@section('content')
<div class="checkout-main-grid">
    <!-- Coluna Esquerda: O Plano e Valor -->
    <div class="checkout-side-sum">
        <div class="sum-content">
            <span class="badge badge-primary-light mb-2">Assinatura Digital</span>
            <h2 class="font-weight-800 mb-0">
                {{ match($venda->tipo_negociacao ?? ($venda->plano ?? 'mensal')) {
                    'mensal'       => 'Plano Mensal',
                    'anual'        => 'Plano Anual Premium',
                    'anual_avista' => 'Plano Anual à Vista',
                    'anual_12x'    => 'Anual - 12 prestações',
                    default        => 'Assinatura Basileia'
                } }}
            </h2>
            
            <div class="display-value my-4 text-primary">
                R$ {{ number_format($venda->valor, 2, ',', '.') }}
                <small class="text-muted d-block mt-n2" style="font-size: 0.8rem; font-weight: 600;">{{ ($venda->tipo_negociacao ?? '') === 'mensal' ? 'COBRANÇA MENSAL' : 'COBRANÇA ANUAL' }}</small>
            </div>

            @php
                $isAnual = str_contains($venda->tipo_negociacao ?? '', 'anual');
            @endphp
            
            @if($isAnual)
            <div class="promo-box mb-4">
                <i class="fas fa-gift mr-2"></i> Você economizou no Plano Anual!
            </div>
            @endif

            <ul class="premium-benefits mt-4">
                <li class="p-benefit">
                    <i class="fas fa-check"></i>
                    <div>
                        <strong>Gestão com IA Integrada</strong>
                        <p class="small text-muted mb-0">Assistente para membros e solicitações.</p>
                    </div>
                </li>
                <li class="p-benefit">
                    <i class="fas fa-check"></i>
                    <div>
                        <strong>Automação de Cultos</strong>
                        <p class="small text-muted mb-0">Lembretes e avisos 100% automáticos.</p>
                    </div>
                </li>
                <li class="p-benefit">
                    <i class="fas fa-check"></i>
                    <div>
                        <strong>Células e Eventos</strong>
                        <p class="small text-muted mb-0">Controle total de presença e cursos.</p>
                    </div>
                </li>
            </ul>

            <div class="security-stamp mt-5 pt-3 d-none d-lg-flex">
                <i class="fas fa-shield-check fa-2x text-success mr-3"></i>
                <div>
                    <span class="d-block font-weight-bold small text-dark">Ambiente Seguro</span>
                    <span class="small text-muted">Proteção de dados SSL 256 bits</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna Direita: Pagamento -->
    <div class="checkout-side-pay">
        <div class="pay-content">
            <h5 class="font-weight-800 text-dark mb-4">Forma de Pagamento</h5>
            
            <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
                @csrf

                @php
                    $metodoAtual = old('payment_method', $restritoMetodo ?? 'credit_card');
                @endphp

                @if(!isset($restritoMetodo))
                <div class="payment-tabs mb-4">
                    <label class="tab-item {{ $metodoAtual === 'credit_card' ? 'active' : '' }}" onclick="updateMethod('credit_card', this)">
                        <input type="radio" name="payment_method" value="credit_card" {{ $metodoAtual === 'credit_card' ? 'checked' : '' }}>
                        <i class="fas fa-credit-card"></i> Cartão
                    </label>
                    <label class="tab-item {{ $metodoAtual === 'pix' ? 'active' : '' }}" onclick="updateMethod('pix', this)">
                        <input type="radio" name="payment_method" value="pix" {{ $metodoAtual === 'pix' ? 'checked' : '' }}>
                        <i class="fas fa-bolt"></i> PIX
                    </label>
                    <label class="tab-item {{ $metodoAtual === 'boleto' ? 'active' : '' }}" onclick="updateMethod('boleto', this)">
                        <input type="radio" name="payment_method" value="boleto" {{ $metodoAtual === 'boleto' ? 'checked' : '' }}>
                        <i class="fas fa-barcode"></i> Boleto
                    </label>
                </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ $restritoMetodo }}">
                    <div class="restrito-pill mb-4">
                        <span class="text-uppercase small font-weight-bold">Pagando via:</span>
                        <span class="text-primary ml-1">
                            <i class="fas fa-{{ $restritoMetodo === 'credit_card' ? 'credit-card' : ($restritoMetodo === 'pix' ? 'bolt' : 'barcode') }} mr-1"></i>
                            <strong>{{ $restritoMetodo === 'credit_card' ? 'CARTÃO DE CRÉDITO' : ($restritoMetodo === 'pix' ? 'PIX INSTANTÂNEO' : 'BOLETO BANCÁRIO') }}</strong>
                        </span>
                    </div>
                @endif

                <div class="form-wrapper-premium">
                    <div class="form-group mb-3">
                        <label class="sum-label">Seu E-mail comercial</label>
                        <input type="email" class="form-control-minimal" value="{{ $venda->email_cliente ?? ($venda->cliente->email ?? '') }}" readonly>
                    </div>

                    {{-- Seção: Cartão --}}
                    <div id="box-card" style="{{ $metodoAtual === 'credit_card' ? 'display:block' : 'display:none' }}">
                        <div class="form-group mb-3">
                            <label class="sum-label">Número do Cartão de Crédito</label>
                            <input type="text" name="numero_cartao" class="form-control-minimal" placeholder="0000 0000 0000 0000" oninput="formatarCard(this)">
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <div class="form-group mb-3">
                                    <label class="sum-label">Validade (MM/AA)</label>
                                    <input type="text" name="expiry" class="form-control-minimal" placeholder="MM/AA" oninput="formatarDate(this)">
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group mb-3">
                                    <label class="sum-label">CVC</label>
                                    <input type="text" name="cvv" class="form-control-minimal" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="sum-label">Nome Completo (como no cartão)</label>
                            <input type="text" name="nome_cartao" class="form-control-minimal" placeholder="NOME NO CARTÃO">
                        </div>
                    </div>

                    {{-- Informativo Pix/Boleto --}}
                    <div id="box-info" style="{{ $metodoAtual === 'credit_card' ? 'display:none' : 'display:block' }}">
                        <div class="info-alert mb-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span id="txt-pagamento">O QR Code / Boleto será gerado ao confirmar.</span>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="sum-label">CPF ou CNPJ do Titular</label>
                        <input type="text" name="cpf_titular" class="form-control-minimal" placeholder="000.000.000-00" oninput="formatarDoc(this)" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary-elite" id="btn-finalizar">
                    <i class="fas fa-lock mr-2"></i> Confirmar Pagamento de R$ {{ number_format($venda->valor, 2, ',', '.') }}
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .checkout-main-grid { display: flex; flex-direction: row; min-height: 600px; }
    .checkout-side-sum { width: 42%; background: #fbfbfc; padding: 50px 45px; border-right: 1px solid #f2f4f7; }
    .checkout-side-pay { width: 58%; padding: 50px 55px; background: #fff; }
    
    .font-weight-800 { font-weight: 800; }
    .display-value { font-size: 2.6rem; font-weight: 800; letter-spacing: -2px; }
    
    .badge-primary-light { background: rgba(76, 29, 149, 0.08); color: var(--primary); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 5px 12px; border-radius: 6px; letter-spacing: 0.5px; }
    .promo-box { background: #f0fdf4; color: #166534; font-size: 0.75rem; font-weight: 700; padding: 8px 15px; border-radius: 8px; border: 1px solid #dcfce7; }
    
    .premium-benefits { list-style: none; padding: 0; }
    .p-benefit { display: flex; gap: 14px; margin-bottom: 22px; align-items: flex-start; }
    .p-benefit i { color: #10b981; font-size: 0.9rem; margin-top: 5px; }
    .p-benefit strong { font-size: 0.9rem; color: #111827; display: block; }
    .p-benefit p { font-size: 0.825rem; line-height: 1.4; }

    .payment-tabs { display: flex; background: #f4f5f7; padding: 5px; border-radius: 12px; gap: 4px; }
    .tab-item { flex: 1; text-align: center; padding: 10px 0; font-size: 0.75rem; font-weight: 800; color: #6b7280; border-radius: 9px; cursor: pointer; transition: all 0.2s; margin-bottom: 0; text-transform: uppercase; }
    .tab-item.active { background: #fff; color: var(--primary); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .tab-item i { margin-right: 6px; font-size: 0.9rem; }
    .tab-item input { display: none; }

    .restrito-pill { background: #f8f9fa; border: 1px solid #edf0f5; padding: 10px 18px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; }

    .sum-label { display: block; font-size: 0.725rem; font-weight: 800; color: #4b5563; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control-minimal { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 11px 16px; font-size: 0.95rem; font-weight: 500; transition: all 0.2s; background: #fff; }
    .form-control-minimal:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(76, 29, 149, 0.1); outline: none; }

    .info-alert { background: #f0f7ff; color: #1e40af; font-size: 0.85rem; font-weight: 600; padding: 12px 18px; border-radius: 10px; border: 1px solid #dbeafe; }

    .btn-primary-elite { background: var(--primary-gradient); color: white; border: none; width: 100%; padding: 18px; border-radius: 14px; font-weight: 800; font-size: 1rem; box-shadow: 0 10px 25px rgba(76, 29, 149, 0.25); transition: all 0.25s; cursor: pointer; }
    .btn-primary-elite:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(76, 29, 149, 0.35); }

    @media (max-width: 900px) {
        .checkout-main-grid { flex-direction: column; }
        .checkout-side-sum, .checkout-side-pay { width: 100%; border: none; }
        .checkout-side-sum { order: 2; padding: 35px; }
        .checkout-side-pay { order: 1; padding: 35px; }
    }
</style>

<script>
function updateMethod(metodo, element) {
    document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    document.getElementById('box-card').style.display = metodo === 'credit_card' ? 'block' : 'none';
    document.getElementById('box-info').style.display = metodo === 'credit_card' ? 'none' : 'block';
    
    const txt = document.getElementById('txt-pagamento');
    const btn = document.getElementById('btn-finalizar');
    const valor = "R$ {{ number_format($venda->valor, 2, ',', '.') }}";
    
    if (metodo === 'pix') {
        txt.innerText = "Um QR Code Pix será gerado para o seu pagamento seguro.";
        btn.innerHTML = '<i class="fas fa-bolt mr-2"></i> Gerar QR Code PIX ' + valor;
    } else if (metodo === 'boleto') {
        txt.innerText = "O boleto bancário será gerado e poderá ser pago em qualquer banco.";
        btn.innerHTML = '<i class="fas fa-barcode mr-2"></i> Gerar Boleto de ' + valor;
    } else {
        btn.innerHTML = '<i class="fas fa-lock mr-2"></i> Confirmar Pagamento de ' + valor;
    }
}

function formatarCard(input) { let v = input.value.replace(/\D/g, '').substring(0, 16); input.value = v.replace(/(.{4})/g, '$1 ').trim(); }
function formatarDate(input) { let v = input.value.replace(/\D/g, '').substring(0, 4); if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2); input.value = v; }
function formatarDoc(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) { v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'); }
    else { v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5'); }
    input.value = v;
}

document.getElementById('checkout-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-finalizar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESSANDO PAGAMENTO...';
});
</script>
@endsection
