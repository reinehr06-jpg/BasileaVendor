@extends('layouts.app')
@section('title', 'Links de Pagamento')

@section('content')
<style>
    /* Design Tokens */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
        --vibrant-purple: #7c3aed;
        --deep-purple: #4c1d95;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        position: relative;
        z-index: 10;
    }

    .page-title h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--deep-purple);
        margin-bottom: 4px;
    }

    .page-title p {
        font-size: 0.85rem;
        color: #64748b;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid #f1f5f9;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-info h3 {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        margin-bottom: 4px;
    }

    .stat-info .value {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* Main Action Button */
    .btn-create-link {
        background: linear-gradient(135deg, var(--vibrant-purple) 0%, var(--deep-purple) 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.3);
        border: none;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-create-link:hover {
        opacity: 0.9;
        transform: scale(1.02);
    }

    /* Modern Table Card */
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border: 1px solid #f1f5f9;
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table th {
        background: #f8fafc;
        padding: 14px 24px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        text-align: left;
        font-weight: 700;
    }

    .modern-table td {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .link-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .link-date {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    .capacity-bar {
        width: 120px;
        height: 8px;
        background: #f1f5f9;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 4px;
    }

    .capacity-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-active { background: #dcfce7; color: #15803d; }
    .status-expired { background: #fee2e2; color: #b91c1c; }
    .status-full { background: #ffedd5; color: #c2410c; }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .modal-content {
        background: white;
        width: 100%;
        max-width: 650px;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        padding: 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 30px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .section-divider {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--vibrant-purple);
        font-weight: 700;
        margin: 24px 0 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-divider::after {
        content: '';
        height: 1px;
        flex-grow: 1;
        background: #e2e8f0;
    }
</style>

<div class="container-fluid py-4">
    
    <!-- Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Gestão de Integração de Eventos</h1>
            <p>Monitore suas vendas por link em tempo real</p>
        </div>
        @if(!$config_faltante)
        <button class="btn-create-link" onclick="openBasileiaModal(true)">
            <i class="fas fa-plus"></i> Novo Link de Pagamento
        </button>
        @endif
    </div>

    @if($config_faltante)
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px; background: #fef2f2;">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <strong class="d-block text-danger">Atenção! Configuração Pendente</strong>
                <span class="text-muted small">Sua API Key do Asaas não foi localizada. Configure-a para habilitar a geração de links.</span>
            </div>
            <a href="{{ route('master.configuracoes', ['tab' => 'integracoes']) }}" class="btn btn-danger btn-sm ml-auto">Configurar Agora</a>
        </div>
    </div>
    @endif

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">
                <i class="fas fa-link"></i>
            </div>
            <div class="stat-info">
                <h3>Links Ativos</h3>
                <div class="value">{{ $eventos->where('status', 'ativo')->count() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #ecfdf5; color: #059669;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3>Vendas Realizadas</h3>
                <div class="value">{{ $eventos->sum('vagas_ocupadas') }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff7ed; color: #d97706;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Vagas Ocupadas</h3>
                <div class="value">{{ number_format(($eventos->sum('vagas_ocupadas') / max(1, $eventos->sum('vagas_total')) * 100), 1) }}%</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="m-0 font-weight-bold" style="font-size: 1rem; color: #334155;">Monitoramento de Vendas</h3>
            <div class="search-box">
                <input type="text" class="form-control form-control-sm" placeholder="Buscar link..." style="border-radius: 8px; width: 220px;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Produto / Evento</th>
                        <th>Investimento</th>
                        <th>Performance de Vagas</th>
                        <th>Status</th>
                        <th>Link Direto</th>
                        <th class="text-right">Gerenciamento</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventos as $evento)
                    <tr>
                        <td>
                            <div class="link-title">{{ $evento->titulo }}</div>
                            <div class="link-date">{{ $evento->created_at->translatedFormat('d M, Y \à\s H:i') }}</div>
                        </td>
                        <td>
                            <span class="font-weight-bold text-dark">R$ {{ number_format($evento->valor, 2, ',', '.') }}</span>
                        </td>
                        <td>
                            <div class="capacity-bar">
                                @php $perc = $evento->vagas_total > 0 ? ($evento->vagas_ocupadas / $evento->vagas_total * 100) : 0; @endphp
                                <div class="capacity-fill" style="width: {{ $perc }}%; background: {{ $perc >= 100 ? '#ef4444' : '#10b981' }}"></div>
                            </div>
                            <span class="small font-weight-bold text-muted">{{ $evento->vagas_ocupadas }} / {{ $evento->vagas_total }} vendas</span>
                        </td>
                        <td>
                            @if($evento->status === 'ativo')
                                <span class="status-badge status-active">Ativo</span>
                            @elseif($evento->vagas_ocupadas >= $evento->vagas_total)
                                <span class="status-badge status-full">Esgotado</span>
                            @else
                                <span class="status-badge status-expired">Expirado</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-light p-2" onclick="copyToClipboard('{{ $evento->checkout_url }}', this)" title="Copiar Link">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <a href="{{ $evento->checkout_url }}" target="_blank" class="small text-primary font-weight-bold">Ver no Asaas <i class="fas fa-external-link-alt ml-1"></i></a>
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Ações Disponíveis</div>
                                    @if($evento->vagas_ocupadas < $evento->vagas_total)
                                    <form action="{{ route('master.integracoes.eventos.toggle', $evento) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-{{ $evento->status === 'ativo' ? 'pause' : 'play' }} fa-sm fa-fw mr-2 text-gray-400"></i>
                                            {{ $evento->status === 'ativo' ? 'Suspender Link' : 'Reativar Link' }}
                                        </button>
                                    </form>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('master.integracoes.eventos.destroy', $evento) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar este link? Esta ação removerá o link também no Asaas.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash fa-sm fa-fw mr-2 text-danger"></i> Remover Link
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <img src="https://illustrations.popsy.co/purple/searching.svg" style="width: 120px; margin-bottom: 20px;" alt="Vazio">
                            <p class="font-weight-bold">Nenhum link ativo encontrado.</p>
                            <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="openBasileiaModal(true)">Crie o seu primeiro agora</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Novo Link -->
<div class="modal-overlay" id="modalNovoLink">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="m-0 font-weight-bold" style="font-size: 1.1rem; color: #1e293b;">Gerar Novo Link Master</h3>
            <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="openBasileiaModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{ route('master.integracoes.eventos.store') }}" method="POST" id="formNovoLink">
                @csrf
                
                <div class="section-divider">Informações do Produto</div>
                
                <div class="form-group">
                    <label class="form-label">Título Comercial *</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ex: MasterClass Basiléia Vendas" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Descrição Breve</label>
                    <textarea name="descricao" class="form-control" rows="2" placeholder="Explique brevemente o que está sendo vendido..."></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Preço Unitário (R$)</label>
                        <input type="number" name="valor" step="0.01" min="0" class="form-control" placeholder="0.00 (aberto)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Limite de Vagas *</label>
                        <input type="number" name="vagas_total" min="1" max="10000" value="100" class="form-control" required>
                    </div>
                </div>

                <div class="section-divider">Regras de Negócio & Contato</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">WhatsApp Suporte (55...)</label>
                        <input type="text" name="whatsapp_vendedor" class="form-control" placeholder="Ex: 5511999999999" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data de Expiração</label>
                        <input type="date" name="data_fim" class="form-control">
                    </div>
                </div>

                <div class="section-divider">Configurações Técnicas Asaas</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Forma Permitida</label>
                        <select name="billing_type" class="form-control">
                            <option value="UNDEFINED">PIX, Cartão e Boleto</option>
                            <option value="PIX">Apenas PIX</option>
                            <option value="CREDIT_CARD">Apenas Cartão</option>
                            <option value="BOLETO">Apenas Boleto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Link</label>
                        <select name="charge_type" class="form-control">
                            <option value="DETACHED">Venda Direta (Avulsa)</option>
                            <option value="RECURRENT">Assinatura Mensal</option>
                            <option value="INSTALLMENT">Venda Parcelada</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 rounded-lg mt-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                    <div class="d-flex flex-column gap-2">
                        <label class="d-flex align-items-center gap-2 m-0 cursor-pointer text-muted small">
                            <input type="checkbox" name="notification_enabled" checked value="1">
                            Enviar notificações automáticas por e-mail (Asaas)
                        </label>
                        <label class="d-flex align-items-center gap-2 m-0 cursor-pointer text-muted small">
                            <input type="checkbox" name="is_address_required" value="1">
                            Exigir endereço completo no checkout
                        </label>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-create-link w-100 justify-content-center py-3">
                        <i class="fas fa-rocket mr-2"></i> Criar e Ativar Link no Asaas
                    </button>
                    <button type="button" class="btn btn-link w-100 mt-2 text-muted small" onclick="openBasileiaModal(false)">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openBasileiaModal(show) {
        console.log('Tentando abrir modal:', show);
        const modal = document.getElementById('modalNovoLink');
        if (modal) {
            modal.style.display = show ? 'flex' : 'none';
            if(show) {
                document.body.style.overflow = 'hidden'; // Trava o scroll do fundo
            } else {
                document.body.style.overflow = 'auto'; 
            }
        } else {
            console.error('Modal modalNovoLink não encontrado no DOM');
        }
    }

    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const icon = btn.querySelector('i');
            const originalIcon = icon.className;
            icon.className = 'fas fa-check text-success';
            setTimeout(() => {
                icon.className = originalIcon;
            }, 2000);
        });
    }

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
        let modal = document.getElementById('modalNovoLink');
        if (event.target == modal) {
            openBasileiaModal(false);
        }
    }
</script>
@endsection
