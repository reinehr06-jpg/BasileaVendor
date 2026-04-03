@extends('layouts.app')
@section('title', 'Nova Venda')

@section('content')
<style>
    /* Payment Method Cards */
    .payment-methods { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .payment-card {
        border: 2px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 18px 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--surface);
        position: relative;
    }
    .payment-card:hover { border-color: rgba(var(--primary-rgb), 0.3); transform: translateY(-2px); box-shadow: var(--shadow-sm); }
    .payment-card.selected { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.03); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12); }
    .payment-card.selected::after { content: '\2713'; position: absolute; top: 8px; right: 10px; background: var(--primary); color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; }
    .payment-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 1.4rem; }
    .payment-icon i { color: white; }
    .payment-icon.pix { background: linear-gradient(135deg, #00b4d8, #0077b6); }
    .payment-icon.boleto { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .payment-icon.card { background: linear-gradient(135deg, #7c3aed, #673AB7); }
    .payment-label { font-weight: 700; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 2px; }
    .payment-hint { font-size: 0.75rem; color: var(--text-muted); }

    /* Negotiation Type Cards */
    .negotiation-types { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .negotiation-card {
        border: 2px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 18px 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--surface);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .negotiation-card:hover { border-color: rgba(var(--primary-rgb), 0.3); transform: translateY(-2px); box-shadow: var(--shadow-sm); }
    .negotiation-card.selected { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.03); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12); }
    .negotiation-card.selected::after { content: '\2713'; position: absolute; top: 8px; right: 10px; background: var(--primary); color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; }
    .negotiation-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; font-size: 1.3rem; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); }
    .negotiation-label { font-weight: 700; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 2px; }
    .negotiation-hint { font-size: 0.75rem; color: var(--text-muted); }

    @media (max-width: 600px) {
        .payment-methods { grid-template-columns: 1fr; }
        .negotiation-types { grid-template-columns: 1fr; }
    }
</style>
<x-page-hero title="Nova Venda" subtitle="Cadastre uma nova venda para seu cliente" icon="fas fa-plus-circle" />

<!-- Auto Data Bar -->
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; flex-wrap: wrap; gap: 32px;">
        <div>
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600;">Vendedor</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-top: 2px;">{{ Auth::user()->name }}</div>
        </div>
        <div>
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600;">Data</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-top: 2px;">{{ now()->format('d/m/Y') }}</div>
        </div>
        <div>
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600;">Status</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--success); margin-top: 2px;">Aguardando pagamento</div>
        </div>
        <div>
            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600;">Origem</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--text); margin-top: 2px;">Manual</div>
        </div>
    </div>
</div>

<form action="{{ route('vendedor.vendas.store') }}" method="POST" id="formNovaVenda" autocomplete="off">
    @csrf
    <input type="text" name="fake_username" style="display:none">
    <input type="password" name="fake_password" style="display:none">

    <!-- ===== BLOCO 1: Identificação do Cliente ===== -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header"><i class="fas fa-building"></i> Identificação do Cliente</div>

        <div class="form-row">
            <div class="form-group">
                <label>Nome da Igreja <span class="required">*</span></label>
                <input type="text" name="nome_igreja" autocomplete="off" class="form-control @error('nome_igreja') is-invalid @enderror" value="{{ old('nome_igreja') }}" required placeholder="Digite o nome completo da igreja">
                @error('nome_igreja') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Nome do Pastor <span class="required">*</span></label>
                <input type="text" name="nome_pastor" autocomplete="off" class="form-control @error('nome_pastor') is-invalid @enderror" value="{{ old('nome_pastor') }}" required placeholder="Digite o nome do pastor responsável">
                @error('nome_pastor') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Localidade <span class="required">*</span></label>
                <input type="text" name="localidade" class="form-control" value="{{ old('localidade') }}" required placeholder="Cidade, estado ou país">
            </div>
            <div class="form-group">
                <label>Moeda <span class="required">*</span></label>
                <select name="moeda" class="form-control">
                    <option value="BRL" {{ old('moeda') == 'BRL' ? 'selected' : '' }}>🇧🇷 BRL - Real</option>
                    <option value="USD" {{ old('moeda') == 'USD' ? 'selected' : '' }}>🇺🇸 USD - Dólar</option>
                    <option value="EUR" {{ old('moeda') == 'EUR' ? 'selected' : '' }}>🇪🇺 EUR - Euro</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Quantidade de Membros <span class="required">*</span></label>
                <input type="number" name="quantidade_membros" id="inputMembros" autocomplete="off" class="form-control @error('quantidade_membros') is-invalid @enderror" value="{{ old('quantidade_membros') }}" required min="1" placeholder="Número de membros da igreja">
                @error('quantidade_membros') <div class="field-error">{{ $message }}</div> @enderror
                <div class="field-hint">O sistema sugere planos automaticamente com base na quantidade.</div>
            </div>
            <div class="form-group">
                <label>CNPJ da Igreja ou CPF do Pastor <span class="required">*</span></label>
                <input type="text" name="documento" id="inputDocumento" autocomplete="off" class="form-control @error('documento') is-invalid @enderror" value="{{ old('documento') }}" required placeholder="Digite o documento" maxlength="18">
                @error('documento') <div class="field-error">{{ $message }}</div> @enderror
                <div id="documentoWarning" style="display: none; margin-top: 8px;"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>WhatsApp de Contato <span class="required">*</span></label>
                <div style="display:flex; gap:8px;">
                    <select name="ddi" id="inputDdi" class="form-control" style="flex:0 0 85px; padding-left: 8px; padding-right: 2px;">
                        <option value="55" data-flag="🇧🇷">🇧🇷 +55</option>
                        <option value="1" data-flag="🇺🇸">🇺🇸 +1</option>
                        <option value="54" data-flag="🇦🇷">🇦🇷 +54</option>
                        <option value="351" data-flag="🇵🇹">🇵🇹 +351</option>
                        <option value="52" data-flag="🇲🇽">🇲🇽 +52</option>
                        <option value="56" data-flag="🇨🇱">🇨🇱 +56</option>
                        <option value="57" data-flag="🇨🇴">🇨🇴 +57</option>
                        <option value="598" data-flag="🇺🇾">🇺🇾 +598</option>
                        <option value="595" data-flag="🇵🇾">🇵🇾 +595</option>
                        <option value="591" data-flag="🇧🇴">🇧🇴 +591</option>
                        <option value="593" data-flag="🇪🇨">🇪🇨 +593</option>
                        <option value="51" data-flag="🇵🇪">🇵🇪 +51</option>
                        <option value="58" data-flag="🇻🇪">🇻🇪 +58</option>
                        <option value="44" data-flag="🇬🇧">🇬🇧 +44</option>
                        <option value="49" data-flag="🇩🇪">🇩🇪 +49</option>
                        <option value="33" data-flag="🇫🇷">🇫🇷 +33</option>
                        <option value="39" data-flag="🇮🇹">🇮🇹 +39</option>
                        <option value="34" data-flag="🇪🇸">🇪🇸 +34</option>
                    </select>
                    <input type="text" name="whatsapp" id="inputWhatsapp" class="form-control" value="{{ old('whatsapp') }}" required placeholder="(00) 00000-0000" maxlength="20" style="flex:1;">
                </div>
                <div id="whatsappWarning" style="display:none; margin-top:5px; color:#ef4444; font-size:0.82rem;"></div>
            </div>
            <div class="form-group">
                <label>E-mail do Cliente <span class="required">*</span></label>
                <input type="email" name="email_cliente" id="inputEmail" autocomplete="off" class="form-control @error('email_cliente') is-invalid @enderror" value="{{ old('email_cliente') }}" required placeholder="email@igreja.com">
                <div id="emailWarning" style="display:none; margin-top:5px; color:#ef4444; font-size:0.82rem;"></div>
                @error('email_cliente') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex: 0.3;">
                <label>CEP <span class="required">*</span></label>
                <input type="text" name="cep" id="inputCep" class="form-control" value="{{ old('cep') }}" required placeholder="00000-000">
            </div>
            <div class="form-group" style="flex: 0.5;">
                <label>Endereço <span class="required">*</span></label>
                <input type="text" name="endereco" id="inputEndereco" class="form-control" value="{{ old('endereco') }}" required placeholder="Avenida Brasil">
            </div>
            <div class="form-group" style="flex: 0.2;">
                <label>Número <span class="required">*</span></label>
                <input type="text" name="numero" id="inputNumero" class="form-control" value="{{ old('numero') }}" required placeholder="123">
            </div>
        </div>
        
        <div class="form-row" style="margin-top: 5px;">
            <div class="form-group" style="flex: 0.3;">
                <label>Complemento</label>
                <input type="text" name="complemento" id="inputComplemento" class="form-control" value="{{ old('complemento') }}" placeholder="Sala 4, Bloco B">
            </div>
            <div class="form-group" style="flex: 0.3;">
                <label>Bairro <span class="required">*</span></label>
                <input type="text" name="bairro" id="inputBairro" class="form-control" value="{{ old('bairro') }}" required placeholder="Centro">
            </div>
            <div class="form-group" style="flex: 0.3;">
                <label>Cidade <span class="required">*</span></label>
                <input type="text" name="cidade" id="inputCidade" class="form-control" value="{{ old('cidade') }}" required placeholder="São Paulo">
            </div>
            <div class="form-group" style="flex: 0.1;">
                <label>UF <span class="required">*</span></label>
                <input type="text" name="estado" id="inputEstado" class="form-control" value="{{ old('estado') }}" required placeholder="SP" maxlength="2">
            </div>
        </div>
    </div>

    <!-- ===== BLOCO 2: Dados Comerciais ===== -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header"><i class="fas fa-tag"></i> Dados Comerciais</div>

        <!-- Plan Selection -->
        <div class="form-group">
            <label>Selecione o Plano <span class="required">*</span></label>
            <input type="hidden" name="plano" id="inputPlano" value="{{ old('plano') }}">
            <div class="planos-grid" id="planosGrid">
                @foreach($planos as $p)
                <div class="plano-card {{ old('plano') == $p['nome'] ? 'selected' : '' }}"
                     data-nome="{{ $p['nome'] }}"
                     data-min="{{ $p['min_membros'] }}"
                     data-max="{{ $p['max_membros'] }}"
                     data-mensal="{{ $p['valor_mensal'] }}"
                     data-anual="{{ $p['valor_anual'] }}"
                     data-consulte="{{ $p['consulte'] ?? false ? '1' : '0' }}">
                    <div class="plano-name">Basiléia {{ $p['nome'] }}</div>
                    <div class="plano-range">
                        @if($p['max_membros'] == 99999)
                            Acima de {{ $p['min_membros'] - 1 }} membros
                        @else
                            Até {{ $p['max_membros'] }} membros
                        @endif
                    </div>
                    @if(!empty($p['consulte']))
                        <div class="plano-price" style="font-size: 0.9rem; color: var(--primary);">
                            <i class="fas fa-headset"></i> Negociar
                        </div>
                        <div class="plano-price-label">valor personalizado</div>
                    @else
                        <div class="plano-price" data-mensal="{{ $p['valor_mensal'] }}" data-anual="{{ $p['valor_anual'] }}">
                            R$ {{ number_format($p['valor_mensal'], 2, ',', '.') }}
                        </div>
                        <div class="plano-price-label">por mês</div>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="field-hint">O plano ideal é selecionado automaticamente com base no número de membros.</div>
            @error('plano') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <!-- Negotiation Type (PRIMEIRO - antes do pagamento) -->
        <div class="form-group">
            <label>Período de Contrato <span class="required">*</span></label>
            <input type="hidden" name="tipo_negociacao" id="selectTipoNegociacao" value="{{ old('tipo_negociacao') }}">
            <div class="negotiation-types" id="negotiationGrid">
                <div class="negotiation-card {{ old('tipo_negociacao') == 'mensal' ? 'selected' : '' }}" data-value="mensal">
                    <div class="negotiation-icon"><i class="fas fa-calendar"></i></div>
                    <div class="negotiation-label">Mensal</div>
                    <div class="negotiation-hint">Cobrança recorrente</div>
                </div>
                <div class="negotiation-card {{ old('tipo_negociacao') == 'anual' ? 'selected' : '' }}" data-value="anual">
                    <div class="negotiation-icon"><i class="fas fa-calendar-days"></i></div>
                    <div class="negotiation-label">Anual</div>
                    <div class="negotiation-hint">Economize com desconto</div>
                </div>
            </div>
            @error('tipo_negociacao') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <!-- Payment Method (filtrado pelo período) -->
        <div class="form-group" style="margin-top: 20px;">
            <label>Forma de Pagamento <span class="required">*</span></label>
            <input type="hidden" name="forma_pagamento" id="selectFormaPagamento" value="{{ old('forma_pagamento') }}">
            <div id="paymentHint" style="margin-bottom:10px; color:var(--muted); font-size:0.85rem; display:none;">
                Selecione o período de contrato primeiro.
            </div>
            <div class="payment-methods" id="paymentMethodsGrid">
                <div class="payment-card {{ old('forma_pagamento') == 'PIX' ? 'selected' : '' }}" data-value="PIX" data-mensal="1" data-anual="1">
                    <div class="payment-icon pix"><i class="fas fa-bolt"></i></div>
                    <div class="payment-label">PIX</div>
                    <div class="payment-hint">Enviado para aprovação do ADM</div>
                </div>
                <div class="payment-card {{ old('forma_pagamento') == 'BOLETO' ? 'selected' : '' }}" data-value="BOLETO" data-mensal="0" data-anual="1">
                    <div class="payment-icon boleto"><i class="fas fa-file-lines"></i></div>
                    <div class="payment-label">Boleto</div>
                    <div class="payment-hint">Vencimento em 3 dias</div>
                </div>
                <div class="payment-card {{ old('forma_pagamento') == 'CREDIT_CARD' ? 'selected' : '' }}" data-value="CREDIT_CARD" data-mensal="1" data-anual="1">
                    <div class="payment-icon card"><i class="fas fa-credit-card"></i></div>
                    <div class="payment-label">Cartão de Crédito</div>
                    <div class="payment-hint">Cobrança recorrente</div>
                </div>
            </div>
            @error('forma_pagamento') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <!-- Installment Row -->
        <div class="form-row hidden" id="parcelamentoRow" style="margin-top: 4px;">
            <div class="form-group">
                <label>Número de Parcelas</label>
                <select name="parcelas" id="selectParcelas" class="form-control">
                    @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ old('parcelas', '1') == $i ? 'selected' : '' }}>{{ $i }}x{{ $i == 1 ? ' (à vista)' : '' }}</option>
                    @endfor
                </select>
                <div class="field-hint">Selecione o número de parcelas no cartão de crédito.</div>
            </div>
            <div class="form-group">
                <label>Valor da Parcela</label>
                <div style="padding: 10px 14px; background: var(--bg); border-radius: var(--radius-sm); font-weight: 700; color: var(--primary); font-size: 1.1rem;" id="valorParcela">
                    R$ 0,00
                </div>
                <div class="field-hint">Calculado automaticamente.</div>
            </div>
        </div>

        <!-- Discount Row (hidden for Performance) -->
        <div class="form-row" id="descontoRow">
            <div class="form-group" style="flex: 0.4;">
                <label>Desconto (%)</label>
                <div class="input-group">
                    <input type="number" step="0.1" name="desconto" id="inputDesconto" autocomplete="off" class="form-control @error('desconto') is-invalid @enderror" value="{{ old('desconto', 0) }}" min="0" max="{{ $maxDesconto }}" placeholder="0">
                    <span class="input-group-text">%</span>
                </div>
                @error('desconto') <div class="field-error">{{ $message }}</div> @enderror
                <div class="field-hint">Máximo: {{ $maxDesconto }}%. Acima de 5% requer aprovação.</div>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Observação</label>
                <textarea name="observacao" class="form-control" rows="2" placeholder="Informações adicionais sobre a venda..." style="resize: vertical; min-height: 42px; max-height: 100px;">{{ old('observacao') }}</textarea>
            </div>
        </div>

        <!-- Performance Value Row -->
        <div class="form-row hidden" id="valorPerformanceRow">
            <div class="form-group" style="flex: 0.5;">
                <label>Valor Combinado <span class="required">*</span></label>
                <div class="input-group">
                    <span class="input-group-text" style="border-left: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-sm) 0 0 var(--radius-sm);" id="perfCurrencyPrefix">R$</span>
                    <input type="number" step="0.01" name="valor_performance" id="inputValorPerformance" autocomplete="off" class="form-control @error('valor_performance') is-invalid @enderror" value="{{ old('valor_performance') }}" min="0.01" placeholder="0,00" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                </div>
                @error('valor_performance') <div class="field-error">{{ $message }}</div> @enderror
                <div class="field-hint">Este valor será enviado para aprovação do administrador.</div>
            </div>
            <div class="form-group" style="flex: 0.5;">
                <label>Observação da Negociação</label>
                <textarea name="observacao_negociacao" class="form-control" placeholder="Detalhes da negociação...">{{ old('observacao_negociacao') }}</textarea>
            </div>
        </div>

        <!-- Performance Warning -->
        <div id="avisoAprovacaoPerformance" class="hidden" style="margin-top: 8px;">
            <div class="warning-box">
                <i class="fas fa-triangle-exclamation"></i>
                <div>
                    <div class="warning-title">Plano requer aprovação</div>
                    <div class="warning-text">O plano Basiléia Performance sempre requer aprovação do administrador. Após preencher o valor combinado, a venda será enviada para análise.</div>
                </div>
            </div>
        </div>

        <!-- Value Summary -->
        <div class="valor-summary hidden" id="valorResumo">
            <div>
                <div class="label">Valor Final da Cobrança</div>
                <div class="detalhes" id="resumoDetalhes"></div>
            </div>
            <div class="valor" id="valorFinal">R$ 0,00</div>
        </div>
    </div>

    <!-- ===== Footer Actions ===== -->
    <div class="d-flex justify-end gap-2" style="padding-top: 8px;">
        <a href="{{ route('vendedor.vendas') }}" class="btn btn-outline">
            <i class="fas fa-xmark"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary btn-lg" id="btnSalvar">
            <i class="fas fa-check"></i> Gerar Cobrança e Salvar
        </button>
    </div>
</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputMembros = document.getElementById('inputMembros');
    const inputPlano = document.getElementById('inputPlano');
    const selectTipo = document.getElementById('selectTipoNegociacao');
    const selectFormaPagamento = document.getElementById('selectFormaPagamento');
    const inputDesconto = document.getElementById('inputDesconto');
    const inputDocumento = document.getElementById('inputDocumento');
    const inputWhatsapp = document.getElementById('inputWhatsapp');
    const valorResumo = document.getElementById('valorResumo');
    const valorFinal = document.getElementById('valorFinal');
    const resumoDetalhes = document.getElementById('resumoDetalhes');
    const cards = document.querySelectorAll('.plano-card');
    const parcelamentoRow = document.getElementById('parcelamentoRow');
    const selectParcelas = document.getElementById('selectParcelas');
    const valorParcelaEl = document.getElementById('valorParcela');
    const inputValorPerformance = document.getElementById('inputValorPerformance');
    const selectMoeda = document.querySelector('select[name="moeda"]');

    // Currency symbols
    const currencySymbols = { BRL: 'R$', USD: 'US$', EUR: '€' };
    function getCurrency() { return selectMoeda ? selectMoeda.value : 'BRL'; }
    function getSymbol() { return currencySymbols[getCurrency()] || 'R$'; }
    function fmtCurrency(value) {
        return getSymbol() + ' ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Currency change
    if (selectMoeda) {
        selectMoeda.addEventListener('change', function() {
            // Update Performance input prefix
            const perfPrefix = document.getElementById('perfCurrencyPrefix');
            if (perfPrefix) perfPrefix.textContent = getSymbol();
            // Recalculate everything
            updatePriceLabels();
            calcularValor();
            calcularValorParcela();
        });
    }

    // === PAYMENT METHOD CARD SELECT ===
    document.querySelectorAll('.payment-card').forEach(card => {
        card.addEventListener('click', function() {
            if (!selectTipo.value) {
                BasileiaToast.warning('Selecione o período de contrato primeiro.');
                return;
            }
            document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            selectFormaPagamento.value = this.dataset.value;
            
            // Parcelamento só para Anual + Cartão
            const isAnual = selectTipo.value === 'anual';
            const isCartao = this.dataset.value === 'CREDIT_CARD';
            parcelamentoRow.classList.toggle('hidden', !(isAnual && isCartao));
            
            if (isAnual && isCartao) calcularValorParcela();
        });
    });

    // === NEGOTIATION TYPE CARD SELECT ===
    function filtrarPagamentos(tipo) {
        const paymentHint = document.getElementById('paymentHint');
        document.querySelectorAll('.payment-card').forEach(pc => {
            const disponivel = tipo === 'mensal' ? pc.dataset.mensal === '1' : pc.dataset.anual === '1';
            pc.style.display = disponivel ? '' : 'none';
            if (!disponivel && pc.classList.contains('selected')) {
                pc.classList.remove('selected');
                selectFormaPagamento.value = '';
            }
        });
        // Atualizar hint do Boleto
        document.querySelectorAll('.payment-card').forEach(pc => {
            const hint = pc.querySelector('.payment-hint');
            if (pc.dataset.value === 'CREDIT_CARD') {
                hint.textContent = tipo === 'anual' ? 'Até 12x sem juros' : 'Cobrança mensal recorrente';
            }
            if (pc.dataset.value === 'PIX') {
                hint.textContent = 'Enviado para aprovação do ADM';
            }
        });
        paymentHint.style.display = 'none';
    }

    document.querySelectorAll('.negotiation-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.negotiation-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            selectTipo.value = this.dataset.value;
            updatePriceLabels();
            calcularValor();

            // Filtrar métodos de pagamento disponíveis
            filtrarPagamentos(this.dataset.value);

            // Parcelamento só para Anual + Cartão
            const isAnual = this.dataset.value === 'anual';
            const isCartao = selectFormaPagamento.value === 'CREDIT_CARD';
            parcelamentoRow.classList.toggle('hidden', !(isAnual && isCartao));
        });
    });

    // Mostrar hint de pagamento se nenhum período selecionado
    if (!selectTipo.value) {
        document.getElementById('paymentHint').style.display = 'block';
        document.querySelectorAll('.payment-card').forEach(pc => pc.style.display = 'none');
    } else {
        filtrarPagamentos(selectTipo.value);
    }

    // Plan card selection
    cards.forEach(card => {
        card.addEventListener('click', function() {
            if (this.classList.contains('disabled')) return;
            cards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            inputPlano.value = this.dataset.nome;
            calcularValor();
        });
    });

    // Filter plans by member count
    inputMembros.addEventListener('input', function() {
        const membros = parseInt(this.value) || 0;
        let planoIdealIndex = -1;
        cards.forEach((card, index) => {
            const min = parseInt(card.dataset.min);
            const max = parseInt(card.dataset.max);
            if (membros >= min && membros <= max && planoIdealIndex === -1) {
                planoIdealIndex = index;
            }
        });
        if (planoIdealIndex === -1 && membros > 0) {
            planoIdealIndex = cards.length - 1;
        }
        let planoAutoSelecionado = false;
        cards.forEach((card, index) => {
            if (planoIdealIndex === -1) {
                card.classList.add('disabled');
                card.classList.remove('selected');
                return;
            }
            if (index >= planoIdealIndex) {
                card.classList.remove('disabled');
                if (!planoAutoSelecionado) {
                    cards.forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    inputPlano.value = card.dataset.nome;
                    planoAutoSelecionado = true;
                }
            } else {
                card.classList.add('disabled');
                card.classList.remove('selected');
            }
        });
        if (!planoAutoSelecionado) {
            inputPlano.value = '';
            valorResumo.classList.add('hidden');
        }
        calcularValor();
    });

    // Discount change
    inputDesconto.addEventListener('input', function() {
        calcularValor();
        calcularValorParcela();
    });

    // Performance value change
    inputValorPerformance.addEventListener('input', function() {
        calcularValor();
        if (selectFormaPagamento.value === 'CREDIT_CARD') calcularValorParcela();
    });

    // Payment method change - handled by card click above
    selectParcelas.addEventListener('change', calcularValorParcela);

    function calcularValorParcela() {
        const selected = document.querySelector('.plano-card.selected');
        if (!selected) { valorParcelaEl.textContent = 'R$ 0,00'; return; }
        const isConsulte = selected.dataset.consulte === '1';
        const parcelas = parseInt(selectParcelas.value) || 1;
        let valorTotal;

        if (isConsulte) {
            // Performance plan: use the entered combined value
            valorTotal = parseFloat(inputValorPerformance.value) || 0;
            if (valorTotal <= 0) { valorParcelaEl.textContent = 'A definir'; return; }
        } else {
            // Regular plans: calculate from base price minus discount
            const tipo = selectTipo.value;
            const base = tipo === 'anual' ? parseFloat(selected.dataset.anual) : parseFloat(selected.dataset.mensal);
            const desconto = parseFloat(inputDesconto.value) || 0;
            valorTotal = base - (base * (desconto / 100));
        }

        const valorParcela = valorTotal / parcelas;
        valorParcelaEl.textContent = fmtCurrency(valorParcela);
    }

    function updatePriceLabels() {
        const tipo = selectTipo.value;
        cards.forEach(card => {
            const priceEl = card.querySelector('.plano-price');
            const labelEl = card.querySelector('.plano-price-label');
            if (!priceEl.dataset.mensal) return;
            const mensal = parseFloat(priceEl.dataset.mensal);
            const anual = parseFloat(priceEl.dataset.anual);
            if (tipo === 'anual') {
                priceEl.textContent = fmtCurrency(anual);
                labelEl.textContent = 'por ano';
            } else {
                priceEl.textContent = fmtCurrency(mensal);
                labelEl.textContent = 'por mês';
            }
        });
    }

    function calcularValor() {
        const selected = document.querySelector('.plano-card.selected');
        if (!selected) { valorResumo.classList.add('hidden'); return; }

        const isConsulte = selected.dataset.consulte === '1';
        const descontoRow = document.getElementById('descontoRow');
        const valorPerformanceRow = document.getElementById('valorPerformanceRow');
        const avisoAprovacao = document.getElementById('avisoAprovacaoPerformance');

        if (isConsulte) {
            descontoRow.classList.add('hidden');
            valorPerformanceRow.classList.remove('hidden');
            avisoAprovacao.classList.remove('hidden');
            valorResumo.classList.remove('hidden');
            const valorDigitado = parseFloat(inputValorPerformance.value) || 0;
            valorFinal.textContent = valorDigitado > 0
                ? fmtCurrency(valorDigitado)
                : 'A definir';
            resumoDetalhes.textContent = 'Plano Basiléia ' + selected.dataset.nome + ' — Valor negociado';
            return;
        }

        descontoRow.classList.remove('hidden');
        valorPerformanceRow.classList.add('hidden');
        avisoAprovacao.classList.add('hidden');

        const tipo = selectTipo.value;
        const base = tipo === 'anual' ? parseFloat(selected.dataset.anual) : parseFloat(selected.dataset.mensal);
        const desconto = parseFloat(inputDesconto.value) || 0;
        const final_ = base - (base * (desconto / 100));

        valorResumo.classList.remove('hidden');
        valorFinal.textContent = fmtCurrency(final_);

        let detalhes = 'Plano Basiléia ' + selected.dataset.nome + ' (' + tipo + ')';
        if (desconto > 0) detalhes += ' — ' + desconto + '% desconto';
        resumoDetalhes.textContent = detalhes;
    }

    // CPF/CNPJ mask + validação em tempo real
    let documentoInputTimeout;
    inputDocumento.addEventListener('input', function(e) {
        let v = this.value.replace(/\D/g, '');
        if (v.length <= 11) {
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            v = v.substring(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d)/, '$1-$2');
        }
        this.value = v;

        // Validação em tempo real enquanto digita
        const doc = v.replace(/\D/g, '');
        const warning = document.getElementById('documentoWarning');
        warning.style.display = 'none';
        inputDocumento.classList.remove('is-invalid');

        // CPF completo (11 dígitos) ou CNPJ completo (14 dígitos)
        if ((doc.length === 11) || (doc.length === 14)) {
            clearTimeout(documentoInputTimeout);
            documentoInputTimeout = setTimeout(() => {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '/api/verificar-documento?documento=' + doc, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.exists && data.has_active_sale) {
                                warning.style.display = 'block';
                                warning.innerHTML = '<div class="warning-box" style="border-color:#dc2626;background:#fef2f2;"><i class="fas fa-triangle-exclamation" style="color:#dc2626;"></i><div><div class="warning-title" style="color:#dc2626;">Cliente já possui venda ativa!</div><div class="warning-text" style="color:#7f1d1d;"><strong>' + (data.cliente.nome_igreja||'') + '</strong> — Venda #' + (data.venda.id||'') + '</div></div></div>';
                                inputDocumento.classList.add('is-invalid');
                            } else if (data.exists) {
                                warning.style.display = 'block';
                                warning.innerHTML = '<div class="warning-box" style="border-color:#f59e0b;background:#fef3c7;"><i class="fas fa-info-circle" style="color:#f59e0b;"></i><div><div class="warning-title" style="color:#92400e;">Cliente já cadastrado</div><div class="warning-text" style="color:#78350f;"><strong>' + (data.cliente.nome_igreja||'') + '</strong> já existe no sistema.</div></div></div>';
                                inputDocumento.classList.add('is-invalid');
                            }
                        } catch(e) { warning.style.display = 'none'; }
                    }
                };
                xhr.onerror = function() { warning.style.display = 'none'; };
                xhr.send();
            }, 200);
        }
    });

    // WhatsApp mask
    inputWhatsapp.addEventListener('input', function(e) {
        let v = this.value.replace(/\D/g, '');
        v = v.substring(0, 11);
        if (v.length > 6) {
            v = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
        } else if (v.length > 2) {
            v = '(' + v.substring(0,2) + ') ' + v.substring(2);
        } else if (v.length > 0) {
            v = '(' + v;
        }
        this.value = v;
    });

    // CEP mask e ViaCEP
    const inputCep = document.getElementById('inputCep');
    if (inputCep) {
        inputCep.addEventListener('input', function(e) {
            let v = this.value.replace(/\D/g, '');
            if (v.length > 5) v = v.substring(0,5) + '-' + v.substring(5,8);
            this.value = v;
        });

        inputCep.addEventListener('blur', function() {
            let cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('inputEndereco').value = data.logradouro;
                            document.getElementById('inputBairro').value = data.bairro;
                            document.getElementById('inputCidade').value = data.localidade;
                            document.getElementById('inputEstado').value = data.uf;
                            document.getElementById('inputNumero').focus();
                        }
                    });
            }
        });
    }

    // Init
    if (inputMembros.value) inputMembros.dispatchEvent(new Event('input'));
    updatePriceLabels();
    calcularValor();

    // ===== Validação em tempo real: Email duplicado =====
    const inputEmail = document.getElementById('inputEmail');
    const emailWarning = document.getElementById('emailWarning');
    let emailTimeout = null;
    if (inputEmail) {
        inputEmail.addEventListener('input', function() {
            clearTimeout(emailTimeout);
            const email = this.value.trim();
            emailWarning.style.display = 'none';
            this.classList.remove('is-invalid');

            if (email.length < 5 || !email.includes('@')) return;

            emailTimeout = setTimeout(function() {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '/api/verificar-email?email=' + encodeURIComponent(email), true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.exists) {
                                emailWarning.textContent = '⚠ Este e-mail já está cadastrado no sistema.';
                                emailWarning.style.display = 'block';
                                inputEmail.classList.add('is-invalid');
                            }
                        } catch(e) {}
                    }
                };
                xhr.onerror = function() {};
                xhr.send();
            }, 500);
        });
    }

    // ===== Validação em tempo real: WhatsApp duplicado =====
    const whatsappWarning = document.getElementById('whatsappWarning');
    let whatsappTimeout = null;
    if (inputWhatsapp) {
        inputWhatsapp.addEventListener('input', function() {
            clearTimeout(whatsappTimeout);
            const ddi = document.getElementById('inputDdi').value;
            const numero = this.value.replace(/\D/g, '');
            whatsappWarning.style.display = 'none';

            if (numero.length < 8) return;

            const numeroCompleto = '+' + ddi + numero;

            whatsappTimeout = setTimeout(function() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '/api/verificar-whatsapp?whatsapp=' + encodeURIComponent(numeroCompleto), true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.exists) {
                                whatsappWarning.textContent = '⚠ Este WhatsApp já está cadastrado no sistema.';
                                whatsappWarning.style.display = 'block';
                            }
                        } catch(e) {}
                    }
                };
                xhr.onerror = function() {};
                xhr.send();
            }, 500);
        });
    }

    // ===== Máscara de WhatsApp =====
    if (inputWhatsapp) {
        inputWhatsapp.addEventListener('input', function(e) {
            let v = this.value.replace(/\D/g, '');
            if (v.length > 11) v = v.substring(0, 11);
            if (v.length > 6) {
                this.value = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
            } else if (v.length > 2) {
                this.value = '(' + v.substring(0,2) + ') ' + v.substring(2);
            } else if (v.length > 0) {
                this.value = '(' + v;
            }
        });
    }
});
</script>
@endsection
