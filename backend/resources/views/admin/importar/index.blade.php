@extends('layouts.app')

@section('title', 'Importar Contatos')

@section('content')
<style>
    .import-page { max-width: 800px; margin: 0 auto; padding: 30px; }
    .import-card {
        background: white;
        border-radius: 20px;
        padding: 32px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
    }
    .import-header { margin-bottom: 28px; }
    .import-header h1 { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
    .import-header p { color: var(--text-muted); }
    
    .upload-zone {
        border: 2px dashed #CBD5E1;
        border-radius: 16px;
        padding: 48px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #F8FAFC;
    }
    .upload-zone:hover { border-color: var(--primary); background: rgba(76, 29, 149, 0.02); }
    .upload-zone.dragover { border-color: var(--primary); background: rgba(76, 29, 149, 0.05); }
    .upload-icon { font-size: 3rem; color: var(--primary); margin-bottom: 16px; }
    .upload-text { font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
    .upload-hint { font-size: 0.85rem; color: var(--text-muted); }
    
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary); font-size: 0.9rem; }
    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        font-size: 0.95rem;
        background: white;
        transition: all 0.2s;
    }
    .form-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(76, 29, 149, 0.1); }
    
    .btn {
        padding: 14px 28px;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, #7C3AED 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(76, 29, 149, 0.3); }
    
    .info-box {
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 12px;
        padding: 16px;
        margin-top: 24px;
    }
    .info-box h4 { color: #1E40AF; font-size: 0.9rem; margin-bottom: 8px; }
    .info-box ul { margin: 0; padding-left: 20px; color: #1E3A8A; font-size: 0.85rem; }
    .info-box li { margin-bottom: 4px; }
</style>

<div class="import-page">
    <div class="import-card">
        <div class="import-header">
            <h1>📥 Importar Contatos</h1>
            <p>Importe contatos de um arquivo CSV para adicionar à sua base de leads</p>
        </div>
        
        <form action="{{ route('admin.importar.processar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Selecione a Campanha (opcional)</label>
                <select name="campanha_id" class="form-select">
                    <option value="">Nenhuma campanha específica</option>
                    @foreach(\App\Models\Campanha::where('status', 'ativa')->orderBy('nome')->get() as $campanha)
                        <option value="{{ $campanha->id }}">{{ $campanha->nome }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="upload-zone" id="uploadZone">
                <input type="file" name="arquivo" id="arquivo" accept=".csv" style="display: none;" required>
                <div class="upload-icon">📄</div>
                <div class="upload-text">Clique ou arraste o arquivo CSV aqui</div>
                <div class="upload-hint">Formato suportado: CSV (separado por vírgula)</div>
            </div>
            
            <div id="fileInfo" style="display: none; margin-top: 16px; padding: 12px; background: #F0FDF4; border-radius: 10px; text-align: center;">
                <i class="fas fa-check-circle" style="color: #16A34A; margin-right: 8px;"></i>
                <span id="fileName" style="color: #166534; font-weight: 600;"></span>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 24px; justify-content: center;">
                <i class="fas fa-upload"></i>
                Importar Contatos
            </button>
        </form>
        
        <div class="info-box">
            <h4>📋 Formato do Arquivo CSV</h4>
            <ul>
                <li><strong>nome</strong> - Nome completo do contato (obrigatório)</li>
                <li><strong>email</strong> - E-mail do contato</li>
                <li><strong>telefone</strong> - Telefone com DDD</li>
                <li><strong>whatsapp</strong> - WhatsApp com DDD</li>
                <li><strong>documento</strong> - CPF ou CNPJ</li>
                <li><strong>nome_igreja</strong> - Nome da igreja (se aplicável)</li>
                <li><strong>nome_pastor</strong> - Nome do pastor</li>
                <li><strong>cidade</strong> - Cidade</li>
                <li><strong>estado</strong> - Estado (UF)</li>
            </ul>
        </div>
    </div>
</div>

<script>
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('arquivo');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');

uploadZone.addEventListener('click', () => fileInput.click());

uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        mostrarArquivo(files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        mostrarArquivo(e.target.files[0]);
    }
});

function mostrarArquivo(file) {
    fileInfo.style.display = 'block';
    fileName.textContent = file.name;
}
</script>
@endsection