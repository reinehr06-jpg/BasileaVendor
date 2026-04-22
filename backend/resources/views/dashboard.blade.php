<style>
    .dashboard-viewport {
        height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow: hidden;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 10px;
    }

    .dashboard-header h2 {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin: 0;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .kpi-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
    }

    .stat-card.mini {
        padding: 16px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-card.mini .stat-icon {
        width: 32px;
        height: 32px;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .stat-card.mini .stat-value {
        font-size: 1.25rem;
    }

    .stat-card.mini .stat-label {
        font-size: 0.7rem;
        margin-top: 2px;
    }

    .main-area {
        flex: 1;
        display: grid;
        grid-template-columns: 2.5fr 1fr;
        gap: 20px;
        min-height: 0; /* Important for flex-child overflow */
    }

    .chart-box {
        display: flex;
        flex-direction: column;
    }

    .chart-box .card-body {
        flex: 1;
        position: relative;
    }

    .insights-box {
        display: flex;
        flex-direction: column;
        gap: 15px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .insight-mini {
        padding: 15px;
        background: rgba(255,255,255,0.5);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: var(--transition);
    }

    .insight-mini:hover {
        background: white;
        transform: translateX(5px);
    }

    .insight-mini-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .insight-mini-info h4 {
        margin: 0;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .insight-mini-info .value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Custom Scrollbar for insights */
    .insights-box::-webkit-scrollbar { width: 4px; }
    .insights-box::-webkit-scrollbar-track { background: transparent; }
    .insights-box::-webkit-scrollbar-thumb { background: rgba(var(--primary-rgb), 0.2); border-radius: 10px; }

    @media (max-width: 1400px) {
        .kpi-row { grid-template-columns: repeat(3, 1fr); }
        .dashboard-viewport { overflow-y: auto; height: auto; }
        .main-area { grid-template-columns: 1fr; }
    }
</style>

<div class="dashboard-viewport">
    <div class="dashboard-header">
        <div>
            <h2>Comando Central Basiléia</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">{{ $tituloSessao }} — <span class="text-primary" style="font-weight: 600;">{{ $periodoLabel }}</span></p>
        </div>
        <div class="d-flex gap-2">
            <div class="period-selector" style="background: rgba(var(--primary-rgb), 0.05); padding: 4px; border-radius: 10px;">
                <a href="?periodo=week" class="btn btn-sm {{ $periodo === 'week' ? 'btn-primary' : 'btn-ghost' }}" style="padding: 6px 15px; font-size: 0.75rem;">Semana</a>
                <a href="?periodo=month" class="btn btn-sm {{ $periodo === 'month' ? 'btn-primary' : 'btn-ghost' }}" style="padding: 6px 15px; font-size: 0.75rem;">Mês</a>
                <a href="?periodo=year" class="btn btn-sm {{ $periodo === 'year' ? 'btn-primary' : 'btn-ghost' }}" style="padding: 6px 15px; font-size: 0.75rem;">Ano</a>
            </div>
        </div>
    </div>

    {{-- KPIs Row --}}
    <div class="kpi-row animate-up">
        <div class="stat-card glass-card mini">
            <div class="stat-icon primary"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-value">R$ {{ number_format($totalRecebido, 0, ',', '.') }}</div>
            <div class="stat-label">Receita Bruta</div>
        </div>
        <div class="stat-card glass-card mini">
            <div class="stat-icon success"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value">{{ $vendasAtivas }}</div>
            <div class="stat-label">Vendas Ativas</div>
        </div>
        <div class="stat-card glass-card mini">
            <div class="stat-icon info"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ $clientesAtivos }}</div>
            <div class="stat-label">Base Clientes</div>
        </div>
        {{-- Marketing Cards --}}
        <div class="stat-card glass-card mini">
            <div class="stat-icon primary" style="background: rgba(76, 29, 149, 0.1); color: #4C1D95;"><i class="fas fa-bullhorn"></i></div>
            <div class="stat-value">{{ $campanhasAtivas }}</div>
            <div class="stat-label">Campanhas On</div>
        </div>
        <div class="stat-card glass-card mini">
            <div class="stat-icon info" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="fas fa-address-book"></i></div>
            <div class="stat-value">{{ $totalLeads }}</div>
            <div class="stat-label">Total Leads</div>
        </div>
        <div class="stat-card glass-card mini">
            <div class="stat-icon warning"><i class="fas fa-hand-holding-dollar"></i></div>
            <div class="stat-value" style="font-size: 1.1rem;">R$ {{ number_format($comissoesPendentes, 0, ',', '.') }}</div>
            <div class="stat-label">Pendente</div>
        </div>
    </div>

    <div class="main-area">
        {{-- Graph Area --}}
        <div class="card glass-card chart-box animate-up" style="animation-delay: 0.2s;">
            <div class="card-header justify-between">
                <div><i class="fas fa-chart-line mr-2"></i> Fluxo de Performance</div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">Valores em Reais (R$)</div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        {{-- Insights Area --}}
        <div class="insights-box animate-up" style="animation-delay: 0.3s;">
            <div class="insight-mini">
                <div class="insight-mini-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="insight-mini-info">
                    <h4>Pico de Conversão</h4>
                    <div class="value">{{ $melhorFaixa }}</div>
                </div>
            </div>

            <div class="insight-mini">
                <div class="insight-mini-icon" style="color: var(--success);"><i class="fas fa-money-check-dollar"></i></div>
                <div class="insight-mini-info">
                    <h4>Ticket Médio</h4>
                    <div class="value">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
                </div>
            </div>

            @if(!$isPersonal && $vendedoresAtivos > 0)
            <div class="insight-mini">
                <div class="insight-mini-icon"><i class="fas fa-user-group"></i></div>
                <div class="insight-mini-info">
                    <h4>Força de Vendas</h4>
                    <div class="value">{{ $vendedoresAtivos }} Agentes</div>
                </div>
            </div>
            @endif

            <div class="insight-mini" style="border-right: 4px solid var(--danger);">
                <div class="insight-mini-icon" style="color: var(--danger);"><i class="fas fa-user-minus"></i></div>
                <div class="insight-mini-info">
                    <h4>Taxa de Churn</h4>
                    <div class="value" style="color: var(--danger);">{{ $churnMes }} Perdas</div>
                </div>
            </div>

            <div class="insight-mini">
                <div class="insight-mini-icon" style="color: #25D366;"><i class="fab fa-whatsapp"></i></div>
                <div class="insight-mini-info">
                    <h4>Renovações</h4>
                    <div class="value">{{ $renovacoesMes }} Efetuadas</div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        const labels = {!! json_encode(collect($graficoData)->map(fn($s) => $s['label'])) !!};
        const data = {!! json_encode(collect($graficoData)->map(fn($s) => $s['total'])) !!};

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(76, 29, 149, 0.2)');
        gradient.addColorStop(1, 'rgba(76, 29, 149, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'],
                datasets: [{
                    label: 'Receita',
                    data: data.length ? data : [0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#4C1D95',
                    borderWidth: 4,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4C1D95',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
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
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#4C1D95',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        ticks: { 
                            color: '#64748b', 
                            font: { size: 10, weight: '600' },
                            callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); }
                        }
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
@endsection
