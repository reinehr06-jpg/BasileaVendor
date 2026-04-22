@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<style>
    .dashboard-viewport {
        height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
        gap: 15px;
        overflow: hidden;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 5px;
    }

    .dashboard-header h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
    }

    .kpi-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
    }

    .stat-card.mini {
        padding: 12px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-card.mini .stat-icon {
        width: 30px;
        height: 30px;
        font-size: 0.8rem;
        margin-bottom: 6px;
    }

    .stat-card.mini .stat-value {
        font-size: 1.15rem;
        font-weight: 800;
    }

    .stat-card.mini .stat-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        opacity: 0.6;
    }

    .main-area {
        flex: 1;
        display: grid;
        grid-template-columns: 2.5fr 1fr;
        gap: 15px;
        min-height: 0;
    }

    .chart-box {
        background: white;
        display: flex;
        flex-direction: column;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
    }

    .insights-column {
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* Spreads cards to fill vertical space */
        height: 100%;
        gap: 10px;
    }

    .insight-item {
        flex: 1;
        background: white;
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: var(--transition);
        text-decoration: none !important;
    }

    .insight-item:hover {
        transform: translateX(5px);
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .insight-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .insight-info .label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .insight-info .value {
        font-weight: 800;
        font-size: 1.15rem;
        color: var(--text-primary);
    }
</style>

<div class="dashboard-viewport">
    <div class="dashboard-header">
        <div>
            <h2>{{ $tituloSessao }}</h2>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">{{ $periodoLabel }} • Atualizado em tempo real</p>
        </div>
        <select class="filter-select" style="height: 34px; border-radius: 10px;" onchange="window.location.href='?periodo='+this.value">
            <option value="month" {{ $periodo === 'month' ? 'selected' : '' }}>Este Mês</option>
            <option value="week" {{ $periodo === 'week' ? 'selected' : '' }}>Esta Semana</option>
            <option value="year" {{ $periodo === 'year' ? 'selected' : '' }}>Este Ano</option>
        </select>
    </div>

    <!-- Mini KPIs Row -->
    <div class="kpi-row">
        <div class="stat-card mini glass-card">
            <div class="stat-icon success"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-value">R$ {{ number_format($totalRecebido, 0, ',', '.') }}</div>
            <div class="stat-label">Faturamento</div>
        </div>
        <div class="stat-card mini glass-card">
            <div class="stat-icon primary"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value">{{ $vendasAtivas }}</div>
            <div class="stat-label">Vendas</div>
        </div>
        <div class="stat-card mini glass-card">
            <div class="stat-icon info"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ $clientesAtivos }}</div>
            <div class="stat-label">Clientes</div>
        </div>
        <div class="stat-card mini glass-card">
            <div class="stat-icon warning"><i class="fas fa-bullhorn"></i></div>
            <div class="stat-value">{{ $totalLeads }}</div>
            <div class="stat-label">Leads</div>
        </div>
        <div class="stat-card mini glass-card" style="background: rgba(145, 85, 253, 0.05); border: 1px solid rgba(145, 85, 253, 0.2);">
            <div class="stat-icon" style="background: #9155FD; color: white;"><i class="fas fa-rocket"></i></div>
            <div class="stat-value">{{ $leadsHoje }}</div>
            <div class="stat-label">Captação Hoje</div>
        </div>
        <div class="stat-card mini glass-card">
            <div class="stat-icon success"><i class="fas fa-chart-line"></i></div>
            <div class="stat-value">{{ number_format($conversaoLeads, 1) }}%</div>
            <div class="stat-label">Conversão</div>
        </div>
    </div>

    <!-- Main Analysis Area -->
    <div class="main-area">
        <div class="chart-box glass-card">
            <div class="card-header d-flex justify-between" style="padding: 10px 15px; border-bottom: 1px solid var(--border-light);">
                <div style="font-weight: 800; font-size: 0.9rem;"><i class="fas fa-chart-area text-primary me-2"></i>Desempenho Comercial</div>
                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">{{ $periodoLabel }}</div>
            </div>
            <div style="flex: 1; padding: 15px; position: relative;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="insights-column">
            <div class="insight-item glass-card">
                <div class="insight-icon" style="background: #e0f2fe; color: #0284c7;"><i class="fas fa-tag"></i></div>
                <div class="insight-info">
                    <div class="label">Ticket Médio</div>
                    <div class="value">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="insight-item glass-card">
                <div class="insight-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-sync"></i></div>
                <div class="insight-info">
                    <div class="label">Renovações</div>
                    <div class="value">{{ $renovacoesMes }} <small style="font-size: 0.6rem; opacity: 0.6;">UND</small></div>
                </div>
            </div>
            <div class="insight-item glass-card">
                <div class="insight-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-user-minus"></i></div>
                <div class="insight-info">
                    <div class="label">Churn Rate</div>
                    <div class="value">{{ number_format($churnMes, 1) }}%</div>
                </div>
            </div>
            <a href="{{ route('master.ia') }}" class="insight-item glass-card" style="background: rgba(145, 85, 253, 0.04); border-color: var(--primary);">
                <div class="insight-icon" style="background: #9155FD; color: white;"><i class="fas fa-brain"></i></div>
                <div class="insight-info">
                    <div class="label">IA Insights</div>
                    <div class="value" style="color: #9155FD;">Ver Operação <i class="fas fa-arrow-right ms-1" style="font-size: 0.7rem;"></i></div>
                </div>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('mainChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(145, 85, 253, 0.2)');
        gradient.addColorStop(1, 'rgba(145, 85, 253, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($graficoData['labels']) !!},
                datasets: [{
                    label: 'Vendas (R$)',
                    data: {!! json_encode($graficoData['valores']) !!},
                    borderColor: '#9155FD',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#9155FD',
                    pointBorderColor: '#fff',
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
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(226, 232, 240, 0.5)', drawBorder: false },
                        ticks: { 
                            color: '#94a3b8', 
                            font: { size: 10 },
                            callback: function(value) { return 'R$ ' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
