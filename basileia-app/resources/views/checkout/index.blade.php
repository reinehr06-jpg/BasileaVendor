@extends('layouts.checkout')

@section('title', 'Finalizar Pagamento - Basileia Vendas')

@section('content')
<!-- Coluna Esquerda: Resumo e Valor -->
<div class="summary-side">
    <a href="javascript:history.back()" class="btn-back">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>

    <div class="brand-logo">Basiléia <span style="font-weight: 400; opacity: 0.6;">Vendas</span></div>
    
    <div class="mt-4">
        <h5 class="text-muted small font-weight-bold text-uppercase mb-2">Plano Escolhido</h5>
        <h2 class="font-weight-bold mb-0">
            {{ match($venda->tipo_negociacao ?? ($venda->plano ?? 'mensal')) {
                'mensal'       => 'Plano Mensal',
                'anual'        => 'Plano Anual Premium',
                'anual_avista' => 'Plano Anual à Vista',
                'anual_12x'    => 'Plano Anual - 12x',
                default        => 'Assinatura Basileia'
            } }}
        </h2>
        
        <div class="plan-price">
            R$ {{ number_format($venda->valor, 2, ',', '.') }}
            <small>/ {{ ($venda->tipo_negociacao ?? '') === 'mensal' ? 'mês' : 'ano' }}</small>
        </div>
        
        @if(str_contains($venda->tipo_negociacao ?? '', 'anual'))
        <span class="badge badge-success px-3 py-1 mb-4" style="font-size: 0.75rem; border-radius: 20px;">
            <i class="fas fa-certificate mr-1"></i> ECONOMIA DE 20% ATIVADA
        </span>
        @endif

        <ul class="feature-list">
            <li class="feature-item">
                <i class="fas fa-microchip"></i>
                <span><strong>Gestão de Membros com IA</strong>: Auxílio inteligente para solicitações da igreja.</span>
            </li>
            <li class="feature-item">
                <i class="fas fa-bell"></i>
                <span><strong>Lembretes Automáticos</strong>: Notificações inteligentes de Cultos e Células.</span>
            </li>
            <li class="feature-item">
                <i class="fas fa-calendar-check"></i>
                <span><strong>Gestão de Eventos</strong>: Ciclos de cursos e reuniões 100% automatizados.</span>
            </li>
            <li class="feature-item">
                <i class="fas fa-users-gear"></i>
                <span><strong>Igreja Conectada</strong>: Hub completo para controle de ministérios e liderança.</span>
            </li>
            <li class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Acesso Ilimitado e Suporte Prioritário.</span>
            </li>
        </ul>

        <div class="total-row d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-dark">Total Geral</span>
            <span class="font-weight-bold h4 mb-0 text-primary">R$ {{ number_format($venda->valor, 2, ',', '.') }}</span>
        </div>
        <p class="small text-muted mt-2">Pagamento seguro processado via Asaas Gateway.</p>
    </div>
</div>

<!-- Coluna Direita: Pagamento -->
<div class="payment-side">
    <div class="form-section-title">Informações de Contato</div>
    <div class="form-group mb-5">
        <label class="small text-muted font-weight-bold text-uppercase">E-mail para Acesso</label>
        <input type="email" class="form-control form-control-premium" 
               value="{{ $venda->email_cliente ?? ($venda->cliente->email ?? '') }}" readonly>
        <span class="small text-muted d-block mt-2"><i class="fas fa-info-circle mr-1"></i> Os dados de acesso serão enviados para este e-mail.</span>
    </div>

    <div class="form-section-title">Método de Pagamento</div>
    
    <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
        @csrf

        {{-- Gestão de Abas: Só exibe se não houver restrição --}}
        @php
            $metodoAtual = old('payment_method', $restritoMetodo ?? 'credit_card');
            $exibirAbas = !isset($restritoMetodo);
        @endphp

        @if($exibirAbas)
        <div class="btn-group btn-group-toggle d-flex mb-4" data-toggle="buttons">
            <label class="btn btn-outline-primary active flex-fill py-3 border-radius-left" onclick="mostrarSecao('cartao')">
                <input type="radio" name="payment_method" value="credit_card" checked>
                <i class="fas fa-credit-card mr-1"></i> Cartão
            </label>
            <label class="btn btn-outline-primary flex-fill py-3" onclick="mostrarSecao('pix')">
                <input type="radio" name="payment_method" value="pix">
                <i class="fas fa-bolt mr-1"></i> PIX
            </label>
            <label class="btn btn-outline-primary flex-fill py-3 border-radius-right" onclick="mostrarSecao('boleto')">
                <input type="radio" name="payment_method" value="boleto">
                <i class="fas fa-barcode mr-1"></i> Boleto
            </label>
        </div>
        @else
            <input type="hidden" name="payment_method" value="{{ $restritoMetodo }}">
            <div class="p-3 bg-light rounded border mb-4 d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-uppercase small text-muted">Método Selecionado</span>
                <span class="text-primary font-weight-bold">
                    <i class="fas fa-{{ $restritoMetodo === 'credit_card' ? 'credit-card' : ($restritoMetodo === 'pix' ? 'bolt' : 'barcode') }} mr-1"></i>
                    {{ $restritoMetodo === 'credit_card' ? 'Cartão de Crédito' : ($restritoMetodo === 'pix' ? 'PIX' : 'Boleto') }}
                </span>
            </div>
        @endif

        {{-- Seção: Cartão --}}
        <div id="secao-cartao" style="{{ $metodoAtual === 'credit_card' ? 'display:block' : 'display:none' }}">
            <div class="form-group mb-4">
                <label class="small text-muted font-weight-bold text-uppercase">Número do Cartão</label>
                <input type="text" name="numero_cartao" class="form-control form-control-premium" placeholder="0000 0000 0000 0000" oninput="formatarCartao(this)">
            </div>
            
            <div class="form-group mb-4">
                <label class="small text-muted font-weight-bold text-uppercase">Nome do Titular</label>
                <input type="text" name="nome_cartao" class="form-control form-control-premium" placeholder="COMO NO CARTÃO">
            </div>

            <div class="row">
                <div class="col-8">
                    <div class="form-group">
                        <label class="small text-muted font-weight-bold text-uppercase">Validade</label>
                        <input type="text" name="expiry" class="form-control form-control-premium" placeholder="MM / AA" oninput="formatarValidade(this)">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="small text-muted font-weight-bold text-uppercase">CVC</label>
                        <input type="text" name="cvv" class="form-control form-control-premium" placeholder="123" maxlength="4">
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção: PIX --}}
        <div id="secao-pix" style="{{ $metodoAtual === 'pix' ? 'display:block' : 'display:none' }}">
            <div class="alert alert-light border border-dashed py-4 text-center">
                <i class="fas fa-bolt text-warning mb-2 h2"></i>
                <p class="font-weight-bold mb-0">Confirmação Instantânea</p>
                <small class="text-muted">A aprovação do seu acesso será feita em segundos.</small>
            </div>
        </div>

        {{-- Seção: Boleto --}}
        <div id="secao-boleto" style="{{ $metodoAtual === 'boleto' ? 'display:block' : 'display:none' }}">
            <div class="alert alert-light border border-dashed py-4 text-center">
                <i class="fas fa-barcode text-primary mb-2 h2"></i>
                <p class="font-weight-bold mb-0">Boleto Bancário</p>
                <small class="text-muted">A liberação ocorre em até 2 dias úteis após o pagamento.</small>
            </div>
        </div>

        <div class="form-group mt-4">
            <label class="small text-muted font-weight-bold text-uppercase">CPF / CNPJ do Titular</label>
            <input type="text" name="cpf_titular" class="form-control form-control-premium" placeholder="000.000.000-00" oninput="formatarCpf(this)" required>
        </div>

        <button type="submit" class="btn-subscribe mt-4" id="btn-submit">
            <i class="fas fa-check-circle mr-2"></i> Pagar R$ {{ number_format($venda->valor, 2, ',', '.') }}
        </button>

        <div class="text-center mt-4">
            <i class="fas fa-lock text-muted mr-1"></i> <span class="small text-muted">Transação Protegida por Basiléia</span>
        </div>
    </form>
</div>

<script>
function mostrarSecao(secao) {
    document.getElementById('secao-cartao').style.display = secao === 'cartao' ? 'block' : 'none';
    document.getElementById('secao-pix').style.display    = secao === 'pix'    ? 'block' : 'none';
    document.getElementById('secao-boleto').style.display = secao === 'boleto' ? 'block' : 'none';
    
    // Altera texto do botão
    const btn = document.getElementById('btn-submit');
    const valor = "R$ {{ number_format($venda->valor, 2, ',', '.') }}";
    if (secao === 'pix') btn.innerHTML = '<i class="fas fa-bolt mr-2"></i> Gerar PIX ' + valor;
    else if (secao === 'boleto') btn.innerHTML = '<i class="fas fa-barcode mr-2"></i> Gerar Boleto ' + valor;
    else btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Pagar ' + valor;
}

function formatarCartao(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}

function formatarValidade(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length > 2) v = v.substring(0, 2) + ' / ' + v.substring(2);
    input.value = v;
}

function formatarCpf(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) {
        v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    }
    input.value = v;
}

document.getElementById('checkout-form').addEventListener('submit', function() {
    document.getElementById('btn-submit').disabled = true;
    document.getElementById('btn-submit').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processando...';
});
</script>
@endsection
