@extends('layouts.app')

@section('title', 'Contatos')

@section('content')
<div class="animate-up">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-address-book text-primary"></i> Gestão de Leads</h2>
            <p>Monitore e gerencie o fluxo de contatos vindos de todas as suas fontes de marketing.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary">
                <i class="fas fa-file-export"></i> Exportar
            </button>
            <button type="button" class="btn btn-primary" onclick="alert('Funcionalidade em desenvolvimento')">
                <i class="fas fa-plus"></i> Novo Lead
            </button>
        </div>
    </div>

    {{-- KPIs Premium --}}
    <div class="stats-bar mb-4">
        <div class="stat-card glass-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ number_format($contatos->total()) }}</div>
            <div class="stat-label">Total de Contatos</div>
        </div>
        <div class="stat-card glass-card">
            <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
            <div class="stat-value">{{ $contatos->where('status', 'convertido')->count() }}</div>
            <div class="stat-label">Convertidos</div>
        </div>
        <div class="stat-card glass-card">
            <div class="stat-icon warning"><i class="fas fa-user-clock"></i></div>
            <div class="stat-value">{{ $contatos->where('status', 'lead')->count() }}</div>
            <div class="stat-label">Leads em Aberto</div>
        </div>
        <div class="stat-card glass-card">
            <div class="stat-icon danger"><i class="fas fa-user-times"></i></div>
            <div class="stat-value">{{ $contatos->whereIn('status', ['perdido', 'lead_ruim'])->count() }}</div>
            <div class="stat-label">Perdidos / Ruins</div>
        </div>
    </div>

    {{-- Filtros Premium --}}
    <div class="card glass-card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Inteligência de Filtros
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label>Busca Inteligente</label>
                        <input type="text" name="busca" class="form-control" placeholder="Nome, telefone, email ou documento..." value="{{ request('busca') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="lead" {{ request('status') === 'lead' ? 'selected' : '' }}>Lead</option>
                            <option value="convertido" {{ request('status') === 'convertido' ? 'selected' : '' }}>Convertido</option>
                            <option value="perdido" {{ request('status') === 'perdido' ? 'selected' : '' }}>Perdido</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Canal</label>
                        <select name="canal" class="form-control">
                            <option value="">Todos</option>
                            <option value="meta_ads">Meta Ads</option>
                            <option value="google_ads">Google Ads</option>
                            <option value="whatsapp_link">WhatsApp</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Aplicar Filtros
                    </button>
                    <a href="{{ route('admin.contatos.index') }}" class="btn btn-outline" title="Limpar">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: rgba(var(--primary-rgb), 0.03);">
                        <tr>
                            <th style="padding: 16px 24px;">Contato / Lead</th>
                            <th>Canal / Campanha</th>
                            <th>Status Atual</th>
                            <th>Atribuído a</th>
                            <th>Entrada</th>
                            <th class="text-right" style="padding-right: 24px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contatos as $contato)
                        <tr>
                            <td style="padding: 16px 24px;">
                                <div class="d-flex align-center">
                                    <div class="avatar-circle mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-center; justify-content: center; font-weight: 700; font-size: 0.9rem; margin-right: 12px;">
                                        {{ strtoupper(substr($contato->nome, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-primary);">{{ $contato->nome }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            <i class="fab fa-whatsapp text-success"></i> {{ $contato->whatsapp ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; margin-bottom: 4px; display: inline-block;">
                                    {{ ucfirst(str_replace('_', ' ', $contato->canal_origem ?: 'Indefinido')) }}
                                </span>
                                @if($contato->campanha)
                                    <div style="font-size: 0.7rem; color: var(--primary); font-weight: 600;">
                                        <i class="fas fa-tag"></i> {{ Str::limit($contato->campanha->nome, 20) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($contato->status === 'lead')
                                    <span class="badge" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2);">Lead Ativo</span>
                                @elseif($contato->status === 'convertido')
                                    <span class="badge" style="background: rgba(22, 163, 74, 0.1); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.2);">Convertido</span>
                                @elseif($contato->status === 'perdido')
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">Perdido</span>
                                @else
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);">Ruim</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-center gap-1" style="font-size: 0.85rem;">
                                    @if($contato->agente)
                                        <i class="fas fa-user-circle text-primary"></i> {{ $contato->agente->name }}
                                    @elseif($contato->vendedor)
                                        <i class="fas fa-user-tie text-success"></i> {{ $contato->vendedor->user->name }}
                                    @else
                                        <span class="text-muted"><i class="fas fa-hourglass-start"></i> Aguardando</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; font-weight: 500;">{{ $contato->entry_date->format('d/m/Y') }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $contato->entry_date->diffForHumans() }}</div>
                            </td>
                            <td class="text-right" style="padding-right: 24px;">
                                <div class="d-flex justify-end gap-1">
                                    <button class="btn btn-icon btn-sm btn-outline-primary" onclick="verContato({{ $contato->id }})" title="Ficha do Lead">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-icon btn-sm btn-outline-warning" onclick="editarContato({{ $contato->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-sm btn-outline-secondary dropdown-toggle no-caret" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                            <li><a class="dropdown-item" href="#" onclick="mudarStatus({{ $contato->id }}, 'convertido')"><i class="fas fa-check text-success mr-2"></i> Converter</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mudarStatus({{ $contato->id }}, 'lead')"><i class="fas fa-redo text-primary mr-2"></i> Retornar Lead</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="perderContato({{ $contato->id }})"><i class="fas fa-times mr-2"></i> Perder</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 opacity-20"></i>
                                    <h4>Nenhum lead encontrado</h4>
                                    <p>Tente ajustar seus filtros para encontrar o que procura.</p>
                                    <a href="{{ route('admin.contatos.index') }}" class="btn btn-outline-primary mt-2">Ver Todos</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginação Premium --}}
    <div class="d-flex justify-between align-center mt-4">
        <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
            Exibindo <span class="text-primary">{{ $contatos->firstItem() }}</span> a <span class="text-primary">{{ $contatos->lastItem() }}</span> de <span class="text-primary">{{ $contatos->total() }}</span> resultados
        </div>
        <div class="pagination-premium">
            {{ $contatos->appends(request()->query())->links() }}
        </div>
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