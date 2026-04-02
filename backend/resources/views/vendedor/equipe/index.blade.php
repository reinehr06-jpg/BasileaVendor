@extends('layouts.app')
@section('title', 'Minha Equipe')

@section('content')
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }

    .equipe-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .equipe-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background: var(--primary-light);
        color: var(--primary);
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-box {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .stat-box .stat-icon {
        font-size: 1.25rem;
        color: var(--primary);
        margin-bottom: 4px;
    }
    .stat-box .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }
    .stat-box .stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .meta-progress-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .meta-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .meta-progress-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .meta-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-primary);
    }
    .progress-bar-bg {
        background: var(--bg);
        height: 12px;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 6px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .progress-bar-fill.green { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .progress-bar-fill.yellow { background: linear-gradient(90deg, #eab308, #ca8a04); }
    .progress-bar-fill.red { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .progress-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .progress-percentage {
        font-size: 1.5rem;
        font-weight: 800;
    }

    .member-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s;
    }
    .member-card:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(76,29,149,0.1);
    }
    .member-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .member-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
    }
    .member-details h4 {
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    .member-details p {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin: 2px 0 0;
    }
    .member-stats {
        display: flex;
        gap: 24px;
        text-align: center;
    }
    .member-stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .member-stat-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .add-member-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .add-member-btn:hover {
        background: var(--primary-dark);
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.3;
    }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-users" style="margin-right: 8px;"></i>Minha Equipe</h2>
        <p>Gerencie sua equipe de vendas</p>
    </div>
    <div class="equipe-badge">
        <i class="fas fa-crown"></i>
        Gestor
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 16px; background: #dcfce7; border: 1px solid #86efac; border-radius: 8px; color: #166534;">
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 16px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; color: #991b1b;">
        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>{{ session('error') }}
    </div>
@endif

<!-- Stats da Equipe -->
<div class="stats-row animate-in">
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ $stats['total_vendedores'] }}</div>
        <div class="stat-label">Vendedores</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-value">{{ $stats['total_vendas'] }}</div>
        <div class="stat-label">Vendas (Mês)</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value">R$ {{ number_format($stats['valor_vendido'], 0, ',', '.') }}</div>
        <div class="stat-label">Vendido (Mês)</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value" style="color: var(--success);">R$ {{ number_format($stats['valor_recebido'], 0, ',', '.') }}</div>
        <div class="stat-label">Recebido (Mês)</div>
    </div>
</div>

<!-- Meta da Equipe -->
<div class="meta-progress-card animate-in">
    <div class="meta-progress-header">
        <h3><i class="fas fa-bullseye" style="margin-right: 8px; color: var(--primary);"></i>Meta - {{ $equipe->nome }}</h3>
    </div>
    
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px;">
        <div class="progress-percentage {{ $stats['percentual_meta'] >= 100 ? 'text-success' : ($stats['percentual_meta'] >= 50 ? 'text-warning' : 'text-danger') }}" style="font-size: 2rem; min-width: 80px;">
            {{ $stats['percentual_meta'] }}%
        </div>
        <div style="flex: 1;">
            <div class="progress-bar-bg" style="height: 16px; border-radius: 8px;">
                <div class="progress-bar-fill {{ $stats['percentual_meta'] >= 100 ? 'green' : ($stats['percentual_meta'] >= 50 ? 'yellow' : 'red') }}" 
                     style="width: {{ min($stats['percentual_meta'], 100) }}%;"></div>
            </div>
            <div class="progress-info">
                <span>R$ {{ number_format($stats['valor_recebido'], 2, ',', '.') }} recebido</span>
                <span>R$ {{ number_format($stats['meta_mensal'], 2, ',', '.') }} meta</span>
            </div>
        </div>
    </div>

    <form action="{{ route('vendedor.equipe.atualizar-meta') }}" method="POST" style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
        @csrf
        @method('PUT')
        <span style="font-size: 0.82rem; color: var(--text-muted);">Definir meta:</span>
        <div style="position: relative;">
            <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;">R$</span>
            <input type="number" step="0.01" name="meta_mensal" class="form-control" 
                   value="{{ $equipe->meta_mensal }}" style="width: 160px; text-align: right; padding-left: 32px;">
        </div>
        <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-check"></i></button>
    </form>
</div>

<!-- Lista de Vendedores -->
<div class="animate-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            <i class="fas fa-user-tie" style="margin-right: 8px;"></i>Vendedores da Equipe
        </h3>
        @if($vendedoresDisponiveis->count() > 0)
        <button class="add-member-btn" onclick="BasileiaModal.open('addMemberModal')">
            <i class="fas fa-plus"></i> Adicionar Vendedor
        </button>
        @endif
    </div>
    
    @if($vendedores->count() > 0)
        <div style="display: grid; gap: 12px;">
            @foreach($vendedores as $vendedor)
            @php
                $vendasMes = $vendedor->vendas()
                    ->whereBetween('created_at', [\Carbon\Carbon::now()->startOfMonth(), \Carbon\Carbon::now()])
                    ->whereNotIn('status', ['Cancelado', 'Expirado'])
                    ->get();
                $valorVendido = $vendasMes->sum('valor');
                $valorRecebido = $vendasMes->where('status', 'PAGO')->sum('valor');
            @endphp
            <div class="member-card">
                <div class="member-info">
                    <div class="member-avatar">{{ strtoupper(substr($vendedor->user->name, 0, 1)) }}</div>
                    <div class="member-details">
                        <h4>{{ $vendedor->user->name }}</h4>
                        <p>{{ $vendedor->user->email }} • {{ $vendedor->telefone ?? 'Sem telefone' }}</p>
                    </div>
                </div>
                <div class="member-stats">
                    <div>
                        <div class="member-stat-value">{{ $vendasMes->count() }}</div>
                        <div class="member-stat-label">Vendas</div>
                    </div>
                    <div>
                        <div class="member-stat-value">R$ {{ number_format($valorVendido, 0, ',', '.') }}</div>
                        <div class="member-stat-label">Vendido</div>
                    </div>
                    <div>
                        <div class="member-stat-value" style="color: var(--success);">R$ {{ number_format($valorRecebido, 0, ',', '.') }}</div>
                        <div class="member-stat-label">Recebido</div>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('vendedor.equipe.remover-membro', $vendedor->id) }}" style="display: inline;" onsubmit="return confirm('Deseja realmente remover este vendedor da equipe?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn danger" title="Remover da Equipe">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-users-slash"></i>
            <h3>Nenhum vendedor na equipe</h3>
            <p>Adicione vendedores à sua equipe para começar.</p>
        </div>
    @endif
</div>

<!-- Modal: Adicionar Membro -->
<div class="modal-overlay" id="addMemberModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus" style="margin-right: 8px;"></i>Adicionar à Equipe</h2>
            <button class="modal-close" onclick="BasileiaModal.close('addMemberModal')">&times;</button>
        </div>
        <form action="{{ route('vendedor.equipe.adicionar-membro') }}" method="POST">
            @csrf
            <div class="modal-body">
                @if($vendedoresDisponiveis->count() > 0)
                    <div class="form-group">
                        <label>Selecione o Vendedor</label>
                        <select name="vendedor_id" class="form-control" required>
                            <option value="">Escolher vendedor...</option>
                            @foreach($vendedoresDisponiveis as $vd)
                                <option value="{{ $vd->id }}">{{ $vd->user->name }} ({{ $vd->user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="empty-state" style="padding: 20px;">
                        <i class="fas fa-user-slash" style="font-size: 2rem;"></i>
                        <h4 style="margin-top: 12px;">Nenhum vendedor disponível</h4>
                        <p style="font-size: 0.9rem;">Todos os vendedores ativos já estão em equipes.</p>
                    </div>
                @endif
            </div>
            @if($vendedoresDisponiveis->count() > 0)
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('addMemberModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Adicionar</button>
            </div>
            @endif
        </form>
    </div>
</div>

@endsection