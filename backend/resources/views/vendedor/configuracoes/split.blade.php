@extends('layouts.app')
@section('title', 'Comissões e Repasse')

@section('content')
<style>
    .page-header { margin-bottom: 24px; }
    .page-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
    .page-header .subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; }

    .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
    .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); background: #f8fafc; }
    .card-header h3 { font-size: 1.1rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px; }
    .card-body { padding: 24px; }

    .rates-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 20px; }
    .rate-card { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 16px; text-align: center; }
    .rate-card .rate-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 6px; }
    .rate-card .rate-value { font-size: 1.4rem; font-weight: 800; color: var(--primary); }
    .rate-card .rate-tag { display: inline-block; margin-top: 6px; font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; font-weight: 600; }
    .rate-tag.vendedor { background: #dbeafe; color: #1e40af; }
    .rate-tag.gestor { background: #fef3c7; color: #92400e; }
    .rate-tag.split { background: #dcfce7; color: #166534; }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; color: var(--text-main); }
    .form-group input { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; }
    .form-group input:disabled { background: #f1f5f9; cursor: not-allowed; }
    .help-text { display: block; margin-top: 6px; font-size: 0.8rem; color: var(--text-muted); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }

    .checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600; font-size: 0.95rem; }
    .checkbox-label input[type="checkbox"] { width: 20px; height: 20px; accent-color: var(--primary); }

    .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; transition: 0.2s; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }

    .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; }
    .alert-success { background: #dcfce7; color: #166534; border-left: 4px solid #22c55e; }
    .alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
    .alert-warning { background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }
    .alert-info { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }

    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .status-badge.pendente { background: #fef3c7; color: #92400e; }
    .status-badge.validado { background: #dcfce7; color: #166534; }
    .status-badge.erro { background: #fee2e2; color: #991b1b; }

    .info-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
    .info-box h4 { font-size: 0.9rem; color: #0369a1; margin-bottom: 8px; }
    .info-box ul { margin: 0; padding-left: 20px; color: #0c4a6e; font-size: 0.85rem; }
    .info-box li { margin-bottom: 4px; }

    .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); }

    .section-divider { border: none; border-top: 1px solid var(--border); margin: 24px 0; }
</style>

<div class="page-header">
    <h2>💰 Comissões e Repasse</h2>
    <p class="subtitle">Configure sua carteira Asaas para receber repasses automáticos.</p>
</div>

@if(session('success'))
<div class="alert alert-success">✅ {{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert alert-error">
    @foreach($errors->all() as $error)
        <div>❌ {{ $error }}</div>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3>📊 Suas Comissões e Repasses</h3>
    </div>
    <div class="card-body">
        <div class="rates-grid">
            <div class="rate-card">
                <div class="rate-label">Vendedor - 1ª Venda</div>
                <div class="rate-value">{{ $vendedor->comissao_inicial ?? $vendedor->comissao ?? 10 }}%</div>
                <span class="rate-tag vendedor">Comissão</span>
            </div>
            <div class="rate-card">
                <div class="rate-label">Vendedor - Recorrência</div>
                <div class="rate-value">{{ $vendedor->comissao_recorrencia ?? $vendedor->comissao ?? 10 }}%</div>
                <span class="rate-tag vendedor">Comissão</span>
            </div>
            @if($vendedor->is_gestor)
            <div class="rate-card">
                <div class="rate-label">Gestor - 1ª Venda</div>
                <div class="rate-value">{{ $vendedor->comissao_gestor_primeira ?? 0 }}%</div>
                <span class="rate-tag gestor">Comissão Gestão</span>
            </div>
            <div class="rate-card">
                <div class="rate-label">Gestor - Recorrência</div>
                <div class="rate-value">{{ $vendedor->comissao_gestor_recorrencia ?? 0 }}%</div>
                <span class="rate-tag gestor">Comissão Gestão</span>
            </div>
            @endif
            <div class="rate-card">
                <div class="rate-label">Split - 1ª Venda</div>
                <div class="rate-value">{{ $vendedor->valor_split_inicial ?? 0 }}{{ $vendedor->tipo_split === 'percentual' ? '%' : ' R$' }}</div>
                <span class="rate-tag split">Repasse</span>
            </div>
            <div class="rate-card">
                <div class="rate-label">Split - Recorrência</div>
                <div class="rate-value">{{ $vendedor->valor_split_recorrencia ?? 0 }}{{ $vendedor->tipo_split === 'percentual' ? '%' : ' R$' }}</div>
                <span class="rate-tag split">Repasse</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>🔗 Split Asaas</h3>
    </div>
    <div class="card-body">
        @if($splitGlobalAtivo)
            <div class="info-box">
                <h4>ℹ️ Como funciona</h4>
                <ul>
                    <li>O split envia automaticamente uma parte do pagamento para sua conta Asaas.</li>
                    <li>Obtenha seu Wallet ID em: <strong>Asaas → Minha Conta → Integrações</strong></li>
                    <li>Após configurar, o Master validará sua carteira.</li>
                </ul>
            </div>

            <form action="{{ route('vendedor.configuracoes.split.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="split_ativo" value="1" {{ $vendedor->split_ativo ? 'checked' : '' }}>
                        <span>Ativar Split Automático</span>
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Wallet ID Asaas</label>
                        <input type="text" name="asaas_wallet_id" value="{{ $vendedor->asaas_wallet_id }}" placeholder="wallet_xxxxxxxxxx" {{ $vendedor->wallet_status === 'validado' ? 'disabled' : '' }}>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div style="padding-top: 12px;">
                            <span class="status-badge {{ $vendedor->wallet_status ?? 'pendente' }}">
                                @if($vendedor->wallet_status === 'validado') ✅ Validado
                                @elseif($vendedor->wallet_status === 'erro') ❌ Erro
                                @else ⏳ Aguardando
                                @endif
                            </span>
                            @if($vendedor->wallet_validado_em)
                                <span class="help-text">Última validação: {{ $vendedor->wallet_validado_em->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    @if($vendedor->wallet_status !== 'validado')
                        <button type="submit" class="btn btn-primary">💾 Salvar</button>
                    @else
                        <span class="alert alert-info" style="margin: 0; padding: 12px 16px;">
                            ✅ Carteira validada. Contate o Master para alterações.
                        </span>
                    @endif
                </div>
            </form>
        @else
            <div class="alert alert-warning">⚠️ Split global ainda não foi ativado pelo administrador.</div>
        @endif
    </div>
</div>

@endsection
