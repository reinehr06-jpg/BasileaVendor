{{-- Drawer de Edição de Contato --}}
<div class="drawer-overlay" id="contatoDrawer" style="display: none;">
    <div class="drawer-content">
        <div class="drawer-header">
            <h5 class="drawer-title">Editar Contato</h5>
            <button type="button" class="btn-close" onclick="fecharDrawer()"></button>
        </div>
        <div class="drawer-body">
            <form id="contatoForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Documento (CPF/CNPJ)</label>
                    <input type="text" name="documento" class="form-control">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Agente Responsável</label>
                            <select name="agente_id" class="form-select">
                                <option value="">Selecione...</option>
                                {{-- Agentes serão carregados via AJAX --}}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Vendedor Responsável</label>
                            <select name="vendedor_id" class="form-select">
                                <option value="">Selecione...</option>
                                {{-- Vendedores serão carregados via AJAX --}}
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="Separe por vírgulas">
                </div>

                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>

                {{-- Campos da Igreja --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Informações da Igreja</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nome da Igreja</label>
                                    <input type="text" name="nome_igreja" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nome do Pastor</label>
                                    <input type="text" name="nome_pastor" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Responsável</label>
                                    <input type="text" name="nome_responsavel" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Quantidade de Membros</label>
                                    <input type="number" name="quantidade_membros" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Localidade</label>
                            <input type="text" name="localidade" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Endereço --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Endereço</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">CEP</label>
                                    <input type="text" name="cep" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">País</label>
                                    <input type="text" name="pais" class="form-control" value="Brasil">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Endereço</label>
                                    <input type="text" name="endereco" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Número</label>
                                    <input type="text" name="numero" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento" class="form-control">
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" name="bairro" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" name="cidade" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <input type="text" name="estado" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-secondary" onclick="fecharDrawer()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="salvarContato()">Salvar</button>
        </div>
    </div>
</div>

<style>
.drawer-overlay {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.drawer-content {
    background: white;
    width: 100%;
    max-width: 600px;
    height: 100%;
    box-shadow: -2px 0 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.drawer-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: between;
}

.drawer-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 500;
}

.drawer-body {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
}

.drawer-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .drawer-content {
        width: 100%;
        max-width: none;
    }
}
</style>

<script>
let contatoAtual = null;

function abrirDrawer(contatoId) {
    // Carregar dados do contato via AJAX
    fetch(`{{ url('admin/contatos') }}/${contatoId}/drawer`)
        .then(response => response.json())
        .then(data => {
            contatoAtual = data;
            preencherFormulario(data);
            document.getElementById('contatoDrawer').style.display = 'flex';
        })
        .catch(error => {
            console.error('Erro ao carregar contato:', error);
            alert('Erro ao carregar dados do contato.');
        });
}

function fecharDrawer() {
    document.getElementById('contatoDrawer').style.display = 'none';
    contatoAtual = null;
}

function preencherFormulario(data) {
    const form = document.getElementById('contatoForm');

    // Campos básicos
    form.nome.value = data.nome || '';
    form.email.value = data.email || '';
    form.telefone.value = data.telefone || '';
    form.whatsapp.value = data.whatsapp || '';
    form.documento.value = data.documento || '';
    form.tags.value = data.tags ? data.tags.join(', ') : '';
    form.observacoes.value = data.observacoes || '';

    // Responsáveis
    form.agente_id.value = data.agente_id || '';
    form.vendedor_id.value = data.vendedor_id || '';

    // Igreja
    form.nome_igreja.value = data.nome_igreja || '';
    form.nome_pastor.value = data.nome_pastor || '';
    form.nome_responsavel.value = data.nome_responsavel || '';
    form.quantidade_membros.value = data.quantidade_membros || '';
    form.localidade.value = data.localidade || '';

    // Endereço
    form.cep.value = data.cep || '';
    form.pais.value = data.pais || 'Brasil';
    form.endereco.value = data.endereco || '';
    form.numero.value = data.numero || '';
    form.complemento.value = data.complemento || '';
    form.bairro.value = data.bairro || '';
    form.cidade.value = data.cidade || '';
    form.estado.value = data.estado || '';
}

function salvarContato() {
    if (!contatoAtual) return;

    const form = document.getElementById('contatoForm');
    const formData = new FormData(form);

    // Converter tags para array
    const tagsString = formData.get('tags');
    if (tagsString) {
        const tagsArray = tagsString.split(',').map(tag => tag.trim()).filter(tag => tag);
        formData.set('tags', JSON.stringify(tagsArray));
    }

    fetch(`{{ url('admin/contatos') }}/${contatoAtual.id}`, {
        method: 'PUT',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharDrawer();
            location.reload(); // Recarregar página para mostrar mudanças
        } else {
            alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro ao salvar:', error);
        alert('Erro ao salvar contato.');
    });
}

// Fechar drawer ao clicar no overlay
document.getElementById('contatoDrawer').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharDrawer();
    }
});
</script>