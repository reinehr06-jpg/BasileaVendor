@extends('layouts.app')

@section('title', 'Campanha: ' . $campanha->nome)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Header da Campanha --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">{{ $campanha->nome }}</h2>
                            @if($campanha->descricao)
                            <p class="text-muted mb-2">{{ $campanha->descricao }}</p>
                            @endif
                            <div class="d-flex gap-2">
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $campanha->canal)) }}</span>
                                @if($campanha->status === 'ativa')
                                    <span class="badge bg-success">Ativa</span>
                                @elseif($campanha->status === 'pausada')
                                    <span class="badge bg-warning">Pausada</span>
                                @else
                                    <span class="badge bg-secondary">Encerrada</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.campanhas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                            <button type="button" class="btn btn-primary" onclick="editarCampanha()">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPIs da Campanha --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary">{{ number_format($campanha->total_leads) }}</h3>
                            <p class="mb-0">Total de Leads</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ number_format($campanha->total_convertidos) }}</h3>
                            <p class="mb-0">Convertidos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info">{{ number_format($taxaConversao, 1) }}%</h3>
                            <p class="mb-0">Taxa Conversão</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning">
                                @if($campanha->custo_total && $campanha->total_leads > 0)
                                    R$ {{ number_format($campanha->custo_total / $campanha->total_leads, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </h3>
                            <p class="mb-0">Custo por Lead</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Funil de Conversão --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Funil de Conversão</h5>
                        </div>
                        <div class="card-body">
                            <div class="funnel-step mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Leads Recebidos</span>
                                    <strong>{{ number_format($funil['total']) }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                            </div>

                            <div class="funnel-step mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Atendidos</span>
                                    <strong>{{ number_format($funil['atendidos']) }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: {{ $funil['total'] > 0 ? ($funil['atendidos'] / $funil['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div class="funnel-step mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Convertidos</span>
                                    <strong>{{ number_format($funil['convertidos']) }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $funil['total'] > 0 ? ($funil['convertidos'] / $funil['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div class="funnel-step">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Perdidos</span>
                                    <strong>{{ number_format($funil['perdidos']) }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $funil['total'] > 0 ? ($funil['perdidos'] / $funil['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informações da Campanha --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>Informações da Campanha</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Canal:</dt>
                                <dd class="col-sm-7">{{ ucfirst(str_replace('_', ' ', $campanha->canal)) }}</dd>

                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7">
                                    @if($campanha->status === 'ativa')
                                        <span class="badge bg-success">Ativa</span>
                                    @elseif($campanha->status === 'pausada')
                                        <span class="badge bg-warning">Pausada</span>
                                    @else
                                        <span class="badge bg-secondary">Encerrada</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Data Início:</dt>
                                <dd class="col-sm-7">{{ $campanha->data_inicio ? $campanha->data_inicio->format('d/m/Y') : '-' }}</dd>

                                <dt class="col-sm-5">Data Fim:</dt>
                                <dd class="col-sm-7">{{ $campanha->data_fim ? $campanha->data_fim->format('d/m/Y') : '-' }}</dd>

                                <dt class="col-sm-5">Custo Total:</dt>
                                <dd class="col-sm-7">
                                    @if($campanha->custo_total)
                                        R$ {{ number_format($campanha->custo_total, 2, ',', '.') }}
                                        ({{ $campanha->moeda }})
                                    @else
                                        -
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Criado por:</dt>
                                <dd class="col-sm-7">{{ $campanha->criador->name }}</dd>

                                <dt class="col-sm-5">Criado em:</dt>
                                <dd class="col-sm-7">{{ $campanha->created_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Gráfico de Leads por Dia --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Leads por Dia (Últimos 30 dias)</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="atualizarMetricas()">
                                <i class="fas fa-sync"></i> Atualizar
                            </button>
                        </div>
                        <div class="card-body">
                            <canvas id="leadsChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    {{-- Distribuição por Canal --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>Distribuição por Canal de Origem</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="canalChart" width="400" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lista de Leads --}}
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Leads da Campanha ({{ $leads->total() }})</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" onclick="exportarLeads()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Telefone/WhatsApp</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Canal</th>
                                    <th>Data Entrada</th>
                                    <th>Responsável</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                <tr>
                                    <td>
                                        <strong>{{ $lead->nome }}</strong>
                                        @if($lead->documento)
                                        <br><small class="text-muted">{{ $lead->documento }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead->telefone)
                                        <div><i class="fas fa-phone text-muted"></i> {{ $lead->telefone }}</div>
                                        @endif
                                        @if($lead->whatsapp)
                                        <div><i class="fab fa-whatsapp text-success"></i> {{ $lead->whatsapp }}</div>
                                        @endif
                                        @if(!$lead->telefone && !$lead->whatsapp)
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $lead->email ?: '-' }}</td>
                                    <td>
                                        @if($lead->status === 'lead')
                                            <span class="badge bg-primary">Lead</span>
                                        @elseif($lead->status === 'convertido')
                                            <span class="badge bg-success">Convertido</span>
                                        @elseif($lead->status === 'perdido')
                                            <span class="badge bg-danger">Perdido</span>
                                        @else
                                            <span class="badge bg-warning">Lead Ruim</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $lead->canal_origem ?: 'indefinido')) }}</span>
                                    </td>
                                    <td>{{ $lead->entry_date->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($lead->agente)
                                            <i class="fas fa-user text-primary"></i> {{ $lead->agente->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="verLead({{ $lead->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editarLead({{ $lead->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h5>Nenhum lead encontrado</h5>
                                        <p class="text-muted">Esta campanha ainda não recebeu leads.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginação --}}
                    <div class="d-flex justify-content-center mt-3">
                        {{ $leads->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts para Charts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Leads por Dia
const leadsData = @json($leadsPorDia);
const leadsCtx = document.getElementById('leadsChart').getContext('2d');
new Chart(leadsCtx, {
    type: 'line',
    data: {
        labels: Object.keys(leadsData),
        datasets: [{
            label: 'Leads por Dia',
            data: Object.values(leadsData),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Gráfico de Distribuição por Canal
const canalData = @json($porCanal);
const canalCtx = document.getElementById('canalChart').getContext('2d');
new Chart(canalCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(canalData).map(k => k.replace('_', ' ').toUpperCase()),
        datasets: [{
            data: Object.values(canalData),
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function atualizarMetricas() {
    fetch('{{ route("admin.campanhas.metricas", $campanha) }}')
        .then(response => response.json())
        .then(data => {
            console.log('Métricas atualizadas:', data);
            // TODO: Atualizar a interface com os novos dados
        })
        .catch(error => console.error('Erro ao atualizar métricas:', error));
}

function verLead(id) {
    window.location.href = '{{ route("admin.contatos.show", ":id") }}'.replace(':id', id);
}

function editarLead(id) {
    // TODO: Implementar edição inline ou modal
    alert('Funcionalidade de edição será implementada em breve!');
}

function editarCampanha() {
    // TODO: Implementar edição da campanha
    alert('Funcionalidade de edição será implementada em breve!');
}

function exportarLeads() {
    // TODO: Implementar exportação
    alert('Funcionalidade de exportação será implementada em breve!');
}
</script>

<style>
.funnel-step {
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
}
</style>
@endsection