@extends('layouts.app')

@section('title', 'Campanhas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Campanhas de Marketing</h4>
                </div>
                <div class="card-body">

                    {{-- KPIs Globais --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($kpis['total_leads']) }}</h5>
                                    <p class="mb-0">Total de Leads</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($kpis['total_convertidos']) }}</h5>
                                    <p class="mb-0">Convertidos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($kpis['taxa_geral'], 1) }}%</h5>
                                    <p class="mb-0">Taxa Conversão Geral</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>{{ $kpis['campanhas_ativas'] }}</h5>
                                    <p class="mb-0">Campanhas Ativas</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros --}}
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" class="d-flex gap-2">
                                <select name="status" class="form-select" style="width: auto;">
                                    <option value="">Todos os Status</option>
                                    <option value="ativa" {{ request('status') === 'ativa' ? 'selected' : '' }}>Ativa</option>
                                    <option value="pausada" {{ request('status') === 'pausada' ? 'selected' : '' }}>Pausada</option>
                                    <option value="encerrada" {{ request('status') === 'encerrada' ? 'selected' : '' }}>Encerrada</option>
                                </select>
                                <select name="canal" class="form-select" style="width: auto;">
                                    <option value="">Todos os Canais</option>
                                    <option value="meta_ads" {{ request('canal') === 'meta_ads' ? 'selected' : '' }}>Meta Ads</option>
                                    <option value="google_ads" {{ request('canal') === 'google_ads' ? 'selected' : '' }}>Google Ads</option>
                                    <option value="whatsapp_link" {{ request('canal') === 'whatsapp_link' ? 'selected' : '' }}>WhatsApp Link</option>
                                    <option value="instagram" {{ request('canal') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                    <option value="tiktok_ads" {{ request('canal') === 'tiktok_ads' ? 'selected' : '' }}>TikTok Ads</option>
                                    <option value="formulario_web" {{ request('canal') === 'formulario_web' ? 'selected' : '' }}>Formulário Web</option>
                                    <option value="landing_page" {{ request('canal') === 'landing_page' ? 'selected' : '' }}>Landing Page</option>
                                    <option value="organico" {{ request('canal') === 'organico' ? 'selected' : '' }}>Orgânico</option>
                                    <option value="importacao" {{ request('canal') === 'importacao' ? 'selected' : '' }}>Importação</option>
                                    <option value="outro" {{ request('canal') === 'outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="{{ route('admin.campanhas.index') }}" class="btn btn-secondary">Limpar</a>
                            </form>
                        </div>
                    </div>

                    {{-- Tabela de Campanhas --}}
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Canal</th>
                                    <th>Status</th>
                                    <th>Leads</th>
                                    <th>Convertidos</th>
                                    <th>Taxa Conv.</th>
                                    <th>CPL</th>
                                    <th>Último Lead</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campanhas as $campanha)
                                <tr>
                                    <td>
                                        <strong>{{ $campanha->nome }}</strong>
                                        @if($campanha->descricao)
                                        <br><small class="text-muted">{{ Str::limit($campanha->descricao, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $campanha->canal)) }}</span>
                                    </td>
                                    <td>
                                        @if($campanha->status === 'ativa')
                                            <span class="badge bg-success">Ativa</span>
                                        @elseif($campanha->status === 'pausada')
                                            <span class="badge bg-warning">Pausada</span>
                                        @else
                                            <span class="badge bg-secondary">Encerrada</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($campanha->total_leads) }}</td>
                                    <td>{{ number_format($campanha->total_convertidos) }}</td>
                                    <td>{{ number_format($campanha->taxa_conversao, 1) }}%</td>
                                    <td>
                                        @if($campanha->cpl)
                                            R$ {{ number_format($campanha->cpl, 2, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($campanha->ultimo_lead)
                                            {{ $campanha->ultimo_lead->format('d/m/Y') }}
                                        @else
                                            Nunca
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.campanhas.show', $campanha) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="#" onclick="editarCampanha({{ $campanha->id }})" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                        <h5>Nenhuma campanha encontrada</h5>
                                        <p class="text-muted">Crie sua primeira campanha para começar a capturar leads.</p>
                                        <button type="button" class="btn btn-primary" onclick="novaCampanha()">
                                            <i class="fas fa-plus"></i> Criar Primeira Campanha
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Botão Nova Campanha --}}
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="novaCampanha()">
                            <i class="fas fa-plus"></i> Nova Campanha
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Nova Campanha --}}
<div class="modal fade" id="campanhaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nova Campanha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="campanhaForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nome da Campanha *</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Canal *</label>
                                <select name="canal" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <option value="meta_ads">Meta Ads (Facebook/Instagram)</option>
                                    <option value="google_ads">Google Ads</option>
                                    <option value="whatsapp_link">WhatsApp Link</option>
                                    <option value="instagram">Instagram Orgânico</option>
                                    <option value="tiktok_ads">TikTok Ads</option>
                                    <option value="formulario_web">Formulário Web</option>
                                    <option value="landing_page">Landing Page</option>
                                    <option value="organico">Orgânico</option>
                                    <option value="importacao">Importação</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="ativa">Ativa</option>
                                    <option value="pausada">Pausada</option>
                                    <option value="encerrada">Encerrada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Data Início</label>
                                <input type="date" name="data_inicio" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3" placeholder="Descreva o objetivo da campanha..."></textarea>
                    </div>

                    {{-- Campos UTM --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Parâmetros UTM (Opcional)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">UTM Source</label>
                                        <input type="text" name="utm_source" class="form-control" placeholder="facebook, google">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">UTM Medium</label>
                                        <input type="text" name="utm_medium" class="form-control" placeholder="cpc, social">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">UTM Campaign</label>
                                        <input type="text" name="utm_campaign" class="form-control" placeholder="campanha-verao-2026">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ref Parameter</label>
                                        <input type="text" name="ref_param" class="form-control" placeholder="campanha-verao">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Campanha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function novaCampanha() {
    document.getElementById('modalTitle').textContent = 'Nova Campanha';
    document.getElementById('campanhaForm').action = '{{ route("admin.campanhas.store") }}';
    document.getElementById('campanhaForm').reset();
    new bootstrap.Modal(document.getElementById('campanhaModal')).show();
}

function editarCampanha(id) {
    // TODO: Implementar edição via AJAX
    alert('Funcionalidade de edição será implementada em breve!');
}
</script>
@endsection