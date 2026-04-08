@extends('layouts.app')

@push('css')
<style>
/* Estilos Limpos para a Página de Detalhes */
.detail-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    border: 1px solid var(--materio-border);
    margin-bottom: 24px;
}
.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--materio-border);
    padding-bottom: 16px;
    margin-bottom: 20px;
}
.detail-header h2 {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--materio-text-main);
    margin: 0;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}
.info-item label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--materio-text-muted);
    text-transform: uppercase;
    margin-bottom: 6px;
}
.info-item .value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--materio-text-main);
}
.info-item .value.highlight {
    color: #166534;
    font-weight: 800;
}

/* Dropdown Gigante para Vendedor */
.vendedor-dropdown {
    width: 100%;
    padding: 14px 16px;
    font-size: 1.1rem;
    font-weight: 700;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    color: var(--materio-text-main);
    transition: all 0.2s;
    cursor: pointer;
    appearance: auto;
}
.vendedor-dropdown:focus {
    outline: none;
    border-color: #f97316;
    background: white;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
}
</style>
@endpush

@section('content')
<div class="materio-container">
    <x-page-hero title="Detalhes do Cliente Asaas" subtitle="{{ $cliente->nome }} — {{ $cliente->email ?? 'Sem e-mail' }}" icon="fas fa-cloud-arrow-down">
        <a href="{{ route('master.clientes-asaas.edit', $cliente->id) }}" class="btn btn-primary" style="font-weight:700; background:#f97316; border-color:#f97316;">
            <i class="fas fa-edit"></i> Editar Dados
        </a>
    </x-page-hero>

    @php
        $tipoLabel = match($cliente->tipo_cobranca ?? '') {
            'installment'  => 'Parcelamento',
            'subscription' => 'Assinatura',
            default        => 'Avulso',
        };

        // Identificando as comissões formatadas
        $comissaoVendedor = ($cliente->comissao_vendedor_calculada ?? 0) > 0 ? 'R$ ' . number_format($cliente->comissao_vendedor_calculada, 2, ',', '.') : '—';
        $comissaoGestor = ($cliente->comissao_gestor_calculada ?? 0) > 0 ? 'R$ ' . number_format($cliente->comissao_gestor_calculada, 2, ',', '.') : '—';
        
        $mesLabel = \Carbon\Carbon::parse($mesReferencia . '-01')->translatedFormat('F/Y');
        $mesSimples = \Carbon\Carbon::parse($mesReferencia . '-01')->translatedFormat('F');
    @endphp

    <div style="display: flex; flex-wrap: wrap; gap: 24px;">
        {{-- COLUNA ESQUERDA: INFOS DO CLIENTE (Ocupa 2/3 da tela) --}}
        <div style="flex: 1 1 60%; min-width: 320px;">
            <div class="detail-card">
                <div class="detail-header">
                    <h2>{{ $cliente->nome }}</h2>
                    <div style="display:flex; gap:8px;">
                        <span style="font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:8px; background: #e0f2fe; color: #0284c7;">
                            {{ $tipoLabel }}
                        </span>
                        <span style="font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:8px; background: {{ $cliente->diagnostico_status === 'ATIVO' ? '#dcfce7' : ($cliente->diagnostico_status === 'CHURN' ? '#ffedd5' : '#fee2e2') }}; color: {{ $cliente->diagnostico_status === 'ATIVO' ? '#166534' : ($cliente->diagnostico_status === 'CHURN' ? '#c2410c' : '#991b1b') }};">
                            {{ $cliente->diagnostico_status ?? 'DESCONHECIDO' }}
                        </span>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <label>E-mail</label>
                        <div class="value">{{ $cliente->email ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <label>CPF/CNPJ</label>
                        <div class="value">{{ $cliente->documento ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <label>Telefone</label>
                        <div class="value">{{ $cliente->telefone ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <label>ID Asaas (Cliente)</label>
                        <div class="value" style="font-size:0.8rem; font-family:monospace;">{{ $cliente->asaas_customer_id }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-header">
                    <h2>Dados de Pagamento ({{ ucfirst($mesLabel) }})</h2>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <label>1º Pagamento da Conta</label>
                        <div class="value">
                            @if($cliente->primeiro_pagamento_at)
                                {{ \Carbon\Carbon::parse($cliente->primeiro_pagamento_at)->format('d/m/Y') }}
                            @else — @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Último Pagamento Confirmado</label>
                        <div class="value">
                            @if($cliente->ultimo_pagamento_confirmado_at)
                                {{ \Carbon\Carbon::parse($cliente->ultimo_pagamento_confirmado_at)->format('d/m/Y') }}
                            @else — @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Valor Pago em {{ ucfirst($mesSimples) }}</label>
                        <div class="value highlight">
                            R$ {{ number_format($cliente->valor_marco_pago ?? 0, 2, ',', '.') }}
                        </div>
                    </div>
                    @if($cliente->tipo_cobranca === 'installment')
                    <div class="info-item">
                        <label>Fração (Parcelas)</label>
                        <div class="value">{{ $cliente->parcelas_pagas ?? 0 }} / {{ $cliente->parcelas_total ?? 1 }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- COLUNA DIREITA: VENDEDOR & COMISSÃO (Ocupa 1/3 da tela) --}}
        <div style="flex: 1 1 30%; min-width: 300px;">
            <div class="detail-card" style="border: 2px solid #f97316;">
                <div class="detail-header" style="border-bottom-color: #fcd34d;">
                    <h2 style="color: #ea580c;"><i class="fas fa-users"></i> Atribuição Comercial</h2>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:0.8rem; font-weight:800; color:var(--materio-text-muted); text-transform:uppercase; margin-bottom:10px;">Vendedor Responsável</label>
                    <div style="position:relative;">
                        <input type="hidden" id="cliente_id" value="{{ $cliente->id }}">
                        <select id="vendedor_select" class="vendedor-dropdown">
                            <option value="">— Selecionar Vendedor —</option>
                            @if($listaG->count() > 0)
                            <optgroup label="Gestores">
                                @foreach($listaG as $v)
                                <option value="{{ $v->id }}" {{ $cliente->vendedor_id == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                            @if($listaV->count() > 0)
                            <optgroup label="Vendedores">
                                @foreach($listaV as $v)
                                <option value="{{ $v->id }}" {{ $cliente->vendedor_id == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        <i class="fas fa-chevron-down" style="position:absolute; right:16px; top:16px; pointer-events:none; color:#94a3b8;"></i>
                    </div>
                    <button type="button" id="btn_salvar_vendedor" onclick="salvarAtribuicao()" class="materio-btn-primary" style="margin-top:12px; width:100%; padding:12px; font-weight:700;">
                        <i class="fas fa-save"></i> Salvar Vendedor
                    </button>
                    <div id="save_feedback" style="font-size:0.75rem; font-weight:700; color:#166534; margin-top:8px; display:none;">
                        <i class="fas fa-check"></i> Salvo com sucesso!
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:0.8rem; font-weight:800; color:var(--materio-text-muted); text-transform:uppercase; margin-bottom:10px;">Percentuais de Comissão (%)</label>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1;">
                            <label style="font-size:0.7rem; color:#64748b; font-weight:600;">Vendedor %</label>
                            <input type="number" id="perc_vendedor" step="0.5" min="0" max="100" value="{{ $percVendedor ?? 0 }}" 
                                style="width:100%; padding:10px; border:2px solid #e2e8f0; border-radius:8px; font-size:1rem; font-weight:700; text-align:center;">
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:0.7rem; color:#64748b; font-weight:600;">Gestor %</label>
                            <input type="number" id="perc_gestor" step="0.5" min="0" max="100" value="{{ $percGestor ?? 0 }}"
                                style="width:100%; padding:10px; border:2px solid #e2e8f0; border-radius:8px; font-size:1rem; font-weight:700; text-align:center;">
                        </div>
                    </div>
                </div>

                <div style="background:var(--materio-body-bg); padding:16px; border-radius:10px;">
                    <h4 style="font-size:0.85rem; font-weight:800; color:var(--materio-text-muted); text-transform:uppercase; margin-bottom:12px;">Comissões Projetadas ({{ ucfirst($mesSimples) }})</h4>
                    
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:0.85rem; font-weight:600; color:var(--materio-text-main);">Vendedor:</span>
                        <span id="comissao_vendedor_box" style="font-size:0.95rem; font-weight:800; color:#166534; text-align:right;">
                            {{ $comissaoVendedor }}
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.85rem; font-weight:600; color:var(--materio-text-main);">Gestor:</span>
                        <span id="comissao_gestor_box" style="font-size:0.9rem; font-weight:800; color:#2563eb; text-align:right;">
                            {{ $comissaoGestor }}
                        </span>
                    </div>

                    <hr style="margin: 12px 0; border-color: #e2e8f0;">

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.8rem; font-weight:600; color:var(--materio-text-muted);">Tipo Comissão:</span>
                        <span style="font-size:0.65rem; font-weight:800; padding:2px 6px; border-radius:6px; background:#f1f5f9; color:var(--materio-text-main);">
                            {{ strtoupper($cliente->comissao_tipo === 'inicial_antecipada' ? 'Inicial Antecipada' : ($cliente->comissao_tipo === 'mensal' ? 'Mensal Recorrente' : 'Inválida')) }}
                        </span>
                    </div>
                </div>

                @if($cliente->vendedor_id)
                <div style="margin-top:20px; text-align:center;">
                   <button onclick="confirmarVendaSistema(this)" class="materio-btn-primary" style="width:100%; padding:14px; font-size:1rem; font-weight:800; border-radius:10px; background:#166534; border:none; color:white;">
                       <i class="fas fa-check-double"></i> Aprovar e Puxar para o Basileia
                   </button>
                   <div style="font-size:0.7rem; color:var(--materio-text-muted); margin-top:8px;">Isso irá gerar as conciliações e comissões permanentemente no sistema.</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
async function salvarAtribuicao() {
    const select = document.getElementById('vendedor_select');
    const vendId = select.value;
    const clientId = document.getElementById('cliente_id').value;
    const percVendedor = document.getElementById('perc_vendedor').value || 0;
    const percGestor = document.getElementById('perc_gestor').value || 0;
    const feedback = document.getElementById('save_feedback');
    const origBorder = select.style.borderColor;

    select.disabled = true;
    select.style.borderColor = '#f97316';
    feedback.style.display = 'none';

    try {
        const resp = await fetch(`{{ url('master/clientes-asaas') }}/${clientId}/vendedor`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ vendedor_id: vendId || null, perc_vendedor: percVendedor, perc_gestor: percGestor }),
        });
        const data = await resp.json();

        if (data.success) {
            // Atualiza visual
            select.style.borderColor = '#22c55e';
            feedback.style.display = 'block';
            
            // Atualiza os paineis de comissão
            document.getElementById('comissao_vendedor_box').innerHTML = vendId && data.comissao_vendedor !== 'R$ 0,00' ? data.comissao_vendedor : '—';
            document.getElementById('comissao_gestor_box').innerHTML = vendId && data.comissao_gestor !== 'R$ 0,00' ? data.comissao_gestor : '—';

            setTimeout(() => { select.style.borderColor = origBorder; feedback.style.display = 'none'; }, 3000);
            
            // Recarrega se acabou de atribuir para mostrar o botao de puxar pro basileia
            if(vendId) {
                setTimeout(()=> window.location.reload(), 800);
            }
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível atribuir.'));
            select.style.borderColor = '#ef4444';
        }
    } catch(e) {
        alert('Erro de conexão: ' + e.message);
    } finally {
        select.disabled = false;
    }
}

async function confirmarVendaSistema(btn) {
    const clientId = document.getElementById('cliente_id').value;
    
    // Buscar informações do cliente para o modal
    const nome = document.querySelector('.detail-header h2')?.textContent || 'Cliente';
    const valorPlano = document.getElementById('comissao_vendedor_box')?.textContent || '—';
    const valorGestor = document.getElementById('comissao_gestor_box')?.textContent || '—';
    const vendedorSelect = document.getElementById('vendedor_select');
    const vendedorNome = vendedorSelect?.options[vendedorSelect.selectedIndex]?.text || 'Não selecionado';
    
    // Criar modal de confirmação
    const modalHtml = `
        <div id="confirm-modal" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;">
            <div style="background:white;border-radius:16px;padding:24px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <h3 style="margin:0 0 20px;color:#1e293b;font-size:1.2rem;font-weight:800;"><i class="fas fa-check-circle" style="color:#22c55e;"></i> Confirmar Atribuição</h3>
                
                <div style="background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:16px;">
                    <div style="margin-bottom:12px;">
                        <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Cliente</div>
                        <div style="font-size:1rem;font-weight:700;color:#1e293b;">${nome}</div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Vendedor Atribuído</div>
                        <div style="font-size:1rem;font-weight:700;color:#4C1D95;">${vendedorNome}</div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div>
                            <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Comissão Vendedor</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#166534;">${valorPlano}</div>
                        </div>
                        <div>
                            <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;">Comissão Gestor</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#2563eb;">${valorGestor}</div>
                        </div>
                    </div>
                </div>
                
                <p style="font-size:0.85rem;color:#64748b;margin-bottom:20px;">
                    Ao confirmar, o cliente será criado no sistema e as comissões serão registradas definitivamente.
                </p>
                
                <div style="display:flex;gap:12px;">
                    <button onclick="closeConfirmModal()" style="flex:1;padding:12px;border:2px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:700;cursor:pointer;">Cancelar</button>
                    <button id="btn-execute-confirm" onclick="executeConfirmacao(${clientId})" style="flex:1;padding:12px;border:none;border-radius:8px;background:#22c55e;color:white;font-weight:700;cursor:pointer;"><i class="fas fa-check"></i> Confirmar</button>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente se houver
    const existing = document.getElementById('confirm-modal');
    if (existing) existing.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (modal) modal.remove();
}

async function executeConfirmacao(clientId) {
    const btn = document.getElementById('btn-execute-confirm');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    }

    try {
        const resp = await fetch(`{{ url('master/clientes-asaas') }}/${clientId}/confirmar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        });
        const data = await resp.json();

        if (data.success) {
            closeConfirmModal();
            // Mostrar feedback de sucesso
            document.body.insertAdjacentHTML('beforeend', `
                <div id="success-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;">
                    <div style="background:white;border-radius:16px;padding:32px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                        <i class="fas fa-check-circle" style="font-size:3rem;color:#22c55e;margin-bottom:16px;display:block;"></i>
                        <h3 style="color:#1e293b;margin:0 0 8px;">Cliente Confirmado!</h3>
                        <p style="color:#64748b;font-size:0.9rem;">Redirecionando para a lista de clientes...</p>
                    </div>
                </div>
            `);
            setTimeout(() => { window.location.href = "{{ route('master.clientes') }}"; }, 1500);
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível aprovar.'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Confirmar';
            }
        }
    } catch(e) {
        alert('Erro: ' + e.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Confirmar';
        }
    }
}
</script>
@endpush
@endsection

