@extends('layouts.app')

@section('title', 'Calendário Geral')

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
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
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
        background: var(--surface-hover, #F8FAFC);
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

    .evento-status {
        font-size: 0.7rem; padding: 3px 8px; border-radius: 6px;
        display: inline-block; margin-left: 6px;
    }
    .status-agendado { background: rgba(37, 99, 235, 0.1); color: #2563EB; }
    .status-concluido { background: rgba(5, 150, 105, 0.1); color: #059669; }
    .status-cancelado { background: rgba(220, 38, 38, 0.1); color: #DC2626; }
    .status-faltou { background: rgba(217, 119, 6, 0.1); color: #D97706; }

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
    .btn-primary:hover { background: var(--primary-dark, #3B0E7A); }

    .kpis-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .kpi-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        border: 1px solid var(--border-light);
        text-align: center;
    }
    .kpi-value { font-size: 1.8rem; font-weight: 800; color: var(--primary); }
    .kpi-label { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; }
</style>

<div class="calendario-page">
    <div class="page-header">
        <h1 class="page-title">📅 Calendário Geral</h1>
        <button class="btn btn-primary" onclick="novoEvento()">
            <i class="fas fa-plus"></i> Novo Evento
        </button>
    </div>

    {{-- KPIs --}}
    <div class="kpis-row">
        <div class="kpi-card">
            <div class="kpi-value">{{ $eventos->count() }}</div>
            <div class="kpi-label">Total de Eventos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $eventos->where('status', 'agendado')->count() }}</div>
            <div class="kpi-label">Agendados</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $eventos->where('status', 'concluido')->count() }}</div>
            <div class="kpi-label">Concluídos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $eventos->where('data_hora_inicio', '>=', now())->where('data_hora_inicio', '<=', now()->addDays(7))->count() }}</div>
            <div class="kpi-label">Próximos 7 dias</div>
        </div>
    </div>

    <div class="filtros">
        <select class="filtro-select" id="filtroTipo" onchange="filtrar()">
            <option value="">Todos os Tipos</option>
            <option value="follow_up">Follow-up</option>
            <option value="reuniao">Reunião</option>
            <option value="lembrete">Lembrete</option>
            <option value="vencimento">Vencimento</option>
        </select>
        <select class="filtro-select" id="filtroStatus" onchange="filtrar()">
            <option value="">Todos os Status</option>
            <option value="agendado">Agendado</option>
            <option value="concluido">Concluído</option>
            <option value="cancelado">Cancelado</option>
            <option value="faltou">Faltou</option>
        </select>
    </div>

    <div class="equipe-grid">
        @forelse($eventos->groupBy('user_id') as $userId => $eventosUsuario)
        @php $usuario = $eventosUsuario->first()->usuario; @endphp
        <div class="vendedor-card" data-user="{{ $userId }}">
            <div class="vendedor-header">
                <div class="vendedor-avatar">{{ $usuario ? substr($usuario->name, 0, 2) : '??' }}</div>
                <div>
                    <div class="vendedor-nome">{{ $usuario->name ?? 'Desconhecido' }}</div>
                    <div class="vendedor-funcao">{{ $eventosUsuario->count() }} evento(s)</div>
                </div>
            </div>
            <div class="eventos-lista">
                @foreach($eventosUsuario as $evento)
                <div class="evento-item" data-tipo="{{ $evento->tipo }}" data-status="{{ $evento->status }}">
                    <div class="evento-horario">
                        <div class="evento-hora">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('H:i') }}</div>
                        <div class="evento-data">{{ \Carbon\Carbon::parse($evento->data_hora_inicio)->format('d/m') }}</div>
                    </div>
                    <div class="evento-info">
                        <div class="evento-titulo">{{ $evento->titulo }}</div>
                        <span class="evento-badge badge-{{ $evento->tipo }}">{{ ucfirst(str_replace('_', ' ', $evento->tipo)) }}</span>
                        <span class="evento-status status-{{ $evento->status }}">{{ ucfirst($evento->status) }}</span>
                        @if($evento->contato)
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                <i class="fas fa-user"></i> {{ $evento->contato->nome }}
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="vendedor-card" style="grid-column: 1 / -1;">
            <div class="sem-eventos">
                <i class="fas fa-calendar" style="font-size: 2rem; margin-bottom: 8px;"></i>
                <p>Nenhum evento cadastrado</p>
            </div>
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

function filtrar() {
    const tipo = document.getElementById('filtroTipo').value;
    const status = document.getElementById('filtroStatus').value;

    document.querySelectorAll('.evento-item').forEach(item => {
        let show = true;
        if (tipo && item.dataset.tipo !== tipo) show = false;
        if (status && item.dataset.status !== status) show = false;
        item.style.display = show ? 'flex' : 'none';
    });
}

document.getElementById('eventoModal').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>
@endsection
