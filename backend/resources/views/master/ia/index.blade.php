@extends('layouts.app')

@section('title', 'Inteligência Artificial - Logs e Métricas')

@section('content')
<style>
    :root {
        --materio-primary: #9155FD;
        --materio-primary-light: #F4EFFF;
        --materio-secondary: #8A8D93;
        --materio-bg: #F4F5FA;
        --materio-surface: #FFFFFF;
        --materio-text-main: #4D5156;
        --materio-text-muted: #89898E;
        --materio-border: #E6E6E9;
        --materio-shadow: 0 4px 18px 0 rgba(0,0,0,0.1);
        --materio-radius: 10px;
        --materio-success: #56CA00;
        --materio-info: #16B1FF;
        --materio-warning: #FFB400;
        --materio-error: #FF4C51;
    }

    .ia-page { max-width: 1400px; margin: 0 auto; padding: 24px; }

    .ia-tabs { display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 1px solid var(--materio-border); padding-bottom: 2px; }
    .ia-tab-btn {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 20px; border: none; background: transparent;
        color: var(--materio-text-muted); font-weight: 600; font-size: 0.9rem;
        cursor: pointer; transition: all 0.2s; border-radius: 8px 8px 0 0;
    }
    .ia-tab-btn:hover { color: var(--materio-primary); background: rgba(145,85,253,0.05); }
    .ia-tab-btn.active { color: var(--materio-primary); background: var(--materio-primary-light); }

    .ia-card {
        background: var(--materio-surface); border-radius: var(--materio-radius);
        box-shadow: var(--materio-shadow); padding: 24px; border: 1px solid var(--materio-border); margin-bottom: 20px;
    }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .stat-card {
        background: var(--materio-bg); padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--materio-border);
    }
    .stat-label { font-size: 0.75rem; color: var(--materio-text-muted); text-transform: uppercase; font-weight: 700; }
    .stat-value { font-size: 2rem; font-weight: 800; color: var(--materio-text-main); margin-top: 8px; }
    .stat-value.success { color: var(--materio-success); }
    .stat-value.error { color: var(--materio-error); }

    .filtros-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; align-items: flex-end; }
    .filtro-group { display: flex; flex-direction: column; gap: 4px; }
    .filtro-label { font-size: 0.8rem; font-weight: 600; color: var(--materio-text-main); }
    .filtro-select, .filtro-input { padding: 8px 12px; border: 1px solid var(--materio-border); border-radius: 8px; font-size: 0.9rem; }
    .filtro-btn {
        background: var(--materio-primary); color: white; border: none; padding: 10px 20px;
        border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .filtro-btn:hover { background: #8043e6; transform: translateY(-1px); }

    .table-container { overflow-x: auto; }
    .ia-table { width: 100%; border-collapse: collapse; }
    .ia-table th {
        text-align: left; padding: 14px 12px; background: var(--materio-bg);
        border-bottom: 2px solid var(--materio-border); font-size: 0.75rem;
        text-transform: uppercase; color: var(--materio-text-muted); font-weight: 700;
    }
    .ia-table td { padding: 14px 12px; border-bottom: 1px solid var(--materio-border); font-size: 0.9rem; }
    .ia-table tr:hover { background: rgba(145,85,253,0.03); }

    .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
    .badge-sucesso { background: #dcfce7; color: #166534; }
    .badge-erro { background: #fee2e2; color: #991b1b; }
    .badge-tarefa { background: var(--materio-primary-light); color: var(--materio-primary); }

    .usuario-card {
        display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--materio-bg);
        border-radius: 8px; margin-bottom: 8px; border: 1px solid var(--materio-border);
    }
    .usuario-avatar {
        width: 40px; height: 40px; border-radius: 50%; background: var(--materio-primary);
        color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;
    }
    .usuario-info { flex: 1; }
    .usuario-nome { font-weight: 700; color: var(--materio-text-main); }
    .usuario-perfil { font-size: 0.8rem; color: var(--materio-text-muted); }
    .usuario-stats { text-align: right; }
    .usuario-total { font-weight: 700; font-size: 1.1rem; }
    .usuario-tempo { font-size: 0.8rem; color: var(--materio-text-muted); }

    .empty-state { text-align: center; padding: 60px; color: var(--materio-text-muted); }
</style>

<div class="ia-page">
    <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">
        🤖 Inteligência Artificial
    </h1>

    <!-- Abas -->
    <div class="ia-tabs">
        <button class="ia-tab-btn active" onclick="mostrarAba('logs')">
            <i class="fas fa-list"></i> Logs
        </button>
        <button class="ia-tab-btn" onclick="mostrarAba('usuarios')">
            <i class="fas fa-users"></i> Por Usuário
        </button>
        <button class="ia-tab-btn" onclick="mostrarAba('tarefas')">
            <i class="fas fa-tasks"></i> Tarefas
        </button>
    </div>

    <!-- KPIs -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total de Chamadas</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Taxa de Sucesso</div>
            <div class="stat-value success">{{ $stats['taxaSucesso'] }}%</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Tempo Médio (ms)</div>
            <div class="stat-value">{{ $stats['mediaTempo'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total de Erros</div>
            <div class="stat-value error">{{ $stats['erros'] }}</div>
        </div>
    </div>

    <!-- Aba: Logs -->
    <div id="aba-logs" class="ia-tab-content">
        <div class="ia-card">
            <div class="filtros-row">
                <form method="GET" action="{{ route('master.ia') }}" style="display: flex; gap: 12px; flex-wrap: wrap; width: 100%;">
                    <div class="filtro-group">
                        <label class="filtro-label">Tipo</label>
                        <select name="filtro" class="filtro-select" onchange="this.form.submit()">
                            <option value="todos" {{ $filtro === 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="gestor" {{ $filtro === 'gestor' ? 'selected' : '' }}>Gestores</option>
                            <option value="vendedor" {{ $filtro === 'vendedor' ? 'selected' : '' }}>Vendedores</option>
                            <option value="erros" {{ $filtro === 'erros' ? 'selected' : '' }}>Apenas Erros</option>
                        </select>
                    </div>
                    <div class="filtro-group">
                        <label class="filtro-label">Data Início</label>
                        <input type="date" name="data_inicio" class="filtro-input" value="{{ $dataInicio }}" onchange="this.form.submit()">
                    </div>
                    <div class="filtro-group">
                        <label class="filtro-label">Data Fim</label>
                        <input type="date" name="data_fim" class="filtro-input" value="{{ $dataFim }}" onchange="this.form.submit()">
                    </div>
                    <div class="filtro-group">
                        <label class="filtro-label">Tarefa</label>
                        <select name="tarefa" class="filtro-select" onchange="this.form.submit()">
                            <option value="todos">Todas</option>
                            <option value="sugestao_resposta" {{ $tarefa === 'sugestao_resposta' ? 'selected' : '' }}>Sugestão Resposta</option>
                            <option value="score_lead" {{ $tarefa === 'score_lead' ? 'selected' : '' }}>Score Lead</option>
                            <option value="motivo_perda" {{ $tarefa === 'motivo_perda' ? 'selected' : '' }}>Motivo Perda</option>
                            <option value="resumo_conversa" {{ $tarefa === 'resumo_conversa' ? 'selected' : '' }}>Resumo Conversa</option>
                            <option value="proxima_acao" {{ $tarefa === 'proxima_acao' ? 'selected' : '' }}>Próxima Ação</option>
                            <option value="analise_vendedor" {{ $tarefa === 'analise_vendedor' ? 'selected' : '' }}>Análise Vendedor</option>
                        </select>
                    </div>
                    <button type="submit" class="filtro-btn">Filtrar</button>
                </form>
            </div>

            <div class="table-container">
                <table class="ia-table">
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
                            <td>{{ \Carbon\Carbon::parse($log->executado_em)->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <strong>{{ $log->user_name ?? 'Sistema' }}</strong>
                                @if($log->user_email)
                                    <br><small style="color: var(--materio-text-muted);">{{ $log->user_email }}</small>
                                @endif
                            </td>
                            <td><span class="badge-status badge-tarefa">{{ $log->tarefa }}</span></td>
                            <td>{{ $log->duracao_ms ?? '-' }} ms</td>
                            <td>
                                @if($log->sucesso)
                                    <span class="badge-status badge-sucesso">✓ Sucesso</span>
                                @else
                                    <span class="badge-status badge-erro">✗ Erro</span>
                                    @if($log->erro)
                                        <br><small style="color: var(--materio-error);">{{ $log->erro }}</small>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                Nenhum registro de IA encontrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Aba: Por Usuário -->
    <div id="aba-usuarios" class="ia-tab-content" style="display: none;">
        <div class="ia-card">
            <h3 style="margin-bottom: 16px;">Uso por Usuário</h3>
            @forelse($usuarios as $usuario)
            <div class="usuario-card">
                <div class="usuario-avatar">
                    {{ strtoupper(substr($usuario->name ?? 'S', 0, 2)) }}
                </div>
                <div class="usuario-info">
                    <div class="usuario-nome">{{ $usuario->name ?? 'Desconhecido' }}</div>
                    <div class="usuario-perfil">{{ $usuario->perfil ?? '-' }}</div>
                </div>
                <div class="usuario-stats">
                    <div class="usuario-total">{{ $usuario->total_chamadas }}</div>
                    <div class="usuario-tempo">Média: {{ round($usuario->tempo_medio ?? 0) }}ms</div>
                </div>
            </div>
            @empty
            <div class="empty-state">Nenhum dado de usuário encontrado.</div>
            @endforelse
        </div>
    </div>

    <!-- Aba: Tarefas -->
    <div id="aba-tarefas" class="ia-tab-content" style="display: none;">
        <div class="ia-card">
            <h3 style="margin-bottom: 16px;">Tarefas mais usadas</h3>
            @forelse($tarefasPopulares as $tarefa)
            <div class="usuario-card">
                <div class="usuario-info">
                    <div class="usuario-nome">{{ $tarefa->tarefa }}</div>
                </div>
                <div class="usuario-stats">
                    <div class="usuario-total">{{ $tarefa->total }}</div>
                    <div class="usuario-tempo">chamadas</div>
                </div>
            </div>
            @empty
            <div class="empty-state">Nenhuma tarefa registrada.</div>
            @endforelse
        </div>
    </div>

</div>

<script>
function mostrarAba(aba) {
    document.querySelectorAll('.ia-tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.ia-tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('aba-' + aba).style.display = 'block';
    event.target.classList.add('active');
}
</script>
@endsection