@extends('layouts.app')

@section('title', 'Contatos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Gerenciamento de Contatos</h4>
                </div>
                <div class="card-body">

                    {{-- KPIs Globais --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>{{ number_format($contatos->total()) }}</h5>
                                    <p class="mb-0">Total de Contatos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $contatos->where('status', 'convertido')->count() }}</h5>
                                    <p class="mb-0">Convertidos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $contatos->where('status', 'lead')->count() }}</h5>
                                    <p class="mb-0">Leads Ativos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $contatos->whereIn('status', ['perdido', 'lead_ruim'])->count() }}</h5>
                                    <p class="mb-0">Perdidos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros Avançados --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <button class="btn btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                                    <i class="fas fa-filter"></i> Filtros Avançados
                                </button>
                            </h5>
                        </div>
                        <div class="collapse show" id="filtrosCollapse">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Busca</label>
                                        <input type="text" name="busca" class="form-control" placeholder="Nome, telefone, email..." value="{{ request('busca') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="">Todos</option>
                                            <option value="lead" {{ request('status') === 'lead' ? 'selected' : '' }}>Lead</option>
                                            <option value="convertido" {{ request('status') === 'convertido' ? 'selected' : '' }}>Convertido</option>
                                            <option value="perdido" {{ request('status') === 'perdido' ? 'selected' : '' }}>Perdido</option>
                                            <option value="lead_ruim" {{ request('status') === 'lead_ruim' ? 'selected' : '' }}>Lead Ruim</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Campanha</label>
                                        <select name="campanha_id" class="form-select">
                                            <option value="">Todas</option>
                                            @foreach($campanhas as $campanha)
                                            <option value="{{ $campanha->id }}" {{ request('campanha_id') == $campanha->id ? 'selected' : '' }}>
                                                {{ $campanha->nome }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Canal</label>
                                        <select name="canal" class="form-select">
                                            <option value="">Todos</option>
                                            <option value="meta_ads" {{ request('canal') === 'meta_ads' ? 'selected' : '' }}>Meta Ads</option>
                                            <option value="google_ads" {{ request('canal') === 'google_ads' ? 'selected' : '' }}>Google Ads</option>
                                            <option value="whatsapp_link" {{ request('canal') === 'whatsapp_link' ? 'selected' : '' }}>WhatsApp Link</option>
                                            <option value="instagram" {{ request('canal') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                            <option value="formulario_web" {{ request('canal') === 'formulario_web' ? 'selected' : '' }}>Formulário Web</option>
                                            <option value="organico" {{ request('canal') === 'organico' ? 'selected' : '' }}>Orgânico</option>
                                            <option value="importacao" {{ request('canal') === 'importacao' ? 'selected' : '' }}>Importação</option>
                                            <option value="outro" {{ request('canal') === 'outro' ? 'selected' : '' }}>Outro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Agente</label>
                                        <select name="agente_id" class="form-select">
                                            <option value="">Todos</option>
                                            @foreach($agentes as $agente)
                                            <option value="{{ $agente->id }}" {{ request('agente_id') == $agente->id ? 'selected' : '' }}>
                                                {{ $agente->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Período</label>
                                        <div class="input-group">
                                            <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                                            <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                        <a href="{{ route('admin.contatos.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Limpar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Tabela de Contatos --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Contato</th>
                                    <th>Telefone/WhatsApp</th>
                                    <th>Campanha</th>
                                    <th>Canal</th>
                                    <th>Status</th>
                                    <th>Agente</th>
                                    <th>Data Entrada</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contatos as $contato)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $contato->nome }}</strong>
                                            @if($contato->email)
                                            <br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $contato->email }}</small>
                                            @endif
                                            @if($contato->documento)
                                            <br><small class="text-muted"><i class="fas fa-id-card"></i> {{ $contato->documento }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($contato->telefone)
                                        <div><i class="fas fa-phone text-muted"></i> {{ $contato->telefone }}</div>
                                        @endif
                                        @if($contato->whatsapp)
                                        <div><i class="fab fa-whatsapp text-success"></i> {{ $contato->whatsapp }}</div>
                                        @endif
                                        @if(!$contato->telefone && !$contato->whatsapp)
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($contato->campanha)
                                            <a href="{{ route('admin.campanhas.show', $contato->campanha) }}" class="text-decoration-none">
                                                {{ Str::limit($contato->campanha->nome, 20) }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst(str_replace('_', ' ', $contato->canal_origem ?: 'indefinido')) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($contato->status === 'lead')
                                            <span class="badge bg-primary">Lead</span>
                                        @elseif($contato->status === 'convertido')
                                            <span class="badge bg-success">Convertido</span>
                                        @elseif($contato->status === 'perdido')
                                            <span class="badge bg-danger">Perdido</span>
                                        @else
                                            <span class="badge bg-warning">Lead Ruim</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($contato->agente)
                                            <i class="fas fa-user text-primary"></i> {{ $contato->agente->name }}
                                        @elseif($contato->vendedor)
                                            <i class="fas fa-user-tie text-success"></i> {{ $contato->vendedor->user->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $contato->entry_date->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $contato->entry_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info" onclick="verContato({{ $contato->id }})" title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editarContato({{ $contato->id }})" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="mudarStatus({{ $contato->id }}, 'lead')">Marcar como Lead</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="mudarStatus({{ $contato->id }}, 'convertido')">Marcar como Convertido</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="perderContato({{ $contato->id }})">Marcar como Perdido</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                        <h4>Nenhum contato encontrado</h4>
                                        <p class="text-muted mb-3">Não há contatos que correspondam aos filtros aplicados.</p>
                                        <a href="{{ route('admin.contatos.index') }}" class="btn btn-primary">
                                            <i class="fas fa-times"></i> Limpar Filtros
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginação --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ $contatos->firstItem() }}-{{ $contatos->lastItem() }} de {{ $contatos->total() }} contatos
                        </div>
                        {{ $contatos->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Mudar Status --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status do Contato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Novo Status</label>
                        <select name="status" class="form-select" id="statusSelect" required>
                            <option value="lead">Lead</option>
                            <option value="convertido">Convertido</option>
                            <option value="perdido">Perdido</option>
                            <option value="lead_ruim">Lead Ruim</option>
                        </select>
                    </div>
                    <div class="mb-3" id="motivoDiv" style="display: none;">
                        <label class="form-label">Motivo</label>
                        <textarea name="motivo" class="form-control" rows="3" placeholder="Explique o motivo da mudança..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function verContato(id) {
    window.location.href = '{{ route("admin.contatos.show", ":id") }}'.replace(':id', id);
}

function editarContato(id) {
    // TODO: Implementar drawer de edição
    alert('Funcionalidade de edição será implementada em breve!');
}

function mudarStatus(contatoId, novoStatus) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const form = document.getElementById('statusForm');
    const motivoDiv = document.getElementById('motivoDiv');
    const statusSelect = document.getElementById('statusSelect');

    form.action = '{{ route("admin.contatos.status", ":id") }}'.replace(':id', contatoId);
    statusSelect.value = novoStatus;

    // Mostrar campo de motivo se for perdido ou lead_ruim
    if (novoStatus === 'perdido' || novoStatus === 'lead_ruim') {
        motivoDiv.style.display = 'block';
    } else {
        motivoDiv.style.display = 'none';
    }

    modal.show();
}

function perderContato(contatoId) {
    mudarStatus(contatoId, 'perdido');
}

// Toggle do filtro de motivo baseado no status
document.getElementById('statusSelect').addEventListener('change', function() {
    const motivoDiv = document.getElementById('motivoDiv');
    const status = this.value;

    if (status === 'perdido' || status === 'lead_ruim') {
        motivoDiv.style.display = 'block';
    } else {
        motivoDiv.style.display = 'none';
    }
});
</script>
@endsection