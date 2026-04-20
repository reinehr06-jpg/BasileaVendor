@extends('layouts.app')

@section('title', 'Editar Prompt de IA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Editar Prompt Customizado</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ia.prompts.update', $prompt) }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" 
                                value="{{ old('nome', $prompt->nome) }}" required>
                            @error('nome')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Função</label>
                            <select name="funcao" class="form-select" required disabled>
                                @foreach($funcoes as $key => $label)
                                    <option value="{{ $key }}" {{ old('funcao', $prompt->funcao) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="funcao" value="{{ $prompt->funcao }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cor</label>
                            <div class="color-picker">
                                @foreach($cores as $hex => $nome)
                                    <label class="color-option">
                                        <input type="radio" name="cor" value="{{ $hex }}" {{ old('cor', $prompt->cor) == $hex ? 'checked' : '' }}>
                                        <span class="color-swatch" style="background: {{ $hex }}">
                                            @if(old('cor', $prompt->cor) == $hex)
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </span>
                                        <span class="color-name">{{ $nome }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Prompt Personalizado</label>
                            <textarea name="prompt_personalizado" class="form-control" rows="12" 
                                required>{{ old('prompt_personalizado', $prompt->prompt_personalizado) }}</textarea>
                            @error('prompt_personalizado')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="ativo" class="form-check-input" id="ativo" 
                                {{ old('ativo', $prompt->ativo) ? 'checked' : '' }}>
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