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

    <form id="edit-form">
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
                <div class="form-group">
                    <label>Valor Plano Mensal (R$)</label>
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
                        <option value="ATIVO" {{ $cliente->diagnostico_status === 'ATIVO' ? 'selected' : '' }}>ATIVO - Pagando em dia</option>
                        <option value="CHURN" {{ $cliente->diagnostico_status === 'CHURN' ? 'selected' : '' }}>CHURN - Tem cobrança vencida</option>
                        <option value="CANCELADO" {{ $cliente->diagnostico_status === 'CANCELADO' ? 'selected' : '' }}>CANCELADO - Não pagou/Cancelado</option>
                        <option value="PENDENTE" {{ $cliente->diagnostico_status === 'PENDENTE' ? 'selected' : '' }}>PENDENTE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de Comissão</label>
                    <select name="comissao_tipo">
                        <option value="inicial" {{ $cliente->comissao_tipo === 'inicial' ? 'selected' : '' }}>Inicial (1º pagamento)</option>
                        <option value="inicial_antecipada" {{ $cliente->comissao_tipo === 'inicial_antecipada' ? 'selected' : '' }}>Inicial Antecipada (parcelado)</option>
                        <option value="recorrencia" {{ $cliente->comissao_tipo === 'recorrencia' ? 'selected' : '' }}>Recorrência</option>
                    </select>
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
                            <span style="font-size:1rem; font-weight:800; color:#166534;">R$ {{ number_format($cliente->comissao_vendedor_calculada ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="font-size:0.85rem; color:#64748b;">Gestor:</span>
                            <span style="font-size:0.9rem; font-weight:800; color:#2563eb;">R$ {{ number_format($cliente->comissao_gestor_calculada ?? 0, 2, ',', '.') }}</span>
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

@push('js')
<script>
document.getElementById('edit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Converter campos numéricos
    if (data.parcelas_total) data.parcelas_total = parseInt(data.parcelas_total);
    if (data.parcelas_pagas) data.parcelas_pagas = parseInt(data.parcelas_pagas);
    if (data.valor_plano_mensal) data.valor_plano_mensal = parseFloat(data.valor_plano_mensal);
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
            alert('Cliente atualizado com sucesso!\n\nComissão Vendedor: ' + result.comissao_vendedor + '\nComissão Gestor: ' + result.comissao_gestor);
            window.location.href = '{{ route("master.clientes-asaas.show", $cliente->id) }}';
        } else {
            alert('Erro: ' + (result.message || 'Não foi possível salvar.'));
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    } catch(e) {
        alert('Erro de conexão: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = origText;
    }
});
</script>
@endpush
@endsection
