@extends('layouts.app')

@section('title', 'Primeira Mensagem')

@section('content')
<style>
    .pm-page { max-width: 900px; margin: 0 auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
    
    .pm-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
        margin-bottom: 20px;
    }
    .pm-header { 
        display: flex; justify-content: space-between; align-items: center; 
        margin-bottom: 20px;
    }
    .pm-title { font-weight: 700; font-size: 1.1rem; color: var(--text-primary); }
    
    .pm-form { margin-top: 20px; }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary); font-size: 0.9rem; }
    .form-input {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(76, 29, 149, 0.1); }
    .form-textarea {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        font-size: 0.95rem;
        min-height: 120px;
        resize: vertical;
    }
    .form-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(76, 29, 149, 0.1); }
    
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, #7C3AED 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(76, 29, 149, 0.3); }
    .btn-secondary { background: #E2E8F0; color: var(--text-primary); }
    .btn-secondary:hover { background: #CBD5E1; }
    .btn-ghost { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
    .btn-ghost:hover { background: rgba(76, 29, 149, 0.05); }
    
    .sugestoes-container { margin-top: 16px; }
    .sugestao-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: var(--surface-hover);
        border-radius: 10px;
        margin-bottom: 10px;
        border: 1px solid var(--border-light);
        cursor: pointer;
        transition: all 0.2s;
    }
    .sugestao-item:hover { border-color: var(--primary); background: rgba(76, 29, 149, 0.03); }
    .sugestao-texto { color: var(--text-primary); font-size: 0.9rem; flex: 1; }
    .sugestao-usar {
        padding: 6px 12px;
        background: var(--primary);
        color: white;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .sugestao-item:hover .sugestao-usar { opacity: 1; }
    
    .mensagem-ativa {
        background: linear-gradient(135deg, rgba(76, 29, 149, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
        border: 2px solid var(--primary);
    }
    .ativa-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: var(--primary);
        color: white;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .status-rascunho { background: #F1F5F9; color: #64748B; }
    .status-pendente { background: #FEF3C7; color: #92400E; }
    .status-aprovada { background: #D1FAE5; color: #065F46; }
    .status-rejeitada { background: #FEE2E2; color: #991B1B; }
    
    .historico-item {
        padding: 16px;
        background: var(--surface-hover);
        border-radius: 12px;
        margin-bottom: 12px;
        border: 1px solid var(--border-light);
    }
    .historico-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .historico-titulo { font-weight: 600; color: var(--text-primary); }
    .historico-mensagem { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; }
    
    .loading {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="pm-page">
    <div class="page-header">
        <h1 class="page-title">💬 Primeira Mensagem</h1>
    </div>
    
    {{-- Formulário de Criação/Edição --}}
    <div class="pm-card">
        <div class="pm-header">
            <h2 class="pm-title">{{ isset($mensagemEdit) ? 'Editar Mensagem' : 'Nova Mensagem' }}</h2>
        </div>
        
        <form class="pm-form" method="POST" action="{{ route('configuracoes.primeira-mensagem.store') }}" id="mensagemForm">
            @csrf
            <div class="form-group">
                <label class="form-label">Título da Mensagem</label>
                <input type="text" name="titulo" class="form-input" 
                    placeholder="Ex: Bem-vindo, seja bem-vindo ao nosso serviço!" 
                    value="{{ isset($mensagemEdit) ? $mensagemEdit->titulo : '' }}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mensagem</label>
                <textarea name="mensagem" class="form-textarea" 
                    placeholder="Digite a primeira mensagem que será enviada automaticamente aos seus novos leads..." 
                    maxlength="500" required>{{ isset($mensagemEdit) ? $mensagemEdit->mensagem : '' }}</textarea>
                <small style="color: var(--text-muted); font-size: 0.8rem;">Máximo 500 caracteres</small>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button type="button" class="btn btn-ghost" onclick="gerarComIA()">
                    <i class="fas fa-robot"></i> Gerar com IA
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar como Rascunho
                </button>
            </div>
        </form>
        
        {{-- Sugestões da IA --}}
        <div class="sugestoes-container" id="sugestoesIA" style="display: none;">
            <h4 style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 12px;">Sugestões da IA:</h4>
            <div id="sugestoesLista"></div>
        </div>
    </div>
    
    {{-- Lista de Mensagens --}}
    <div class="pm-card">
        <div class="pm-header">
            <h2 class="pm-title">Minhas Mensagens</h2>
        </div>
        
        @forelse($mensagens as $msg)
        <div class="historico-item {{ $msg->ativa ? 'mensagem-ativa' : '' }}">
            <div class="historico-header">
                <div>
                    <span class="historico-titulo">{{ $msg->titulo }}</span>
                    @if($msg->ativa)
                    <span class="ativa-badge"><i class="fas fa-check-circle"></i> Ativa</span>
                    @endif
                </div>
                <span class="status-badge status-{{ $msg->status }}">{{ ucfirst(str_replace('_', ' ', $msg->status)) }}</span>
            </div>
            <p class="historico-mensagem">{{ $msg->mensagem }}</p>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                @if($msg->status === 'rascunho')
                <form action="{{ route('configuracoes.primeira-mensagem.enviar', $msg) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.8rem;">
                        <i class="fas fa-paper-plane"></i> Enviar para Aprovação
                    </button>
                </form>
                @endif
                @if($msg->status === 'rejeitada')
                <span style="color: #991B1B; font-size: 0.85rem;">
                    <i class="fas fa-times-circle"></i> Motivo: {{ $msg->motivo_rejeicao }}
                </span>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 16px;"></i>
            <p>Nenhuma mensagem criada ainda.</p>
            <p style="font-size: 0.9rem;">Crie sua primeira mensagem acima!</p>
        </div>
        @endforelse
    </div>
</div>

<script>
function gerarComIA() {
    const container = document.getElementById('sugestoesIA');
    const lista = document.getElementById('sugestoesLista');
    const botao = event.target;
    
    botao.innerHTML = '<span class="loading"></span> Gerando...';
    botao.disabled = true;
    
    fetch('{{ route("configuracoes.primeira-mensagem.ia") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            contexto: 'Vendas de planos de gestão ministerial para igrejas'
        })
    })
    .then(res => res.json())
    .then(data => {
        container.style.display = 'block';
        lista.innerHTML = '';
        
        if (data.sugestoes && data.sugestoes.length > 0) {
            data.sugestoes.forEach((sugestao, index) => {
                const div = document.createElement('div');
                div.className = 'sugestao-item';
                div.innerHTML = `
                    <span class="sugestao-texto">${sugestao}</span>
                    <span class="sugestao-usar" onclick="usarSugestao('${sugestao.replace(/'/g, "\\'")}')">Usar</span>
                `;
                lista.appendChild(div);
            });
        } else {
            lista.innerHTML = '<p style="color: var(--text-muted);">Nenhuma sugestão gerada. Tente novamente.</p>';
        }
    })
    .catch(err => {
        lista.innerHTML = '<p style="color: #FF4C51;">Erro ao gerar sugestões. Verifique a configuração da IA.</p>';
    })
    .finally(() => {
        botao.innerHTML = '<i class="fas fa-robot"></i> Gerar com IA';
        botao.disabled = false;
    });
}

function usarSugestao(texto) {
    document.querySelector('textarea[name="mensagem"]').value = texto;
    document.querySelector('input[name="titulo"]').focus();
}
</script>
@endsection