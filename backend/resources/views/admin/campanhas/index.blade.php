@extends('layouts.app')

@section('title', 'Campanhas')

@section('content')
<div class="animate-up">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-bullhorn text-primary"></i> Marketing de Resultados</h2>
            <p>Gerencie suas campanhas e monitore a performance de captação em tempo real.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="novaCampanha()">
            <i class="fas fa-plus"></i> Nova Campanha
        </button>
    </div>

    {{-- KPIs Premium --}}
    <div class="stats-bar mb-4">
        <div class="stat-card glass-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ number_format($kpis['total_leads']) }}</div>
            <div class="stat-label">Total de Leads</div>
        </div>
        <div class="stat-card glass-card">
            <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
            <div class="stat-value">{{ number_format($kpis['total_convertidos']) }}</div>
            <div class="stat-label">Convertidos</div>
        </div>
        <div class="stat-card glass-card">
            <div class="stat-icon info"><i class="fas fa-chart-line"></i></div>
            <div class="stat-value">{{ number_format($kpis['taxa_geral'], 1) }}%</div>
            <div class="stat-label">Conversão Geral</div>
        </div>
        <div class="stat-card glass-card">
            <div class="stat-icon warning"><i class="fas fa-rocket"></i></div>
            <div class="stat-value">{{ $kpis['campanhas_ativas'] }}</div>
            <div class="stat-label">Campanhas Ativas</div>
        </div>
    </div>

    <div class="card glass-card">
        <div class="card-header justify-between">
            <div class="d-flex align-center gap-2">
                <i class="fas fa-list-ul"></i> Ativos de Marketing
            </div>
            
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-control" style="width: auto; height: 36px; padding: 0 12px; font-size: 0.8rem;">
                    <option value="">Status</option>
                    <option value="ativa" {{ request('status') === 'ativa' ? 'selected' : '' }}>Ativa</option>
                    <option value="pausada" {{ request('status') === 'pausada' ? 'selected' : '' }}>Pausada</option>
                </select>
                <select name="canal" class="form-control" style="width: auto; height: 36px; padding: 0 12px; font-size: 0.8rem;">
                    <option value="">Canal</option>
                    <option value="meta_ads" {{ request('canal') === 'meta_ads' ? 'selected' : '' }}>Meta Ads</option>
                    <option value="google_ads" {{ request('canal') === 'google_ads' ? 'selected' : '' }}>Google Ads</option>
                    <option value="whatsapp_link" {{ request('canal') === 'whatsapp_link' ? 'selected' : '' }}>WhatsApp</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Filtrar</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: rgba(var(--primary-rgb), 0.03);">
                        <tr>
                            <th style="padding: 16px 24px;">Campanha</th>
                            <th>Canal de Origem</th>
                            <th>Status</th>
                            <th>Performance</th>
                            <th>Conversão</th>
                            <th>Custo/Lead</th>
                            <th class="text-right" style="padding-right: 24px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campanhas as $campanha)
                        <tr>
                            <td style="padding: 16px 24px;">
                                <div style="font-weight: 700; color: var(--text-primary);">{{ $campanha->nome }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #CMP-{{ str_pad($campanha->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-center gap-1">
                                    @php
                                        $icon = match($campanha->canal) {
                                            'meta_ads' => 'fab fa-facebook-square',
                                            'google_ads' => 'fab fa-google',
                                            'whatsapp_link' => 'fab fa-whatsapp',
                                            'instagram' => 'fab fa-instagram',
                                            'tiktok_ads' => 'fab fa-tiktok',
                                            default => 'fas fa-link'
                                        };
                                        $color = match($campanha->canal) {
                                            'meta_ads' => '#1877F2',
                                            'google_ads' => '#EA4335',
                                            'whatsapp_link' => '#25D366',
                                            'instagram' => '#E4405F',
                                            'tiktok_ads' => '#000000',
                                            default => 'var(--text-muted)'
                                        };
                                    @endphp
                                    <i class="{{ $icon }}" style="color: {{ $color }};"></i>
                                    <span style="font-size: 0.85rem; font-weight: 500;">{{ ucfirst(str_replace('_', ' ', $campanha->canal)) }}</span>
                                </div>
                            </td>
                            <td>
                                @if($campanha->status === 'ativa')
                                    <span class="badge" style="background: rgba(22, 163, 74, 0.1); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.2);">Ativa</span>
                                @elseif($campanha->status === 'pausada')
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);">Pausada</span>
                                @else
                                    <span class="badge" style="background: rgba(161, 161, 181, 0.1); color: #a1a1b5; border: 1px solid rgba(161, 161, 181, 0.2);">Encerrada</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 0.9rem; font-weight: 700;">{{ number_format($campanha->total_leads) }} <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;">leads</span></div>
                            </td>
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <div style="flex: 1; height: 6px; background: #eee; border-radius: 3px; max-width: 60px; overflow: hidden;">
                                        <div style="width: {{ $campanha->taxa_conversao }}%; height: 100%; background: var(--primary); border-radius: 3px;"></div>
                                    </div>
                                    <span style="font-size: 0.85rem; font-weight: 600;">{{ number_format($campanha->taxa_conversao, 1) }}%</span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">
                                    @if($campanha->cpl)
                                        <span style="color: var(--text-primary);">R$ {{ number_format($campanha->cpl, 2, ',', '.') }}</span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">N/A</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right" style="padding-right: 24px;">
                                <div class="d-flex justify-end gap-1">
                                    <a href="{{ route('admin.campanhas.show', $campanha) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Visualizar Detalhes">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <button onclick="editarCampanha({{ $campanha->id }})" class="btn btn-icon btn-sm btn-outline-warning" title="Editar Campanha">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-bullhorn fa-3x mb-3 opacity-20"></i>
                                    <p>Nenhuma campanha estratégica ativa no momento.</p>
                                    <button type="button" class="btn btn-outline-primary mt-2" onclick="novaCampanha()">
                                        <i class="fas fa-plus"></i> Iniciar Primeira Campanha
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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