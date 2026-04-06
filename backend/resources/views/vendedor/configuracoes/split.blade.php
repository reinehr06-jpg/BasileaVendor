@extends('layouts.app')
@section('title', 'Split de Pagamento')

@section('content')
<style>
    .split-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        border: 1px solid #ededf2;
        box-shadow: 0 2px 8px rgba(50,50,71,0.06);
        margin-bottom: 20px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6b7280; font-size: 0.9rem; }
    .info-value { font-weight: 700; color: #1e1b4b; font-size: 0.95rem; }
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .status-badge.validado { background: #dcfce7; color: #166534; }
    .status-badge.erro { background: #fef2f2; color: #991b1b; }
    .status-badge.pendente { background: #fef3c7; color: #92400e; }
    .alert {
        padding: 14px 18px;
        border-radius: 10px;
        font-size: 0.88rem;
        margin-bottom: 20px;
    }
    .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .rate-display {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin: 20px 0;
    }
    .rate-box {
        background: #f8f7ff;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .rate-label { font-size: 0.8rem; color: #6b7280; margin-bottom: 8px; }
    .rate-value { font-size: 1.8rem; font-weight: 800; color: #4C1D95; }
    .rate-type { font-size: 0.85rem; color: #a1a1b5; margin-top: 4px; }
    .lock-notice {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 10px;
        padding: 16px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .lock-notice i { color: #f59e0b; font-size: 1.2rem; }
    .lock-notice span { color: #92400e; font-size: 0.88rem; }
</style>

<div class="split-card">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #4C1D95); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-code-branch" style="color: white; font-size: 1.2rem;"></i>
        </div>
        <div>
            <h2 style="font-size: 1.2rem; font-weight: 800; color: #1e1b4b; margin: 0;">Split de Pagamento</h2>
            <p style="color: #6b7280; font-size: 0.85rem; margin: 0;">Valores definidos pelo administrador</p>
        </div>
    </div>

    @if($splitGlobalAtivo)
    
    <div class="rate-display">
        <div class="rate-box">
            <div class="rate-label">Primeira Venda</div>
            <div class="rate-value">
                {{ $vendedor->tipo_split === 'percentual' ? $vendedor->valor_split_inicial . '%' : 'R$ ' . number_format($vendedor->valor_split_inicial, 2, ',', '.') }}
            </div>
            <div class="rate-type">Comissão Atribuída</div>
        </div>
        <div class="rate-box">
            <div class="rate-label">Renovações</div>
            <div class="rate-value">
                {{ $vendedor->tipo_split === 'percentual' ? $vendedor->valor_split_recorrencia . '%' : 'R$ ' . number_format($vendedor->valor_split_recorrencia, 2, ',', '.') }}
            </div>
            <div class="rate-type">Comissão Atribuída</div>
        </div>
        
        @if($vendedor->is_gestor && ($vendedor->comissao_gestor_primeira > 0 || $vendedor->comissao_gestor_recorrencia > 0))
        <div class="rate-box" style="border-color: #f59e0b; background: #fffbeb;">
            <div class="rate-label" style="color: #92400e;">Comissão de Gestor</div>
            <div class="rate-value" style="color: #f59e0b;">
                {{ $vendedor->comissao_gestor_primeira }}%
            </div>
            <div class="rate-type">1ª Venda Equipe</div>
            <div style="margin-top: 8px; font-size: 0.85rem; color: #f59e0b; font-weight: 600;">
                {{ $vendedor->comissao_gestor_recorrencia }}% Renovações
            </div>
        </div>
        @endif
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    <h3 style="font-size: 1.05rem; font-weight: 800; color: #1e1b4b; margin-bottom: 16px;">Sua Carteira Asaas (Wallet ID)</h3>

    @if($vendedor->wallet_status === 'validado')
        <div class="info-row">
            <span class="info-label">Wallet ID Asaas</span>
            <span class="info-value" style="font-family: monospace; font-size: 0.85rem;">{{ $vendedor->asaas_wallet_id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status da Carteira</span>
            <span><span class="status-badge validado">✅ Validada</span></span>
        </div>
        @if($vendedor->wallet_validado_em)
        <div class="info-row">
            <span class="info-label">Última Validação</span>
            <span class="info-value">{{ $vendedor->wallet_validado_em->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        <div class="lock-notice">
            <i class="fas fa-lock"></i>
            <span>Sua carteira foi validada. Para alteração, entre em contato com o suporte.</span>
        </div>
    @else
        <form action="{{ route('configuracoes.split.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Insira sua Wallet ID gerada no Asaas <span class="required">*</span></label>
                <input type="text" name="asaas_wallet_id" class="form-control" placeholder="Ex: b655eecb-ef79-4d2b..." value="{{ old('asaas_wallet_id', $vendedor->asaas_wallet_id) }}" required>
                <div class="field-hint" style="margin-top: 6px;">Ex: aca61e3d-dcb7-4d6b-acb4-022e3e...</div>
            </div>
            
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span class="status-badge {{ $vendedor->wallet_status ?? 'pendente' }}">
                    @if($vendedor->wallet_status === 'erro') ❌ Erro na Validação, verifique
                    @else ⏳ Aguardando Envio/Validação
                    @endif
                </span>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Salvar Wallet ID</button>
            </div>
        </form>
    @endif

    @else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
        Split global ainda não foi ativado pelo administrador.
    </div>
    @endif
</div>

<div class="split-card">
    <h3 style="font-size: 1rem; font-weight: 700; color: #1e1b4b; margin-bottom: 16px;">
        <i class="fas fa-info-circle" style="color: #4C1D95; margin-right: 8px;"></i>
        Como funciona o Split?
    </h3>
    <ul style="color: #6b7280; font-size: 0.88rem; line-height: 1.8; padding-left: 20px;">
        <li>O split divide automaticamente o valor do pagamento entre você e a administradora.</li>
        <li>Você recebe diretamente na sua conta Asaas configurada.</li>
        <li>Não precisa emitir notas fiscais para receber suas comissões.</li>
        <li>Os valores definidos aqui foram configurados pelo administrador.</li>
    </ul>
</div>

@endsection
