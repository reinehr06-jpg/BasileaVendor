@extends('layouts.app')
@section('title', 'Integrações — Eventos / Links')

@section('content')
<div class="integracoes-page">
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 style="font-size:1.1rem; font-weight:700;">Criar Evento</h2>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">Gere links de pagamento com vagas limitadas</p>
            </div>
        </div>
        <form action="{{ route('master.integracoes.eventos.store') }}" method="POST" style="padding:20px;">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div style="grid-column:1/-1;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Título do Evento *</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ex: Live de Lançamento 2026" required>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="2" placeholder="Breve descrição do evento..."></textarea>
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Valor (R$) *</label>
                    <input type="number" name="valor" step="0.01" min="0.01" class="form-control" placeholder="197.00" required>
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Total de Vagas *</label>
                    <input type="number" name="vagas_total" min="1" max="10000" class="form-control" placeholder="10" required>
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">WhatsApp do Vendedor *</label>
                    <input type="text" name="whatsapp_vendedor" class="form-control" placeholder="5511999999999" required>
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Telefone (opcional)</label>
                    <input type="text" name="telefone_vendedor" class="form-control" placeholder="5511999999999">
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Início</label>
                    <input type="datetime-local" name="data_inicio" class="form-control">
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Fim</label>
                    <input type="datetime-local" name="data_fim" class="form-control">
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Criar Evento e Gerar Link</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light);">
            <h2 style="font-size:1.1rem; font-weight:700;">Eventos Criados</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Valor</th>
                        <th>Vagas</th>
                        <th>Status</th>
                        <th>Link</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventos as $evento)
                    <tr>
                        <td>
                            <strong>{{ $evento->titulo }}</strong>
                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ $evento->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>R$ {{ number_format($evento->valor, 2, ',', '.') }}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="flex:1; height:6px; background:var(--border); border-radius:3px; overflow:hidden;">
                                    <div style="width:{{ $evento->vagas_total > 0 ? ($evento->vagas_ocupadas / $evento->vagas_total * 100) : 0 }}%; height:100%; background:{{ $evento->vagas_ocupadas >= $evento->vagas_total ? '#ef4444' : '#10b981' }}; border-radius:3px;"></div>
                                </div>
                                <span style="font-size:0.8rem; font-weight:600; white-space:nowrap;">{{ $evento->vagas_ocupadas }}/{{ $evento->vagas_total }}</span>
                            </div>
                        </td>
                        <td>
                            @if($evento->status === 'ativo')
                                <span class="badge badge-success">Ativo</span>
                            @elseif($evento->status === 'esgotado')
                                <span class="badge badge-danger">Esgotado</span>
                            @else
                                <span class="badge badge-secondary">Expirado</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <code style="font-size:0.75rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $evento->checkout_url }}</code>
                                <button type="button" class="btn btn-sm btn-outline" onclick="navigator.clipboard.writeText('{{ $evento->checkout_url }}'); this.innerHTML='<i class=\'fas fa-check\'></i>';" title="Copiar link">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td style="white-space:nowrap;">
                            @if($evento->status !== 'esgotado')
                            <form action="{{ route('master.integracoes.eventos.toggle', $evento) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $evento->status === 'ativo' ? 'btn-warning' : 'btn-success' }}" title="{{ $evento->status === 'ativo' ? 'Expirar' : 'Reativar' }}">
                                    <i class="fas fa-{{ $evento->status === 'ativo' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('master.integracoes.eventos.destroy', $evento) }}" method="POST" style="display:inline;" onsubmit="return confirm('Remover este evento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Remover"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">
                        <i class="fas fa-link" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                        Nenhum evento criado. Use o formulário acima para criar o primeiro.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($eventos->hasPages())
        <div style="padding:16px 20px; border-top:1px solid var(--border-light);">
            {{ $eventos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
