@extends('layouts.app')

@section('title', 'IA Lab - Configurações')

@section('content')
<div class="animate-up">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-microchip text-primary"></i> IA Lab</h2>
            <p>Laboratório de Inteligência Artificial: Personalize os algoritmos de processamento.</p>
        </div>
        <a href="{{ route('admin.ia.prompts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Perfil
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success animate-up">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card glass-card">
        <div class="card-header">
            <i class="fas fa-terminal"></i> Prompts e Instruções do Sistema
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: rgba(var(--primary-rgb), 0.05);">
                        <tr>
                            <th style="padding: 18px 24px;">Configuração</th>
                            <th>Módulo / Função</th>
                            <th>Status Operacional</th>
                            <th>Responsável</th>
                            <th>Última Atualização</th>
                            <th class="text-right" style="padding-right: 24px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prompts as $prompt)
                            <tr>
                                <td style="padding: 16px 24px;">
                                    <div class="d-flex align-center gap-2">
                                        <span class="color-indicator" style="background: {{ $prompt->cor }}; box-shadow: 0 0 10px {{ $prompt->cor }}44; width: 12px; height: 12px;"></span>
                                        <strong>{{ $prompt->nome }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: rgba(var(--primary-rgb), 0.08); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.1);">
                                        {{ $funcoes[$prompt->funcao] ?? $prompt->funcao }}
                                    </span>
                                </td>
                                <td>
                                    @if($prompt->ativo)
                                        <span class="badge" style="background: rgba(22, 163, 74, 0.1); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.2);">
                                            <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 4px;"></i> Online
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(161, 161, 181, 0.1); color: #a1a1b5; border: 1px solid rgba(161, 161, 181, 0.2);">
                                            <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 4px;"></i> Offline
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 0.85rem;">{{ $prompt->creator?->name ?? 'Sistema' }}</td>
                                <td class="text-muted" style="font-size: 0.85rem;">{{ $prompt->updated_at->diffForHumans() }}</td>
                                <td class="text-right" style="padding-right: 24px;">
                                    <div class="d-flex justify-end gap-1">
                                        <form method="POST" action="{{ route('admin.ia.prompts.toggle', $prompt) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-icon btn-sm {{ $prompt->ativo ? 'btn-outline-warning' : 'btn-outline-primary' }}" title="{{ $prompt->ativo ? 'Desativar' : 'Ativar' }}">
                                                <i class="fas fa-{{ $prompt->ativo ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.ia.prompts.edit', $prompt) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Editar Configuração">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.ia.prompts.destroy', $prompt) }}" class="delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Excluir Algoritmo">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-robot fa-3x mb-3 opacity-20"></i>
                                        <p>Nenhum algoritmo configurado no laboratório.</p>
                                        <a href="{{ route('admin.ia.prompts.create') }}" class="btn btn-outline-primary mt-2">Criar Primeiro Prompt</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- IA Stats / Cards (Inspiration) --}}
    <div class="row mt-4 animate-up" style="animation-delay: 0.2s;">
        <div class="col-md-4">
            <div class="stat-card glass-card">
                <div class="stat-icon primary"><i class="fas fa-brain"></i></div>
                <div class="stat-value">{{ $prompts->where('ativo', true)->count() }}</div>
                <div class="stat-label">Modelos Ativos</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card glass-card">
                <div class="stat-icon info"><i class="fas fa-bolt"></i></div>
                <div class="stat-value">98.2%</div>
                <div class="stat-label">Taxa de Resposta</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card glass-card">
                <div class="stat-icon success"><i class="fas fa-magic"></i></div>
                <div class="stat-value">Basiléia AI</div>
                <div class="stat-label">Core Engine</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', e => {
        if (!confirm('Confirmar exclusão?')) e.preventDefault();
    });
});
</script>
@endsection