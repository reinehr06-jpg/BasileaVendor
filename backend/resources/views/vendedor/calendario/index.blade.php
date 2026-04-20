@extends('layouts.app')

@section('title', 'Meu Calendário')

@section('content')
<style>
    .calendario-page { max-width: 1200px; margin: 0 auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
    
    .calendario-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        margin-bottom: 28px;
    }
    .dia-cabecalho {
        text-align: center;
        padding: 12px;
        font-weight: 700;
        color: var(--text-muted);
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .dia {
        background: white;
        border-radius: 12px;
        min-height: 120px;
        padding: 10px;
        border: 1px solid var(--border-light);
        cursor: pointer;
        transition: all 0.2s;
    }
    .dia:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(76, 29, 149, 0.1); }
    .dia-numero { font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .dia-hoje .dia-numero { 
        background: var(--primary); 
        color: white; 
        width: 28px; height: 28px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center;
    }
    .dia-outro-mes { opacity: 0.4; }
    
    .evento-chip {
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 6px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: white;
        cursor: pointer;
    }
    .evento-follow_up { background: var(--primary); }
    .evento-reuniao { background: #16B1FF; }
    .evento-lembrete { background: #FFB400; }
    .evento-vencimento { background: #FF4C51; }
    
    .proximos-eventos {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
    }
    .proximos-header { 
        display: flex; justify-content: space-between; align-items: center; 
        margin-bottom: 20px;
    }
    .proximos-title { font-weight: 700; font-size: 1.1rem; color: var(--text-primary); }
    
    .evento-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: var(--surface-hover);
        border-radius: 12px;
        margin-bottom: 12px;
        border: 1px solid var(--border-light);
    }
    .evento-horario {
        min-width: 80px;
        text-align: center;
    }
    .evento-hora { font-weight: 700; color: var(--text-primary); }
    .evento-data { font-size: 0.75rem; color: var(--text-muted); }
    
    .evento-info { flex: 1; }
    .evento-titulo { font-weight: 600; color: var(--text-primary); }
    .evento-tipo { 
        font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; 
        display: inline-block; margin-top: 4px;
    }
    
    .evento-acoes { display: flex; gap: 8px; }
    
    .btn {
        padding: 8px 14px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.85rem;
    }
    .btn-success { background: #D1FAE5; color: #065F46; }
    .btn-success:hover { background: #A7F3D0; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-dark); }
</style>

<div class="calendario-page">
    <div class="page-header">
        <h1 class="page-title">📅 Meu Calendário</h1>
        <button class="btn btn-primary" onclick="novoEvento()">
            <i class="fas fa-plus"></i> Novo Evento
        </button>
    </div>
    
    <div class="proximos-eventos">
        <div class="proximos-header">
            <h2 class="proximos-title">Próximos Eventos</h2>
            <span style="color: var(--text-muted); font-size: 0.9rem;">Próximos 7 dias</span>
        </div>
        
        @forelse($eventos as $evento)
        <div class="evento-item">
            <div class="evento-horario">
                <div class="evento-hora">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('H:i') }}</div>
                <div class="evento-data">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('d/m') }}</div>
            </div>
            <div class="evento-info">
                <div class="evento-titulo">{{ $evento->titulo }}</div>
                <span class="evento-tipo evento-{{ $evento->tipo }}">
                    {{ ucfirst($evento->tipo) }}
                </span>
            </div>
            <div class="evento-acoes">
                @if($evento->status === 'agendado')
                <form action="{{ route('calendario.concluir', $evento) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" title="Marcar como concluído">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fas fa-calendar-xmark" style="font-size: 3rem; margin-bottom: 16px;"></i>
            <p>Nenhum evento agendado.</p>
            <button class="btn btn-primary" onclick="novoEvento()">Criar primeiro evento</button>
        </div>
        @endforelse
    </div>
</div>

<div class="modal" id="eventoModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--border-light);">
            <h3 style="font-size: 1.2rem; font-weight: 700;">Novo Evento</h3>
            <button onclick="fecharModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('calendario.store') }}" method="POST" style="padding: 24px;">
            @csrf
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Título *</label>
                <input type="text" name="titulo" required style="width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px;">
            </div>
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Tipo *</label>
                <select name="tipo" required style="width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px;">
                    <option value="follow_up">Follow-up</option>
                    <option value="reunião">Reunião</option>
                    <option value="lembrete">Lembrete</option>
                    <option value="vencimento">Vencimento</option>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Data/Hora Início *</label>
                    <input type="datetime-local" name="data_hora_inicio" required style="width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Data/Hora Fim</label>
                    <input type="datetime-local" name="data_hora_fim" style="width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px;">
                </div>
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Descrição</label>
                <textarea name="descricao" rows="3" style="width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 10px;"></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="fecharModal()" class="btn" style="background: #E2E8F0; color: var(--text-primary);">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Evento</button>
            </div>
        </form>
    </div>
</div>

<script>
function novoEvento() {
    document.getElementById('eventoModal').classList.add('active');
}

function fecharModal() {
    document.getElementById('eventoModal').classList.remove('active');
}

document.getElementById('eventoModal').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>
@endsection