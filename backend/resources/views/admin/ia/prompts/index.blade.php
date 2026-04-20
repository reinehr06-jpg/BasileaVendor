@extends('layouts.app')

@section('title', 'Configurar Prompts de IA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Prompts Customizados</h4>
                    <p class="text-muted mb-0">Personalize os prompts usados pela IA para cada tarefa</p>
                </div>
                <a href="{{ route('admin.ia.prompts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Prompt
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Cor</th>
                                <th>Nome</th>
                                <th>Função</th>
                                <th>Status</th>
                                <th>Criado por</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prompts as $prompt)
                                <tr>
                                    <td>
                                        <span class="color-indicator" style="background: {{ $prompt->cor }}"></span>
                                    </td>
                                    <td>
                                        <strong>{{ $prompt->nome }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $funcoes[$prompt->funcao] ?? $prompt->funcao }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($prompt->ativo)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td>{{ $prompt->creator?->name ?? '-' }}</td>
                                    <td>{{ $prompt->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="{{ route('admin.ia.prompts.toggle', $prompt) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $prompt->ativo ? 'warning' : 'success' }}">
                                                    <i class="fas fa-{{ $prompt->ativo ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.ia.prompts.edit', $prompt) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.ia.prompts.destroy', $prompt) }}" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhum prompt customizado ainda.
                                        <a href="{{ route('admin.ia.prompts.create') }}">Criar primeiro</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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