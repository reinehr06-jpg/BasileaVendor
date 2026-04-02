@extends('layouts.app')
@section('title', 'Vendedores')

@section('content')
<style>
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-left: 4px;
    }
    .action-btn:hover { background: var(--bg); color: var(--primary); border-color: var(--primary); }
    .action-btn.danger { color: var(--danger); border-color: var(--danger-light); }
    .action-btn.danger:hover { background: var(--danger-light); }
    .action-btn.success { color: var(--success); border-color: var(--success-light); }
    .action-btn.success:hover { background: var(--success-light); }

    .input-group { display: flex; align-items: stretch; }
    .input-group .form-control { border-radius: 8px 0 0 8px; }
    .input-group-addon { display: flex; align-items: center; justify-content: center; padding: 0 14px; background: #f1f5f9; border: 1px solid var(--border); border-left: none; border-radius: 0 8px 8px 0; font-weight: 700; color: var(--primary); font-size: 0.95rem; }

    .form-section {
        background: var(--bg);
        border-radius: var(--radius-md);
        padding: 18px 20px;
        margin-bottom: 16px;
        border-left: 3px solid var(--primary);
    }
    .form-section-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--primary);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .form-section-title i {
        font-size: 0.9rem;
    }

    .commission-highlight {
        background: linear-gradient(135deg, rgba(76,29,149,0.04), rgba(76,29,149,0.08));
        border: 1px dashed rgba(76,29,149,0.2);
        border-radius: var(--radius-md);
        padding: 16px 18px;
        margin-top: 8px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .info-item {
        padding: 10px 14px;
        background: var(--bg);
        border-radius: var(--radius-sm);
    }
    .info-item label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .info-item .value {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .vendedor-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .export-dropdown { position: relative; display: inline-block; }
    .export-dropdown-content { display: none; position: absolute; right: 0; background: var(--surface); min-width: 180px; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100; margin-top: 4px; }
    .export-dropdown:hover .export-dropdown-content { display: block; }
    .export-item { display: block; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 0.875rem; transition: 0.15s; }
    .export-item:hover { background: var(--bg); color: var(--primary); }
    .export-item:first-child { border-radius: 8px 8px 0 0; }
    .export-item:last-child { border-radius: 0 0 8px 8px; }
    .export-item i { margin-right: 8px; width: 16px; }

    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-users" style="margin-right: 8px;"></i>Gestão de Vendedores</h2>
        <p>Cadastre e gerencie sua equipe de vendas.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <div class="export-dropdown">
            <button class="btn btn-outline">
                <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="margin-left: 6px; font-size: 0.7rem;"></i>
            </button>
            <div class="export-dropdown-content">
                <a href="?formato=excel" class="export-item"><i class="fas fa-file-excel"></i> Excel</a>
                <a href="?formato=pdf" class="export-item"><i class="fas fa-file-pdf"></i> PDF</a>
                <a href="?formato=csv" class="export-item"><i class="fas fa-file-csv"></i> CSV</a>
            </div>
        </div>
        <button class="btn btn-primary" onclick="BasileiaModal.open('createModal')">
            <i class="fas fa-plus"></i> Novo Vendedor
        </button>
    </div>
</div>

<div class="filters-bar">
    <div style="position: relative; flex-grow: 1;">
        <i class="fas fa-magnifying-glass" style="position: absolute; left: 14px; top: 11px; color: var(--text-muted);"></i>
        <input type="text" class="search-input" id="searchInput" style="padding-left: 40px;" placeholder="Buscar por nome, e-mail ou telefone..." oninput="filterTable()">
    </div>
    <select class="filter-select" id="statusFilter" onchange="filterTable()">
        <option value="">Status: Todos</option>
        <option value="ativo">Ativo</option>
        <option value="inativo">Inativo</option>
        <option value="bloqueado">Bloqueado</option>
    </select>
</div>

<div class="table-container">
    @if(isset($vendedores) && count($vendedores) > 0)
    <table id="vendedoresTable">
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>E-mail</th>
                <th>Perfil</th>
                <th>Comissão</th>
                <th>Split</th>
                <th>Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendedores as $vendedor)
            <tr class="vendedor-row"
                data-name="{{ strtolower($vendedor->name) }}"
                data-email="{{ strtolower($vendedor->email) }}"
                data-telefone="{{ strtolower($vendedor->vendedor?->telefone ?? '') }}"
                data-status="{{ $vendedor->status }}">
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="vendedor-avatar">{{ strtoupper(substr($vendedor->name, 0, 1)) }}</div>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $vendedor->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $vendedor->vendedor?->telefone ?? 'Sem telefone' }}</div>
                        </div>
                    </div>
                </td>
                <td style="color: var(--text-secondary); font-size: 0.875rem;">{{ $vendedor->email }}</td>
                <td>
                    <span class="badge {{ $vendedor->perfil === 'gestor' ? 'badge-info' : 'badge-secondary' }}">
                        {{ $vendedor->perfil === 'gestor' ? 'Gestor' : 'Vendedor' }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-primary">{{ $vendedor->vendedor?->comissao ?? '0' }}%</span>
                </td>
                <td>
                    @if($vendedor->vendedor?->split_ativo)
                        @php $walletStatus = $vendedor->vendedor?->wallet_status ?? 'pendente'; @endphp
                        @if($walletStatus === 'validado')
                            <span class="badge badge-success">Validado</span>
                        @elseif($walletStatus === 'erro')
                            <span class="badge badge-danger">Erro</span>
                        @else
                            <span class="badge badge-warning">Pendente</span>
                        @endif
                    @else
                        <span class="badge badge-secondary">Inativo</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $vendedor->status === 'ativo' ? 'success' : ($vendedor->status === 'bloqueado' ? 'danger' : 'warning') }}">{{ ucfirst($vendedor->status) }}</span>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                    <button class="action-btn" title="Selecionar Equipe" onclick='openEquipeModal({{ $vendedor->id }}, "{{ addslashes($vendedor->name) }}", {{ json_encode($equipes ?? []) }})'>
                        <i class="fas fa-people-group"></i>
                    </button>
                    <button class="action-btn" title="Visualizar" onclick='openViewModal({{ json_encode([
                        'id' => $vendedor->id,
                        'name' => $vendedor->name,
                        'email' => $vendedor->email,
                        'telefone' => $vendedor->vendedor?->telefone ?? 'Não informado',
                        'perfil' => $vendedor->perfil,
                        'comissao' => $vendedor->vendedor?->comissao ?? 0,
                        'comissao_inicial' => $vendedor->vendedor?->comissao_inicial ?? 0,
                        'comissao_recorrencia' => $vendedor->vendedor?->comissao_recorrencia ?? 0,
                        'meta_mensal' => $vendedor->vendedor?->meta_mensal ?? 0,
                        'status' => $vendedor->status,
                        'created_at' => $vendedor->created_at->format('d/m/Y H:i'),
                        'split_ativo' => $vendedor->vendedor?->split_ativo ?? false,
                        'wallet_status' => $vendedor->vendedor?->wallet_status ?? 'pendente',
                        'gestor_nome' => $vendedor->vendedor?->gestor?->name ?? 'Nenhum',
                    ]) }})'>
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn" title="Editar" onclick='openEditModal({{ json_encode([
                        'id' => $vendedor->id,
                        'name' => $vendedor->name,
                        'email' => $vendedor->email,
                        'telefone' => $vendedor->vendedor?->telefone ?? '',
                        'perfil' => $vendedor->perfil,
                        'comissao' => $vendedor->vendedor?->comissao ?? 0,
                        'comissao_inicial' => $vendedor->vendedor?->comissao_inicial ?? $vendedor->vendedor?->comissao ?? 0,
                        'comissao_recorrencia' => $vendedor->vendedor?->comissao_recorrencia ?? $vendedor->vendedor?->comissao ?? 0,
                        'meta_mensal' => $vendedor->vendedor?->meta_mensal ?? 0,
                        'status' => $vendedor->status,
                        'gestor_id' => $vendedor->vendedor?->gestor_id ?? '',
                        'comissao_gestor_primeira' => $vendedor->vendedor?->comissao_gestor_primeira ?? 0,
                        'comissao_gestor_recorrencia' => $vendedor->vendedor?->comissao_gestor_recorrencia ?? 0,
                        'split_ativo' => $vendedor->vendedor?->split_ativo ?? false,
                        'asaas_wallet_id' => $vendedor->vendedor?->asaas_wallet_id ?? '',
                        'tipo_split' => $vendedor->vendedor?->tipo_split ?? 'percentual',
                        'valor_split_inicial' => $vendedor->vendedor?->valor_split_inicial ?? 0,
                        'valor_split_recorrencia' => $vendedor->vendedor?->valor_split_recorrencia ?? 0,
                    ]) }})'>
                        <i class="fas fa-pen"></i>
                    </button>
                    @if($vendedor->status === 'ativo')
                    <form method="POST" action="{{ route('master.vendedores.toggle', $vendedor->id) }}" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Inativar Vendedor', message: 'Deseja realmente inativar este vendedor?', type: 'warning', confirmText: 'Inativar', onConfirm: () => this.submit()});">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="action-btn danger" title="Inativar">
                            <i class="fas fa-ban"></i>
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('master.vendedores.toggle', $vendedor->id) }}" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Reativar Vendedor', message: 'Deseja reativar este vendedor?', type: 'success', confirmText: 'Reativar', onConfirm: () => this.submit()});">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="action-btn success" title="Reativar">
                            <i class="fas fa-circle-check"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-users"></i></div>
        <h3>Nenhum vendedor encontrado</h3>
        <p>Nenhum vendedor cadastrado até o momento.</p>
    </div>
    @endif
</div>

<!-- ========== MODAL: Criar Vendedor ========== -->
<div class="modal-overlay" id="createModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus" style="margin-right: 8px;"></i>Cadastrar Vendedor</h2>
            <button class="modal-close" onclick="BasileiaModal.close('createModal')">&times;</button>
        </div>
        <form action="{{ route('master.vendedores.store') }}" method="POST" class="modal-body">
            @csrf

            <!-- Seção 1: Dados Pessoais -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-user"></i> Dados Pessoais</div>
                <div class="form-group">
                    <label>Nome Completo <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: João da Silva">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>E-mail (Acesso) <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="vendedor@email.com">
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <div style="display:flex; gap:8px;">
                            <select name="telefone_ddi" class="form-control" style="flex:0 0 120px; font-size:0.82rem;">
                                <option value="55">🇧🇷 +55</option>
                                <option value="1">🇺🇸 +1</option>
                                <option value="7">🇷🇺 +7</option>
                                <option value="20">🇪🇬 +20</option>
                                <option value="27">🇿🇦 +27</option>
                                <option value="30">🇬🇷 +30</option>
                                <option value="31">🇳🇱 +31</option>
                                <option value="32">🇧🇪 +32</option>
                                <option value="33">🇫🇷 +33</option>
                                <option value="34">🇪🇸 +34</option>
                                <option value="36">🇭🇺 +36</option>
                                <option value="39">🇮🇹 +39</option>
                                <option value="40">🇷🇴 +40</option>
                                <option value="41">🇨🇭 +41</option>
                                <option value="43">🇦🇹 +43</option>
                                <option value="44">🇬🇧 +44</option>
                                <option value="45">🇩🇰 +45</option>
                                <option value="46">🇸🇪 +46</option>
                                <option value="47">🇳🇴 +47</option>
                                <option value="48">🇵🇱 +48</option>
                                <option value="49">🇩🇪 +49</option>
                                <option value="51">🇵🇪 +51</option>
                                <option value="52">🇲🇽 +52</option>
                                <option value="53">🇨🇺 +53</option>
                                <option value="54">🇦🇷 +54</option>
                                <option value="56">🇨🇱 +56</option>
                                <option value="57">🇨🇴 +57</option>
                                <option value="58">🇻🇪 +58</option>
                                <option value="60">🇲🇾 +60</option>
                                <option value="61">🇦🇺 +61</option>
                                <option value="62">🇮🇩 +62</option>
                                <option value="63">🇵🇭 +63</option>
                                <option value="64">🇳🇿 +64</option>
                                <option value="65">🇸🇬 +65</option>
                                <option value="66">🇹🇭 +66</option>
                                <option value="81">🇯🇵 +81</option>
                                <option value="82">🇰🇷 +82</option>
                                <option value="84">🇻🇳 +84</option>
                                <option value="86">🇨🇳 +86</option>
                                <option value="90">🇹🇷 +90</option>
                                <option value="91">🇮🇳 +91</option>
                                <option value="92">🇵🇰 +92</option>
                                <option value="93">🇦🇫 +93</option>
                                <option value="94">🇱🇰 +94</option>
                                <option value="95">🇲🇲 +95</option>
                                <option value="98">🇮🇷 +98</option>
                                <option value="212">🇲🇦 +212</option>
                                <option value="213">🇩🇿 +213</option>
                                <option value="216">🇹🇳 +216</option>
                                <option value="218">🇱🇾 +218</option>
                                <option value="220">🇬🇲 +220</option>
                                <option value="221">🇸🇳 +221</option>
                                <option value="222">🇲🇷 +222</option>
                                <option value="223">🇲🇱 +223</option>
                                <option value="224">🇬🇳 +224</option>
                                <option value="225">🇨🇮 +225</option>
                                <option value="226">🇧🇫 +226</option>
                                <option value="227">🇳🇪 +227</option>
                                <option value="228">🇹🇬 +228</option>
                                <option value="229">🇧🇯 +229</option>
                                <option value="230">🇲🇺 +230</option>
                                <option value="231">🇱🇷 +231</option>
                                <option value="232">🇸🇱 +232</option>
                                <option value="233">🇬🇭 +233</option>
                                <option value="234">🇳🇬 +234</option>
                                <option value="235">🇹🇩 +235</option>
                                <option value="236">🇨🇫 +236</option>
                                <option value="237">🇨🇲 +237</option>
                                <option value="238">🇨🇻 +238</option>
                                <option value="239">🇸🇹 +239</option>
                                <option value="240">🇬🇶 +240</option>
                                <option value="241">🇬🇦 +241</option>
                                <option value="242">🇨🇬 +242</option>
                                <option value="243">🇨🇩 +243</option>
                                <option value="244">🇦🇴 +244</option>
                                <option value="245">🇬🇼 +245</option>
                                <option value="246">🇮🇴 +246</option>
                                <option value="247">🇦🇨 +247</option>
                                <option value="248">🇸🇨 +248</option>
                                <option value="249">🇸🇩 +249</option>
                                <option value="250">🇷🇼 +250</option>
                                <option value="251">🇪🇹 +251</option>
                                <option value="252">🇸🇴 +252</option>
                                <option value="253">🇩🇯 +253</option>
                                <option value="254">🇰🇪 +254</option>
                                <option value="255">🇹🇿 +255</option>
                                <option value="256">🇺🇬 +256</option>
                                <option value="257">🇧🇮 +257</option>
                                <option value="258">🇲🇿 +258</option>
                                <option value="260">🇿🇲 +260</option>
                                <option value="261">🇲🇬 +261</option>
                                <option value="262">🇷🇪 +262</option>
                                <option value="263">🇿🇼 +263</option>
                                <option value="264">🇳🇦 +264</option>
                                <option value="265">🇲🇼 +265</option>
                                <option value="266">🇱🇸 +266</option>
                                <option value="267">🇧🇼 +267</option>
                                <option value="268">🇸🇿 +268</option>
                                <option value="269">🇰🇲 +269</option>
                                <option value="290">🇸🇭 +290</option>
                                <option value="291">🇪🇷 +291</option>
                                <option value="297">🇦🇼 +297</option>
                                <option value="298">🇫🇴 +298</option>
                                <option value="299">🇬🇱 +299</option>
                                <option value="350">🇬🇮 +350</option>
                                <option value="351">🇵🇹 +351</option>
                                <option value="352">🇱🇺 +352</option>
                                <option value="353">🇮🇪 +353</option>
                                <option value="354">🇮🇸 +354</option>
                                <option value="355">🇦🇱 +355</option>
                                <option value="356">🇲🇹 +356</option>
                                <option value="357">🇨🇾 +357</option>
                                <option value="358">🇫🇮 +358</option>
                                <option value="359">🇧🇬 +359</option>
                                <option value="370">🇱🇹 +370</option>
                                <option value="371">🇱🇻 +371</option>
                                <option value="372">🇪🇪 +372</option>
                                <option value="373">🇲🇩 +373</option>
                                <option value="374">🇦🇲 +374</option>
                                <option value="375">🇧🇾 +375</option>
                                <option value="376">🇦🇩 +376</option>
                                <option value="377">🇲🇨 +377</option>
                                <option value="378">🇸🇲 +378</option>
                                <option value="380">🇺🇦 +380</option>
                                <option value="381">🇷🇸 +381</option>
                                <option value="382">🇲🇪 +382</option>
                                <option value="383">🇽🇰 +383</option>
                                <option value="385">🇭🇷 +385</option>
                                <option value="386">🇸🇮 +386</option>
                                <option value="387">🇧🇦 +387</option>
                                <option value="389">🇲🇰 +389</option>
                                <option value="420">🇨🇿 +420</option>
                                <option value="421">🇸🇰 +421</option>
                                <option value="423">🇱🇮 +423</option>
                                <option value="500">🇫🇰 +500</option>
                                <option value="501">🇧🇿 +501</option>
                                <option value="502">🇬🇹 +502</option>
                                <option value="503">🇸🇻 +503</option>
                                <option value="504">🇭🇳 +504</option>
                                <option value="505">🇳🇮 +505</option>
                                <option value="506">🇨🇷 +506</option>
                                <option value="507">🇵🇦 +507</option>
                                <option value="508">🇵🇲 +508</option>
                                <option value="509">🇭🇹 +509</option>
                                <option value="590">🇬🇵 +590</option>
                                <option value="591">🇧🇴 +591</option>
                                <option value="592">🇬🇾 +592</option>
                                <option value="593">🇪🇨 +593</option>
                                <option value="594">🇬🇫 +594</option>
                                <option value="595">🇵🇾 +595</option>
                                <option value="596">🇲🇶 +596</option>
                                <option value="597">🇸🇷 +597</option>
                                <option value="598">🇺🇾 +598</option>
                                <option value="599">🇨🇼 +599</option>
                                <option value="670">🇹🇱 +670</option>
                                <option value="672">🇳🇫 +672</option>
                                <option value="673">🇧🇳 +673</option>
                                <option value="674">🇳🇷 +674</option>
                                <option value="675">🇵🇬 +675</option>
                                <option value="676">🇹🇴 +676</option>
                                <option value="677">🇸🇧 +677</option>
                                <option value="678">🇻🇺 +678</option>
                                <option value="679">🇫🇯 +679</option>
                                <option value="680">🇵🇼 +680</option>
                                <option value="681">🇼🇫 +681</option>
                                <option value="682">🇨🇰 +682</option>
                                <option value="683">🇳🇺 +683</option>
                                <option value="685">🇼🇸 +685</option>
                                <option value="686">🇰🇮 +686</option>
                                <option value="687">🇳🇨 +687</option>
                                <option value="688">🇹🇻 +688</option>
                                <option value="689">🇵🇫 +689</option>
                                <option value="690">🇹🇰 +690</option>
                                <option value="691">🇫🇲 +691</option>
                                <option value="692">🇲🇭 +692</option>
                                <option value="850">🇰🇵 +850</option>
                                <option value="852">🇭🇰 +852</option>
                                <option value="853">🇲🇴 +853</option>
                                <option value="855">🇰🇭 +855</option>
                                <option value="856">🇱🇦 +856</option>
                                <option value="880">🇧🇩 +880</option>
                                <option value="886">🇹🇼 +886</option>
                                <option value="960">🇲🇻 +960</option>
                                <option value="961">🇱🇧 +961</option>
                                <option value="962">🇯🇴 +962</option>
                                <option value="963">🇸🇾 +963</option>
                                <option value="964">🇮🇶 +964</option>
                                <option value="965">🇰🇼 +965</option>
                                <option value="966">🇸🇦 +966</option>
                                <option value="967">🇾🇪 +967</option>
                                <option value="968">🇴🇲 +968</option>
                                <option value="970">🇵🇸 +970</option>
                                <option value="971">🇦🇪 +971</option>
                                <option value="972">🇮🇱 +972</option>
                                <option value="973">🇧🇭 +973</option>
                                <option value="974">🇶🇦 +974</option>
                                <option value="975">🇧🇹 +975</option>
                                <option value="976">🇲🇳 +976</option>
                                <option value="977">🇳🇵 +977</option>
                                <option value="992">🇹🇯 +992</option>
                                <option value="993">🇹🇲 +993</option>
                                <option value="994">🇦🇿 +994</option>
                                <option value="995">🇬🇪 +995</option>
                                <option value="996">🇰🇬 +996</option>
                                <option value="998">🇺🇿 +998</option>
                            </select>
                            <input type="text" name="telefone" id="createTelefone" class="form-control" placeholder="+55 (00) 00000-0000" style="flex:1;">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="padding: 10px; background: rgba(76,29,149,0.05); border: 1px dashed var(--primary); border-radius: var(--radius-sm);">
                    <label style="color: var(--primary); font-weight: 700;"><i class="fas fa-lock" style="margin-right: 5px;"></i> Senha Provisória</label>
                    <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); margin-top: 5px;">Basileia123</div>
                    <div class="field-hint" style="color: var(--text-secondary); margin-top: 5px;">O vendedor será <b>obrigado</b> a trocar esta senha no primeiro acesso.</div>
                </div>
            </div>

            <!-- Seção 2: Função e Equipe -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-user-tag"></i> Função e Equipe</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Perfil <span class="required">*</span></label>
                        <select name="perfil" id="createPerfil" class="form-control" onchange="toggleGestorFields()">
                            <option value="vendedor" selected>Vendedor</option>
                            <option value="gestor">Gestor de Equipe</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="createGestorRow">
                    <label>Gestor Responsável</label>
                    <select name="gestor_id" class="form-control">
                        <option value="">Nenhum (equipe do Admin)</option>
                        @foreach($gestores ?? [] as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Seção 3: Comissões -->
            <div class="form-section" id="vendedorComissaoSection">
                <div class="form-section-title"><i class="fas fa-hand-holding-dollar"></i> Comissões do Vendedor</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Comissão Inicial (%) <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="comissao_inicial" class="form-control commission-input" required min="0" max="100" placeholder="10">
                            <span class="input-group-addon">%</span>
                        </div>
                        <div class="field-hint">% sobre o valor na primeira venda. Máx: 100%.</div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Comissão Recorrência (%) <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="comissao_recorrencia" class="form-control commission-input" required min="0" max="100" placeholder="5">
                            <span class="input-group-addon">%</span>
                        </div>
                        <div class="field-hint">% sobre o valor em renovações. Máx: 100%.</div>
                    </div>
                </div>
            </div>

            <!-- Seção 3B: Comissões do Gestor (só para perfil gestor) -->
            <div class="form-section" id="gestorComissaoSection" style="display: none;">
                <div class="form-section-title"><i class="fas fa-user-tie"></i> Comissões do Gestor</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Comissão Gestor - 1ª Venda (%)</label>
                        <input type="number" step="0.01" name="comissao_gestor_primeira" class="form-control" placeholder="3.00">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Comissão Gestor - Recorrência (%)</label>
                        <input type="number" step="0.01" name="comissao_gestor_recorrencia" class="form-control" placeholder="1.00">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('createModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Cadastrar Vendedor</button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL: Editar Vendedor ========== -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-pen" style="margin-right: 8px;"></i>Editar Vendedor</h2>
            <button class="modal-close" onclick="BasileiaModal.close('editModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding-top: 0;">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('edit-tab-dados', this)">📋 Dados Gerais</button>
                <button class="tab-btn" onclick="switchTab('edit-tab-comissoes', this)">💰 Comissões</button>
                <button class="tab-btn" onclick="switchTab('edit-tab-split', this)">🔗 Split Asaas</button>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <!-- Aba: Dados Gerais -->
                <div id="edit-tab-dados" class="tab-content active">
                    <div class="form-group">
                        <label>Nome Completo <span class="required">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>E-mail <span class="required">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                            <div class="form-group">
                                <label>Telefone</label>
                                <div style="display:flex; gap:8px;">
                                    <select id="editTelefoneDdi" class="form-control" style="flex:0 0 120px; font-size:0.82rem;">
                                        <option value="55">🇧🇷 +55</option>
                                        <option value="1">🇺🇸 +1</option>
                                        <option value="7">🇷🇺 +7</option>
                                        <option value="20">🇪🇬 +20</option>
                                        <option value="27">🇿🇦 +27</option>
                                        <option value="30">🇬🇷 +30</option>
                                        <option value="31">🇳🇱 +31</option>
                                        <option value="32">🇧🇪 +32</option>
                                        <option value="33">🇫🇷 +33</option>
                                        <option value="34">🇪🇸 +34</option>
                                        <option value="36">🇭🇺 +36</option>
                                        <option value="39">🇮🇹 +39</option>
                                        <option value="40">🇷🇴 +40</option>
                                        <option value="41">🇨🇭 +41</option>
                                        <option value="43">🇦🇹 +43</option>
                                        <option value="44">🇬🇧 +44</option>
                                        <option value="45">🇩🇰 +45</option>
                                        <option value="46">🇸🇪 +46</option>
                                        <option value="47">🇳🇴 +47</option>
                                        <option value="48">🇵🇱 +48</option>
                                        <option value="49">🇩🇪 +49</option>
                                        <option value="51">🇵🇪 +51</option>
                                        <option value="52">🇲🇽 +52</option>
                                        <option value="53">🇨🇺 +53</option>
                                        <option value="54">🇦🇷 +54</option>
                                        <option value="56">🇨🇱 +56</option>
                                        <option value="57">🇨🇴 +57</option>
                                        <option value="58">🇻🇪 +58</option>
                                        <option value="60">🇲🇾 +60</option>
                                        <option value="61">🇦🇺 +61</option>
                                        <option value="62">🇮🇩 +62</option>
                                        <option value="63">🇵🇭 +63</option>
                                        <option value="64">🇳🇿 +64</option>
                                        <option value="65">🇸🇬 +65</option>
                                        <option value="66">🇹🇭 +66</option>
                                        <option value="81">🇯🇵 +81</option>
                                        <option value="82">🇰🇷 +82</option>
                                        <option value="84">🇻🇳 +84</option>
                                        <option value="86">🇨🇳 +86</option>
                                        <option value="90">🇹🇷 +90</option>
                                        <option value="91">🇮🇳 +91</option>
                                        <option value="92">🇵🇰 +92</option>
                                        <option value="93">🇦🇫 +93</option>
                                        <option value="94">🇱🇰 +94</option>
                                        <option value="95">🇲🇲 +95</option>
                                        <option value="98">🇮🇷 +98</option>
                                        <option value="212">🇲🇦 +212</option>
                                        <option value="213">🇩🇿 +213</option>
                                        <option value="216">🇹🇳 +216</option>
                                        <option value="218">🇱🇾 +218</option>
                                        <option value="220">🇬🇲 +220</option>
                                        <option value="221">🇸🇳 +221</option>
                                        <option value="222">🇲🇷 +222</option>
                                        <option value="223">🇲🇱 +223</option>
                                        <option value="224">🇬🇳 +224</option>
                                        <option value="225">🇨🇮 +225</option>
                                        <option value="226">🇧🇫 +226</option>
                                        <option value="227">🇳🇪 +227</option>
                                        <option value="228">🇹🇬 +228</option>
                                        <option value="229">🇧🇯 +229</option>
                                        <option value="230">🇲🇺 +230</option>
                                        <option value="231">🇱🇷 +231</option>
                                        <option value="232">🇸🇱 +232</option>
                                        <option value="233">🇬🇭 +233</option>
                                        <option value="234">🇳🇬 +234</option>
                                        <option value="235">🇹🇩 +235</option>
                                        <option value="236">🇨🇫 +236</option>
                                        <option value="237">🇨🇲 +237</option>
                                        <option value="238">🇨🇻 +238</option>
                                        <option value="239">🇸🇹 +239</option>
                                        <option value="240">🇬🇶 +240</option>
                                        <option value="241">🇬🇦 +241</option>
                                        <option value="242">🇨🇬 +242</option>
                                        <option value="243">🇨🇩 +243</option>
                                        <option value="244">🇦🇴 +244</option>
                                        <option value="245">🇬🇼 +245</option>
                                        <option value="246">🇮🇴 +246</option>
                                        <option value="247">🇦🇨 +247</option>
                                        <option value="248">🇸🇨 +248</option>
                                        <option value="249">🇸🇩 +249</option>
                                        <option value="250">🇷🇼 +250</option>
                                        <option value="251">🇪🇹 +251</option>
                                        <option value="252">🇸🇴 +252</option>
                                        <option value="253">🇩🇯 +253</option>
                                        <option value="254">🇰🇪 +254</option>
                                        <option value="255">🇹🇿 +255</option>
                                        <option value="256">🇺🇬 +256</option>
                                        <option value="257">🇧🇮 +257</option>
                                        <option value="258">🇲🇿 +258</option>
                                        <option value="260">🇿🇲 +260</option>
                                        <option value="261">🇲🇬 +261</option>
                                        <option value="262">🇷🇪 +262</option>
                                        <option value="263">🇿🇼 +263</option>
                                        <option value="264">🇳🇦 +264</option>
                                        <option value="265">🇲🇼 +265</option>
                                        <option value="266">🇱🇸 +266</option>
                                        <option value="267">🇧🇼 +267</option>
                                        <option value="268">🇸🇿 +268</option>
                                        <option value="269">🇰🇲 +269</option>
                                        <option value="290">🇸🇭 +290</option>
                                        <option value="291">🇪🇷 +291</option>
                                        <option value="297">🇦🇼 +297</option>
                                        <option value="298">🇫🇴 +298</option>
                                        <option value="299">🇬🇱 +299</option>
                                        <option value="350">🇬🇮 +350</option>
                                        <option value="351">🇵🇹 +351</option>
                                        <option value="352">🇱🇺 +352</option>
                                        <option value="353">🇮🇪 +353</option>
                                        <option value="354">🇮🇸 +354</option>
                                        <option value="355">🇦🇱 +355</option>
                                        <option value="356">🇲🇹 +356</option>
                                        <option value="357">🇨🇾 +357</option>
                                        <option value="358">🇫🇮 +358</option>
                                        <option value="359">🇧🇬 +359</option>
                                        <option value="370">🇱🇹 +370</option>
                                        <option value="371">🇱🇻 +371</option>
                                        <option value="372">🇪🇪 +372</option>
                                        <option value="373">🇲🇩 +373</option>
                                        <option value="374">🇦🇲 +374</option>
                                        <option value="375">🇧🇾 +375</option>
                                        <option value="376">🇦🇩 +376</option>
                                        <option value="377">🇲🇨 +377</option>
                                        <option value="378">🇸🇲 +378</option>
                                        <option value="380">🇺🇦 +380</option>
                                        <option value="381">🇷🇸 +381</option>
                                        <option value="382">🇲🇪 +382</option>
                                        <option value="383">🇽🇰 +383</option>
                                        <option value="385">🇭🇷 +385</option>
                                        <option value="386">🇸🇮 +386</option>
                                        <option value="387">🇧🇦 +387</option>
                                        <option value="389">🇲🇰 +389</option>
                                        <option value="420">🇨🇿 +420</option>
                                        <option value="421">🇸🇰 +421</option>
                                        <option value="423">🇱🇮 +423</option>
                                        <option value="500">🇫🇰 +500</option>
                                        <option value="501">🇧🇿 +501</option>
                                        <option value="502">🇬🇹 +502</option>
                                        <option value="503">🇸🇻 +503</option>
                                        <option value="504">🇭🇳 +504</option>
                                        <option value="505">🇳🇮 +505</option>
                                        <option value="506">🇨🇷 +506</option>
                                        <option value="507">🇵🇦 +507</option>
                                        <option value="508">🇵🇲 +508</option>
                                        <option value="509">🇭🇹 +509</option>
                                        <option value="590">🇬🇵 +590</option>
                                        <option value="591">🇧🇴 +591</option>
                                        <option value="592">🇬🇾 +592</option>
                                        <option value="593">🇪🇨 +593</option>
                                        <option value="594">🇬🇫 +594</option>
                                        <option value="595">🇵🇾 +595</option>
                                        <option value="596">🇲🇶 +596</option>
                                        <option value="597">🇸🇷 +597</option>
                                        <option value="598">🇺🇾 +598</option>
                                        <option value="599">🇨🇼 +599</option>
                                        <option value="670">🇹🇱 +670</option>
                                        <option value="672">🇳🇫 +672</option>
                                        <option value="673">🇧🇳 +673</option>
                                        <option value="674">🇳🇷 +674</option>
                                        <option value="675">🇵🇬 +675</option>
                                        <option value="676">🇹🇴 +676</option>
                                        <option value="677">🇸🇧 +677</option>
                                        <option value="678">🇻🇺 +678</option>
                                        <option value="679">🇫🇯 +679</option>
                                        <option value="680">🇵🇼 +680</option>
                                        <option value="681">🇼🇫 +681</option>
                                        <option value="682">🇨🇰 +682</option>
                                        <option value="683">🇳🇺 +683</option>
                                        <option value="685">🇼🇸 +685</option>
                                        <option value="686">🇰🇮 +686</option>
                                        <option value="687">🇳🇨 +687</option>
                                        <option value="688">🇹🇻 +688</option>
                                        <option value="689">🇵🇫 +689</option>
                                        <option value="690">🇹🇰 +690</option>
                                        <option value="691">🇫🇲 +691</option>
                                        <option value="692">🇲🇭 +692</option>
                                        <option value="850">🇰🇵 +850</option>
                                        <option value="852">🇭🇰 +852</option>
                                        <option value="853">🇲🇴 +853</option>
                                        <option value="855">🇰🇭 +855</option>
                                        <option value="856">🇱🇦 +856</option>
                                        <option value="880">🇧🇩 +880</option>
                                        <option value="886">🇹🇼 +886</option>
                                        <option value="960">🇲🇻 +960</option>
                                        <option value="961">🇱🇧 +961</option>
                                        <option value="962">🇯🇴 +962</option>
                                        <option value="963">🇸🇾 +963</option>
                                        <option value="964">🇮🇶 +964</option>
                                        <option value="965">🇰🇼 +965</option>
                                        <option value="966">🇸🇦 +966</option>
                                        <option value="967">🇾🇪 +967</option>
                                        <option value="968">🇴🇲 +968</option>
                                        <option value="970">🇵🇸 +970</option>
                                        <option value="971">🇦🇪 +971</option>
                                        <option value="972">🇮🇱 +972</option>
                                        <option value="973">🇧🇭 +973</option>
                                        <option value="974">🇶🇦 +974</option>
                                        <option value="975">🇧🇹 +975</option>
                                        <option value="976">🇲🇳 +976</option>
                                        <option value="977">🇳🇵 +977</option>
                                        <option value="992">🇹🇯 +992</option>
                                        <option value="993">🇹🇲 +993</option>
                                        <option value="994">🇦🇿 +994</option>
                                        <option value="995">🇬🇪 +995</option>
                                        <option value="996">🇰🇬 +996</option>
                                        <option value="998">🇺🇿 +998</option>
                                    </select>
                                    <input type="text" name="telefone" id="editTelefone" class="form-control" style="flex:1;">
                                </div>
                            </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nova Senha</label>
                            <input type="text" name="password" class="form-control" placeholder="Deixe vazio para manter a atual">
                            <div class="field-hint">Preencha apenas se deseja alterar.</div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="editStatus" class="form-control">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Perfil <span class="required">*</span></label>
                            <select name="perfil" id="editPerfil" class="form-control">
                                <option value="vendedor">Vendedor</option>
                                <option value="gestor">Gestor de Equipe</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gestor Responsável</label>
                            <select name="gestor_id" id="editGestor" class="form-control">
                                <option value="">Nenhum (equipe do Admin)</option>
                                @foreach($gestores ?? [] as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Aba: Comissões -->
                <div id="edit-tab-comissoes" class="tab-content">
                    <div class="form-section" style="margin-top: 12px;">
                        <div class="form-section-title"><i class="fas fa-chart-bar"></i> Comissões do Vendedor</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Comissão Inicial (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="comissao_inicial" id="editComissaoInicial" class="form-control commission-input" min="0" max="100">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Comissão Recorrência (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="comissao_recorrencia" id="editComissaoRecorrencia" class="form-control commission-input" min="0" max="100">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-user-tie"></i> Comissões do Gestor</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Gestor - 1ª Venda (R$)</label>
                                <input type="number" step="0.01" name="comissao_gestor_primeira" id="editComissaoGestorPrimeira" class="form-control">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Gestor - Recorrência (R$)</label>
                                <input type="number" step="0.01" name="comissao_gestor_recorrencia" id="editComissaoGestorRecorrencia" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aba: Split Asaas -->
                <div id="edit-tab-split" class="tab-content">
                    <div class="form-section" style="margin-top: 12px;">
                        <div class="form-section-title"><i class="fas fa-link"></i> Configuração de Split</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Wallet ID (Asaas)</label>
                                <input type="text" name="asaas_wallet_id" id="editWalletId" class="form-control" placeholder="ID da wallet no Asaas">
                            </div>
                            <div class="form-group">
                                <label>Tipo de Split</label>
                                <select name="tipo_split" id="editTipoSplit" class="form-control">
                                    <option value="percentual">Percentual (%)</option>
                                    <option value="fixo">Valor Fixo (R$)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Valor Split - Inicial</label>
                                <input type="number" step="0.01" name="valor_split_inicial" id="editSplitInicial" class="form-control" placeholder="0.00">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Valor Split - Recorrência</label>
                                <input type="number" step="0.01" name="valor_split_recorrencia" id="editSplitRecorrencia" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('editModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODAL: Visualizar Vendedor ========== -->
<div class="modal-overlay" id="viewModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-user" style="margin-right: 8px;"></i>Detalhes do Vendedor</h2>
            <button class="modal-close" onclick="BasileiaModal.close('viewModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Nome</label>
                    <div class="value" id="viewName"></div>
                </div>
                <div class="info-item">
                    <label>E-mail</label>
                    <div class="value" id="viewEmail" style="font-weight: 600;"></div>
                </div>
                <div class="info-item">
                    <label>Telefone</label>
                    <div class="value" id="viewTelefone" style="font-weight: 600;"></div>
                </div>
                <div class="info-item">
                    <label>Perfil</label>
                    <div class="value" id="viewPerfil"></div>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <div id="viewStatus"></div>
                </div>
                <div class="info-item">
                    <label>Gestor</label>
                    <div class="value" id="viewGestor" style="font-weight: 600;"></div>
                </div>
                 <div class="info-item">
                     <label>Comissão Inicial (Venda)</label>
                     <div class="value text-primary" id="viewComissaoInicial"></div>
                 </div>
                 <div class="info-item">
                     <label>Comissão Recorrência (Venda)</label>
                     <div class="value text-primary" id="viewComissaoRecorrencia"></div>
                 </div>
                 <div class="info-item">
                     <label>Comissão Gestor (1ª Venda)</label>
                     <div class="value text-primary" id="viewComissaoGestorPrimeira"></div>
                 </div>
                 <div class="info-item">
                     <label>Comissão Gestor (Recorrência)</label>
                     <div class="value text-primary" id="viewComissaoGestorRecorrencia"></div>
                 </div>
                 <div class="info-item">
                     <label>Meta Mensal</label>
                     <div class="value text-primary" id="viewMeta"></div>
                 </div>
                 <div class="info-item">
                    <label>Cadastrado em</label>
                    <div class="value" id="viewCreated" style="font-weight: 600; color: var(--text-secondary);"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('viewModal')">Fechar</button>
        </div>
    </div>
</div>

<!-- ========== MODAL: Selecionar Equipe ========== -->
<div class="modal-overlay" id="equipeModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-people-group" style="margin-right: 8px;"></i>Selecionar Equipe</h2>
            <button class="modal-close" onclick="BasileiaModal.close('equipeModal')">&times;</button>
        </div>
        <form id="equipeForm" method="POST" class="modal-body">
            @csrf
            <div class="form-group">
                <label>Vendedor</label>
                <div id="equipeModalVendedorNome" style="font-weight: 700; font-size: 1rem; color: var(--primary); padding: 8px 0;"></div>
            </div>
            <div class="form-group">
                <label>Equipe <span class="required">*</span></label>
                <select name="equipe_id" id="equipeModalSelect" class="form-control" required>
                    <option value="">Selecione uma equipe</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('equipeModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Atribuir Equipe</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function toggleGestorFields() {
        var perfil = document.getElementById('createPerfil').value;
        document.getElementById('createGestorRow').style.display = perfil === 'vendedor' ? 'block' : 'none';
        
        // Always show vendor commission section (both vendedor and gestor can earn sales commission)
        document.getElementById('vendedorComissaoSection').style.display = 'block';
        
        // Only show manager commission section when creating a manager
        document.getElementById('gestorComissaoSection').style.display = perfil === 'gestor' ? 'block' : 'none';
    }

    function openViewModal(data) {
        document.getElementById('viewName').textContent = data.name;
        document.getElementById('viewEmail').textContent = data.email;
        document.getElementById('viewTelefone').textContent = data.telefone;
        document.getElementById('viewPerfil').innerHTML = data.perfil === 'gestor'
            ? '<span class="badge badge-info">Gestor</span>'
            : '<span class="badge badge-secondary">Vendedor</span>';
        document.getElementById('viewStatus').innerHTML = '<span class="badge badge-' + (data.status === 'ativo' ? 'success' : (data.status === 'bloqueado' ? 'danger' : 'warning')) + '">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span>';
        document.getElementById('viewGestor').textContent = data.gestor_nome || 'Nenhum';
         document.getElementById('viewComissaoInicial').textContent = parseFloat(data.comissao_inicial || 0).toFixed(1) + '%';
         document.getElementById('viewComissaoRecorrencia').textContent = parseFloat(data.comissao_recorrencia || 0).toFixed(1) + '%';
         document.getElementById('viewComissaoGestorPrimeira').textContent = parseFloat(data.comissao_gestor_primeira || 0).toFixed(1) + '%';
         document.getElementById('viewComissaoGestorRecorrencia').textContent = parseFloat(data.comissao_gestor_recorrencia || 0).toFixed(1) + '%';
         document.getElementById('viewMeta').textContent = 'R$ ' + parseFloat(data.meta_mensal || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2});
         document.getElementById('viewCreated').textContent = data.created_at;
        BasileiaModal.open('viewModal');
    }

    function openEditModal(data) {
        var form = document.getElementById('editForm');
        form.action = '/master/vendedores/' + data.id;
        document.getElementById('editName').value = data.name;
        document.getElementById('editEmail').value = data.email;
        document.getElementById('editTelefone').value = data.telefone;
        document.getElementById('editStatus').value = data.status;
        document.getElementById('editPerfil').value = data.perfil || 'vendedor';
        document.getElementById('editGestor').value = data.gestor_id || '';
        document.getElementById('editComissaoInicial').value = data.comissao_inicial || data.comissao || 0;
        document.getElementById('editComissaoRecorrencia').value = data.comissao_recorrencia || data.comissao || 0;
        document.getElementById('editComissaoGestorPrimeira').value = data.comissao_gestor_primeira || 0;
        document.getElementById('editComissaoGestorRecorrencia').value = data.comissao_gestor_recorrencia || 0;
        document.getElementById('editWalletId').value = data.asaas_wallet_id || '';
        document.getElementById('editTipoSplit').value = data.tipo_split || 'percentual';
        document.getElementById('editSplitInicial').value = data.valor_split_inicial || 0;
        document.getElementById('editSplitRecorrencia').value = data.valor_split_recorrencia || 0;

        // Reset to first tab
        var firstTab = document.querySelector('#editModal .tab-btn');
        document.querySelectorAll('#editModal .tab-content').forEach(function(c) { c.classList.remove('active'); });
        document.querySelectorAll('#editModal .tab-btn').forEach(function(b) { b.classList.remove('active'); });
        firstTab.classList.add('active');
        document.getElementById('edit-tab-dados').classList.add('active');

        // Always show vendor commissions section (both vendedor and gestor can earn sales commission)
        document.getElementById('edit-tab-comissoes').style.display = 'block';

        BasileiaModal.open('editModal');
    }

    function filterTable() {
        var search = document.getElementById('searchInput').value.toLowerCase();
        var status = document.getElementById('statusFilter').value;
        document.querySelectorAll('.vendedor-row').forEach(function(row) {
            var matchSearch = !search || row.dataset.name.includes(search) || row.dataset.email.includes(search) || row.dataset.telefone.includes(search);
            var matchStatus = !status || row.dataset.status === status;
            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    function openEquipeModal(vendedorId, vendedorNome, equipes) {
        document.getElementById('equipeForm').action = '/master/vendedores/' + vendedorId + '/equipe';
        document.getElementById('equipeModalVendedorNome').textContent = vendedorNome;
        var select = document.getElementById('equipeModalSelect');
        select.innerHTML = '<option value="">Selecione uma equipe</option>';
        equipes.forEach(function(eq) {
            select.innerHTML += '<option value="' + eq.id + '">' + eq.nome + '</option>';
        });
        BasileiaModal.open('equipeModal');
    }

    // === Phone mask with DDI ===
    function applyPhoneMask(input) {
        input.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '').substring(0, 11);
            if (v.length > 6) this.value = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
            else if (v.length > 2) this.value = '(' + v.substring(0,2) + ') ' + v.substring(2);
            else if (v.length > 0) this.value = '(' + v;
        });
    }
    var ct = document.getElementById('createTelefone');
    if (ct) applyPhoneMask(ct);
    var et = document.getElementById('editTelefone');
    if (et) applyPhoneMask(et);

    // === Commission max 100 ===
    document.querySelectorAll('.commission-input').forEach(function(input) {
        input.addEventListener('input', function() {
            if (parseFloat(this.value) > 100) this.value = 100;
            if (parseFloat(this.value) < 0) this.value = 0;
        });
    });

    // === Parse phone with DDI on edit ===
    var origOpenEdit = openEditModal;
    openEditModal = function(data) {
        origOpenEdit(data);
        var tel = data.telefone || '';
        var ddi = '55';
        var num = tel.replace(/\D/g, '');
        if (tel.startsWith('+')) {
            var match = tel.match(/^\+(\d{1,3})/);
            if (match) { ddi = match[1]; num = num.substring(ddi.length); }
        }
        var el = document.getElementById('editTelefoneDdi');
        if (el) el.value = ddi;
        var et = document.getElementById('editTelefone');
        if (et) { et.value = num; }
    };
</script>
@endsection
