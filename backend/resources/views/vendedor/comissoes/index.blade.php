@extends('layouts.app')
@section('title', 'Minhas Comissões')

@section('content')
<style>
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-pendente { background: #fef9c3; color: #854d0e; }
    .badge-confirmada { background: #dcfce7; color: #15803d; }
    .badge-paga { background: #dbeafe; color: #1d4ed8; }
</style>

<div style="background: white; padding: 20px 24px; border-radius: 12px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border);">
    <div>
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--text-primary);"><i class="fas fa-hand-holding-dollar" style="color: var(--primary); margin-right: 8px;"></i> Minhas Comissões</h1>
        <p style="font-size: 0.85rem; margin: 4px 0 0; color: var(--text-muted);">Acompanhe suas comissões e pagamentos</p>
    </div>
</div>

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
    <div class="stat-card" style="background: var(--primary); padding: 20px; border-radius: 12px; color: white;">
        <div class="stat-label" style="font-size: 0.75rem; opacity: 0.8; font-weight: 600; text-transform: uppercase;">Total do Mês</div>
        <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; margin-top: 8px;">R$ {{ number_format((float)($resumo['total'] ?? 0), 2, ',', '.') }}</div>
    </div>
</div>

<form method="GET" action="{{ route('vendedor.comissoes') }}">
<div class="filters-bar" style="background: white; padding: 16px; border-radius: 12px; border: 1px solid var(--border); display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-end;">
    <div style="flex: 1;">
        <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">Mês</label>
        <input type="month" name="mes" class="form-control" value="{{ $mes }}" onchange="this.form.submit()">
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <a href="{{ route('vendedor.comissoes') }}" class="btn btn-ghost btn-sm">Limpar</a>
    </div>
</div>
</form>

<div class="table-container" style="background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: #f8fafc; border-bottom: 1px solid var(--border);">
            <tr>
                <th style="padding: 12px 16px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Cliente</th>
                <th style="padding: 12px 16px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Cobranca</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">%</th>
                <th style="padding: 12px 16px; text-align: right; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Comissão</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Status</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($comissoes) && $comissoes->count() > 0)
                @foreach($comissoes as $c)
                    <tr style="border-bottom: 1px solid var(--border-light);">
                        <td style="padding: 12px 16px;">{{ $c->cliente?->nome_igreja ?? $c->cliente?->nome ?? 'N/A' }}</td>
                        <td style="padding: 12px 16px;">#{{ $c->venda_id }}</td>
                        <td style="padding: 12px 16px; text-align: center;">{{ number_format((float)($c->percentual_aplicado ?? 0), 1) }}%</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700; color: var(--primary);">R$ {{ number_format((float)($c->valor_comissao ?? 0), 2, ',', '.') }}</td>
                        <td style="padding: 12px 16px; text-align: center;"><span class="badge badge-{{ $c->status ?? 'pendente' }}">{{ $c->status ?? 'Pendente' }}</span></td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" style="padding: 40px; text-align: center; color: var(--text-muted);">Nenhuma comissão nas condições filtradas.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; padding: 16px; background: #fefce8; border: 1px solid #fde047; border-radius: 8px; color: #854d0e; font-size: 0.85rem;">
    <i class="fas fa-info-circle"></i> **Aviso de Diagnóstico:** O sistema de paginação está temporariamente desativado para confirmar a causa do erro 500.
</div>
@endsection
