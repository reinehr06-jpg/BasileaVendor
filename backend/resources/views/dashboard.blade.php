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
        padding: 0 10px;
    }

    .dashboard-header h2 {
        font-size: 1.3rem;
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
    }

    .stat-card.mini .stat-icon {
        width: 28px;
        height: 28px;
        font-size: 0.8rem;
        margin-bottom: 6px;
    }

    .stat-card.mini .stat-value {
        font-size: 1.1rem;
        font-weight: 800;
    }

    .stat-card.mini .stat-label {
        font-size: 0.65rem;
        margin-top: 1px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .main-area {
        flex: 1;
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 15px;
        min-height: 0;
    }

    .chart-box {
        display: flex;
        flex-direction: column;
        background: white;
    }

    .chart-box .card-body {
        flex: 1;
        position: relative;
        padding: 10px;
    }

    .insights-box {
        display: flex;
        flex-direction: column;
        gap: 10px;
        overflow-y: auto;
    }

    .insight-mini {
        padding: 12px;
        background: rgba(255,255,255,0.7);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: var(--transition);
    }

    .insight-mini:hover {
        background: white;
        transform: translateX(4px);
        box-shadow: var(--shadow-sm);
    }

    .insight-mini-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
</style>

<div class="dashboard-viewport">
    <div class="dashboard-header">
        <div>
            <h2>{{ $tituloSessao }}</h2>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">{{ $periodoLabel }} • Atualizado em tempo real</p>
        </div>
        <div class="d-flex gap-2">
            <select class="filter-select" style="height: 32px; font-size: 0.8rem;" onchange="window.location.href='?periodo='+this.value">
                <option value="month" {{ $periodo === 'month' ? 'selected' : '' }}>Este Mês</option>
                <option value="week" {{ $periodo === 'week' ? 'selected' : '' }}>Esta Semana</option>
                <option value="year" {{ $periodo === 'year' ? 'selected' : '' }}>Este Ano</option>
            </select>
        </div>
    </div>

    <!-- KPIs Estratégicos -->
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

    <!-- Área de Análise -->
    <div class="main-area">
        <div class="card glass-card chart-box">
            <div class="card-header justify-between" style="padding: 10px 15px;">
                <div style="font-weight: 700; font-size: 0.9rem;"><i class="fas fa-chart-area text-primary me-2"></i>Desempenho Comercial</div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $periodoLabel }}</div>
            </div>
            <div class="card-body">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="insights-box">
            <div class="insight-mini">
                <div class="insight-mini-icon" style="background: #e0f2fe; color: #0284c7;"><i class="fas fa-tag"></i></div>
                <div>
                    <div class="stat-label" style="font-size: 0.65rem;">Ticket Médio</div>
                    <div style="font-weight: 800; font-size: 1rem;">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="insight-mini">
                <div class="insight-mini-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-sync"></i></div>
                <div>
                    <div class="stat-label" style="font-size: 0.65rem;">Renovações</div>
                    <div style="font-weight: 800; font-size: 1rem;">{{ $renovacoesMes }} <small class="text-muted" style="font-weight: 400; font-size: 0.6rem;">unidades</small></div>
                </div>
            </div>
            <div class="insight-mini">
                <div class="insight-mini-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-user-minus"></i></div>
                <div>
                    <div class="stat-label" style="font-size: 0.65rem;">Churn Rate</div>
                    <div style="font-weight: 800; font-size: 1rem;">{{ number_format($churnMes, 1) }}%</div>
                </div>
            </div>
            <a href="{{ route('master.ia') }}" class="insight-mini" style="text-decoration: none; border: 1px solid var(--primary); background: rgba(145, 85, 253, 0.05);">
                <div class="insight-mini-icon" style="background: var(--primary); color: white;"><i class="fas fa-microchip"></i></div>
                <div style="flex: 1;">
                    <div class="stat-label" style="font-size: 0.65rem; color: var(--primary);">IA Lab Insights</div>
                    <div style="font-weight: 800; font-size: 0.85rem; color: var(--primary);">Ver Operação <i class="fas fa-arrow-right ms-1"></i></div>
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
                    pointBackgroundColor: '#9155FD',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4
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
                        backgroundColor: '#fff',
                        titleColor: '#333',
                        bodyColor: '#666',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.parsed.y.toLocaleString('pt-BR');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9', drawBorder: false },
                        ticks: { color: '#64748b', font: { size: 10, weight: '600' }, callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 10, weight: '600' } }
                    }
                }
            }
        });
    });
</script>
@endsection
