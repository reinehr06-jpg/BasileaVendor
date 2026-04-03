@extends('layouts.app')
@section('title', 'Minhas Comissões')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-pendente { background: #fef9c3; color: #854d0e; }
    .badge-confirmada { background: #dcfce7; color: #15803d; }
    .badge-paga { background: #dbeafe; color: #1d4ed8; }
    .badge-inicial { background: #e0f2fe; color: #0369a1; }
    .badge-recorrencia { background: #faf5ff; color: #7e22ce; }
</style>

<x-page-hero title="Minhas Comissões" subtitle="Acompanhe suas comissões e pagamentos" icon="fas fa-hand-holding-dollar" :exports="[
    ['type' => 'excel', 'url' => route('vendedor.comissoes.exportar', ['mes' => $mes]), 'icon' => 'fas fa-file-excel', 'label' => 'Excel'],
]" />

<!-- Summary Cards -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">R$ {{ number_format((float)($resumo['pendente'] ?? 0), 2, ',', '.') }}</div>
        <div class="stat-label">Pendente</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value" style="color: var(--success);">R$ {{ number_format((float)($resumo['confirmada'] ?? 0), 2, ',', '.') }}</div>
        <div class="stat-label">Confirmada</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-building-columns"></i></div>
        <div class="stat-value" style="color: var(--info);">R$ {{ number_format((float)($resumo['paga'] ?? 0), 2, ',', '.') }}</div>
        <div class="stat-label">Paga</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-rotate"></i></div>
        <div class="stat-value">{{ $resumo['recorrencias'] ?? 0 }}</div>
        <div class="stat-label">Recorrências</div>
    </div>
    <div class="stat-card" style="background: var(--primary); border-color: var(--primary);">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value" style="color: white;">R$ {{ number_format((float)($resumo['total'] ?? 0), 2, ',', '.') }}</div>
        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total do Mês</div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('vendedor.comissoes') }}">
<div class="filters-bar">
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-calendar"></i> Mês</label>
        <input type="month" name="mes" class="form-control" value="{{ $mes }}" onchange="this.form.submit()">
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-tag"></i> Tipo</label>
        <select name="tipo" class="form-control" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="inicial" {{ $tipo == 'inicial' ? 'selected' : '' }}>Inicial</option>
            <option value="recorrencia" {{ $tipo == 'recorrencia' ? 'selected' : '' }}>Recorrência</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px;">
        <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted);"><i class="fas fa-circle-check"></i> Status</label>
        <select name="status" class="form-control" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="pendente" {{ $status == 'pendente' ? 'selected' : '' }}>Pendente</option>
            <option value="confirmada" {{ $status == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
            <option value="paga" {{ $status == 'paga' ? 'selected' : '' }}>Paga</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px; align-items: flex-end;">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="{{ route('vendedor.comissoes') }}" class="btn btn-ghost btn-sm">Limpar</a>
    </div>
</div>
</form>

<!-- Table -->
<div class="table-container">
    @if($comissoes->count() > 0)
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-building" style="margin-right: 4px;"></i> Cliente</th>
                <th><i class="fas fa-id-card" style="margin-right: 4px;"></i> CPF/CNPJ</th>
                <th><i class="fas fa-hashtag" style="margin-right: 4px;"></i> Venda</th>
                <th>%</th>
                <th><i class="fas fa-dollar-sign" style="margin-right: 4px;"></i> Comissão</th>
                <th><i class="fas fa-tag" style="margin-right: 4px;"></i> Tipo</th>
                <th><i class="fas fa-calendar-check" style="margin-right: 4px;"></i> Data Pag.</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comissoes as $c)
            @php
                $isDirect = ($vendedor && $c->vendedor_id == $vendedor->id);
                $valorExibido = $isDirect ? $c->valor_comissao : $c->valor_gerente;
                $percentualExibido = $isDirect ? $c->percentual_aplicado : $c->percentual_gerente;
            @endphp
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $c->cliente?->nome_igreja ?? $c->cliente?->nome ?? 'N/A' }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        @if(!$isDirect)
                            <span style="color: var(--primary); font-weight: 600;">[Equipe: {{ $c->vendedor?->user?->name ?? 'Vendedor' }}]</span>
                        @endif
                        {{ $c->cliente?->nome_pastor ?? $c->cliente?->nome_responsavel ?? '' }}
                    </div>
                </td>
                <td style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted);">{{ $c->cliente?->documento ?? '-' }}</td>
                <td style="font-weight: 600; text-align: center;">#{{ $c->venda_id }}</td>
                <td style="text-align: center; font-weight: 700;">{{ number_format((float)($percentualExibido ?? 0), 1) }}%</td>
                <td style="font-weight: 700; color: var(--primary);">R$ {{ number_format((float)($valorExibido ?? 0), 2, ',', '.') }}</td>
                <td>
                    @if($isDirect)
                        <span class="badge" style="background: #e0f2fe; color: #0369a1;"><i class="fas fa-user"></i> Direta</span>
                    @else
                        <span class="badge" style="background: #fdf2f8; color: #9d174d;"><i class="fas fa-users"></i> Equipe</span>
                    @endif
                    <br>
                    <span class="badge badge-{{ $c->tipo_comissao }}" style="margin-top: 4px; font-size: 0.65rem;">
                        <i class="fas fa-{{ $c->tipo_comissao === "recorrencia" ? "rotate" : "star" }}"></i> 
                        {{ ucfirst($c->tipo_comissao) }}
                    </span>
                </td>
                <td>{{ $c->data_pagamento ? $c->data_pagamento->format('d/m/Y') : '-' }}</td>
                <td><span class="badge badge-{{ $c->status }}">{{ ucfirst($c->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.85rem; color: var(--text-muted);">Mostrando {{ $comissoes->firstItem() ?? 0 }} a {{ $comissoes->lastItem() ?? 0 }} de {{ $comissoes->total() }} registros</span>
        <div>{{ $comissoes->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-hand-holding-dollar"></i></div>
        <h3>Nenhuma comissão encontrada</h3>
        <p>As comissões aparecerão aqui quando seus pagamentos forem confirmados.</p>
    </div>
    @endif
</div>

@endsection
