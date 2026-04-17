@extends('chat.layout')

@section('chat-content')
<div class="chat-main" style="padding: 20px;">
    <h4><i class="fas fa-users me-2"></i>Distribuição Round Robin</h4>
    <hr>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $contagem['abertas'] }}</h3>
                    <small class="text-muted">Abertas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ $contagem['pendentes'] }}</h3>
                    <small class="text-muted">Pendentes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $contagem['atendidos'] }}</h3>
                    <small class="text-muted">Atendidos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $contagem['nao_atendidos'] }}</h3>
                    <small class="text-muted">Não Atendidos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <form action="{{ route('gestor.chat.distribuicao.init') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync me-2"></i> Inicializar/Atualizar Fila
            </button>
        </form>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Ordem</th>
                <th>Vendedor</th>
                <th>Total Atendidos</th>
                <th>Último Atendimento</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="filaTable">
            @forelse($vendedores as $vendedor)
            <tr data-vendedor-id="{{ $vendedor->id }}">
                <td>
                    <input type="number" name="ordem[{{ $vendedor->id }}]" 
                           value="{{ $vendedor->fila_status->ordem ?? $loop->iteration }}" 
                           class="form-control form-control-sm" style="width:60px;">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="chat-item-avatar me-2" style="width:32px; height:32px; font-size:14px;">
                            {{ strtoupper(substr($vendedor->nome, 0, 1)) }}
                        </div>
                        {{ $vendedor->nome }}
                    </div>
                </td>
                <td>{{ $vendedor->fila_status->total_atendidos ?? 0 }}</td>
                <td>
                    @if($vendedor->fila_status->ultimo_atendimento_at)
                    {{ \Carbon\Carbon::parse($vendedor->fila_status->ultimo_atendimento_at)->format('d/m H:i') }}
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if($vendedor->fila_status->is_active ?? true)
                    <span class="badge bg-success">Ativo</span>
                    @else
                    <span class="badge bg-secondary">Inativo</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="toggleVendedor({{ $vendedor->id }})">
                        <i class="fas fa-toggle-off"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Nenhum vendedor ativo encontrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <button onclick="salvarOrdem()" class="btn btn-success">
        <i class="fas fa-save me-2"></i> Salvar Ordem
    </button>
</div>

<script>
function salvarOrdem() {
    const ordem = {};
    document.querySelectorAll('#filaTable tr').forEach(tr => {
        const id = tr.dataset.vendedorId;
        const input = tr.querySelector('input[name^="ordem"]');
        if (id && input) {
            ordem[id] = input.value;
        }
    });

    fetch('{{ route('gestor.chat.distribuicao.reorder') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ordem: ordem })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ordem salva com sucesso!');
            location.reload();
        }
    });
}

function toggleVendedor(vendedorId) {
    alert('Funcionalidade de ativar/desativar vendedor em desenvolvimento');
}
</script>
@endsection