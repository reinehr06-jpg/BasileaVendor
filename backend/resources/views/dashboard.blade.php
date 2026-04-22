@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<style>
    .dashboard-viewport {
        height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow-y: auto;
        padding-bottom: 20px;
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
        letter-spacing: -0.5px;
        margin: 0;
        color: var(--text-primary);
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
        transition: transform 0.2s;
    }
    .stat-card.mini:hover { transform: translateY(-3px); }

    .stat-card.mini .stat-icon {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
        margin-bottom: 8px;
    }

    .stat-card.mini .stat-value {
        font-size: 1.15rem;
        font-weight: 800;
    }

    .stat-card.mini .stat-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.7;
    }

    .chart-container-box {
        background: white;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        padding: 15px;
        flex: 1;
        min-height: 300px;
        display: flex;
        flex-direction: column;
    }

    .secondary-metrics-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .insight-card {
        padding: 15px;
        background: white;
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: var(--transition);
        text-decoration: none !important;
    }

    .insight-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .insight-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .insight-card-info .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .insight-card-info .value {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--text-primary);
    }
</style>

<div class="dashboard-viewport">
    <div class="dashboard-header">
        <div>
            <h2>{{ $tituloSessao }}</h2>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">{{ $periodoLabel }} • Atualizado em tempo real</p>
        </div>
        <div class="d-flex gap-2">
            <select class="filter-select" style="height: 36px; border-radius: 10px;" onchange="window.location.href='?periodo='+this.value">
                <option value="month" {{ $periodo === 'month' ? 'selected' : '' }}>Este Mês</option>
                <option value="week" {{ $periodo === 'week' ? 'selected' : '' }}>Esta Semana</option>
                <option value="year" {{ $periodo === 'year' ? 'selected' : '' }}>Este Ano</option>
            </select>
        </div>
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

    <!-- Main Chart Box -->
    <div class="chart-container-box glass-card">
        <div class="d-flex justify-between align-center mb-3">
            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-primary);">
                <i class="fas fa-chart-area text-primary me-2"></i>Desempenho Comercial
            </div>
            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ $periodoLabel }}</div>
        </div>
        <div style="flex: 1; position: relative; min-height: 250px;">
            <canvas id="mainChart"></canvas>
        </div>
    </div>

    <!-- Secondary Metrics Row -->
    <div class="secondary-metrics-row">
        <div class="insight-card glass-card">
            <div class="insight-card-icon" style="background: #e0f2fe; color: #0284c7;"><i class="fas fa-tag"></i></div>
            <div class="insight-card-info">
                <div class="label">Ticket Médio</div>
                <div class="value">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="insight-card glass-card">
            <div class="insight-card-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-sync"></i></div>
            <div class="insight-card-info">
                <div class="label">Renovações</div>
                <div class="value">{{ $renovacoesMes }} <small style="font-size: 0.6rem; opacity: 0.6;">UND</small></div>
            </div>
        </div>
        <div class="insight-card glass-card">
            <div class="insight-card-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-user-minus"></i></div>
            <div class="insight-card-info">
                <div class="label">Churn Rate</div>
                <div class="value">{{ number_format($churnMes, 1) }}%</div>
            </div>
        </div>
        <a href="{{ route('master.ia') }}" class="insight-card glass-card" style="background: rgba(145, 85, 253, 0.04); border-color: rgba(145, 85, 253, 0.3);">
            <div class="insight-card-icon" style="background: #9155FD; color: white;"><i class="fas fa-brain"></i></div>
            <div class="insight-card-info">
                <div class="label">IA Insights</div>
                <div class="value" style="color: #9155FD;">Ver Operação <i class="fas fa-arrow-right ms-1" style="font-size: 0.7rem;"></i></div>
            </div>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('mainChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(145, 85, 253, 0.15)');
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
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.5)', drawBorder: false },
                        ticks: { 
                            color: '#94a3b8', 
                            font: { size: 11 },
                            callback: function(value) { return 'R$ ' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
