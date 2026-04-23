@extends('layouts.app')

@section('title', 'Criar Perfil da IA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Novo Perfil da IA</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ia.prompts.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" 
                                value="{{ old('nome') }}" required placeholder="Ex: Score de Lead Premium">
                            @error('nome')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Função</label>
                            <select name="funcao" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach($funcoes as $key => $label)
                                    <option value="{{ $key }}" {{ old('funcao') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('funcao')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cor</label>
                            <div class="color-picker">
                                @foreach($cores as $hex => $nome)
                                    <label class="color-option">
                                        <input type="radio" name="cor" value="{{ $hex }}" {{ old('cor', '#4C1D95') == $hex ? 'checked' : '' }}>
                                        <span class="color-swatch" style="background: {{ $hex }}">
                                            @if(old('cor', '#4C1D95') == $hex)
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </span>
                                        <span class="color-name">{{ $nome }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Perfil Personalizado (Prompt)</label>
                            <textarea name="prompt_personalizado" class="form-control" rows="12" 
                                required placeholder="Use {nome} para usar contexto...">{{ old('prompt_personalizado') }}</textarea>
                            <div class="form-text">Use variáveis como {nome}, {email}, etc. Entre chaves simples.</div>
                            @error('prompt_personalizado')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="ativo" class="form-check-input" id="ativo" {{ old('ativo', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ativo">Ativo</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="{{ route('admin.ia.prompts.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.color-picker { display: flex; gap: 16px; flex-wrap: wrap; }
.color-option { cursor: pointer; text-align: center; }
.color-option input { display: none; }
.color-swatch {
    display: flex; align-items: center; justify-content: center;
    width: 48px; height: 48px; border-radius: 8px;
    color: white; font-size: 14px;
    transition: transform 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.color-option:hover .color-swatch { transform: scale(1.1); }
.color-option input:checked + .color-swatch { box-shadow: 0 0 0 3px white, 0 0 0 5px currentColor; }
.color-name { font-size: 11px; margin-top: 4px; color: var(--text-secondary); }
</style>
@endsection