@extends('layouts.app')

@push('css')
<style>
.edit-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    border: 1px solid var(--materio-border);
    margin-bottom: 24px;
}
.edit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--materio-border);
    padding-bottom: 16px;
    margin-bottom: 20px;
}
.edit-header h2 {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--materio-text-main);
    margin: 0;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.form-group {
    margin-bottom: 0;
}
.form-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--materio-text-muted);
    text-transform: uppercase;
    margin-bottom: 6px;
}
.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    font-size: 0.95rem;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    color: var(--materio-text-main);
    transition: all 0.2s;
}
.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #4C1D95;
    background: white;
    box-shadow: 0 0 0 3px rgba(76, 29, 149, 0.1);
}
.form-group.full-width {
    grid-column: 1 / -1;
}
.btn-save {
    padding: 14px 28px;
    background: #4C1D95;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-save:hover {
    background: #3B0764;
}
.btn-cancel {
    padding: 14px 28px;
    background: white;
    color: #64748b;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-cancel:hover {
    border-color: #4C1D95;
    color: #4C1D95;
}
</style>
@endpush

@section('content')
<div class="materio-container">
    <x-page-hero title="Editar Cliente Asaas" subtitle="{{ $cliente->nome }}" icon="fas fa-edit" />

    <form id="edit-form" action="{{ route('master.clientes-asaas.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="edit-card">
            <div class="edit-header">
                <h2><i class="fas fa-user"></i> Dados do Cliente</h2>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" value="{{ $cliente->nome }}" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" value="{{ $cliente->email }}">
                </div>
                <div class="form-group">
                    <label>CPF/CNPJ</label>
                    <input type="text" name="documento" value="{{ $cliente->documento }}">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" value="{{ $cliente->telefone }}">
                </div>
            </div>
        </div>

        <div class="edit-card">
            <div class="edit-header">
                <h2><i class="fas fa-credit-card"></i> Dados de Pagamento</h2>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Tipo de Cobrança</label>
                    <select name="tipo_cobranca">
                        <option value="subscription" {{ $cliente->tipo_cobranca === 'subscription' ? 'selected' : '' }}>Assinatura Recorrente</option>
                        <option value="installment" {{ $cliente->tipo_cobranca === 'installment' ? 'selected' : '' }}>Parcelamento</option>
                        <option value="avulso" {{ $cliente->tipo_cobranca === 'avulso' ? 'selected' : '' }}>Avulso</option>
                    </select>
                </div>
                <div class="form-group" id="group_valor_total" style="display: {{ $cliente->tipo_cobranca === 'installment' ? 'block' : 'none' }};">
                    <label>Valor Total da Cobrança (R$)</label>
                    <input type="number" name="valor_total_cobranca" step="0.01" min="0" value="{{ $cliente->valor_total_cobranca ?? ($cliente->tipo_cobranca === 'installment' ? (($cliente->valor_plano_mensal ?? 0) * ($cliente->parcelas_total ?? 1)) : 0) }}">
                </div>
                <div class="form-group">
                    <label id="label_valor_mensal">Valor Plano Mensal (R$)</label>
                    <input type="number" name="valor_plano_mensal" step="0.01" min="0" value="{{ $cliente->valor_plano_mensal ?? 0 }}">
                </div>
                <div class="form-group">
                    <label>Parcelas Total</label>
                    <input type="number" name="parcelas_total" min="1" value="{{ $cliente->parcelas_total ?? 1 }}">
                </div>
                <div class="form-group">
                    <label>Parcelas Pagas</label>
                    <input type="number" name="parcelas_pagas" min="0" value="{{ $cliente->parcelas_pagas ?? 0 }}">
                </div>
                <div class="form-group">
                    <label>1º Pagamento (Início)</label>
                    <input type="date" name="primeiro_pagamento_at" value="{{ $cliente->primeiro_pagamento_at }}">
                </div>
                <div class="form-group">
                    <label>Último Pagamento Confirmado</label>
                    <input type="date" name="ultimo_pagamento_confirmado_at" value="{{ $cliente->ultimo_pagamento_confirmado_at }}">
                </div>
                <div class="form-group">
                    <label>Próximo Vencimento</label>
                    <input type="date" name="proximo_vencimento_at" value="{{ $cliente->proximo_vencimento_at }}">
                </div>
            </div>
        </div>

        <div class="edit-card">
            <div class="edit-header">
                <h2><i class="fas fa-chart-line"></i> Classificação</h2>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Status (Diagnóstico)</label>
                    <select name="diagnostico_status">
                        <option value="ATIVO" {{ ($cliente->diagnostico_status ?? null) === 'ATIVO' ? 'selected' : '' }}>ATIVO - Pagando em dia</option>
                        <option value="CHURN" {{ ($cliente->diagnostico_status ?? null) === 'CHURN' ? 'selected' : '' }}>CHURN - Tem cobrança vencida</option>
                        <option value="CANCELADO" {{ ($cliente->diagnostico_status ?? null) === 'CANCELADO' ? 'selected' : '' }}>CANCELADO - Não pagou/Cancelado</option>
                        <option value="PENDENTE" {{ ($cliente->diagnostico_status ?? null) === 'PENDENTE' ? 'selected' : '' }}>PENDENTE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de Comissão</label>
                    <select name="comissao_tipo">
                        <option value="sem_comissao" {{ ($cliente->comissao_tipo ?? null) === 'sem_comissao' ? 'selected' : '' }}>Sem Comissão (já pago)</option>
                        <option value="inicial" {{ ($cliente->comissao_tipo ?? null) === 'inicial' ? 'selected' : '' }}>Inicial (1º pagamento)</option>
                        <option value="inicial_antecipada" {{ ($cliente->comissao_tipo ?? null) === 'inicial_antecipada' ? 'selected' : '' }}>Inicial Antecipada (parcelado)</option>
                        <option value="recorrencia" {{ ($cliente->comissao_tipo ?? null) === 'recorrencia' ? 'selected' : '' }}>Recorrência</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="edit-card">
            <div class="edit-header">
                <h2><i class="fas fa-link"></i> Múltiplas Faturas Asaas</h2>
            </div>

            <div class="form-group full-width">
                <label>IDs de Cobrança / Assinatura (Um por linha)</label>
                @php
                    $multiIdsRaw = property_exists($cliente, 'multi_asaas_ids') ? $cliente->multi_asaas_ids : null;
                    $multiIdsJson = $multiIdsRaw ? json_decode((string)$multiIdsRaw, true) : null;
                    $multiIdsStr = is_array($multiIdsJson) ? implode("\n", $multiIdsJson) : '';
                @endphp
                <textarea name="multi_asaas_ids" rows="4" class="form-control" placeholder="sub_...&#10;pay_..." style="width: 100%; padding: 12px; border-radius: 8px; border: 2px solid #e2e8f0; background: #f8fafc;">{{ $multiIdsStr }}</textarea>
                <div style="font-size: 0.8rem; color: var(--materio-text-muted); margin-top: 8px;">
                    <i class="fas fa-info-circle"></i> Use isso se o cliente tiver dois cartões ou faturas separadas no Asaas para o mesmo produto.
                </div>
            </div>
        </div>

        <div class="edit-card">
            <div class="edit-header">
                <h2><i class="fas fa-user-tie"></i> Atribuição Comercial</h2>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Vendedor Responsável</label>
                    <select name="vendedor_id">
                        <option value="">— Sem vendedor —</option>
                        @if($listaG->count() > 0)
                        <optgroup label="Gestores">
                            @foreach($listaG as $v)
                            <option value="{{ $v->id }}" {{ $cliente->vendedor_id == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if($listaV->count() > 0)
                        <optgroup label="Vendedores">
                            @foreach($listaV as $v)
                            <option value="{{ $v->id }}" {{ $cliente->vendedor_id == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                    </select>
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <div style="background:#f0fdf4; padding:16px; border-radius:10px; width:100%;">
                        <div style="font-size:0.75rem; font-weight:700; color:#166534; text-transform:uppercase; margin-bottom:8px;">Comissão Calculada</div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span style="font-size:0.85rem; color:#64748b;">Vendedor:</span>
                            <span id="live-comissao-vendedor" style="font-size:1rem; font-weight:800; color:#166534;">R$ {{ number_format($cliente->comissao_vendedor_calculada ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="font-size:0.85rem; color:#64748b;">Gestor:</span>
                            <span id="live-comissao-gestor" style="font-size:0.9rem; font-weight:800; color:#2563eb;">R$ {{ number_format($cliente->comissao_gestor_calculada ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex; gap:16px; justify-content:flex-end; margin-top:24px;">
            <a href="{{ route('master.clientes-asaas.show', $cliente->id) }}" class="btn-cancel">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('edit-form');
    if (!editForm) return;

    const selectTipo = document.querySelector('select[name="tipo_cobranca"]');
    const selectVendedor = document.querySelector('select[name="vendedor_id"]');
    const groupValorTotal = document.getElementById('group_valor_total');
    const labelValorMensal = document.getElementById('label_valor_mensal');
    const inputValorTotal = document.querySelector('input[name="valor_total_cobranca"]');
    const inputValorMensal = document.querySelector('input[name="valor_plano_mensal"]');
    const inputParcelasTotal = document.querySelector('input[name="parcelas_total"]');
    const inputParcelasPagas = document.querySelector('input[name="parcelas_pagas"]');
    const selectComissaoTipo = document.querySelector('select[name="comissao_tipo"]');
    const selectStatus = document.querySelector('select[name="diagnostico_status"]');
    const textareaMultiIds = document.querySelector('textarea[name="multi_asaas_ids"]');

    // Script carregado silêncio

    function updateFormVisibility() {
        if (!selectTipo || !groupValorTotal || !labelValorMensal || !inputValorMensal) return;
        
        if (selectTipo.value === 'installment') {
            groupValorTotal.style.display = 'block';
            labelValorMensal.textContent = 'Valor da Parcela (R$)';
            inputValorMensal.readOnly = true;
            inputValorMensal.style.background = '#e2e8f0';
        } else {
            groupValorTotal.style.display = 'none';
            labelValorMensal.textContent = 'Valor Plano Mensal (R$)';
            inputValorMensal.readOnly = false;
            inputValorMensal.style.background = '#f8fafc';
        }
    }

    function calculateInstallment() {
        if (!selectTipo || !inputValorTotal || !inputParcelasTotal || !inputValorMensal) return;
        
        if (selectTipo.value === 'installment') {
            const total = parseFloat(inputValorTotal.value) || 0;
            const parcelas = parseInt(inputParcelasTotal.value) || 1;
            inputValorMensal.value = (total / parcelas).toFixed(2);
        }
    }

    async function updateCommissionPreview() {
        if (!selectVendedor || !selectComissaoTipo || !inputValorMensal || !selectStatus) {
            console.log('Faltando seletores no DOM para preview');
            return;
        }

        const veldId = selectVendedor.value;
        const tipoCom = selectComissaoTipo.value;
        const valMensal = parseFloat(inputValorMensal.value) || 0;
        const diagStat = selectStatus.value;
        const multiIds = textareaMultiIds ? textareaMultiIds.value.split("\n").map(s => s.trim()).filter(s => s !== "") : [];
        
        const elV = document.getElementById('live-comissao-vendedor');
        const elG = document.getElementById('live-comissao-gestor');

        if (!elV || !elG) return;

        if (!veldId) {
            elV.textContent = 'R$ 0,00';
            elG.textContent = 'R$ 0,00';
            return;
        }

        try {
            console.log('DEBUG: Enviando pedido de calculo...');
            const response = await fetch('{{ route("master.clientes-asaas.calculate-preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    vendedor_id: veldId,
                    comissao_tipo: tipoCom,
                    valor_plano_mensal: valMensal,
                    parcelas_total: inputParcelasTotal ? inputParcelasTotal.value : 1,
                    parcelas_pagas: inputParcelasPagas ? inputParcelasPagas.value : 0,
                    diagnostico_status: diagStat,
                    multi_asaas_ids: multiIds
                })
            });

            if (!response.ok) {
                console.error("ERRO NO SERVIDOR (AJAX): " + response.status);
                return;
            }

            const data = await response.json();
            console.log('Commission Debug:', data);
            
            if (data.success) {
                elV.textContent = data.vendedor;
                elG.textContent = data.gestor;
                
                // Super-Diagnóstico Visível
                // Silent warning in console
                if (data.diagnostic) console.warn(data.diagnostic);
            } else {
                console.error("ERRO LOGICO (JSON): " + (data.message || "Erro desconhecido"));
            }
        } catch (e) {
            console.error('ERRO JS CRITICO: ' + e.message);
            console.error('Erro ao calcular prévia:', e);
        }
    }

    // Event Listeners
    if (selectTipo) {
        selectTipo.addEventListener('change', () => {
            updateFormVisibility();
            updateCommissionPreview();
        });
    }

    if (inputValorTotal) {
        inputValorTotal.addEventListener('input', () => {
            calculateInstallment();
            updateCommissionPreview();
        });
    }

    if (inputParcelasTotal) {
        inputParcelasTotal.addEventListener('input', () => {
            calculateInstallment();
            updateCommissionPreview();
        });
    }

    if (selectVendedor) selectVendedor.addEventListener('change', updateCommissionPreview);
    if (selectComissaoTipo) selectComissaoTipo.addEventListener('change', updateCommissionPreview);
    if (selectStatus) selectStatus.addEventListener('change', updateCommissionPreview);
    if (inputValorMensal) inputValorMensal.addEventListener('input', updateCommissionPreview);
    if (textareaMultiIds) textareaMultiIds.addEventListener('change', updateCommissionPreview);
    if (inputParcelasPagas) {
        inputParcelasPagas.addEventListener('input', updateCommissionPreview);
    }

    // Initial load
    updateFormVisibility();
    updateCommissionPreview();

    // Submit handler
    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Multi Asaas IDs (convert to array)
        if (data.multi_asaas_ids) {
            data.multi_asaas_ids = data.multi_asaas_ids.split("\n").map(s => s.trim()).filter(s => s !== "");
        } else {
            data.multi_asaas_ids = [];
        }
        
        // Converter campos numéricos
        if (data.parcelas_total) data.parcelas_total = parseInt(data.parcelas_total);
        if (data.parcelas_pagas) data.parcelas_pagas = parseInt(data.parcelas_pagas);
        if (data.valor_plano_mensal) data.valor_plano_mensal = parseFloat(data.valor_plano_mensal);
        if (data.valor_total_cobranca && data.tipo_cobranca === 'installment') {
            data.valor_total_cobranca = parseFloat(data.valor_total_cobranca);
        } else {
            data.valor_total_cobranca = null;
        }
        if (data.vendedor_id === '') data.vendedor_id = null;

        const btn = document.querySelector('.btn-save');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

        try {
            const resp = await fetch('{{ route("master.clientes-asaas.update", $cliente->id) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await resp.json();
            
            if (result.success) {
                // Redireciona para a listagem na aba correta (Aba de Sem Vendedor para continuar o ritmo)
                window.location.href = '{{ route("master.clientes-asaas.index", ["aba" => "sem_vendedor"]) }}';
            } else {
                alert(result.message || 'Erro ao salvar alterações.');
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        } catch(e) {
            console.error('Erro ao salvar:', e);
            alert('Erro crítico ao salvar. Verifique o console ou a rede.');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
        return false;
    });
});
</script>
@endsection
@endsection
