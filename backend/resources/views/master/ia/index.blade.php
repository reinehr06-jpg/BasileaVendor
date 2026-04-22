@extends('layouts.app')
@section('title', 'Inteligência Artificial - Logs e Métricas')

@section('content')
<style>
    .ia-tabs { display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 2px; }
    .ia-tab-btn {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 20px; border: none; background: transparent;
        color: var(--text-muted); font-weight: 600; font-size: 0.9rem;
        cursor: pointer; transition: all 0.2s; border-radius: 8px 8px 0 0;
    }
    .ia-tab-btn:hover { color: var(--primary); background: rgba(var(--primary-rgb), 0.05); }
    .ia-tab-btn.active { color: var(--primary); background: #f4efff; border-bottom: 2px solid var(--primary); }

    .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
    .badge-sucesso { background: #dcfce7; color: #166534; }
    .badge-erro { background: #fee2e2; color: #991b1b; }
    .badge-tarefa { background: #f4efff; color: var(--primary); }

    .usuario-card {
        display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc;
        border-radius: 8px; margin-bottom: 8px; border: 1px solid var(--border);
    }
    .usuario-avatar {
        width: 40px; height: 40px; border-radius: 50%; background: var(--primary);
        color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;
    }
    .usuario-info { flex: 1; }
    .usuario-nome { font-weight: 700; color: var(--text-primary); }
    .usuario-perfil { font-size: 0.8rem; color: var(--text-muted); }
    .usuario-stats { text-align: right; }
    .usuario-total { font-weight: 700; font-size: 1.1rem; }
    .usuario-tempo { font-size: 0.8rem; color: var(--text-muted); }
</style>

<x-page-hero 
    title="Ia Lab" 
    subtitle="Monitoramento de Inteligência Artificial e Logs de Operação" 
    icon="fas fa-microchip"
/>

<!-- KPIs -->
<div class="stats-bar">
    <div class="stat-card" style="background: var(--primary); border-color: var(--primary);">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-bolt"></i></div>
        <div class="stat-value" style="color: white;">{{ $stats['total'] }}</div>
        <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total Chamadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ $stats['taxaSucesso'] }}%</div>
        <div class="stat-label">Sucesso</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--info);">{{ $stats['mediaTempo'] }}ms</div>
        <div class="stat-label">Tempo Médio</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $stats['erros'] }}</div>
        <div class="stat-label">Erros</div>
    </div>
</div>

<!-- Abas -->
<div class="ia-tabs mt-4">
    <button class="ia-tab-btn active" onclick="mostrarAba('logs')">
        <i class="fas fa-list"></i> Logs Detalhados
    </button>
    <button class="ia-tab-btn" onclick="mostrarAba('usuarios')">
        <i class="fas fa-users"></i> Uso por Usuário
    </button>
    <button class="ia-tab-btn" onclick="mostrarAba('tarefas')">
        <i class="fas fa-tasks"></i> Ranking de Tarefas
    </button>
</div>

<!-- Aba: Logs -->
<div id="aba-logs" class="ia-tab-content">
    <form method="GET" action="{{ route('master.ia') }}">
    <div class="filters-bar">
        <select name="filtro" class="filter-select" onchange="this.form.submit()">
            <option value="todos" {{ $filtro === 'todos' ? 'selected' : '' }}>Todos Perfis</option>
            <option value="gestor" {{ $filtro === 'gestor' ? 'selected' : '' }}>Gestores</option>
            <option value="vendedor" {{ $filtro === 'vendedor' ? 'selected' : '' }}>Vendedores</option>
            <option value="erros" {{ $filtro === 'erros' ? 'selected' : '' }}>Apenas Erros</option>
        </select>
        <input type="date" name="data_inicio" class="filter-select" value="{{ $dataInicio }}" onchange="this.form.submit()" placeholder="Início">
        <input type="date" name="data_fim" class="filter-select" value="{{ $dataFim }}" onchange="this.form.submit()" placeholder="Fim">
        <select name="tarefa" class="filter-select" onchange="this.form.submit()">
            <option value="todos">Todas Tarefas</option>
            <option value="sugestao_resposta" {{ $tarefa === 'sugestao_resposta' ? 'selected' : '' }}>Sugestão Resposta</option>
            <option value="score_lead" {{ $tarefa === 'score_lead' ? 'selected' : '' }}>Score Lead</option>
            <option value="motivo_perda" {{ $tarefa === 'motivo_perda' ? 'selected' : '' }}>Motivo Perda</option>
            <option value="resumo_conversa" {{ $tarefa === 'resumo_conversa' ? 'selected' : '' }}>Resumo Conversa</option>
            <option value="proxima_acao" {{ $tarefa === 'proxima_acao' ? 'selected' : '' }}>Próxima Ação</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Usuário</th>
                    <th>Tarefa</th>
                    <th>Duração</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($log->executado_em)->format('d/m/Y H:i') }}</td>
                    <td>
                        <div style="font-weight: 600;">{{ $log->user_name ?? 'Sistema' }}</div>
                        <small class="text-muted">{{ $log->user_email }}</small>
                    </td>
                    <td><span class="badge-status badge-tarefa">{{ ucfirst(str_replace('_', ' ', $log->tarefa)) }}</span></td>
                    <td>{{ $log->duracao_ms ?? '-' }} ms</td>
                    <td>
                        @if($log->sucesso)
                            <span class="badge-status badge-sucesso">✓ Sucesso</span>
                        @else
                            <span class="badge-status badge-erro">✗ Erro</span>
                            @if($log->erro)
                                <div style="font-size: 0.7rem; color: #dc2626; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $log->erro }}">
                                    {{ $log->erro }}
                                </div>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">Nenhum log encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Aba: Por Usuário -->
<div id="aba-usuarios" class="ia-tab-content" style="display: none;">
    <div class="table-container p-4">
        @forelse($usuarios as $usuario)
        <div class="usuario-card">
            <div class="usuario-avatar">{{ strtoupper(substr($usuario->name ?? 'S', 0, 2)) }}</div>
            <div class="usuario-info">
                <div class="usuario-nome">{{ $usuario->name ?? 'Desconhecido' }}</div>
                <div class="usuario-perfil">{{ ucfirst($usuario->perfil ?? '-') }}</div>
            </div>
            <div class="usuario-stats">
                <div class="usuario-total">{{ $usuario->total_chamadas }} chamadas</div>
                <div class="usuario-tempo">Média: {{ round($usuario->tempo_medio ?? 0) }}ms</div>
            </div>
        </div>
        @empty
        <div class="text-center py-4">Nenhum dado de usuário.</div>
        @endforelse
    </div>
</div>

<!-- Aba: Tarefas -->
<div id="aba-tarefas" class="ia-tab-content" style="display: none;">
    <div class="table-container p-4">
        @forelse($tarefasPopulares as $tarefa)
        <div class="usuario-card">
            <div class="usuario-info">
                <div class="usuario-nome">{{ ucfirst(str_replace('_', ' ', $tarefa->tarefa)) }}</div>
            </div>
            <div class="usuario-stats">
                <div class="usuario-total">{{ $tarefa->total }}</div>
                <div class="usuario-tempo">requisições</div>
            </div>
        </div>
        @empty
        <div class="text-center py-4">Nenhuma tarefa registrada.</div>
        @endforelse
    </div>
</div>

<script>
function mostrarAba(aba) {
    document.querySelectorAll('.ia-tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.ia-tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('aba-' + aba).style.display = 'block';
    event.currentTarget.classList.add('active');
}
</script>
@endsection