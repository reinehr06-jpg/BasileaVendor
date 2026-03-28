@extends('layouts.app')
@section('title', 'Comissões por Plano')

@section('content')
<style>
    .rule-card {
        background: var(--surface);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 20px;
    }
    .rule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--border-light);
    }
    .rule-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
    }
    .rule-values {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    .rule-value-group label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .rule-value-group input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        font-weight: 600;
    }
    .rule-value-group input:focus {
        border-color: var(--primary);
        outline: none;
    }
    @media (max-width: 768px) {
        .rule-values { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-hand-holding-dollar" style="margin-right: 8px;"></i>Comissões por Plano</h2>
        <p>Configure os valores fixos de comissão para vendedores e gerentes por plano.</p>
    </div>
    <a href="{{ route('master.configuracoes.integracoes') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

<div class="alert alert-info" style="margin-bottom: 24px;">
    <i class="fas fa-info-circle"></i>
    <span>
        <strong>Sistema Híbrido:</strong> Se existir uma regra de comissão fixa para o plano, ela será usada.
        Caso contrário, o sistema usa a comissão percentual definida no cadastro do vendedor.
    </span>
</div>

@foreach($rules as $rule)
<form action="{{ route('master.configuracoes.comissoes.update', $rule->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="rule-card">
        <div class="rule-header">
            <h3><i class="fas fa-box" style="margin-right: 8px;"></i>Plano {{ $rule->plano_nome }}</h3>
            <label class="checkbox-label" style="margin: 0;">
                <input type="checkbox" name="active" value="1" {{ $rule->active ? 'checked' : '' }}>
                <span style="font-size: 0.85rem; font-weight: 600;">Ativo</span>
            </label>
        </div>
        
        <div class="rule-values">
            <div class="rule-value-group">
                <label>Vendedor - 1ª Parcela (R$)</label>
                <input type="number" step="0.01" name="seller_fixed_value_first_payment" value="{{ $rule->seller_fixed_value_first_payment }}" min="0">
            </div>
            <div class="rule-value-group">
                <label>Vendedor - Recorrência (R$)</label>
                <input type="number" step="0.01" name="seller_fixed_value_recurring" value="{{ $rule->seller_fixed_value_recurring }}" min="0">
            </div>
            <div class="rule-value-group">
                <label>Gerente - 1ª Parcela (R$)</label>
                <input type="number" step="0.01" name="manager_fixed_value_first_payment" value="{{ $rule->manager_fixed_value_first_payment }}" min="0">
            </div>
            <div class="rule-value-group">
                <label>Gerente - Recorrência (R$)</label>
                <input type="number" step="0.01" name="manager_fixed_value_recurring" value="{{ $rule->manager_fixed_value_recurring }}" min="0">
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Salvar</button>
        </div>
    </div>
</form>
@endforeach

@endsection
