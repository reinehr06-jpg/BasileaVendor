@extends('layouts.app')
@section('title', 'Integrações — Basileia Vendas')

@section('content')
<div class="integracoes-page">
    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:24px;">
        <div class="card" style="flex:1; min-width:220px; padding:20px;">
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">Vendas Pagas</div>
            <div style="font-size:1.8rem; font-weight:800; color:var(--primary);">{{ $totalVendas }}</div>
        </div>
        <div class="card" style="flex:1; min-width:220px; padding:20px;">
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">Pendentes</div>
            <div style="font-size:1.8rem; font-weight:800; color:#f59e0b;">{{ $totalPendentes }}</div>
        </div>
        <div class="card" style="flex:1; min-width:220px; padding:20px;">
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">Faturamento Total</div>
            <div style="font-size:1.8rem; font-weight:800; color:#10b981;">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</div>
        </div>
        <div class="card" style="flex:1; min-width:220px; padding:20px;">
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">Ambiente Asaas</div>
            <div style="font-size:1.3rem; font-weight:700; color:{{ $asaasStatus === 'production' ? '#10b981' : '#f59e0b' }};">
                {{ $asaasStatus === 'production' ? '🚀 Produção' : '🧪 Sandbox' }}
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light);">
            <h2 style="font-size:1.1rem; font-weight:700;">Status da Integração</h2>
        </div>
        <div style="padding:20px;">
            <div style="display:flex; gap:16px; align-items:center; margin-bottom:12px;">
                <span style="width:10px; height:10px; border-radius:50%; background:{{ $asaasApiKey ? '#10b981' : '#ef4444' }}; display:inline-block;"></span>
                <span>API Key: {{ $asaasApiKey ? 'Configurada' : 'Não configurada' }}</span>
            </div>
            <div style="display:flex; gap:16px; align-items:center; margin-bottom:12px;">
                <span style="width:10px; height:10px; border-radius:50%; background:{{ $splitAtivo ? '#10b981' : '#94a3b8' }}; display:inline-block;"></span>
                <span>Split Global: {{ $splitAtivo ? 'Ativo' : 'Inativo' }}</span>
            </div>
            <a href="{{ route('master.configuracoes', ['tab' => 'integracoes']) }}" class="btn btn-primary" style="margin-top:8px;">
                <i class="fas fa-cog"></i> Configurar Integração Asaas
            </a>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light);">
            <h2 style="font-size:1.1rem; font-weight:700;">Últimas Cobranças</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasCobrancas as $c)
                    <tr>
                        <td>#{{ $c->id }}</td>
                        <td>{{ $c->venda->cliente->nome ?? '—' }}</td>
                        <td>R$ {{ number_format($c->valor, 2, ',', '.') }}</td>
                        <td><span class="badge badge-{{ $c->status === 'confirmado' ? 'success' : ($c->status === 'pendente' ? 'warning' : 'danger') }}">{{ $c->status }}</span></td>
                        <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">Nenhuma cobrança registrada</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light);">
            <h2 style="font-size:1.1rem; font-weight:700;">Últimos Webhooks</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Pagamento ID</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimosWebhooks as $w)
                    <tr>
                        <td>{{ $w->evento ?? '—' }}</td>
                        <td><code>{{ $w->asaas_payment_id ?? '—' }}</code></td>
                        <td><span class="badge badge-{{ $w->processado_em ? 'success' : 'warning' }}">{{ $w->processado_em ? 'Processado' : 'Pendente' }}</span></td>
                        <td>{{ $w->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">Nenhum webhook registrado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
