<style>
    .lead-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .lead-profile-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 25px;
    }

    .lead-avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.3);
    }

    .lead-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .info-item {
        padding: 15px;
        background: rgba(255,255,255,0.5);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-md);
    }

    .info-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .timeline-premium {
        position: relative;
        padding-left: 30px;
    }

    .timeline-premium::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border-light);
    }

    .timeline-premium-item {
        position: relative;
        padding-bottom: 25px;
    }

    .timeline-premium-item::before {
        content: '';
        position: absolute;
        left: -34px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid white;
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
    }

    .timeline-premium-card {
        background: white;
        padding: 15px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        flex: 1;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        font-size: 0.75rem;
        transition: var(--transition);
    }

    .btn-action i { font-size: 1.1rem; }
</style>

<div class="lead-header animate-up">
    <div>
        <h2 class="page-title">Ficha do Lead</h2>
        <p class="text-muted" style="margin: 0;">Gerencie os detalhes e o histórico de <strong>{{ $contato->nome }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.contatos.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left mr-2"></i> Voltar
        </a>
        <button class="btn btn-primary" onclick="editarContato()">
            <i class="fas fa-edit mr-2"></i> Editar Dados
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Profile Card --}}
        <div class="card glass-card lead-profile-card animate-up" style="animation-delay: 0.1s;">
            <div class="lead-avatar">
                {{ substr($contato->nome, 0, 1) }}
            </div>
            <div style="flex: 1;">
                <div class="d-flex align-center gap-3 mb-1">
                    <h2 style="margin: 0; font-weight: 800;">{{ $contato->nome }}</h2>
                    @if($contato->status === 'lead')
                        <span class="badge badge-primary">Lead Ativo</span>
                    @elseif($contato->status === 'convertido')
                        <span class="badge badge-success">Cliente Convertido</span>
                    @elseif($contato->status === 'perdido')
                        <span class="badge badge-danger">Lead Perdido</span>
                    @else
                        <span class="badge badge-warning">Lead Ruim</span>
                    @endif
                </div>
                <p class="text-muted"><i class="fas fa-envelope mr-2"></i> {{ $contato->email ?: 'E-mail não cadastrado' }}</p>
                <div class="d-flex gap-2 mt-3">
                    @if($contato->canal_origem)
                        <span class="badge btn-ghost" style="font-size: 0.7rem;"><i class="fas fa-bullhorn mr-1"></i> {{ ucfirst(str_replace('_', ' ', $contato->canal_origem)) }}</span>
                    @endif
                    @if($contato->campanha)
                        <span class="badge btn-ghost" style="font-size: 0.7rem;"><i class="fas fa-flag mr-1"></i> {{ $contato->campanha->nome }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="card glass-card mt-4 animate-up" style="animation-delay: 0.2s;">
            <div class="card-header"><i class="fas fa-info-circle mr-2"></i> Informações Detalhadas</div>
            <div class="card-body">
                <div class="lead-info-grid">
                    <div class="info-item">
                        <div class="info-label">Telefone</div>
                        <div class="info-value">{{ $contato->telefone ?: '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">WhatsApp</div>
                        <div class="info-value">{{ $contato->whatsapp ?: '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Documento</div>
                        <div class="info-value">{{ $contato->documento ?: '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Agente Responsável</div>
                        <div class="info-value">{{ $contato->agente->name ?? 'Não atribuído' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data de Entrada</div>
                        <div class="info-value">{{ $contato->entry_date->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Igreja / Organização</div>
                        <div class="info-value">{{ $contato->nome_igreja ?: '-' }}</div>
                    </div>
                </div>

                @if($contato->observacoes)
                <div class="mt-4 p-3 bg-light rounded-lg">
                    <div class="info-label mb-2">Observações Internas</div>
                    <p class="text-muted" style="font-size: 0.9rem; line-height: 1.6;">{{ $contato->observacoes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card glass-card mt-4 animate-up" style="animation-delay: 0.3s;">
            <div class="card-header justify-between">
                <div><i class="fas fa-history mr-2"></i> Histórico de Interações</div>
                <button class="btn btn-sm btn-ghost" onclick="mudarStatus()">Nova Atualização</button>
            </div>
            <div class="card-body">
                @if($contato->statusLogs->count() > 0)
                    <div class="timeline-premium">
                        @foreach($contato->statusLogs->orderBy('created_at', 'desc')->get() as $log)
                        <div class="timeline-premium-item">
                            <div class="timeline-premium-card">
                                <div class="d-flex justify-between align-center mb-2">
                                    <span class="badge {{ $log->status_novo === 'convertido' ? 'badge-success' : ($log->status_novo === 'perdido' ? 'badge-danger' : 'badge-primary') }}" style="font-size: 0.65rem;">
                                        {{ strtoupper($log->status_novo) }}
                                    </span>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                                <p style="font-size: 0.85rem; margin-bottom: 5px;">{{ $log->motivo ?: 'Alteração de status realizada pelo sistema.' }}</p>
                                <div class="d-flex align-center gap-1 mt-2">
                                    <i class="fas fa-user-circle text-muted" style="font-size: 0.7rem;"></i>
                                    <small class="text-muted">Por: <strong>{{ $log->usuario->name }}</strong></small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-ghost fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum histórico registrado para este lead.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Quick Actions --}}
        <div class="card glass-card animate-up" style="animation-delay: 0.4s;">
            <div class="card-header">Ações Estratégicas</div>
            <div class="card-body">
                <div class="action-buttons">
                    <button class="btn-action btn-ghost" onclick="enviarMensagem()" style="color: #25D366; background: rgba(37, 211, 102, 0.05);">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </button>
                    <button class="btn-action btn-ghost" onclick="agendarContato()" style="color: var(--primary); background: rgba(var(--primary-rgb), 0.05);">
                        <i class="fas fa-calendar-alt"></i>
                        Follow-up
                    </button>
                    <button class="btn-action btn-ghost" onclick="mudarStatus()" style="color: var(--warning); background: rgba(245, 158, 11, 0.05);">
                        <i class="fas fa-sync"></i>
                        Status
                    </button>
                </div>
                <div class="d-grid mt-3">
                    <button class="btn btn-primary w-full" onclick="agendarContato()">
                        <i class="fas fa-plus mr-2"></i> Criar Nova Tarefa
                    </button>
                </div>
            </div>
        </div>

        {{-- Location / Church --}}
        @if($contato->nome_igreja || $contato->cidade)
        <div class="card glass-card mt-4 animate-up" style="animation-delay: 0.5s;">
            <div class="card-header">Localização e Organização</div>
            <div class="card-body">
                <div class="d-flex align-center gap-3 mb-4">
                    <div style="width: 45px; height: 45px; border-radius: 12px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                        <i class="fas fa-church"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem;">{{ $contato->nome_igreja ?: 'Instituição não inf.' }}</h4>
                        <small class="text-muted">{{ $contato->nome_pastor ?: 'Pastor não informado' }}</small>
                    </div>
                </div>
                <div class="info-item mb-3">
                    <div class="info-label">Endereço</div>
                    <div class="info-value" style="font-size: 0.85rem;">
                        {{ $contato->endereco ?: 'Endereço não cadastrado' }}<br>
                        {{ $contato->cidade }} - {{ $contato->estado }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tamanho da Congregação</div>
                    <div class="info-value">{{ $contato->quantidade_membros ?: '-' }} membros</div>
                </div>
            </div>
        </div>
        @endif

        {{-- UTM Tracking --}}
        @if($contato->utm_source)
        <div class="card glass-card mt-4 animate-up" style="animation-delay: 0.6s;">
            <div class="card-header">Rastreamento UTM</div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-between">
                        <small class="text-muted">Origem:</small>
                        <span class="badge btn-ghost" style="font-size: 0.65rem;">{{ $contato->utm_source }}</span>
                    </div>
                    <div class="d-flex justify-between">
                        <small class="text-muted">Mídia:</small>
                        <span class="badge btn-ghost" style="font-size: 0.65rem;">{{ $contato->utm_medium }}</span>
                    </div>
                    <div class="d-flex justify-between">
                        <small class="text-muted">Campanha:</small>
                        <span class="badge btn-ghost" style="font-size: 0.65rem;">{{ $contato->utm_campaign }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal Status --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card" style="background: white;">
            <div class="modal-header">
                <h5 class="modal-title">Atualizar Status do Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.contatos.status', $contato) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label font-bold">Qual o novo status?</label>
                        <select name="status" class="form-select" id="statusSelect" required>
                            <option value="lead" {{ $contato->status === 'lead' ? 'selected' : '' }}>Lead Ativo</option>
                            <option value="convertido" {{ $contato->status === 'convertido' ? 'selected' : '' }}>Convertido / Venda</option>
                            <option value="perdido" {{ $contato->status === 'perdido' ? 'selected' : '' }}>Perdido / Desistência</option>
                            <option value="lead_ruim" {{ $contato->status === 'lead_ruim' ? 'selected' : '' }}>Lead Ruim / Desqualificado</option>
                        </select>
                    </div>
                    <div class="mb-0" id="motivoDiv">
                        <label class="form-label font-bold">Observações / Motivo</label>
                        <textarea name="motivo" class="form-control" rows="4" placeholder="Descreva brevemente o motivo da alteração ou o que foi conversado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Confirmar Alteração</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarContato() {
    alert('Função de edição avançada está sendo preparada!');
}

function mudarStatus() {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function agendarContato() {
    alert('O módulo de agendamento está sendo atualizado para a versão premium.');
}

function enviarMensagem() {
    @if($contato->whatsapp)
        const numero = '{{ $contato->whatsapp }}'.replace(/\D/g, '');
        window.open(`https://wa.me/55${numero}`, '_blank');
    @else
        alert('Este lead não possui um número de WhatsApp válido cadastrado.');
    @endif
}
</script>
#6c757d;
}
</style>
@endsection