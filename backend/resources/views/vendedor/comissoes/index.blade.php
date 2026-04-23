@extends('layouts.app')
@section('title', 'Minhas Comissões')

@section('header_title', 'Minhas Comissões')
@section('header_description', 'Detalhamento de ganhos e valores a receber.')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-pendente { background: #fef9c3; color: #854d0e; }
    .badge-confirmada { background: #dcfce7; color: #15803d; }
    .badge-paga { background: #dbeafe; color: #1d4ed8; }
    .badge-inicial { background: #e0f2fe; color: #0369a1; }
    .badge-recorrencia { background: #faf5ff; color: #7e22ce; }
</style>

<x-page-hero 
    title="Minhas Comissões" 
    subtitle="Acompanhe seus ganhos e comissões do período." 
    icon="fas fa-hand-holding-dollar"
    :exports="[
        ['type' => 'excel', 'url' => route('vendedor.comissoes.exportar', ['mes' => $mes, 'formato' => 'excel']), 'icon' => 'fas fa-file-excel', 'label' => 'Excel'],
        ['type' => 'pdf', 'url' => route('vendedor.comissoes.exportar', ['mes' => $mes, 'formato' => 'pdf']), 'icon' => 'fas fa-file-pdf', 'label' => 'PDF'],
        ['type' => 'csv', 'url' => route('vendedor.comissoes.exportar', ['mes' => $mes, 'formato' => 'csv']), 'icon' => 'fas fa-file-csv', 'label' => 'CSV'],
    ]"
/>

<!-- Summary Cards -->
<div class="stats-bar" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
        <div class="stat-label" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Pendente</div>
        <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--warning); margin-top: 8px;">R$ {{ number_format((float)($resumo['pendente'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
        <div class="stat-label" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Confirmada</div>
        <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--success); margin-top: 8px;">R$ {{ number_format((float)($resumo['confirmada'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
        <div class="stat-label" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Paga</div>
        <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--info); margin-top: 8px;">R$ {{ number_format((float)($resumo['paga'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="stat-card" style="background: var(--primary); padding: 20px; border-radius: 12px; color: white; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);">
        <div class="stat-label" style="font-size: 0.75rem; color: rgba(255,255,255,0.9); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Total do Mês</div>
        <div class="stat-value" style="font-size: 1.6rem; font-weight: 800; color: #ffffff; margin-top: 8px; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">R$ {{ number_format((float)($resumo['total'] ?? 0), 2, ',', '.') }}</div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('vendedor.comissoes') }}">
<div class="filters-bar" style="background: white; padding: 16px; border-radius: 12px; border: 1px solid var(--border); display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-end;">
    <div style="flex: 1;">
        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);"><i class="fas fa-calendar"></i> Mês</label>
        <input type="month" name="mes" class="form-control" value="{{ $mes }}" onchange="this.form.submit()">
    </div>
    <div style="flex: 1;">
        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);"><i class="fas fa-tag"></i> Tipo</label>
        <select name="tipo" class="form-control" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="inicial" {{ isset($tipo) && $tipo == 'inicial' ? 'selected' : '' }}>Inicial</option>
            <option value="recorrencia" {{ isset($tipo) && $tipo == 'recorrencia' ? 'selected' : '' }}>Recorrência</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="{{ route('vendedor.comissoes') }}" class="btn btn-ghost btn-sm">Limpar</a>
    </div>
</div>
</form>

<div class="table-container" style="background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: #f8fafc; border-bottom: 1px solid var(--border);">
            <tr>
                <th style="padding: 14px 16px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Cliente</th>
                <th style="padding: 14px 16px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Venda</th>
                <th style="padding: 14px 16px; text-align: right; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Base de Cálculo</th>
                <th style="padding: 14px 16px; text-align: center; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">%</th>
                <th style="padding: 14px 16px; text-align: right; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Valor Comissão</th>
                <th style="padding: 14px 16px; text-align: center; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Status</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($comissoes) && $comissoes->count() > 0)
                @foreach($comissoes as $c)
                    <tr style="border-bottom: 1px solid var(--border-light); transition: background 0.2s;">
                        <td style="padding: 14px 16px;">
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $c->cliente?->nome_igreja ?? $c->cliente?->nome ?? 'N/A' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $c->cliente?->documento ?? '-' }}</div>
                        </td>
                        <td style="padding: 14px 16px; font-weight: 600;">#{{ $c->venda_id }}</td>
                        <td style="padding: 14px 16px; text-align: right; font-weight: 600;">R$ {{ number_format((float)($c->valor_venda ?? 0), 2, ',', '.') }}</td>
                        <td style="padding: 14px 16px; text-align: center; font-weight: 700;">{{ number_format((float)($c->percentual_aplicado ?? 0), 1) }}%</td>
                        <td style="padding: 14px 16px; text-align: right; font-weight: 700; color: var(--primary);">R$ {{ number_format((float)($c->valor_comissao ?? 0), 2, ',', '.') }}</td>
                        <td style="padding: 14px 16px; text-align: center;">
                            <span class="badge badge-{{ $c->status ?? 'pendente' }}">{{ ucfirst($c->status ?? 'pendente') }}</span>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 2.5rem; opacity: 0.2; margin-bottom: 16px;"><i class="fas fa-hand-holding-dollar"></i></div>
                        <h4 style="margin-bottom: 8px; font-weight: 600;">Nenhuma comissão encontrada</h4>
                        <p style="font-size: 0.85rem;">As comissões aparecerão aqui conforme as vendas forem confirmadas.</p>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    
    {{ $comissoes->onEachSide(1)->links() }}
</div>
@endsection
