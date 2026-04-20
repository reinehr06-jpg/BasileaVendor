@extends('layouts.app')

@section('title', 'Calendário da Equipe')

@section('content')
<style>
    .calendario-page { max-width: 1400px; margin: 0 auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
    
    .filtros { 
        display: flex; gap: 12px; margin-bottom: 24px; 
        background: white; padding: 16px; border-radius: 12px;
        border: 1px solid var(--border-light);
    }
    .filtro-select {
        padding: 10px 16px; border: 1px solid #E2E8F0; border-radius: 8px;
        font-size: 0.9rem; min-width: 180px;
    }
    
    .equipe-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }
    
    .vendedor-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
    }
    .vendedor-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-light);
    }
    .vendedor-avatar {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, var(--primary) 0%, #7C3AED 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .vendedor-nome { font-weight: 700; color: var(--text-primary); }
    .vendedor-funcao { font-size: 0.8rem; color: var(--text-muted); }
    
    .eventos-lista { max-height: 300px; overflow-y: auto; }
    
    .evento-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--surface-hover);
        border-radius: 10px;
        margin-bottom: 8px;
        border: 1px solid var(--border-light);
    }
    .evento-horario {
        min-width: 60px;
        text-align: center;
    }
    .evento-hora { font-weight: 700; color: var(--primary); font-size: 0.9rem; }
    .evento-data { font-size: 0.7rem; color: var(--text-muted); }
    
    .evento-info { flex: 1; }
    .evento-titulo { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
    
    .evento-badge {
        font-size: 0.7rem; padding: 3px 8px; border-radius: 6px;
        display: inline-block; margin-top: 4px;
    }
    .badge-follow_up { background: rgba(76, 29, 149, 0.1); color: var(--primary); }
    .badge-reuniao { background: rgba(22, 177, 255, 0.1); color: #16B1FF; }
    .badge-lembrete { background: rgba(255, 180, 0, 0.1); color: #FFB400; }
    .badge-vencimento { background: rgba(255, 76, 81, 0.1); color: #FF4C51; }
    
    .sem-eventos {
        text-align: center;
        padding: 30px;
        color: var(--text-muted);
    }
    
    .btn {
        padding: 10px 18px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-dark); }
</style>

<div class="calendario-page">
    <div class="page-header">
        <h1 class="page-title">📅 Calendário da Equipe</h1>
        <button class="btn btn-primary" onclick="novoEvento()">
            <i class="fas fa-plus"></i> Novo Evento
        </button>
    </div>
    
    <div class="filtros">
        <select class="filtro-select" onchange="filtrarVendedor(this.value)">
            <option value="">Todos os Vendedores</option>
            @foreach($vendedores as $vendedor)
                <option value="{{ $vendedor->id }}">{{ $vendedor->user->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="equipe-grid">
        {{-- Meus Eventos --}}
        <div class="vendedor-card">
            <div class="vendedor-header">
                <div class="vendedor-avatar">{{ substr(Auth::user()->name, 0, 2) }}</div>
                <div>
                    <div class="vendedor-nome">{{ Auth::user()->name }}</div>
                    <div class="vendedor-funcao">Meus Eventos</div>
                </div>
            </div>
            <div class="eventos-lista">
                @forelse($meusEventos as $evento)
                <div class="evento-item">
                    <div class="evento-horario">
                        <div class="evento-hora">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('H:i') }}</div>
                        <div class="evento-data">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('d/m') }}</div>
                    </div>
                    <div class="evento-info">
                        <div class="evento-titulo">{{ $evento->titulo }}</div>
                        <span class="evento-badge badge-{{ $evento->tipo }}">{{ ucfirst($evento->tipo) }}</span>
                    </div>
                </div>
                @empty
                <div class="sem-eventos">
                    <i class="fas fa-calendar" style="font-size: 2rem; margin-bottom: 8px;"></i>
                    <p>Nenhum evento</p>
                </div>
                @endforelse
            </div>
        </div>
        
        {{-- Eventos da Equipe --}}
        @foreach($vendedores as $vendedor)
        <div class="vendedor-card" data-vendedor="{{ $vendedor->id }}">
            <div class="vendedor-header">
                <div class="vendedor-avatar">{{ substr($vendedor->user->name, 0, 2) }}</div>
                <div>
                    <div class="vendedor-nome">{{ $vendedor->user->name }}</div>
                    <div class="vendedor-funcao">Vendedor</div>
                </div>
            </div>
            <div class="eventos-lista">
                @php
                    $eventosVendedor = $eventosEquipe->where('user_id', $vendedor->user_id);
                @endphp
                @forelse($eventosVendedor as $evento)
                <div class="evento-item">
                    <div class="evento-horario">
                        <div class="evento-hora">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('H:i') }}</div>
                        <div class="evento-data">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('d/m') }}</div>
                    </div>
                    <div class="evento-info">
                        <div class="evento-titulo">{{ $evento->titulo }}</div>
                        <span class="evento-badge badge-{{ $evento->tipo }}">{{ ucfirst($evento->tipo) }}</span>
                    </div>
                </div>
                @empty
                <div class="sem-eventos">
                    <i class="fas fa-calendar" style="font-size: 2rem; margin-bottom: 8px;"></i>
                    <p>Nenhum evento</p>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
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
                    <option value="reuniao">Reunião</option>
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

function filtrarVendedor(id) {
    const cards = document.querySelectorAll('.vendedor-card');
    cards.forEach(card => {
        if (!id || card.dataset.vendedor === id) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

document.getElementById('eventoModal').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>
@endsection