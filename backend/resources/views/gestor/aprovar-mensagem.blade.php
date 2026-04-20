@extends('layouts.app')

@section('title', 'Aprovar Mensagens')

@section('content')
<style>
    .aprovar-page { max-width: 900px; margin: 0 auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
    
    .aprovar-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
        margin-bottom: 20px;
    }
    .aprovar-header { 
        display: flex; justify-content: space-between; align-items: center; 
        margin-bottom: 20px;
    }
    .aprovar-title { font-weight: 700; font-size: 1.1rem; color: var(--text-primary); }
    
    .pendente-item {
        padding: 20px;
        background: var(--surface-hover);
        border-radius: 14px;
        margin-bottom: 16px;
        border: 1px solid var(--border-light);
    }
    .pendente-user {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    .user-avatar {
        width: 44px; height: 44px;
        background: linear-gradient(135deg, var(--primary) 0%, #7C3AED 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
    }
    .user-nome { font-weight: 700; color: var(--text-primary); }
    .user-funcao { font-size: 0.8rem; color: var(--text-muted); }
    
    .mensagem-box {
        padding: 16px;
        background: white;
        border-radius: 10px;
        border: 1px solid var(--border-light);
        margin-bottom: 16px;
    }
    .mensagem-titulo { font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .mensagem-texto { color: var(--text-muted); line-height: 1.6; white-space: pre-wrap; }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    .btn-success { background: #059669; color: white; }
    .btn-success:hover { background: #047857; }
    .btn-danger { background: #DC2626; color: white; }
    .btn-danger:hover { background: #B91C1C; }
    .btn-secondary { background: #E2E8F0; color: var(--text-primary); }
    .btn-secondary:hover { background: #CBD5E1; }
    
    .acoes { display: flex; gap: 12px; }
    
    .empty-state {
        text-align: center;
        padding: 60px;
        color: var(--text-muted);
    }
    .empty-icon { font-size: 4rem; margin-bottom: 16px; }
</style>

<div class="aprovar-page">
    <div class="page-header">
        <h1 class="page-title">✅ Aprovar Mensagens</h1>
    </div>
    
    <div class="aprovar-card">
        <div class="aprovar-header">
            <h2 class="aprovar-title">Mensagens Pendentes de Aprovação</h2>
            <span style="color: var(--text-muted);">{{ $pendentes->count() }} pendente(s)</span>
        </div>
        
        @forelse($pendentes as $mensagem)
        <div class="pendente-item">
            <div class="pendente-user">
                <div class="user-avatar">{{ substr($mensagem->usuario->name, 0, 2) }}</div>
                <div>
                    <div class="user-nome">{{ $mensagem->usuario->name }}</div>
                    <div class="user-funcao">{{ ucfirst($mensagem->perfil) }} • Enviado em {{ $mensagem->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            
            <div class="mensagem-box">
                <div class="mensagem-titulo">{{ $mensagem->titulo }}</div>
                <div class="mensagem-texto">{{ $mensagem->mensagem }}</div>
            </div>
            
            <div class="acoes">
                <form action="{{ route('gestor.aprovar-mensagem.aprovar', $mensagem) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Aprovar
                    </button>
                </form>
                <button class="btn btn-danger" onclick="rejeitar({{ $mensagem->id }})">
                    <i class="fas fa-times"></i> Rejeitar
                </button>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">🎉</div>
            <h3>Nenhuma mensagem pendente!</h3>
            <p>Todas as mensagens foram revisadas.</p>
        </div>
        @endforelse
    </div>
</div>

<div class="modal" id="rejeitarModal">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--border-light);">
            <h3 style="font-size: 1.2rem; font-weight: 700;">Rejeitar Mensagem</h3>
            <button onclick="fecharModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form id="rejeitarForm" method="POST" style="padding: 24px;">
            @csrf
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Motivo da Rejeição *</label>
                <textarea name="motivo" rows="4" required 
                    style="width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px;"
                    placeholder="Explique o motivo da rejeição para que o vendedor possa corrigir..."></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="fecharModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-danger">Rejeitar</button>
            </div>
        </form>
    </div>
</div>

<script>
let mensagemId = 0;

function rejeitar(id) {
    mensagemId = id;
    document.getElementById('rejeitarForm').action = '/gestor/configuracoes/aprovar-mensagem/' + id + '/rejeitar';
    document.getElementById('rejeitarModal').classList.add('active');
}

function fecharModal() {
    document.getElementById('rejeitarModal').classList.remove('active');
}

document.getElementById('rejeitarModal').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>
@endsection