@section('title', 'Meus Clientes')

@section('header_title', 'Meus Clientes')
@section('header_description', 'Gerencie sua base de clientes e histórico de compras.')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-ativo { background: #dcfce7; color: #15803d; }
    .badge-pendente { background: #fef9c3; color: #854d0e; }
    .badge-churn { background: #fee2e2; color: #b91c1c; }
    .badge-inadimplente { background: #fef3c7; color: #92400e; }
    .badge-cancelado { background: #f1f5f9; color: #64748b; }
    .action-btn { background: white; border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; transition: 0.2s; }
    .action-btn:hover { border-color: var(--primary); background: #f8fafc; }
</style>

<x-page-hero title="Meus Clientes" subtitle="Gerencie sua carteira de clientes" icon="fas fa-building">
    <x-slot:actions>
        <a href="{{ route('vendedor.vendas.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Venda</a>
    </x-slot:actions>
</x-page-hero>

<!-- Summary Cards -->
<div class="stats-bar">
    <div class="stat-card" style="background: var(--primary); border-color: var(--primary);">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-building"></i></div>
        <div class="stat-value" style="color: white;">{{ $cards['total'] }}</div>
        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total na Carteira</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ $cards['ativos'] }}</div>
        <div class="stat-label">Em Dia</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">{{ $cards['pendentes'] }}</div>
        <div class="stat-label">Pendentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $cards['inadimplentes'] }}</div>
        <div class="stat-label">Inadimplentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fce7f3; color: #9d174d;"><i class="fas fa-user-minus"></i></div>
        <div class="stat-value" style="color: #9d174d;">{{ $cards['churn'] }}</div>
        <div class="stat-label">Churn</div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('vendedor.clientes') }}">
<div class="filters-bar">
    <div style="flex-grow: 1; position: relative;">
        <i class="fas fa-magnifying-glass" style="position: absolute; left: 14px; top: 11px; color: var(--text-muted);"></i>
        <input type="text" name="busca" class="search-input" style="padding-left: 40px;" value="{{ request('busca') }}" placeholder="Nome, igreja, CNPJ...">
    </div>
    <select name="status" class="filter-select">
        <option value="">Todos os Status</option>
        <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
        <option value="inadimplente" {{ request('status') == 'inadimplente' ? 'selected' : '' }}>Inadimplente</option>
        <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
        <option value="churn" {{ request('status') == 'churn' ? 'selected' : '' }}>Churn</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
    <a href="{{ route('vendedor.clientes') }}" class="btn btn-ghost btn-sm">Limpar</a>
</div>
</form>

<!-- Table -->
<div class="table-container">
    @if($clientes->count() > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-building" style="margin-right: 4px;"></i> Igreja</th>
                <th><i class="fas fa-user" style="margin-right: 4px;"></i> Responsável</th>
                <th><i class="fas fa-users" style="margin-right: 4px;"></i> Membros</th>
                <th><i class="fas fa-dollar-sign" style="margin-right: 4px;"></i> Financeiro</th>
                <th><i class="fas fa-circle-check" style="margin-right: 4px;"></i> Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $c)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $c->nome_igreja ?? $c->nome }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-location-dot" style="margin-right: 2px;"></i> {{ $c->localidade ?? 'Não informada' }}</div>
                </td>
                <td>
                    <div style="font-weight: 600;">{{ $c->nome_pastor ?? $c->nome_responsavel ?? 'Não informado' }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-phone" style="margin-right: 2px;"></i> {{ $c->contato ?? $c->telefone ?? '' }}</div>
                </td>
                <td style="font-weight: 700; text-align: center;">{{ $c->quantidade_membros ?? '-' }}</td>
                <td>
                    @if($c->temCobrancaAberta())
                        <span class="badge" style="background: #fee2e2; color: #b91c1c;">
                            <i class="fas fa-triangle-exclamation"></i> Débito
                        </span>
                    @else
                        <span class="badge" style="background: #dcfce7; color: #15803d;">
                            <i class="fas fa-circle-check"></i> Em Dia
                        </span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $c->status ?? 'ativo' }}">{{ ucfirst($c->status ?? 'Ativo') }}</span>
                    @if($c->data_ultimo_pagamento)
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-calendar-check"></i> {{ $c->data_ultimo_pagamento->format('d/m/Y') }}</div>
                    @endif
                </td>
                <td style="text-align: right;">
                    <a href="{{ route('vendedor.clientes.show', $c->id) }}" class="action-btn">
                        <i class="fas fa-eye"></i> Histórico
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.85rem; color: var(--text-muted);">Mostrando {{ $clientes->firstItem() ?? 0 }} a {{ $clientes->lastItem() ?? 0 }} de {{ $clientes->total() }} clientes</span>
        <div>{{ $clientes->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-users"></i></div>
        <h3>Nenhum cliente na sua base</h3>
        <p>Os clientes aparecerão aqui assim que você registrar a primeira venda para eles.</p>
        <a href="{{ route('vendedor.vendas.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Venda</a>
    </div>
    @endif
</div>

@endsection
