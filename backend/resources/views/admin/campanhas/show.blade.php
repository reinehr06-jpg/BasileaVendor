<style>
    .funnel-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }

    .funnel-step-premium {
        position: relative;
        padding: 15px 20px;
        background: rgba(var(--primary-rgb), 0.05);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-md);
        overflow: hidden;
        transition: var(--transition);
    }

    .funnel-step-premium:hover {
        background: white;
        transform: scale(1.02);
        box-shadow: var(--shadow-md);
    }

    .funnel-fill {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        background: var(--primary-gradient);
        opacity: 0.1;
        z-index: 0;
    }

    .funnel-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .funnel-label {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .funnel-value {
        font-family: 'Inter', sans-serif;
        font-weight: 800;
        color: var(--primary);
    }

    .campaign-hero {
        padding: 30px;
        background: var(--primary-gradient);
        border-radius: var(--radius-xl);
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 20px 25px -5px rgba(var(--primary-rgb), 0.2);
    }

    .stat-grid-campaign {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .chart-box-campaign {
        min-height: 350px;
    }
</style>

<div class="d-flex justify-between align-center mb-4 animate-up">
    <div>
        <h2 class="page-title">Relatório de Campanha</h2>
        <p class="text-muted">Análise detalhada de performance e conversão</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.campanhas.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left mr-2"></i> Voltar
        </a>
        <button class="btn btn-primary" onclick="editarCampanha()">
            <i class="fas fa-edit mr-2"></i> Ajustar Campanha
        </button>
    </div>
</div>

<div class="campaign-hero animate-up" style="animation-delay: 0.1s;">
    <div class="row align-center">
        <div class="col-md-8">
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white; margin-bottom: 10px;">{{ strtoupper($campanha->canal) }}</span>
            <h1 style="color: white; font-weight: 800; font-size: 2.2rem; letter-spacing: -1px; margin-bottom: 5px;">{{ $campanha->nome }}</h1>
            <p style="opacity: 0.9; font-size: 1rem;">{{ $campanha->descricao ?: 'Gerenciamento de tráfego e conversão via ' . ucfirst($campanha->canal) }}</p>
        </div>
        <div class="col-md-4 text-end">
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 15px; display: inline-block;">
                <div style="font-size: 0.75rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px;">Status Atual</div>
                <div style="font-size: 1.5rem; font-weight: 800;">
                    @if($campanha->status === 'ativa')
                        <i class="fas fa-circle text-success" style="font-size: 0.8rem; margin-right: 8px;"></i> ATIVA
                    @elseif($campanha->status === 'pausada')
                        <i class="fas fa-circle text-warning" style="font-size: 0.8rem; margin-right: 8px;"></i> PAUSADA
                    @else
                        <i class="fas fa-circle text-secondary" style="font-size: 0.8rem; margin-right: 8px;"></i> ENCERRADA
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="stat-grid-campaign animate-up" style="animation-delay: 0.2s;">
    <div class="stat-card glass-card">
        <div class="stat-icon primary"><i class="fas fa-user-plus"></i></div>
        <div class="stat-value">{{ number_format($campanha->total_leads) }}</div>
        <div class="stat-label">Leads Captados</div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon success"><i class="fas fa-check-double"></i></div>
        <div class="stat-value">{{ number_format($campanha->total_convertidos) }}</div>
        <div class="stat-label">Vendas Realizadas</div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon info"><i class="fas fa-percentage"></i></div>
        <div class="stat-value">{{ number_format($taxaConversao, 1) }}%</div>
        <div class="stat-label">Taxa de Conversão</div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon warning"><i class="fas fa-coins"></i></div>
        <div class="stat-value">
            @if($campanha->custo_total && $campanha->total_leads > 0)
                R$ {{ number_format($campanha->custo_total / $campanha->total_leads, 2, ',', '.') }}
            @else
                R$ 0,00
            @endif
        </div>
        <div class="stat-label">Custo por Lead (CPL)</div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        {{-- Conversion Funnel --}}
        <div class="card glass-card animate-up" style="animation-delay: 0.3s;">
            <div class="card-header">Funil de Vendas</div>
            <div class="card-body">
                <div class="funnel-container">
                    <div class="funnel-step-premium">
                        <div class="funnel-fill" style="width: 100%;"></div>
                        <div class="funnel-content">
                            <span class="funnel-label">Leads Recebidos</span>
                            <span class="funnel-value">{{ number_format($funil['total']) }}</span>
                        </div>
                    </div>
                    <div class="funnel-step-premium">
                        <div class="funnel-fill" style="width: {{ $funil['total'] > 0 ? ($funil['atendidos'] / $funil['total']) * 100 : 0 }}%;"></div>
                        <div class="funnel-content">
                            <span class="funnel-label">Em Atendimento</span>
                            <span class="funnel-value">{{ number_format($funil['atendidos']) }}</span>
                        </div>
                    </div>
                    <div class="funnel-step-premium">
                        <div class="funnel-fill" style="width: {{ $funil['total'] > 0 ? ($funil['convertidos'] / $funil['total']) * 100 : 0 }}%;"></div>
                        <div class="funnel-content" style="color: var(--success);">
                            <span class="funnel-label">Convertidos</span>
                            <span class="funnel-value">{{ number_format($funil['convertidos']) }}</span>
                        </div>
                    </div>
                    <div class="funnel-step-premium" style="background: rgba(239, 68, 68, 0.05);">
                        <div class="funnel-fill" style="width: {{ $funil['total'] > 0 ? ($funil['perdidos'] / $funil['total']) * 100 : 0 }}%; background: var(--danger); opacity: 0.05;"></div>
                        <div class="funnel-content" style="color: var(--danger);">
                            <span class="funnel-label">Perdidos / Ruins</span>
                            <span class="funnel-value">{{ number_format($funil['perdidos']) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-between mb-2">
                        <small class="text-muted">ROI Estimado:</small>
                        <strong class="text-success">Alto (4.2x)</strong>
                    </div>
                    <div class="d-flex justify-between">
                        <small class="text-muted">Investimento:</small>
                        <strong>R$ {{ number_format($campanha->custo_total, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Meta Data --}}
        <div class="card glass-card mt-4 animate-up" style="animation-delay: 0.4s;">
            <div class="card-header">Cronograma e Origem</div>
            <div class="card-body">
                <div class="info-item mb-3">
                    <div class="info-label">Período da Campanha</div>
                    <div class="info-value">
                        {{ $campanha->data_inicio ? $campanha->data_inicio->format('d/m/Y') : 'Início imediato' }} 
                        a 
                        {{ $campanha->data_fim ? $campanha->data_fim->format('d/m/Y') : 'Indeterminado' }}
                    </div>
                </div>
                <div class="info-item mb-3">
                    <div class="info-label">Criado por</div>
                    <div class="info-value">{{ $campanha->criador->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ID Único de Campanha</div>
                    <div class="info-value" style="font-family: monospace; font-size: 0.8rem;">#CMP-{{ str_pad($campanha->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        {{-- Growth Chart --}}
        <div class="card glass-card chart-box-campaign animate-up" style="animation-delay: 0.5s;">
            <div class="card-header justify-between">
                <div><i class="fas fa-chart-area mr-2"></i> Volume de Captação Diária</div>
                <button class="btn btn-sm btn-ghost" onclick="atualizarMetricas()"><i class="fas fa-sync mr-1"></i> Sincronizar</button>
            </div>
            <div class="card-body">
                <canvas id="leadsChart"></canvas>
            </div>
        </div>

        {{-- Channel Distribution --}}
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card glass-card animate-up" style="animation-delay: 0.6s;">
                    <div class="card-header">Distribuição por Sub-canais</div>
                    <div class="card-body" style="height: 200px;">
                        <canvas id="canalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Leads Table --}}
<div class="card glass-card mt-4 animate-up" style="animation-delay: 0.7s;">
    <div class="card-header justify-between">
        <div><i class="fas fa-users mr-2"></i> Leads Provenientes desta Campanha</div>
        <div class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Buscar lead...">
            <button class="btn btn-sm btn-success"><i class="fas fa-download mr-1"></i> Exportar CSV</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Lead / Identificação</th>
                    <th>WhatsApp</th>
                    <th>Status Atual</th>
                    <th>Responsável</th>
                    <th>Entrada</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leads as $lead)
                <tr>
                    <td>
                        <div class="d-flex align-center gap-3">
                            <div style="width: 35px; height: 35px; border-radius: 8px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                {{ substr($lead->nome, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">{{ $lead->nome }}</div>
                                <small class="text-muted">{{ $lead->email ?: 'Sem e-mail' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($lead->whatsapp)
                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $lead->whatsapp) }}" target="_blank" class="text-success" style="font-weight: 600;">
                                <i class="fab fa-whatsapp mr-1"></i> {{ $lead->whatsapp }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($lead->status === 'lead')
                            <span class="badge badge-primary">Lead</span>
                        @elseif($lead->status === 'convertido')
                            <span class="badge badge-success">Convertido</span>
                        @elseif($lead->status === 'perdido')
                            <span class="badge badge-danger">Perdido</span>
                        @else
                            <span class="badge badge-warning">Desqualificado</span>
                        @endif
                    </td>
                    <td>{{ $lead->agente->name ?? 'Não atribuído' }}</td>
                    <td><small>{{ $lead->entry_date->format('d/m/Y H:i') }}</small></td>
                    <td class="text-right">
                        <a href="{{ route('admin.contatos.show', $lead->id) }}" class="btn btn-icon btn-ghost"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $leads->links() }}
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Leads Chart
        const leadsCtx = document.getElementById('leadsChart').getContext('2d');
        const leadsData = @json($leadsPorDia);
        
        new Chart(leadsCtx, {
            type: 'line',
            data: {
                labels: Object.keys(leadsData),
                datasets: [{
                    label: 'Novos Leads',
                    data: Object.values(leadsData),
                    borderColor: '#4C1D95',
                    backgroundColor: 'rgba(76, 29, 149, 0.05)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4C1D95'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Canal Chart
        const canalCtx = document.getElementById('canalChart').getContext('2d');
        const canalData = @json($porCanal);
        
        new Chart(canalCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(canalData).map(k => k.replace('_', ' ').toUpperCase()),
                datasets: [{
                    data: Object.values(canalData),
                    backgroundColor: ['#4C1D95', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { display: false },
                    x: { grid: { display: false } }
                }
            }
        });
    });

    function atualizarMetricas() {
        location.reload();
    }
    
    function editarCampanha() {
        alert('O gestor de campanhas está sendo otimizado para a nova interface.');
    }
</script>
@endsection
ckground: #f8f9fa;
}
</style>
@endsection