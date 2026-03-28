@extends('layouts.checkout')

@section('title', 'Finalizar Registro - Basileia Vendas')

@section('content')
<div class="checkout-elite-grid">
    <!-- Lado Esquerdo: Identidade e Valor (Roxo) -->
    <div class="checkout-left-pane">
        <div class="pane-content">
            <div class="elite-logo">
                <img src="/assets/img/logo_oficial.png" alt="Basiléia Vendas" style="max-width: 180px; filter: brightness(0) invert(1);">
            </div>
            
            <div class="elite-status-badge mt-4">
                PLANO PROFISSIONAL ATIVADO
            </div>

            <h1 class="elite-plan-name mt-3">Plano Mensal</h1>
            
            <div class="elite-price-row mt-2">
                <span class="currency">R$</span>
                <span class="value">{{ number_format($venda->valor, 2, ',', '.') }}</span>
                <span class="cycle ml-2">COBRANÇA MENSAL</span>
            </div>

            <ul class="elite-feature-list mt-5">
                <li class="e-feature">
                    <div class="e-check"><i class="fas fa-check"></i></div>
                    <div class="e-text">
                        <strong>Gestão com IA Integrada</strong>
                        <p>Aplicação para solicitações da igreja.</p>
                    </div>
                </li>
                <li class="e-feature">
                    <div class="e-check"><i class="fas fa-check"></i></div>
                    <div class="e-text">
                        <strong>Automação de Cultos</strong>
                        <p>Lembretes e avisos 100% automáticos.</p>
                    </div>
                </li>
                <li class="e-feature">
                    <div class="e-check"><i class="fas fa-check"></i></div>
                    <div class="e-text">
                        <strong>Células e Eventos</strong>
                        <p>Controle total de presença, cursos e células.</p>
                    </div>
                </li>
            </ul>

            <div class="elite-security-row mt-5">
                <div class="icon-security"><i class="fas fa-shield-check"></i></div>
                <div class="text-security">
                    <strong>Pagamento 100% Seguro</strong>
                    <p>Seus dados são protegidos por criptografia SSL.</p>
                </div>
            </div>

            <div class="elite-card-icons mt-4 opacity-75">
                <img src="https://logodownload.org/wp-content/uploads/2016/10/visa-logo-1.png" style="height: 12px; margin-right: 15px;">
                <img src="https://logodownload.org/wp-content/uploads/2014/07/mastercard-logo-1.png" style="height: 20px; margin-right: 15px;">
                <img src="https://logodownload.org/wp-content/uploads/2015/05/amex-logo-american-express-1.png" style="height: 15px; margin-right: 15px;">
                <img src="https://logodownload.org/wp-content/uploads/2015/02/elo-logo-1.png" style="height: 15px;">
            </div>
        </div>
    </div>

    <!-- Lado Direito: Formulário de Pagamento (Branco) -->
    <div class="checkout-right-pane">
        <div class="pane-content-right">
            <h2 class="elite-pay-title">Pagamento Seguro</h2>
            
            <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
                @csrf

                @php
                    $metodoAtual = old('payment_method', $restritoMetodo ?? 'credit_card');
                @endphp

                <div class="d-flex align-items-center mb-4 mt-2">
                    <span class="text-muted small font-weight-bold mr-3" style="letter-spacing: 0.5px; opacity: 0.7;">PAGAMENTO VIA:</span>
                    <div class="elite-method-pill">
                        <i class="fas fa-credit-card mr-1"></i> CARTÃO DE CRÉDITO
                    </div>
                </div>

                {{-- Só exibe as abas se não houver restrição --}}
                @if(!isset($restritoMetodo))
                <div class="payment-tabs-minimal mb-4">
                    <label class="tab-btn {{ $metodoAtual === 'credit_card' ? 'active' : '' }}" onclick="selectMethod('credit_card', this)">
                        <input type="radio" name="payment_method" value="credit_card" checked> CARTÃO
                    </label>
                    <label class="tab-btn {{ $metodoAtual === 'pix' ? 'active' : '' }}" onclick="selectMethod('pix', this)">
                        <input type="radio" name="payment_method" value="pix"> PIX
                    </label>
                    <label class="tab-btn {{ $metodoAtual === 'boleto' ? 'active' : '' }}" onclick="selectMethod('boleto', this)">
                        <input type="radio" name="payment_method" value="boleto"> BOLETO
                    </label>
                </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ $restritoMetodo }}">
                @endif

                <div class="form-elite-inputs">
                    <div class="form-group-lite">
                        <label>E-MAIL DE ACESSO AO PAINEL</label>
                        <input type="email" class="form-control-lite bg-white" value="{{ $venda->email_cliente ?? ($venda->cliente->email ?? '') }}" disabled>
                        <span class="small text-muted mt-1 d-block opacity-75">Este e-mail receberá suas credenciais de login.</span>
                    </div>

                    <div id="box-card-elite" style="{{ $metodoAtual === 'credit_card' ? 'display:block' : 'display:none' }}">
                        <div class="form-group-lite">
                            <label>NÚMERO DO CARTÃO</label>
                            <div class="input-icon-wrap">
                                <input type="text" name="numero_cartao" class="form-control-lite" placeholder="0000 0000 0000 0000" oninput="formatCard(this)">
                                <i class="fas fa-credit-card text-muted"></i>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <div class="form-group-lite">
                                    <label>EXPIRAÇÃO</label>
                                    <input type="text" name="expiry" class="form-control-lite text-center" placeholder="01 / 26" oninput="formatExpiry(this)">
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group-lite">
                                    <label>CVC</label>
                                    <input type="text" name="cvv" class="form-control-lite text-center" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                        <div class="form-group-lite">
                            <label>NOME COMPLETO (ESCRITO NO CARTÃO)</label>
                            <input type="text" name="nome_cartao" class="form-control-lite" placeholder="EDSON REINEHR">
                        </div>
                    </div>

                    <div id="box-info-elite" style="{{ $metodoAtual === 'credit_card' ? 'display:none' : 'display:block' }}">
                        <div class="alert alert-light border border-dashed text-center py-4 mb-3">
                            <i class="fas fa-spinner fa-spin mr-2 text-primary"></i>
                            <span class="small font-weight-bold text-muted">Aguardando geração do código...</span>
                        </div>
                    </div>

                    <div class="form-group-lite mb-4">
                        <label>DOCUMENTO DO PAGADOR (CPF ou CNPJ)</label>
                        <input type="text" name="cpf_titular" class="form-control-lite" placeholder="000.000.000-00" oninput="formatDoc(this)" required>
                    </div>
                </div>

                <button type="submit" class="btn-elite-confirm" id="btn-submit-elite">
                    Assinar Plano por R$ {{ number_format($venda->valor, 2, ',', '.') }}
                </button>

                <div class="text-center mt-4 opacity-75">
                    <span class="small font-weight-bold text-success">
                        <i class="fas fa-lock mr-1"></i> Pagamento 100% Seguro
                    </span>
                </div>
            </form>
            
            <div class="elite-footer-disclaimer mt-auto pt-5 text-center small text-muted">
                &copy; {{ date('Y') }} Basileia Vendas - Enterprise Cloud Operations
            </div>
        </div>
    </div>
</div>

<style>
    .checkout-elite-grid { display: flex; flex-direction: row; min-height: 720px; }
    
    /* Painel Esquerdo */
    .checkout-left-pane { width: 44%; background: linear-gradient(135deg, #7C3AED 0%, #4C1D95 100%); padding: 60px 50px; color: white; display: flex; flex-direction: column; }
    .elite-status-badge { display: inline-block; background: rgba(255,255,255,0.15); color: white; font-size: 0.65rem; font-weight: 800; padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); letter-spacing: 0.5px; }
    .elite-plan-name { font-size: 2.8rem; font-weight: 800; letter-spacing: -1.5px; }
    .elite-price-row { display: flex; align-items: baseline; }
    .elite-price-row .currency { font-size: 1.25rem; font-weight: 700; opacity: 0.8; }
    .elite-price-row .value { font-size: 3.8rem; font-weight: 900; letter-spacing: -3px; margin: 0 4px; }
    .elite-price-row .cycle { font-size: 0.75rem; font-weight: 700; opacity: 0.7; border-left: 2px solid rgba(255,255,255,0.3); padding-left: 10px; }

    .e-feature { display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-start; }
    .e-check { width: 26px; height: 26px; background: #10B981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0; transform: translateY(3px); }
    .e-text strong { font-size: 1rem; font-weight: 800; display: block; line-height: 1.2; }
    .e-text p { font-size: 0.85rem; opacity: 0.7; margin: 2px 0 0; }

    .elite-security-row { display: flex; gap: 16px; background: rgba(255,255,255,0.08); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); align-items: center; }
    .icon-security { width: 36px; height: 36px; background: #10B981; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .text-security strong { font-size: 0.95rem; font-weight: 800; display: block; }
    .text-security p { font-size: 0.8rem; opacity: 0.7; margin: 0; }

    /* Painel Direito */
    .checkout-right-pane { width: 56%; background: #ffffff; padding: 60px 80px; position: relative; }
    .elite-pay-title { font-size: 1.8rem; font-weight: 800; color: #111827; letter-spacing: -0.5px; }
    .elite-method-pill { background: #7C3AED; color: white; padding: 7px 18px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; display: inline-flex; align-items: center; box-shadow: 0 4px 10px rgba(124, 58, 237, 0.2); }

    .form-group-lite { margin-bottom: 22px; }
    .form-group-lite label { display: block; font-size: 0.725rem; font-weight: 800; color: #4b5563; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control-lite { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 13px 18px; font-size: 1rem; font-weight: 500; transition: 0.3s; background: #fdfdfd; }
    .form-control-lite:focus { border-color: #7C3AED; box-shadow: 0 0 0 5px rgba(124, 58, 237, 0.08); outline: none; background: #fff; }
    
    .input-icon-wrap { position: relative; }
    .input-icon-wrap i { position: absolute; right: 18px; top: 16px; font-size: 1.1rem; }

    .btn-elite-confirm { background: #7C3AED; color: white; border: none; width: 100%; padding: 18px; border-radius: 14px; font-weight: 800; font-size: 1.1rem; box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3); transition: 0.3s; cursor: pointer; }
    .btn-elite-confirm:hover { transform: translateY(-3px); box-shadow: 0 12px 35px rgba(124, 58, 237, 0.4); }

    .payment-tabs-minimal { display: flex; gap: 8px; }
    .tab-btn { flex: 1; text-align: center; padding: 12px; border-radius: 10px; border: 1.5px solid #e5e7eb; font-size: 0.75rem; font-weight: 800; color: #6b7280; cursor: pointer; transition: 0.3s; }
    .tab-btn.active { border-color: #7C3AED; color: #7C3AED; background: rgba(124, 58, 237, 0.04); }
    .tab-btn input { display: none; }

    @media (max-width: 992px) {
        .checkout-elite-grid { flex-direction: column; }
        .checkout-left-pane, .checkout-right-pane { width: 100%; padding: 40px 25px; }
        .checkout-left-pane { order: 2; border-top: 1px solid rgba(255,255,255,0.1); }
        .checkout-right-pane { order: 1; padding-bottom: 50px; }
        .elite-plan-name { font-size: 2.2rem; }
        .elite-price-row .value { font-size: 3rem; }
    }
</style>

<script>
function selectMethod(metodo, element) {
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    document.getElementById('box-card-elite').style.display = metodo === 'credit_card' ? 'block' : 'none';
    document.getElementById('box-info-elite').style.display = metodo === 'credit_card' ? 'none' : 'block';
    
    const btn = document.getElementById('btn-submit-elite');
    const valor = "R$ {{ number_format($venda->valor, 2, ',', '.') }}";
    if (metodo === 'pix') btn.innerText = "Gerar QR Code PIX - " + valor;
    else if (metodo === 'boleto') btn.innerText = "Gerar Boleto de " + valor;
    else btn.innerText = "Assinar Plano por " + valor;
}

function formatCard(input) { let v = input.value.replace(/\D/g, '').substring(0, 16); input.value = v.replace(/(.{4})/g, '$1 ').trim(); }
function formatExpiry(input) { let v = input.value.replace(/\D/g, '').substring(0, 4); if (v.length > 2) v = v.substring(0, 2) + ' / ' + v.substring(2); input.value = v; }
function formatDoc(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) { v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'); }
    else { v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5'); }
    input.value = v;
}

document.getElementById('checkout-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit-elite');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-sync fa-spin mr-2"></i> PROCESSANDO PAGAMENTO...';
});
</script>
@endsection
