@extends('layouts.app')

@section('title', 'Contato: ' . $contato->nome)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Header do Contato --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="mb-1">{{ $contato->nome }}</h2>
                            @if($contato->email)
                            <p class="mb-2"><i class="fas fa-envelope text-muted"></i> {{ $contato->email }}</p>
                            @endif
                            <div class="d-flex gap-2 flex-wrap">
                                @if($contato->status === 'lead')
                                    <span class="badge bg-primary">Lead</span>
                                @elseif($contato->status === 'convertido')
                                    <span class="badge bg-success">Convertido</span>
                                @elseif($contato->status === 'perdido')
                                    <span class="badge bg-danger">Perdido</span>
                                @else
                                    <span class="badge bg-warning">Lead Ruim</span>
                                @endif

                                @if($contato->canal_origem)
                                    <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $contato->canal_origem)) }}</span>
                                @endif

                                @if($contato->campanha)
                                    <a href="{{ route('admin.campanhas.show', $contato->campanha) }}" class="badge bg-info text-decoration-none">
                                        {{ Str::limit($contato->campanha->nome, 20) }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('admin.contatos.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                            <button type="button" class="btn btn-primary me-2" onclick="editarContato()">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button type="button" class="btn btn-success" onclick="agendarContato()">
                                <i class="fas fa-calendar-plus"></i> Agendar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Informações Principais --}}
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Informações do Contato</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Nome:</dt>
                                        <dd class="col-sm-8">{{ $contato->nome }}</dd>

                                        <dt class="col-sm-4">Email:</dt>
                                        <dd class="col-sm-8">{{ $contato->email ?: '-' }}</dd>

                                        <dt class="col-sm-4">Telefone:</dt>
                                        <dd class="col-sm-8">{{ $contato->telefone ?: '-' }}</dd>

                                        <dt class="col-sm-4">WhatsApp:</dt>
                                        <dd class="col-sm-8">{{ $contato->whatsapp ?: '-' }}</dd>

                                        <dt class="col-sm-4">Documento:</dt>
                                        <dd class="col-sm-8">{{ $contato->documento ?: '-' }}</dd>

                                        <dt class="col-sm-4">Status:</dt>
                                        <dd class="col-sm-8">
                                            @if($contato->status === 'lead')
                                                <span class="badge bg-primary">Lead</span>
                                            @elseif($contato->status === 'convertido')
                                                <span class="badge bg-success">Convertido</span>
                                            @elseif($contato->status === 'perdido')
                                                <span class="badge bg-danger">Perdido</span>
                                            @else
                                                <span class="badge bg-warning">Lead Ruim</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Campanha:</dt>
                                        <dd class="col-sm-8">
                                            @if($contato->campanha)
                                                <a href="{{ route('admin.campanhas.show', $contato->campanha) }}">{{ $contato->campanha->nome }}</a>
                                            @else
                                                -
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Canal:</dt>
                                        <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $contato->canal_origem ?: 'indefinido')) }}</dd>

                                        <dt class="col-sm-4">Agente:</dt>
                                        <dd class="col-sm-8">
                                            @if($contato->agente)
                                                {{ $contato->agente->name }}
                                            @else
                                                -
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Vendedor:</dt>
                                        <dd class="col-sm-8">
                                            @if($contato->vendedor)
                                                {{ $contato->vendedor->user->name }}
                                            @else
                                                -
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Gestor:</dt>
                                        <dd class="col-sm-8">
                                            @if($contato->gestor)
                                                {{ $contato->gestor->name }}
                                            @else
                                                -
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Entrada:</dt>
                                        <dd class="col-sm-8">{{ $contato->entry_date->format('d/m/Y H:i') }}</dd>
                                    </dl>
                                </div>
                            </div>

                            @if($contato->observacoes)
                            <div class="mt-3">
                                <h6>Observações:</h6>
                                <p class="text-muted">{{ $contato->observacoes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- UTM Parameters --}}
                    @if($contato->utm_source || $contato->utm_medium || $contato->utm_campaign)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Parâmetros UTM</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Source:</strong> {{ $contato->utm_source ?: '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Medium:</strong> {{ $contato->utm_medium ?: '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Campaign:</strong> {{ $contato->utm_campaign ?: '-' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Ref:</strong> {{ $contato->ref_param ?: '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Histórico de Status --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>Histórico de Status</h5>
                        </div>
                        <div class="card-body">
                            @if($contato->statusLogs->count() > 0)
                                <div class="timeline">
                                    @foreach($contato->statusLogs as $log)
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">
                                                {{ ucfirst($log->status_novo) }}
                                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                            </h6>
                                            @if($log->motivo)
                                            <p class="timeline-text">{{ $log->motivo }}</p>
                                            @endif
                                            <small class="text-muted">Por: {{ $log->usuario->name }}</small>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Nenhuma mudança de status registrada.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-md-4">
                    {{-- Informações da Igreja --}}
                    @if($contato->nome_igreja || $contato->nome_pastor || $contato->quantidade_membros)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Informações da Igreja</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Igreja:</dt>
                                <dd class="col-sm-7">{{ $contato->nome_igreja ?: '-' }}</dd>

                                <dt class="col-sm-5">Pastor:</dt>
                                <dd class="col-sm-7">{{ $contato->nome_pastor ?: '-' }}</dd>

                                <dt class="col-sm-5">Responsável:</dt>
                                <dd class="col-sm-7">{{ $contato->nome_responsavel ?: '-' }}</dd>

                                <dt class="col-sm-5">Membros:</dt>
                                <dd class="col-sm-7">{{ $contato->quantidade_membros ?: '-' }}</dd>

                                <dt class="col-sm-5">Localidade:</dt>
                                <dd class="col-sm-7">{{ $contato->localidade ?: '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                    @endif

                    {{-- Endereço --}}
                    @if($contato->cep || $contato->endereco)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Endereço</h5>
                        </div>
                        <div class="card-body">
                            <address>
                                {{ $contato->endereco }} {{ $contato->numero }}<br>
                                @if($contato->complemento){{ $contato->complemento }}<br>@endif
                                {{ $contato->bairro }}<br>
                                {{ $contato->cidade }} - {{ $contato->estado }}<br>
                                CEP: {{ $contato->cep }}<br>
                                {{ $contato->pais }}
                            </address>
                        </div>
                    </div>
                    @endif

                    {{-- Tags --}}
                    @if($contato->tags && count($contato->tags) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Tags</h5>
                        </div>
                        <div class="card-body">
                            @foreach($contato->tags as $tag)
                            <span class="badge bg-secondary me-1">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Ações Rápidas --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>Ações Rápidas</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="mudarStatus()">
                                    <i class="fas fa-exchange-alt"></i> Mudar Status
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="agendarContato()">
                                    <i class="fas fa-calendar-plus"></i> Agendar Follow-up
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="enviarMensagem()">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Mudar Status --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status do Contato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.contatos.status', $contato) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Novo Status</label>
                        <select name="status" class="form-select" id="statusSelect" required>
                            <option value="lead" {{ $contato->status === 'lead' ? 'selected' : '' }}>Lead</option>
                            <option value="convertido" {{ $contato->status === 'convertido' ? 'selected' : '' }}>Convertido</option>
                            <option value="perdido" {{ $contato->status === 'perdido' ? 'selected' : '' }}>Perdido</option>
                            <option value="lead_ruim" {{ $contato->status === 'lead_ruim' ? 'selected' : '' }}>Lead Ruim</option>
                        </select>
                    </div>
                    <div class="mb-3" id="motivoDiv" style="display: {{ in_array($contato->status, ['perdido', 'lead_ruim']) ? 'block' : 'none' }};">
                        <label class="form-label">Motivo</label>
                        <textarea name="motivo" class="form-control" rows="3" placeholder="Explique o motivo da mudança...">{{ $contato->motivo_perda }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle do filtro de motivo baseado no status
document.getElementById('statusSelect').addEventListener('change', function() {
    const motivoDiv = document.getElementById('motivoDiv');
    const status = this.value;

    if (status === 'perdido' || status === 'lead_ruim') {
        motivoDiv.style.display = 'block';
    } else {
        motivoDiv.style.display = 'none';
    }
});

function editarContato() {
    // TODO: Implementar drawer de edição
    alert('Funcionalidade de edição será implementada em breve!');
}

function mudarStatus() {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function agendarContato() {
    // TODO: Abrir modal de agendamento
    alert('Funcionalidade de agendamento será implementada em breve!');
}

function enviarMensagem() {
    @if($contato->whatsapp)
        const numero = '{{ $contato->whatsapp }}'.replace(/\D/g, '');
        const url = `https://wa.me/55${numero}`;
        window.open(url, '_blank');
    @else
        alert('Contato não possui WhatsApp cadastrado.');
    @endif
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.timeline-text {
    margin-bottom: 5px;
    font-size: 0.85rem;
    color: #6c757d;
}
</style>
@endsection