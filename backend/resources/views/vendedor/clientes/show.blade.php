@extends('layouts.app')
@section('title', 'Histórico do Cliente')

@section('content')
<style>
    /* ===== Animações ===== */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }

    /* ===== Layout Grid Direita/Esquerda ===== */
    .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 24px; margin-top: 24px; align-items: start; }
    @media (max-width: 900px) { .profile-grid { grid-template-columns: 1fr; } }

    /* ===== Card Lateral ===== */
    .sidebar-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 24px; text-align: center; }
    .profile-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #e0e7ff, #f3e8ff); color: var(--primary); font-size: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(88,28,135,0.1); }
    .sidebar-card h3 { font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
    .sidebar-card .sub-info { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; }
    .info-list { text-align: left; border-top: 1px solid var(--border); padding-top: 16px; margin-top: 16px; }
    .info-item { margin-bottom: 12px; display: flex; flex-direction: column; gap: 2px; }
    .info-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 0.95rem; font-weight: 600; color: var(--text-main); }
    
    /* ===== Status Estático para Vendedor ===== */
    .status-badge-large { display: inline-block; width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-weight: 600; font-size: 0.9rem; margin-top: 16px; text-transform: uppercase; letter-spacing: 1px; }
    .status-badge-large.ativo { color: #15803d; border-color: #bbf7d0; background: #f0fdf4; }
    .status-badge-large.inativo { color: #475569; border-color: #cbd5e1; background: #f8fafc; }
    .status-badge-large.churn { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
    .status-badge-large.inadimplente { color: #b45309; border-color: #fde68a; background: #fffbeb; }

    /* ===== Main Área & Tabs ===== */
    .main-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
    .tabs-header { display: flex; background: #f8fafc; border-bottom: 1px solid var(--border); }
    .tab-btn { flex: 1; padding: 16px; background: none; border: none; border-bottom: 3px solid transparent; font-weight: 700; color: var(--text-muted); cursor: pointer; font-size: 0.95rem; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .tab-btn:hover { color: var(--primary); background: white; }
    .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); background: white; }
    .tab-content { display: none; padding: 0; }
    .tab-content.active { display: block; animation: fadeInUp 0.3s ease-out both; }

    /* ===== Tabelas ===== */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { background: white; padding: 16px 20px; font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); white-space: nowrap; }
    td { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }

    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-success { background: #dcfce7; color: #15803d; }
    .badge-warning { background: #fef3c7; color: #b45309; }
    .badge-danger { background: #fee2e2; color: #b91c1c; }
    .badge-neutral { background: #f1f5f9; color: #475569; }
</style>

<x-page-hero title="{{ $cliente->nome_igreja ?? $cliente->nome }}" subtitle="Visão 360º — Histórico Comercial da Igreja/Entidade" icon="fas fa-building">
    <button onclick="openAgendarModal()" class="hero-btn" style="background:#059669; border-color:#047857;">
        <i class="fab fa-google"></i> Agendar Reunião
    </button>
</x-page-hero>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
        <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 8px;">TOTAL DE VENDAS</div>
        <div style="font-size: 2rem; font-weight: 800;">{{ $totalVendas ?? 0 }}</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
        <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 8px;">VALOR TOTAL PAGO</div>
        <div style="font-size: 2rem; font-weight: 800;">R$ {{ number_format($valorTotalPago ?? 0, 2, ',', '.') }}</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
        <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 8px;">TICKET MÉDIO</div>
        <div style="font-size: 2rem; font-weight: 800;">R$ {{ number_format($ticketMedio ?? 0, 2, ',', '.') }}</div>
    </div>
</div>

<div class="profile-grid">
    <!-- ===== Sidebar (Dados Cadastrais) ===== -->
    <div class="sidebar-card animate-in">
        <div class="profile-icon">
            {{ strtoupper(substr($cliente->nome_igreja ?? $cliente->nome, 0, 1)) }}
        </div>
        <h3>{{ $cliente->nome_igreja ?? $cliente->nome }}</h3>
        <p class="sub-info">{{ $cliente->localidade ?? 'Localidade não informada' }}</p>

        <!-- Status travado (vendedor não muda status do cliente na unha) -->
        <div class="status-badge-large {{ $cliente->status ?? 'ativo' }}">
            {{ $cliente->status ?? 'ativo' }}
        </div>

        <div class="info-list">
            <div class="info-item">
                <span class="info-label">Pastor / Responsável</span>
                <span class="info-value">{{ $cliente->nome_pastor ?? $cliente->nome_responsavel ?? 'Não informado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Documento (CPF/CNPJ)</span>
                <span class="info-value">{{ $cliente->documento ?? 'Não informado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Contato / WhatsApp</span>
                <span class="info-value">{{ $cliente->contato ?? $cliente->whatsapp ?? $cliente->telefone ?? 'Não informado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Quantidade de Membros</span>
                <span class="info-value">{{ $cliente->quantidade_membros ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Na base desde</span>
                <span class="info-value">{{ $cliente->created_at ? $cliente->created_at->format('d/m/Y') : 'Data não informada' }}</span>
            </div>
        </div>
    </div>

    <!-- ===== Main Panel (Históricos) ===== -->
    <div class="main-card animate-in">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchClientTab('vendas', this)">
                🛍️ Meu Histórico de Vendas ({{ $vendas->count() }})
            </button>
            <button class="tab-btn" onclick="switchClientTab('pagamentos', this)">
                💳 Faturas Associadas ({{ $pagamentos->count() }})
            </button>
        </div>

        <!-- TAB: VENDAS -->
        <div id="tab-vendas" class="tab-content active">
            @if($vendas->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Plano</th>
                            <th>Recorrência</th>
                            <th style="text-align: right;">Valor</th>
                            <th>Status Comercial</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendas as $v)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 600;">{{ $v->created_at ? $v->created_at->format('d/m/Y') : 'Data não informada' }}</td>
                            <td style="font-weight: 700;">{{ $v->plano ?? 'Personalizado' }}</td>
                            <td>{{ ucfirst($v->tipo_negociacao ?? 'Mensal') }}{{ $v->parcelas > 1 ? ' (' . $v->parcelas . 'x)' : '' }}</td>
                            <td style="text-align: right; font-weight: 700; color: var(--primary);">
                                @if($v->parcelas > 1)
                                    R$ {{ number_format($v->valor / $v->parcelas, 2, ',', '.') }}
                                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;">{{ $v->parcelas }}x de R$ {{ number_format($v->valor / $v->parcelas, 2, ',', '.') }}</div>
                                @else
                                    R$ {{ number_format($v->valor, 2, ',', '.') }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $vStatus = strtolower($v->status);
                                    $badgeClass = 'badge-neutral';
                                    if ($vStatus == 'pago') $badgeClass = 'badge-success';
                                    if ($vStatus == 'aguardando pagamento') $badgeClass = 'badge-warning';
                                    if ($vStatus == 'cancelado' || $vStatus == 'vencido') $badgeClass = 'badge-danger';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $v->status }}</span>
                                @if($v->isPagamentoParcelado() && $v->getParcelaAtual() > 0)
                                    <div style="font-size: 0.7rem; color: var(--success); margin-top: 2px;">{{ $v->getProgressoParcelas() }}</div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 10px;">🛍️</div>
                <h3 style="color: var(--text-main); font-size: 1.1rem;">Nenhuma venda registrada</h3>
                <p>Você ainda não fechou vendas com este cliente.</p>
            </div>
            @endif
        </div>

        <!-- TAB: PAGAMENTOS -->
        <div id="tab-pagamentos" class="tab-content">
            @if($pagamentos->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Vencimento</th>
                            <th>Forma de Pgto</th>
                            <th style="text-align: right;">Valor</th>
                            <th>Status do Pgto</th>
                            <th style="text-align: right;">Data Pgto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagamentos as $p)
                        <tr>
                            <td style="font-weight: 700;">{{ \Carbon\Carbon::parse($p->data_vencimento)->format('d/m/Y') }}</td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; text-transform: uppercase; font-size: 0.78rem;">
                                    @php $formaExibida = $p->forma_pagamento_real ?? $p->forma_pagamento; @endphp
                                    @if(strtolower($formaExibida) == 'pix') ⚡ PIX
                                    @elseif(strtolower($formaExibida) == 'boleto') 📄 Boleto
                                    @elseif(strtolower($formaExibida) == 'cartao' || strtolower($formaExibida) == 'cartão') 💳 Cartão
                                    @else 💳 {{ $formaExibida }}
                                    @endif
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--text-main);">R$ {{ number_format($p->valor, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $pStatus = strtolower($p->status);
                                    $pClass = 'badge-neutral';
                                    if ($pStatus == 'pago') $pClass = 'badge-success';
                                    if ($pStatus == 'pendente') $pClass = 'badge-warning';
                                    if ($pStatus == 'vencido' || $pStatus == 'estornado') $pClass = 'badge-danger';
                                @endphp
                                <span class="badge {{ $pClass }}">{{ $p->status }}</span>
                            </td>
                            <td style="text-align: right; font-size: 0.85rem; color: var(--text-muted);">
                                {{ $p->data_pagamento ? \Carbon\Carbon::parse($p->data_pagamento)->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 10px;">💳</div>
                <h3 style="color: var(--text-main); font-size: 1.1rem;">Sem faturas</h3>
                <p>Nenhuma fatura foi gerada ainda para suas vendas com este cliente.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Sistema de abas simples (nome único para evitar conflito com basileia.js)
    function switchClientTab(tabId, btnElement) {
        document.querySelectorAll('.tabs-header .tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.main-card .tab-content').forEach(c => c.classList.remove('active'));
        
        btnElement.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // Modal de Agendamento
    function openAgendarModal() {
        document.getElementById('agendarModal').style.display = 'flex';
        // Set default time to next hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        now.setMinutes(0);
        
        const tzOffset = now.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(now - tzOffset)).toISOString().slice(0, 16);
        document.getElementById('modalInicio').value = localISOTime;
    }

    function closeAgendarModal() {
        document.getElementById('agendarModal').style.display = 'none';
    }

    document.getElementById('agendarModal').addEventListener('click', function(e){
        if(e.target === this) closeAgendarModal();
    });
</script>

<style>
/* Modal Styles for Agendar */
.cal-modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(50,50,71,.6); display:none; align-items:center; justify-content:center; z-index:1000; backdrop-filter:blur(4px); }
.cal-modal { background:var(--surface); border-radius:16px; width:100%; max-width:500px; box-shadow:var(--shadow-xl); }
.cal-modal-head { display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid var(--border-light); }
.cal-modal-head h3 { font-size:1.15rem; font-weight:700; display:flex; align-items:center; gap:8px; }
.cal-modal-close { width:32px; height:32px; border-radius:50%; border:none; background:var(--bg); cursor:pointer; font-size:1.2rem; display:flex; align-items:center; justify-content:center; color:var(--text-muted); }
.cal-modal-close:hover { background:var(--danger-light); color:var(--danger); }
.cal-modal form { padding:24px; }
.cal-field { margin-bottom:16px; }
.cal-field label { display:block; font-weight:600; font-size:.85rem; margin-bottom:6px; }
.cal-field input, .cal-field select, .cal-field textarea { width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:10px; font-size:.9rem; font-family:var(--font); }
.cal-field input:focus, .cal-field select:focus, .cal-field textarea:focus { outline:none; border-color:var(--primary); }
.cal-field-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.cal-modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:8px; }
.cal-btn { padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer; font-size:.88rem; border:1px solid var(--border); transition:all .15s; }
.cal-btn-cancel { background:var(--bg); color:var(--text-primary); }
.cal-btn-save { background:var(--primary); color:#fff; border-color:var(--primary); }
</style>

<div class="cal-modal-overlay" id="agendarModal">
    <div class="cal-modal">
        <div class="cal-modal-head">
            <h3><i class="fab fa-google" style="color:#059669;"></i> Agendar Reunião</h3>
            <button class="cal-modal-close" onclick="closeAgendarModal()">&times;</button>
        </div>
        <form action="{{ route('calendario.store') }}" method="POST">
            @csrf
            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
            <input type="hidden" name="tipo" value="reuniao">
            
            <div class="cal-field">
                <label>Título *</label>
                <input type="text" name="titulo" required value="Reunião: {{ $cliente->nome_igreja ?? $cliente->nome }}">
            </div>
            
            <div class="cal-field-row">
                <div class="cal-field">
                    <label>Início *</label>
                    <input type="datetime-local" name="data_hora_inicio" id="modalInicio" required>
                </div>
                <div class="cal-field">
                    <label>Fim</label>
                    <input type="datetime-local" name="data_hora_fim">
                </div>
            </div>
            
            <div class="cal-field">
                <label>Descrição (Opcional)</label>
                <textarea name="descricao" rows="3" placeholder="Pauta da reunião, links, etc..."></textarea>
            </div>
            
            <div class="cal-modal-actions">
                <button type="button" class="cal-btn cal-btn-cancel" onclick="closeAgendarModal()">Cancelar</button>
                <button type="submit" class="cal-btn cal-btn-save"><i class="fas fa-check"></i> Agendar e Sincronizar</button>
            </div>
        </form>
    </div>
</div>

@endsection
