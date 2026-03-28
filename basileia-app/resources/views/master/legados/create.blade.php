@extends('layouts.app')
@section('title', 'Novo Cliente Legado')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Novo Cliente Legado</h3>
        </div>
        <div class="col-auto">
            <a href="{{ route('master.legados.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('master.legados.store') }}">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Dados do Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="nome" class="form-control" required value="{{ old('nome') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CPF/CNPJ *</label>
                            <input type="text" name="documento" class="form-control" required value="{{ old('documento') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="{{ old('telefone') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Vínculo Comercial</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendedor Responsável *</label>
                            <select name="vendedor_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" {{ old('vendedor_id') == $vendedor->id ? 'selected' : '' }}>
                                        {{ $vendedor->user->name ?? 'Vendedor #' . $vendedor->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gerente</label>
                            <select name="gestor_id" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($gestores as $gestor)
                                    <option value="{{ $gestor->user_id }}" {{ old('gestor_id') == $gestor->user_id ? 'selected' : '' }}>
                                        {{ $gestor->user->name ?? 'Gestor #' . $gestor->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plano</label>
                            <select name="plano_id" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($planos as $plano)
                                    <option value="{{ $plano->id }}" {{ old('plano_id') == $plano->id ? 'selected' : '' }}>
                                        {{ $plano->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data da Venda Original</label>
                            <input type="date" name="data_venda_original" class="form-control" value="{{ old('data_venda_original') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valor da Venda Original</label>
                            <input type="number" name="plano_valor_original" class="form-control" step="0.01" value="{{ old('plano_valor_original') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valor Recorrente</label>
                            <input type="number" name="plano_valor_recorrente" class="form-control" step="0.01" value="{{ old('plano_valor_recorrente') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Comissões</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="generate_old_sale_commission" id="generate_old_sale" value="1" {{ old('generate_old_sale_commission') ? 'checked' : '' }}>
                                <label class="form-check-label" for="generate_old_sale">
                                    Gerar comissão da venda antiga
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="generate_recurring_commission" id="generate_recurring" value="1" {{ old('generate_recurring_commission', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="generate_recurring">
                                    Gerar comissão recorrente
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Importar do Asaas</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Se o cliente já existe no Asaas, você pode importar os dados automaticamente.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">CPF/CNPJ no Asaas</label>
                        <div class="input-group">
                            <input type="text" name="asaas_documento" class="form-control" placeholder="000.000.000-00">
                            <button type="submit" name="import_asaas" value="1" class="btn btn-outline-primary">
                                <i class="fas fa-download"></i> Importar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Salvar Cliente Legado
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
