@extends('layouts.app')
@section('title', 'Equipes')

@section('content')
<style>
    .equipes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .equipe-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .equipe-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .equipe-card-header {
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    .equipe-card-header h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .equipe-color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    .equipe-card-header .badge {
        font-size: 0.75rem;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .equipe-card-body {
        padding: 16px 20px;
    }
    .equipe-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }
    .equipe-stat {
        text-align: center;
        padding: 12px 8px;
        background: var(--bg);
        border-radius: 10px;
    }
    .equipe-stat .stat-value {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--text-primary);
    }
    .equipe-stat .stat-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 600;
        margin-top: 2px;
    }
    .equipe-meta-bar {
        margin-top: 12px;
    }
    .equipe-meta-bar .meta-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    .equipe-meta-bar .meta-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 600;
    }
    .equipe-meta-bar .meta-value {
        font-size: 0.78rem;
        font-weight: 700;
    }
    .progress-bar-lg {
        height: 10px;
        background: #f1f5f9;
        border-radius: 5px;
        overflow: hidden;
    }
    .progress-bar-lg .fill {
        height: 100%;
        border-radius: 5px;
        transition: width 0.6s ease;
    }
    .progress-bar-lg .fill.green { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .progress-bar-lg .fill.yellow { background: linear-gradient(90deg, #eab308, #ca8a04); }
    .progress-bar-lg .fill.red { background: linear-gradient(90deg, #ef4444, #dc2626); }

    .equipe-card-actions {
        padding: 12px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .equipe-membros {
        padding: 0 20px 16px;
    }
    .equipe-membros h4 {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 10px;
    }
    .membro-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        background: var(--bg);
        border-radius: 8px;
        margin-bottom: 6px;
    }
    .membro-item .membro-nome {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .membro-item .membro-email {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    .membro-remove {
        background: none;
        border: none;
        color: var(--danger);
        cursor: pointer;
        font-size: 0.85rem;
        padding: 4px 8px;
        border-radius: 6px;
        transition: 0.2s;
    }
    .membro-remove:hover { background: rgba(239,68,68,0.1); }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 24px 0 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .vendedores-sem-equipe {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 28px;
    }
    .vendedor-se-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: 1px solid var(--border-light);
    }
    .vendedor-se-item:last-child { border-bottom: none; }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .page-header p {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-top: 4px;
    }

    @media (max-width: 768px) {
        .equipes-grid { grid-template-columns: 1fr; }
        .equipe-stats { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-people-group" style="margin-right: 8px;"></i>Equipes</h2>
        <p>Gerencie as equipes de vendas e acompanhe as metas por equipe.</p>
    </div>
    <button class="btn btn-primary" onclick="BasileiaModal.open('createEquipeModal')">
        <i class="fas fa-plus"></i> Nova Equipe
    </button>
</div>

@if(count($equipes) > 0)
<div class="equipes-grid">
    @foreach($equipes as $equipe)
    <div class="equipe-card">
        <div class="equipe-card-header">
            <h3>
                <span class="equipe-color-dot" style="background: {{ $equipe->cor }};"></span>
                {{ $equipe->nome }}
            </h3>
            <span class="badge badge-success">Ativa</span>
        </div>
        <div class="equipe-card-body">
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 12px;">
                <i class="fas fa-user-tie" style="margin-right: 4px;"></i>
                Gestor: <strong>{{ $equipe->gestor->name ?? 'N/A' }}</strong>
            </div>
            <div class="equipe-stats">
                <div class="equipe-stat">
                    <div class="stat-value">{{ $equipe->total_vendedores }}</div>
                    <div class="stat-label">Vendedores</div>
                </div>
                <div class="equipe-stat">
                    <div class="stat-value">{{ $equipe->total_vendas_periodo }}</div>
                    <div class="stat-label">Vendas Mês</div>
                </div>
                <div class="equipe-stat">
                    <div class="stat-value">R$ {{ number_format($equipe->valor_recebido, 0, ',', '.') }}</div>
                    <div class="stat-label">Recebido</div>
                </div>
            </div>
            <div class="equipe-meta-bar">
                <div class="meta-header">
                    <span class="meta-label">Meta: R$ {{ number_format($equipe->meta_mensal, 2, ',', '.') }}</span>
                    <span class="meta-value" style="color: {{ $equipe->percentual_meta >= 100 ? '#16a34a' : ($equipe->percentual_meta >= 50 ? '#ca8a04' : '#dc2626') }};">
                        {{ $equipe->percentual_meta }}%
                    </span>
                </div>
                <div class="progress-bar-lg">
                    <div class="fill {{ $equipe->percentual_meta >= 100 ? 'green' : ($equipe->percentual_meta >= 50 ? 'yellow' : 'red') }}"
                         style="width: {{ min($equipe->percentual_meta, 100) }}%;"></div>
                </div>
            </div>
        </div>

        <div class="equipe-membros">
            <h4>Membros ({{ $equipe->vendedores->count() }})</h4>
            @foreach($equipe->vendedores as $membro)
            <div class="membro-item">
                <div>
                    <div class="membro-nome">{{ $membro->user->name ?? 'N/A' }}</div>
                    <div class="membro-email">{{ $membro->user->email ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('master.equipes.remover-membro', [$equipe->id, $membro->id]) }}" style="display:inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Remover Membro', message: 'Remover {{ $membro->user->name ?? 'este vendedor' }} da equipe?', type: 'warning', confirmText: 'Remover', onConfirm: () => this.submit()});">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="membro-remove" title="Remover da equipe">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        <div class="equipe-card-actions">
            <button class="action-btn" title="Editar Equipe" onclick='openEditEquipeModal({{ json_encode($equipe->only(["id", "nome", "meta_mensal", "cor"])) }})'>
                <i class="fas fa-pen"></i>
            </button>
            <button class="action-btn" title="Adicionar Membro" onclick="openAddMembroModal({{ $equipe->id }}, '{{ $equipe->nome }}')">
                <i class="fas fa-user-plus"></i>
            </button>
            <form method="POST" action="{{ route('master.equipes.destroy', $equipe->id) }}" style="display:inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Remover Equipe', message: 'Deseja realmente remover esta equipe? Os vendedores não serão excluídos.', type: 'danger', confirmText: 'Remover', onConfirm: () => this.submit()});">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-btn danger" title="Remover Equipe">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <div class="empty-icon"><i class="fas fa-people-group"></i></div>
    <h3>Nenhuma equipe cadastrada</h3>
    <p>Crie uma equipe ou atribua um gestor a vendedores para criar automaticamente.</p>
</div>
@endif

@if($vendedoresSemEquipe->count() > 0)
<div class="section-title">
    <i class="fas fa-user-slash"></i> Vendedores sem Equipe ({{ $vendedoresSemEquipe->count() }})
</div>
<div class="vendedores-sem-equipe">
    @foreach($vendedoresSemEquipe as $v)
    <div class="vendedor-se-item">
        <div>
            <strong>{{ $v->user->name ?? 'N/A' }}</strong>
            <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: 8px;">{{ $v->user->email ?? '' }}</span>
        </div>
        @if($equipes->count() > 0)
        <form method="POST" action="{{ route('master.equipes.adicionar-membro', $equipes->first()->id) }}" style="display: flex; align-items: center; gap: 8px;">
            @csrf
            <input type="hidden" name="vendedor_id" value="{{ $v->id }}">
            <select name="equipe_target" class="form-control" style="padding: 4px 8px; font-size: 0.82rem; width: auto;" onchange="this.form.action = '/master/equipes/' + this.value + '/adicionar-membro'">
                @foreach($equipes as $eq)
                    <option value="{{ $eq->id }}">{{ $eq->nome }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary" style="padding: 4px 12px; font-size: 0.82rem;">
                <i class="fas fa-plus"></i> Adicionar
            </button>
        </form>
        @endif
    </div>
    @endforeach
</div>
@endif

<!-- MODAL: Criar Equipe -->
<div class="modal-overlay" id="createEquipeModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-people-group" style="margin-right: 8px;"></i>Nova Equipe</h2>
            <button class="modal-close" onclick="BasileiaModal.close('createEquipeModal')">&times;</button>
        </div>
        <form action="{{ route('master.equipes.store') }}" method="POST" class="modal-body">
            @csrf
            <div class="form-group">
                <label>Nome da Equipe <span class="required">*</span></label>
                <input type="text" name="nome" class="form-control" required placeholder="Ex: Equipe Anthony">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Gestor Responsável <span class="required">*</span></label>
                    <select name="gestor_id" class="form-control" required>
                        <option value="">Selecione o gestor</option>
                        @foreach($gestoresDisponiveis as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Meta Mensal (R$)</label>
                    <input type="number" step="0.01" name="meta_mensal" class="form-control" placeholder="0.00">
                </div>
            </div>
            <div class="form-group">
                <label>Cor da Equipe</label>
                <input type="color" name="cor" class="form-control" value="#4C1D95" style="height: 42px; padding: 4px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('createEquipeModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Criar Equipe</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Editar Equipe -->
<div class="modal-overlay" id="editEquipeModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-pen" style="margin-right: 8px;"></i>Editar Equipe</h2>
            <button class="modal-close" onclick="BasileiaModal.close('editEquipeModal')">&times;</button>
        </div>
        <form id="editEquipeForm" method="POST" class="modal-body">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nome da Equipe <span class="required">*</span></label>
                <input type="text" name="nome" id="editEquipeNome" class="form-control" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Meta Mensal (R$)</label>
                    <input type="number" step="0.01" name="meta_mensal" id="editEquipeMeta" class="form-control">
                </div>
                <div class="form-group">
                    <label>Cor da Equipe</label>
                    <input type="color" name="cor" id="editEquipeCor" class="form-control" style="height: 42px; padding: 4px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('editEquipeModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Adicionar Membro -->
<div class="modal-overlay" id="addMembroModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus" style="margin-right: 8px;"></i>Adicionar Membro</h2>
            <button class="modal-close" onclick="BasileiaModal.close('addMembroModal')">&times;</button>
        </div>
        <form id="addMembroForm" method="POST" class="modal-body">
            @csrf
            <div class="form-group">
                <label>Equipe</label>
                <div id="addMembroEquipeNome" style="font-weight: 700; font-size: 1rem; color: var(--primary); padding: 8px 0;"></div>
            </div>
            <div class="form-group">
                <label>Vendedor <span class="required">*</span></label>
                <select name="vendedor_id" class="form-control" required>
                    <option value="">Selecione o vendedor</option>
                    @foreach($vendedoresSemEquipe as $v)
                        <option value="{{ $v->id }}">{{ $v->user->name ?? 'N/A' }} - {{ $v->user->email ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('addMembroModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Adicionar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openEditEquipeModal(data) {
        const form = document.getElementById('editEquipeForm');
        form.action = '/master/equipes/' + data.id;
        document.getElementById('editEquipeNome').value = data.nome;
        document.getElementById('editEquipeMeta').value = data.meta_mensal;
        document.getElementById('editEquipeCor').value = data.cor || '#4C1D95';
        BasileiaModal.open('editEquipeModal');
    }

    function openAddMembroModal(equipeId, equipeNome) {
        const form = document.getElementById('addMembroForm');
        form.action = '/master/equipes/' + equipeId + '/adicionar-membro';
        document.getElementById('addMembroEquipeNome').textContent = equipeNome;
        BasileiaModal.open('addMembroModal');
    }
</script>
@endsection
