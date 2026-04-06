@extends('layouts.app')
@section('title', 'Links de Pagamento')

@section('content')
<style>
    /* Flatpickr customizado com estilo do Basileia */
    .datepicker {
        background: #fafafa !important;
        border: 1.5px solid #e5e7eb !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-size: 0.9375rem !important;
        width: 100%;
        box-sizing: border-box;
    }
    .datepicker:focus {
        outline: none;
        border-color: #7c3aed !important;
        box-shadow: 0 0 0 3px rgba(124,58,237,0.15) !important;
        background: white !important;
    }
</style>

<!-- Header padrão do sistema -->
<x-page-hero 
    title="Links de Pagamento" 
    subtitle="Gestão comercial e monitoramento de vagas em tempo real." 
    icon="fas fa-link"
/>

@if($config_faltante)
<div class="alert alert-danger animate-up">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        <strong>Configuração do Asaas Pendente</strong>
        <p>Sua API Key não foi configurada. Acesse as configurações para habilitar esta função.</p>
    </div>
    <a href="{{ route('master.configuracoes', ['tab' => 'integracoes']) }}" class="btn btn-sm btn-danger" style="margin-left: auto;">Configurar</a>
</div>
@endif

<!-- Stats Bar oficial do sistema -->
<div class="stats-bar animate-up">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-link"></i></div>
        <div class="stat-value">{{ $eventos->where('status', 'ativo')->count() }}</div>
        <div class="stat-label">Links Ativos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-value">{{ $eventos->sum('vagas_ocupadas') }}</div>
        <div class="stat-label">Vendas Totais</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-users"></i></div>
        <div class="stat-value">
            @php $total_vagas = max(1, $eventos->sum('vagas_total')); @endphp
            {{ number_format(($eventos->sum('vagas_ocupadas') / $total_vagas * 100), 0) }}%
        </div>
        <div class="stat-label">Ocupação Média</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value">R$ {{ number_format($eventos->sum(fn($e) => $e->valor * $e->vagas_ocupadas), 0, ',', '.') }}</div>
        <div class="stat-label">Receita Gerada</div>
    </div>
</div>

<!-- Table Container oficial do sistema -->
<div class="table-container animate-up">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 1rem; font-weight: 700; margin: 0; color: var(--text-primary);">Monitoramento de Links</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-tag"></i> Produto / Evento</th>
                    <th><i class="fas fa-dollar-sign"></i> Valor</th>
                    <th><i class="fas fa-users"></i> Performance / Vagas</th>
                    <th><i class="fas fa-info-circle"></i> Status</th>
                    <th><i class="fas fa-link"></i> Checkout</th>
                    <th style="text-align: right;"><i class="fas fa-bolt"></i> Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eventos as $evento)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--text-primary);">{{ $evento->titulo }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Criado em {{ $evento->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td style="font-weight: 700;">R$ {{ number_format($evento->valor, 2, ',', '.') }}</td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 4px; width: 140px;">
                            @php $perc = $evento->vagas_total > 0 ? ($evento->vagas_ocupadas / $evento->vagas_total * 100) : 0; @endphp
                            <div style="height: 6px; background: var(--bg); border-radius: 3px; overflow: hidden;">
                                <div style="width: {{ $perc }}%; height: 100%; background: {{ $perc >= 100 ? 'var(--danger)' : 'var(--success)' }}; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary);">{{ $evento->vagas_ocupadas }} de {{ $evento->vagas_total }}</span>
                        </div>
                    </td>
                    <td>
                        @if($evento->status === 'ativo')
                            <span class="badge badge-success">Ativo</span>
                        @elseif($evento->vagas_ocupadas >= $evento->vagas_total)
                            <span class="badge badge-danger">Esgotado</span>
                        @else
                            <span class="badge badge-secondary">Expirado</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <button type="button" class="btn btn-icon btn-outline" onclick="copyToClipboard('{{ $evento->checkout_url }}', this)" title="Copiar Link">
                                <i class="fas fa-copy"></i>
                            </button>
                            <a href="{{ $evento->checkout_url }}" target="_blank" class="btn btn-icon btn-outline" title="Visualizar Checkout">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                            <a href="{{ $evento->checkout_url }}" target="_blank" class="text-primary font-weight-bold" style="font-size: 0.8rem; text-decoration: none;">Ver Checkout <i class="fas fa-external-link-alt ml-1"></i></a>
                        </div>
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        @if($evento->vagas_ocupadas < $evento->vagas_total)
                        <form action="{{ route('master.integracoes.eventos.toggle', $evento) }}" method="POST" style="display: inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-icon {{ $evento->status === 'ativo' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $evento->status === 'ativo' ? 'Pausar' : 'Ativar' }}">
                                <i class="fas fa-{{ $evento->status === 'ativo' ? 'pause' : 'play' }}"></i>
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('master.integracoes.eventos.destroy', $evento) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apagar este link? Esta ação é irreversível no Asaas.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-icon btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px;">
                        <i class="fas fa-link-slash d-block mb-3" style="font-size: 2.5rem; color: var(--border);"></i>
                        <h4 class="text-muted">Nenhum link ativo encontrado.</h4>
                        <button class="btn btn-primary mt-3" onclick="openBasileiaModal(true)">Criar meu primeiro link</button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Overlay oficial do sistema -->
<div class="modal-overlay" id="modalNovoLink">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle mr-2"></i>Novo Link Master</h2>
            <button class="modal-close" onclick="openBasileiaModal(false)">&times;</button>
        </div>
        <form action="{{ route('master.integracoes.eventos.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                    1. Informações do Produto
                </div>
                
                <div class="form-group">
                    <label>Título Comercial <span class="required">*</span></label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ex: Treinamento Liderança 2026" required>
                </div>

                <div class="form-group">
                    <label>Descrição Curta</label>
                    <textarea name="descricao" class="form-control" rows="2" placeholder="Opcional: Aparece no topo do checkout..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Valor Unitário (R$)</label>
                        <input type="number" name="valor" step="0.01" min="0" class="form-control" placeholder="0,00 (aberto)">
                        <div class="field-hint">Deixe 0 para valor aberto.</div>
                    </div>
                    <div class="form-group">
                        <label>Limite de Vagas <span class="required">*</span></label>
                        <input type="number" name="vagas_total" min="1" max="10000" value="10" class="form-control" required>
                    </div>
                </div>

                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); margin: 30px 0 20px; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                    2. Regras e Configurações Asaas
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>WhatsApp Suporte <span class="required">*</span></label>
                        <input type="text" name="whatsapp_vendedor" class="form-control" placeholder="55..." required>
                    </div>
                    <div class="form-group">
                        <label>Expiração do Link</label>
                        <input type="text" name="data_fim" class="form-control datepicker" placeholder="Selecione uma data...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Meios de Pagamento</label>
                        <select name="billing_type" class="form-control">
                            <option value="UNDEFINED">Todos (Pix, Cartão, Boleto)</option>
                            <option value="PIX">Apenas PIX</option>
                            <option value="CREDIT_CARD">Apenas Cartão</option>
                            <option value="BOLETO">Apenas Boleto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Cobrança</label>
                        <select name="charge_type" id="charge_type_select" class="form-control" onchange="toggleInstallmentField()">
                            <option value="DETACHED">Cobrança Avulsa</option>
                            <option value="INSTALLMENT">Venda Parcelada</option>
                            <option value="RECURRENT">Assinatura</option>
                        </select>
                    </div>
                </div>

                <!-- Campo de Parcelas (Oculto por padrão) -->
                <div class="form-group" id="installment_field_group" style="display: none;">
                    <label>Máximo de Parcelas <span class="required">*</span></label>
                    <select name="max_installments" class="form-control">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ $i }}x</option>
                        @endfor
                    </select>
                    <div class="field-hint">Apenas para vendas no cartão de crédito.</div>
                </div>

                <div style="background: var(--bg); padding: 15px; border-radius: var(--radius-md); margin-top: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; cursor: pointer; margin-bottom: 8px;">
                        <input type="checkbox" name="notification_enabled" checked value="1" style="width: 16px; height: 16px;">
                        <span>Ativar notificações de pagamento para o cliente</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; cursor: pointer;">
                        <input type="checkbox" name="is_address_required" value="1" style="width: 16px; height: 16px;">
                        <span>Exigir endereço completo no checkout</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="openBasileiaModal(false)">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-rocket"></i> Criar Link no Asaas</button>
            </div>
        </form>
    </div>
</div>

@endsection
