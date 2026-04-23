@extends('layouts.app')

@section('title', 'Primeira Mensagem')

@section('header_title', 'Primeira Mensagem')
@section('header_description', 'Configure suas mensagens automáticas para novos leads.')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Primeira Mensagem Automática</h4>
                    <small class="text-muted">Configure mensagens automáticas para novos leads</small>
                </div>
                <div class="card-body">

                    {{-- IA Sugestões --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-robot"></i> Gerar Sugestões com IA
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Descreva o contexto da mensagem</label>
                                        <textarea id="contextoIA" class="form-control" rows="3" placeholder="Ex: Cliente interessado em plano premium para igreja de 200 membros, orçamento médio, foco em crescimento espiritual..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Quantidade de sugestões</label>
                                        <select id="quantidadeIA" class="form-select">
                                            <option value="3">3 sugestões</option>
                                            <option value="5" selected>5 sugestões</option>
                                            <option value="7">7 sugestões</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" onclick="gerarComIA()">
                                        <i class="fas fa-magic"></i> Gerar com IA
                                    </button>
                                </div>
                            </div>

                            <div id="sugestoesContainer" style="display: none;">
                                <hr>
                                <h6>Sugestões geradas:</h6>
                                <div id="sugestoesList" class="row"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Mensagens Existentes --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Minhas Mensagens</h5>
                            <button type="button" class="btn btn-primary btn-sm" onclick="novaMensagem()">
                                <i class="fas fa-plus"></i> Nova Mensagem
                            </button>
                        </div>
                        <div class="card-body">
                            @if($mensagens->count() > 0)
                                <div class="row">
                                    @foreach($mensagens as $mensagem)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border {{ $mensagem->ativa ? 'border-success' : 'border-secondary' }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">{{ $mensagem->titulo }}</h6>
                                                    @if($mensagem->ativa)
                                                        <span class="badge bg-success">Ativa</span>
                                                    @elseif($mensagem->status === 'aprovada')
                                                        <span class="badge bg-info">Aprovada</span>
                                                    @elseif($mensagem->status === 'pendente_aprovacao')
                                                        <span class="badge bg-warning">Pendente</span>
                                                    @elseif($mensagem->status === 'rejeitada')
                                                        <span class="badge bg-danger">Rejeitada</span>
                                                    @else
                                                        <span class="badge bg-secondary">Rascunho</span>
                                                    @endif
                                                </div>

                                                <p class="card-text small text-muted mb-2">
                                                    {{ \Illuminate\Support\Str::limit($mensagem->mensagem, 100) }}
                                                </p>

                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="visualizarMensagem({{ $mensagem->id }})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="editarMensagem({{ $mensagem->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($mensagem->status === 'rascunho')
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="enviarParaAprovacao({{ $mensagem->id }})">
                                                        <i class="fas fa-paper-plane"></i> Enviar
                                                    </button>
                                                    @endif
                                                </div>

                                                @if($mensagem->motivo_rejeicao)
                                                <div class="mt-2 p-2 bg-light rounded small">
                                                    <strong>Motivo da rejeição:</strong><br>
                                                    {{ $mensagem->motivo_rejeicao }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                                    <h5>Nenhuma mensagem criada</h5>
                                    <p class="text-muted">Crie sua primeira mensagem automática para leads.</p>
                                    <button type="button" class="btn btn-primary" onclick="novaMensagem()">
                                        <i class="fas fa-plus"></i> Criar Primeira Mensagem
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Nova Mensagem --}}
<div class="modal fade" id="mensagemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nova Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="mensagemForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título da Mensagem *</label>
                        <input type="text" name="titulo" class="form-control" required placeholder="Ex: Apresentação Premium">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensagem *</label>
                        <textarea name="mensagem" class="form-control" rows="8" required maxlength="500"
                                  placeholder="Digite sua mensagem personalizada aqui... (máx. 500 caracteres)"></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/500 caracteres
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Dicas:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Seja pessoal e mostre conhecimento sobre o perfil da igreja</li>
                            <li>Mencione benefícios específicos do seu serviço</li>
                            <li>Termine com uma pergunta ou chamada para ação</li>
                            <li>Use até 160 caracteres para caber no WhatsApp</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar como Rascunho</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Visualizar Mensagem --}}
<div class="modal fade" id="visualizarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="mensagemPreview" class="p-3 bg-light rounded"></div>
            </div>
        </div>
    </div>
</div>

<script>
function novaMensagem() {
    document.getElementById('modalTitle').textContent = 'Nova Mensagem';
    document.getElementById('mensagemForm').action = '{{ route("vendedor.primeira-mensagem.store") }}';
    document.getElementById('mensagemForm').reset();
    new bootstrap.Modal(document.getElementById('mensagemModal')).show();
}

function editarMensagem(id) {
    // TODO: Implementar carregamento da mensagem para edição
    alert('Funcionalidade de edição será implementada em breve!');
}

function enviarParaAprovacao(id) {
    if (confirm('Enviar esta mensagem para aprovação do gestor?')) {
        fetch(`{{ url('vendedor/primeira-mensagem') }}/${id}/enviar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao enviar para aprovação.');
        });
    }
}

function visualizarMensagem(id) {
    // Buscar mensagem do servidor ou usar dados locais
    // Por enquanto, placeholder
    document.getElementById('mensagemPreview').innerHTML = '<p>Mensagem será carregada aqui...</p>';
    new bootstrap.Modal(document.getElementById('visualizarModal')).show();
}

function gerarComIA() {
    const contexto = document.getElementById('contextoIA').value.trim();
    const quantidade = document.getElementById('quantidadeIA').value;

    if (!contexto) {
        alert('Por favor, descreva o contexto da mensagem.');
        return;
    }

    document.querySelector('#sugestoesContainer button').disabled = true;
    document.querySelector('#sugestoesContainer button').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';

    fetch('{{ route("vendedor.primeira-mensagem.ia") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            contexto: contexto,
            quantidade: quantidade
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.sugestoes && data.sugestoes.length > 0) {
            mostrarSugestoes(data.sugestoes);
        } else {
            alert('Não foi possível gerar sugestões. Tente novamente.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao gerar sugestões. Verifique se o serviço de IA está configurado.');
    })
    .finally(() => {
        document.querySelector('#sugestoesContainer button').disabled = false;
        document.querySelector('#sugestoesContainer button').innerHTML = '<i class="fas fa-magic"></i> Gerar com IA';
    });
}

function mostrarSugestoes(sugestoes) {
    const container = document.getElementById('sugestoesList');
    container.innerHTML = '';

    sugestoes.forEach((sugestao, index) => {
        const col = document.createElement('div');
        col.className = 'col-md-6 mb-3';

        col.innerHTML = `
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title">Sugestão ${index + 1}</h6>
                    <p class="card-text small">${sugestao}</p>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="usarSugestao('${sugestao.replace(/'/g, "\\'").replace(/"/g, '\\"')}')">
                        <i class="fas fa-check"></i> Usar esta
                    </button>
                </div>
            </div>
        `;

        container.appendChild(col);
    });

    document.getElementById('sugestoesContainer').style.display = 'block';
}

function usarSugestao(sugestao) {
    document.querySelector('#mensagemForm textarea[name="mensagem"]').value = sugestao;
    atualizarContadorCaracteres();
    new bootstrap.Modal(document.getElementById('mensagemModal')).show();
}

// Contador de caracteres
document.querySelector('#mensagemForm textarea[name="mensagem"]').addEventListener('input', atualizarContadorCaracteres);

function atualizarContadorCaracteres() {
    const textarea = document.querySelector('#mensagemForm textarea[name="mensagem"]');
    const contador = document.getElementById('charCount');
    const count = textarea.value.length;

    contador.textContent = count;

    if (count > 450) {
        contador.style.color = 'red';
    } else if (count > 400) {
        contador.style.color = 'orange';
    } else {
        contador.style.color = 'inherit';
    }
}
</script>
@endsection