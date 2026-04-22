@extends('layouts.app')

@section('title', 'Gerenciar Termos de Uso')

@section('content')
<style>
    .termos-page { max-width: 1200px; margin: 0 auto; padding: 30px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
    
    .termos-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
        margin-bottom: 20px;
    }
    
    .termos-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        background: var(--surface-hover);
        border-radius: 14px;
        margin-bottom: 12px;
        border: 1px solid var(--border-light);
    }
    .termos-info { flex: 1; }
    .termos-tipo { 
        display: inline-block;
        padding: 4px 12px;
        background: var(--primary);
        color: white;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .termos-titulo { font-weight: 700; color: var(--text-primary); font-size: 1.1rem; }
    .termos-versao { color: var(--text-muted); font-size: 0.85rem; margin-top: 4px; }
    .termos-status { 
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .status-ativo { background: #D1FAE5; color: #065F46; }
    .status-inativo { background: #FEE2E2; color: #991B1B; }
    
    .termos-acoes { display: flex; gap: 8px; }
    .btn {
        padding: 10px 18px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-secondary { background: #E2E8F0; color: var(--text-primary); }
    .btn-secondary:hover { background: #CBD5E1; }
    .btn-danger { background: #FEE2E2; color: #991B1B; }
    .btn-danger:hover { background: #FECACA; }
    .btn-success { background: #D1FAE5; color: #065F46; }
    .btn-success:hover { background: #A7F3D0; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
    .modal.active { display: flex; align-items: center; justify-content: center; }
    .modal-content { background: white; border-radius: 20px; padding: 28px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .modal-title { font-size: 1.2rem; font-weight: 700; }
    .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
    
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary); }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(76, 29, 149, 0.1);
    }
    .form-textarea { min-height: 200px; resize: vertical; font-family: monospace; }
</style>

<div class="termos-page">
    <div class="page-header">
        <h1 class="page-title">📋 Gerenciar Termos de Uso</h1>
        <button class="btn btn-primary" onclick="abrirModal()">
            <i class="fas fa-plus"></i> Novo Termo
        </button>
    </div>
    
    <div class="termos-card">
        <h3 style="margin-bottom: 20px; font-size: 1rem; color: var(--text-muted);">Termos Cadastrados</h3>
        
        @forelse($termos as $termo)
        <div class="termos-item">
            <div class="termos-info">
                <span class="termos-tipo">{{ $termo->tipo }}</span>
                <div class="termos-titulo">{{ $termo->titulo }}</div>
                <div class="termos-versao">Versão {{ $termo->versao }} • Criado em {{ $termo->created_at->format('d/m/Y') }}</div>
            </div>
            <span class="termos-status {{ $termo->ativo ? 'status-ativo' : 'status-inativo' }}">
                {{ $termo->ativo ? 'Ativo' : 'Inativo' }}
            </span>
            <div class="termos-acoes">
                <a href="{{ route('admin.termos.download', $termo) }}" class="btn btn-secondary" title="Baixar">
                    <i class="fas fa-download"></i>
                </a>
                <button class="btn btn-secondary" onclick="editarTermo({{ $termo->id }}, '{{ $termo->titulo }}', '{{ $termo->versao }}', '{!! addslashes($termo->conteudo_html) !!}')">
                    <i class="fas fa-edit"></i>
                </button>
                <form action="{{ route('admin.termos.toggle', $termo) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn {{ $termo->ativo ? 'btn-danger' : 'btn-success' }}" title="{{ $termo->ativo ? 'Desativar' : 'Ativar' }}">
                        <i class="fas fa-{{ $termo->ativo ? 'times' : 'check' }}"></i>
                    </button>
                </form>
                <form action="{{ route('admin.termos.destroy', $termo) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fas fa-file-contract" style="font-size: 3rem; margin-bottom: 16px;"></i>
            <p>Nenhum termo cadastrado ainda.</p>
        </div>
        @endforelse
    </div>
</div>

<div class="modal" id="termoModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Novo Termo</h3>
            <button class="modal-close" onclick="fecharModal()">&times;</button>
        </div>
        
        <form id="termoForm" method="POST" action="{{ route('admin.termos.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div class="form-group">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                    <option value="uso">Termos de Uso</option>
                    <option value="privacidade">Política de Privacidade</option>
                    <option value="cookies">Política de Cookies</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-input" placeholder="Ex: Termos de Uso Basileia Vendas" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Versão</label>
                <input type="text" name="versao" class="form-input" placeholder="Ex: 1.0.0" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Importar de Arquivo (Opcional)</label>
                <div style="border: 2px dashed #E2E8F0; padding: 20px; border-radius: 12px; text-align: center; background: #f8fafc;">
                    <i class="fas fa-file-upload" style="font-size: 1.5rem; color: var(--primary); margin-bottom: 8px;"></i>
                    <input type="file" name="arquivo_termo" id="arquivo_termo" class="form-control" accept=".pdf,.doc,.docx" style="display: none;" onchange="updateFileName(this)">
                    <label for="arquivo_termo" style="display: block; cursor: pointer; color: var(--primary); font-weight: 700;">
                        Clique para selecionar PDF ou DOCX
                    </label>
                    <div id="file-name" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">O sistema tentará formatar o texto automaticamente</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Conteúdo (HTML)</label>
                <textarea name="conteudo_html" id="conteudo_html" class="form-textarea" placeholder="<h1>Termos de Uso</h1><p>Seu conteúdo aqui...</p>"></textarea>
                <div class="form-text" style="font-size: 0.75rem; color: var(--primary); margin-top: 4px;">
                    <i class="fas fa-magic"></i> Se você subir um arquivo, o conteúdo acima será preenchido após o processamento.
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('termoModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Novo Termo';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('termoForm').action = '{{ route("admin.termos.store") }}';
    document.getElementById('termoForm').reset();
}

function fecharModal() {
    document.getElementById('termoModal').classList.remove('active');
}

function editarTermo(id, titulo, versao, conteudo) {
    document.getElementById('termoModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Editar Termo';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('termoForm').action = '/admin/termos/' + id;
    
    document.querySelector('input[name="titulo"]').value = titulo;
    document.querySelector('input[name="versao"]').value = versao;
    document.querySelector('textarea[name="conteudo_html"]').value = conteudo;
}

function updateFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('file-name').textContent = 'Selecionado: ' + input.files[0].name;
    }
}

document.getElementById('termoModal').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>
@endsection