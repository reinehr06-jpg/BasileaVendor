@extends('layouts.app')
@section('title', 'Clientes Legados Asaas')

@section('content')
<style>
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-left: 4px;
    }
    .action-btn:hover { background: var(--bg); color: var(--primary); border-color: var(--primary); }
    .action-btn.danger { color: var(--danger); border-color: var(--danger-light); }
    .action-btn.danger:hover { background: var(--danger-light); }
    .action-btn.success { color: var(--success); border-color: var(--success-light); }
    .action-btn.success:hover { background: var(--success-light); }
    .action-btn.warning { color: var(--warning); border-color: var(--warning-light); }
    .action-btn.warning:hover { background: var(--warning-light); }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px;
        text-align: center;
    }
    .stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .stat-card .label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .status-badge.success { background: rgba(34,197,94,0.1); color: #16a34a; }
    .status-badge.warning { background: rgba(234,179,8,0.1); color: #ca8a04; }
    .status-badge.danger { background: rgba(239,68,68,0.1); color: #dc2626; }
    .status-badge.info { background: rgba(59,130,246,0.1); color: #2563eb; }
    .status-badge.secondary { background: rgba(107,114,128,0.1); color: #6b7280; }
</style>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Clientes Legados Asaas</h3>
        </div>
        <div class="col-auto">
            <a href="{{ route('master.legados.template') }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-download"></i> Baixar Modelo
            </a>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadCsvModal">
                <i class="fas fa-file-upload"></i> Importar Planilha
            </button>
            <a href="{{ route('master.legados.commissions') }}" class="btn btn-outline-primary">
                <i class="fas fa-money-bill-wave"></i> Comissões Pendentes
            </a>
            <a href="{{ route('master.legados.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Cliente Legado
            </a>
        </div>
    </div>
</div>

<!-- Modal Upload CSV -->
<div class="modal fade" id="uploadCsvModal" tabindex="-1" aria-labelledby="uploadCsvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadCsvModalLabel">Importar Clientes via Planilha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('master.legados.importCsv') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Selecione o arquivo CSV</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                        <div class="form-text">O arquivo deve ser formato CSV com separador ponto-e-vírgula (;)</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Colunas obrigatórias:</strong> nome, documento<br>
                        <strong>Colunas opcionais:</strong> email, telefone, vendedor, gestor, plano, valor_original, valor_recorrente, data_venda, recorrente, gerar_comissao_venda, gerar_comissao_recorrente
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Vendedores disponíveis no sistema:</strong></label>
                        <div style="max-height: 120px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; font-size: 0.85rem;">
                            @forelse($vendedores as $v)
                                <span class="badge bg-primary me-1 mb-1">{{ $v->user->name ?? 'Vendedor #' . $v->id }}</span>
                            @empty
                                <span class="text-muted">Nenhum vendedor encontrado</span>
                            @endforelse
                        </div>
                        <div class="form-text">Use o nome exato do vendedor na planilha (sem acentuação)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Gestores disponíveis:</strong></label>
                        <div style="max-height: 80px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; font-size: 0.85rem;">
                            @forelse($gestores as $g)
                                <span class="badge bg-success me-1 mb-1">{{ $g->user->name ?? 'Gestor #' . $g->id }}</span>
                            @empty
                                <span class="text-muted">Nenhum gestor encontrado</span>
                            @endforelse
                        </div>
                    </div>
                    
                    <a href="{{ route('master.legados.template') }}" target="_blank" class="btn btn-sm btn-link">
                        <i class="fas fa-download"></i> Baixar modelo de planilha
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row mb-4">
    <div class="col-md-2">
        <div class="stat-card">
            <div class="number">{{ $stats['total'] }}</div>
            <div class="label">Total</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="number text-success">{{ $stats['imported'] }}</div>
            <div class="label">Importados</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="number text-warning">{{ $stats['pending'] }}</div>
            <div class="label">Pendentes</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="number text-danger">{{ $stats['not_found'] }}</div>
            <div class="label">Não Encontrados</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="number text-success">{{ $stats['active'] }}</div>
            <div class="label">Ativos</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="number text-danger">{{ $stats['overdue'] }}</div>
            <div class="label">Inadimplentes</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title">Filtros</h5>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome, documento ou email" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="vendedor_id" class="form-select">
                    <option value="">Todos os Vendedores</option>
                    @foreach($vendedores as $vendedor)
                        <option value="{{ $vendedor->id }}" {{ request('vendedor_id') == $vendedor->id ? 'selected' : '' }}>
                            {{ $vendedor->user->name ?? 'Vendedor #' . $vendedor->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="import_status" class="form-select">
                    <option value="">Status Importação</option>
                    <option value="PENDING" {{ request('import_status') == 'PENDING' ? 'selected' : '' }}>Pendente</option>
                    <option value="PROCESSING" {{ request('import_status') == 'PROCESSING' ? 'selected' : '' }}>Processando</option>
                    <option value="IMPORTED" {{ request('import_status') == 'IMPORTED' ? 'selected' : '' }}>Importado</option>
                    <option value="NOT_FOUND" {{ request('import_status') == 'NOT_FOUND' ? 'selected' : '' }}>Não Encontrado</option>
                    <option value="CONFLICT" {{ request('import_status') == 'CONFLICT' ? 'selected' : '' }}>Conflito</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="customer_status" class="form-select">
                    <option value="">Status Cliente</option>
                    <option value="ACTIVE" {{ request('customer_status') == 'ACTIVE' ? 'selected' : '' }}>Ativo</option>
                    <option value="INACTIVE" {{ request('customer_status') == 'INACTIVE' ? 'selected' : '' }}>Inativo</option>
                    <option value="OVERDUE" {{ request('customer_status') == 'OVERDUE' ? 'selected' : '' }}>Inadimplente</option>
                    <option value="CANCELLED" {{ request('customer_status') == 'CANCELLED' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="subscription_status" class="form-select">
                    <option value="">Recorrência</option>
                    <option value="ACTIVE" {{ request('subscription_status') == 'ACTIVE' ? 'selected' : '' }}>Ativa</option>
                    <option value="INACTIVE" {{ request('subscription_status') == 'INACTIVE' ? 'selected' : '' }}>Inativa</option>
                    <option value="CANCELLED" {{ request('subscription_status') == 'CANCELLED' ? 'selected' : '' }}>Cancelada</option>
                    <option value="NONE" {{ request('subscription_status') == 'NONE' ? 'selected' : '' }}>Sem Recorrência</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title">Clientes Importados ({{ $imports->total() }})</h5>
            </div>
            <div class="col-auto">
                <form method="POST" action="{{ route('master.legados.importBatch') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="vendedor_id" value="{{ request('vendedor_id') }}">
                    <button type="submit" class="btn btn-outline-success" onclick="return confirm('Isso irá buscar clientes do Asaas. Continuar?')">
                        <i class="fas fa-download"></i> Importar do Asaas
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Documento</th>
                    <th>Vendedor</th>
                    <th>Plano</th>
                    <th>Status Asaas</th>
                    <th>Recorrência</th>
                    <th>Importação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($imports as $import)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $import->nome ?? 'N/A' }}</div>
                        <div class="small text-muted">{{ $import->email ?? '' }}</div>
                    </td>
                    <td>{{ $import->documento ?? 'N/A' }}</td>
                    <td>
                        @if($import->vendedor)
                            <span class="badge bg-primary">{{ $import->vendedor->user->name ?? 'Vendedor #' . $import->vendedor_id }}</span>
                        @else
                            <span class="badge bg-secondary">Não definido</span>
                        @endif
                    </td>
                    <td>
                        @if($import->plano)
                            {{ $import->plano->nome }}
                            <div class="small text-muted">R$ {{ number_format($import->plano_valor_recorrente ?? 0, 2, ',', '.') }}/mês</div>
                        @else
                            <span class="badge bg-secondary">Não definido</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $import->customer_status_color }}">
                            {{ $import->customer_status ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $import->subscription_status === 'ACTIVE' ? 'success' : ($import->subscription_status === 'NONE' ? 'secondary' : 'warning') }}">
                            {{ $import->subscription_status ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $import->status_color }}">
                            {{ $import->import_status }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('master.legados.show', $import->id) }}" class="action-btn" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="{{ route('master.legados.sync', $import->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="action-btn warning" title="Sincronizar com Asaas">
                                <i class="fas fa-sync"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('master.legados.destroy', $import->id) }}" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn danger" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="text-muted">Nenhum cliente legado encontrado</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $imports->links() }}
    </div>
</div>
@endsection
