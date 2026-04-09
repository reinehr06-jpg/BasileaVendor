@extends('layouts.app')
@section('title', 'Basileia Vendas')

@section('content')
<style>
    .welcome-section {
        margin-bottom: 35px;
        padding: 30px;
        background: linear-gradient(135deg, var(--primary-dark) 0%, #4C1D95 100%);
        border-radius: var(--radius-xl);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 25px -5px rgba(59, 7, 100, 0.2);
    }
    .welcome-text h1 { color: white; margin-bottom: 8px; font-size: 1.8rem; letter-spacing: -0.5px; }
    .welcome-text p { opacity: 0.8; font-size: 0.95rem; }
    
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .kpi-card {
        padding: 24px;
        background: white;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .kpi-card .label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: block; }
    .kpi-card .value { font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
    .kpi-card .footer { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; }
    .kpi-icon { position: absolute; top: 15px; right: 15px; font-size: 1.2rem; opacity: 0.15; color: var(--primary); }

    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }
    .chart-container {
        background: white;
        border-radius: var(--radius-lg);
        padding: 25px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
    }
    .insights-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .insight-card {
        padding: 20px;
        background: var(--surface-hover);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
    }
    .insight-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-weight: 700; font-size: 0.9rem; color: var(--text-primary); }
    .insight-header i { color: var(--primary); }
    .insight-value { font-size: 1.1rem; font-weight: 700; color: var(--primary); }
    .insight-desc { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; }

    @media (max-width: 1200px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .main-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .kpi-grid { grid-template-columns: 1fr; }
        .welcome-section { flex-direction: column; text-align: center; gap: 20px; }
    }
    .period-selector { display: flex; gap: 4px; background: rgba(255,255,255,0.1); border-radius: 8px; padding: 3px; }
    .period-btn { padding: 5px 12px; border: none; background: transparent; color: rgba(255,255,255,0.7); border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600; transition: 0.2s; }
    .period-btn.active { background: white; color: var(--primary-dark); }
    .period-btn:hover:not(.active) { background: rgba(255,255,255,0.15); color: white; }
</style>

<div class="animate-up" style="animation-delay: 0.1s;">
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Olá, {{ Auth::user()->name }} 👋</h1>
            <p>{{ $tituloSessao }} — {{ $periodoLabel }}.</p>
        </div>
        <div class="welcome-badge">
            <span class="badge badge-primary" style="background: rgba(255,255,255,0.1); color: white; padding: 10px 20px; border-radius: 12px; font-size: 0.85rem;">
                Basileia Vendas <i class="fas fa-check-circle" style="margin-left: 8px;"></i>
            </span>
        </div>
    </div>
</div>

<div class="kpi-grid">
    <!-- Faturamento -->
    <div class="kpi-card animate-up" style="animation-delay: 0.2s;">
        <i class="fas fa-money-bill-trend-up kpi-icon"></i>
        <span class="label">{{ $isPersonal ? 'Meu Faturamento' : 'Faturamento de Vendas' }}</span>
        <div class="value">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</div>
        <div class="footer">
            <span class="{{ $recebidoTrend >= 0 ? 'trend-up' : 'trend-down' }}">
                <i class="fas fa-caret-{{ $recebidoTrend >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($recebidoTrend), 1) }}%
            </span>
            <span style="color: var(--text-muted); font-size: 0.7rem;">vs {{ $periodo === 'week' ? 'semana anterior' : ($periodo === 'year' ? 'ano anterior' : 'mês anterior') }}</span>
        </div>
    </div>

    <!-- Vendas Ativas -->
    <div class="kpi-card animate-up" style="animation-delay: 0.3s;">
        <i class="fas fa-shopping-basket kpi-icon"></i>
        <span class="label">Vendas Ativas</span>
        <div class="value">{{ $vendasAtivas }}</div>
        <div class="footer">
            <span class="{{ $vendasTrend >= 0 ? 'trend-up' : 'trend-down' }}">
                <i class="fas fa-caret-{{ $vendasTrend >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($vendasTrend), 1) }}%
            </span>
            <span style="color: var(--text-muted); font-size: 0.7rem;">no período atual</span>
        </div>
    </div>

    <!-- Clientes Ativos -->
    <div class="kpi-card animate-up" style="animation-delay: 0.4s;">
        <i class="fas fa-user-check kpi-icon"></i>
        <span class="label">{{ $isPersonal ? 'Meus Clientes' : 'Base de Clientes' }}</span>
        <div class="value">{{ $clientesAtivos }}</div>
        <div class="footer">
            <span style="color: var(--success); font-weight: 700; font-size: 0.75rem;">Saúde 94%</span>
            <span style="color: var(--text-muted); font-size: 0.7rem;">ativos na recorrência</span>
        </div>
    </div>

    <!-- Comissões -->
    <div class="kpi-card animate-up" style="animation-delay: 0.5s;">
        <i class="fas fa-hand-holding-dollar kpi-icon"></i>
        <span class="label">{{ $isPersonal ? 'Minhas Comissões' : 'Comissões Pendentes' }}</span>
        <div class="value" style="color: var(--warning);">R$ {{ number_format($comissoesPendentes, 2, ',', '.') }}</div>
        <div class="footer">
            <span style="color: var(--text-muted); font-size: 0.7rem;">Referente a <b>{{ $contagemPendentes }}</b> vendas pagas</span>
        </div>
    </div>
</div>

<div class="main-grid">
    <!-- Gráfico de Receita -->
    <div class="chart-container animate-up" style="animation-delay: 0.6s;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="font-size: 1.1rem; color: var(--text-primary);">{{ $isPersonal ? 'Meu Desempenho' : 'Desempenho da Operação' }}</h3>
            <div class="period-selector" style="background: #f1f5f9;">
                <a href="?periodo=week" class="period-btn {{ $periodo === 'week' ? 'active' : '' }}" style="color: #64748b;">Semana</a>
                <a href="?periodo=month" class="period-btn {{ $periodo === 'month' ? 'active' : '' }}" style="color: #64748b;">Mês</a>
                <a href="?periodo=year" class="period-btn {{ $periodo === 'year' ? 'active' : '' }}" style="color: #64748b;">Ano</a>
            </div>
        </div>
        <canvas id="revenueChart" style="max-height: 280px;"></canvas>
    </div>

    <!-- Insights Rápidos -->
    <div class="insights-container animate-up" style="animation-delay: 0.7s;">
        <h3 style="font-size: 1.1rem; color: var(--text-primary); margin-bottom: 5px; margin-left: 5px;">Insights de Gestão</h3>
        
        <div class="insight-card">
            <div class="insight-header"><i class="fas fa-calendar-check"></i> Melhor Período</div>
            <div class="insight-value">{{ $melhorFaixa }}</div>
            <p class="insight-desc">Maior volume de recebimentos efetuados.</p>
        </div>

        @if(!$isPersonal && $vendedoresAtivos > 0)
        <div class="insight-card">
            <div class="insight-header"><i class="fas fa-users"></i> Equipe Ativa</div>
            <div class="insight-value">{{ $vendedoresAtivos }} <small style="font-size: 0.7rem; color: var(--text-muted);">parceiros</small></div>
            <p class="insight-desc">Membros ativos na operação atual.</p>
        </div>
        @endif

        <div class="insight-card">
            <div class="insight-header"><i class="fas fa-money-bill-1-wave"></i> Ticket Médio</div>
            <div class="insight-value">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
            <p class="insight-desc">Valor médio por cliente ativo (incluindo base histórica).</p>
        </div>

        <div class="insight-card" style="border-left: 3px solid var(--danger);">
            <div class="insight-header"><i class="fas fa-user-minus" style="color: var(--danger);"></i> Churn & Perda</div>
            <div class="insight-value" style="color: var(--danger);">{{ $churnMes }} <small style="font-size: 0.7rem; opacity: 0.6;">vendas</small></div>
            <p class="insight-desc">Registros que interromperam o ciclo de pagamento.</p>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Dados vindos do controller
        const labels = {!! json_encode(collect($graficoData)->map(fn($s) => $s['label'])) !!};
        const data = {!! json_encode(collect($graficoData)->map(fn($s) => $s['total'])) !!};

        // Create Gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(76, 29, 149, 0.2)');
        gradient.addColorStop(1, 'rgba(76, 29, 149, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['S1', 'S2', 'S3', 'S4'],
                datasets: [{
                    label: 'Faturamento Bruto',
                    data: data.length ? data : [0, 0, 0, 0],
                    borderColor: '#4C1D95',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4C1D95',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>
@endsection
