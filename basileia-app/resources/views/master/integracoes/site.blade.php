@extends('layouts.app')
@section('title', 'Integrações — Site Contratação')

@section('content')
<div class="integracoes-page">
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light);">
            <h2 style="font-size:1.1rem; font-weight:700;">API Keys</h2>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">Chaves de acesso para o site dash.basileia.global e outros serviços</p>
        </div>
        <div style="padding:20px;">
            <form action="{{ route('master.integracoes.site.keys.store') }}" method="POST" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end; margin-bottom:20px;">
                @csrf
                <div style="flex:2; min-width:200px;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Nome</label>
                    <input type="text" name="name" class="form-control" placeholder="Ex: Basileia Church" required>
                </div>
                <div style="flex:1; min-width:140px;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Serviço</label>
                    <select name="service" class="form-control">
                        <option value="site">Site</option>
                        <option value="eventos">Gestão Comercial (Links)</option>
                        <option value="other">Outro</option>
                    </select>
                </div>
                <div style="flex:1; min-width:100px;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Rate Limit</label>
                    <input type="number" name="rate_limit" class="form-control" value="60" min="1" max="1000">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Gerar Key</button>
            </form>

            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Serviço</th>
                            <th>Key (hash)</th>
                            <th>Último Uso</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apiKeys as $key)
                        <tr>
                            <td>{{ $key->name }}</td>
                            <td><span class="badge badge-info">{{ $key->service }}</span></td>
                            <td><code>{{ substr($key->key, 0, 8) }}...{{ substr($key->key, -4) }}</code></td>
                            <td>{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Nunca' }}</td>
                            <td>
                                <span class="badge badge-{{ $key->active ? 'success' : 'danger' }}">
                                    {{ $key->active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <form action="{{ route('master.integracoes.site.keys.toggle', $key) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $key->active ? 'btn-warning' : 'btn-success' }}" title="{{ $key->active ? 'Desativar' : 'Ativar' }}">
                                        <i class="fas fa-{{ $key->active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('master.integracoes.site.keys.destroy', $key) }}" method="POST" style="display:inline;" onsubmit="return confirm('Remover esta API Key?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remover"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">Nenhuma API Key criada</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light);">
            <h2 style="font-size:1.1rem; font-weight:700;">Webhooks de Saída</h2>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">Configure URLs que receberão notificações de pagamento</p>
        </div>
        <div style="padding:20px;">
            <form action="{{ route('master.integracoes.site.webhooks.store') }}" method="POST" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end; margin-bottom:20px;">
                @csrf
                <div style="flex:1; min-width:150px;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Nome</label>
                    <input type="text" name="name" class="form-control" placeholder="Ex: Church Callback" required>
                </div>
                <div style="flex:2; min-width:200px;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">URL</label>
                    <input type="url" name="url" class="form-control" placeholder="https://seu-site.com/webhook" required>
                </div>
                <div style="flex:2; min-width:200px;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Eventos</label>
                    <select name="events[]" class="form-control" multiple style="height:80px;">
                        <option value="payment.confirmed">payment.confirmed</option>
                        <option value="payment.refused">payment.refused</option>
                        <option value="payment.pending">payment.pending</option>
                        <option value="payment.overdue">payment.overdue</option>
                        <option value="payment.refunded">payment.refunded</option>
                    </select>
                </div>
                <div style="flex:1; min-width:150px;">
                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:4px;">Secret (HMAC)</label>
                    <input type="text" name="secret" class="form-control" placeholder="Opcional">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Adicionar</button>
            </form>

            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>URL</th>
                            <th>Eventos</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($webhooks as $wh)
                        <tr>
                            <td>{{ $wh->name }}</td>
                            <td><code style="font-size:0.8rem;">{{ Str::limit($wh->url, 40) }}</code></td>
                            <td>
                                @foreach($wh->events as $evt)
                                    <span class="badge badge-info" style="margin:2px;">{{ $evt }}</span>
                                @endforeach
                            </td>
                            <td><span class="badge badge-{{ $wh->active ? 'success' : 'danger' }}">{{ $wh->active ? 'Ativo' : 'Inativo' }}</span></td>
                            <td>
                                <form action="{{ route('master.integracoes.site.webhooks.destroy', $wh) }}" method="POST" onsubmit="return confirm('Remover este webhook?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">Nenhum webhook configurado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="padding:20px; border-bottom:1px solid var(--border-light);">
            <h2 style="font-size:1.1rem; font-weight:700;">Documentação da API</h2>
        </div>
        <div style="padding:20px;">
            <h3 style="font-size:0.95rem; margin-bottom:12px;">Endpoint: Criar Sessão de Checkout</h3>
            <div style="background:#1e1e2e; color:#cdd6f4; padding:16px; border-radius:8px; font-family:monospace; font-size:0.85rem; margin-bottom:16px; overflow-x:auto;">
POST {{ url('/api/checkout/session') }}
Headers:
  Content-Type: application/json
  X-API-Key: bv_sua_chave_aqui

Body (Plano):
{
  "plan_id": 1,
  "billing_type": "CREDIT_CARD",
  "installments": 12,
  "customer": {
    "name": "João Silva",
    "email": "joao@email.com",
    "document": "123.456.789-00",
    "phone": "11999999999"
  }
}

Body (Evento):
{
  "evento_slug": "lancamento-2026",
  "billing_type": "PIX",
  "customer": {
    "name": "Maria Santos",
    "email": "maria@email.com",
    "document": "987.654.321-00"
  }
}
            </div>

            <h3 style="font-size:0.95rem; margin-bottom:12px;">Endpoint: Consultar Sessão</h3>
            <div style="background:#1e1e2e; color:#cdd6f4; padding:16px; border-radius:8px; font-family:monospace; font-size:0.85rem; overflow-x:auto;">
GET {{ url('/api/checkout/session/{session_id}') }}
Headers:
  X-API-Key: bv_sua_chave_aqui
            </div>
        </div>
    </div>
</div>
@endsection
