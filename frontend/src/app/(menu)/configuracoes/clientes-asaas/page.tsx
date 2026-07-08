"use client";

import React, { useState, useEffect } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { 
  ArrowLeft, Cloud, History, RefreshCw, Search, Filter, Users, 
  CheckCircle2, AlertTriangle, XCircle, UserX, DollarSign, 
  ChevronDown, Eye, Edit2, UserCheck, AlertCircle, UserMinus, 
  ChevronLeft, ChevronRight, MoreHorizontal, ArrowUpRight, Check
} from "lucide-react";
import { api } from "@/lib/api";
import { toast } from "sonner";
import CustomSelect from "@/components/CustomSelect";
import Pagination from "@/components/Pagination";

type Tab = "todos" | "ativos" | "churn" | "cancelados" | "sem_vendedor";

export default function ClientesAsaasPage() {
  const [activeTab, setActiveTab] = useState<Tab>("todos");
  const [selectedClients, setSelectedClients] = useState<number[]>([]);
  const [isSyncing, setIsSyncing] = useState(false);

  // Filtros
  const [busca, setBusca] = useState("");
  const [vendedorFilter, setVendedorFilter] = useState("todos");
  const [tipoFilter, setTipoFilter] = useState("todas");
  const [currentPage, setCurrentPage] = useState(1);

  // Dados reais
  const [clients, setClients] = useState<any[]>([]);
  const [vendedores, setVendedores] = useState<any[]>([]);
  const [kpis, setKpis] = useState<any>({
    total: 0, ativos: 0, churn: 0, cancelados: 0, sem_vendedor: 0
  });
  const [totalItems, setTotalItems] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const [loading, setLoading] = useState(false);

  const fetchClientes = async () => {
    setLoading(true);
    try {
      const searchParams = new URLSearchParams();
      searchParams.append("page", String(currentPage));
      searchParams.append("aba", activeTab);
      if (busca) searchParams.append("search", busca);
      if (vendedorFilter !== "todos") searchParams.append("vendedor_id", vendedorFilter);
      if (tipoFilter !== "todas") searchParams.append("tipo_cobranca", tipoFilter);

      const res = await api.get<any>(`/clientes-asaas?${searchParams.toString()}`);
      
      if (res.success) {
        setClients(res.data || []);
        setKpis(res.kpis || {});
        setVendedores(res.vendedores || []);
        if (res.meta) {
          setTotalItems(res.meta.total);
          setLastPage(res.meta.last_page);
        }
      }
    } catch (e) {
      toast.error("Erro ao carregar clientes do Asaas");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchClientes();
  }, [currentPage, activeTab, busca, vendedorFilter, tipoFilter]);

  const handleSyncAsaas = async () => {
    setIsSyncing(true);
    const toastId = toast.loading("Iniciando sincronização com Asaas...");
    try {
      let currentOffset = 0;
      let hasMore = true;

      while (hasMore) {
        toast.loading(`Sincronizando... (Offset: ${currentOffset})`, { id: toastId });
        const res = await api.post<any>('/clientes-asaas/sincronizar', { offset: currentOffset });
        
        if (res.success) {
          hasMore = !!res.hasMore;
          currentOffset = res.nextOffset || (currentOffset + 20);
        } else {
          toast.error(res.message || "Erro na sincronização.", { id: toastId });
          hasMore = false;
        }
      }
      
      toast.success("Sincronização concluída com sucesso!", { id: toastId });
      fetchClientes(); // Atualiza a tabela com os clientes syncados
    } catch (err) {
      console.error(err);
      toast.error("Falha ao comunicar com o servidor.", { id: toastId });
    } finally {
      setIsSyncing(false);
    }
  };

  const vendedorOptions = [
    { value: "todos", label: "Vendedor: Todos" },
    ...vendedores.map(v => ({ value: String(v.id), label: v.nome })),
    { value: "sem_vendedor", label: "Sem Vendedor" }
  ];

  const tipoOptions = [
    { value: "todas", label: "Tipo: Todos" },
    { value: "subscription", label: "Assinatura" },
    { value: "installment", label: "Parcelamento" }
  ];

  const toggleSelectAll = () => {
    if (selectedClients.length === clients.length) setSelectedClients([]);
    else setSelectedClients(clients.map(c => c.id));
  };

  const toggleSelect = (id: number) => {
    setSelectedClients(prev => 
      prev.includes(id) ? prev.filter(cId => cId !== id) : [...prev, id]
    );
  };
  
  // Formatters
  const formatDate = (dateStr: string) => {
    if (!dateStr) return "Nunca pagou";
    const date = new Date(dateStr);
    return date.toLocaleDateString("pt-BR");
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F8FAFC]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1400px] mx-auto gap-[24px]">
            
            {/* VOLTAR */}
            <Link 
              href="/configuracoes"
              className="flex items-center gap-[8px] text-[14px] font-[600] text-[#64748B] hover:text-[#0F172A] transition-colors w-fit"
            >
              <ArrowLeft className="w-[16px] h-[16px]" />
              Voltar para Configurações
            </Link>

            {/* HEADER BANNER PREMIUM */}
            <div className="relative overflow-hidden rounded-[16px] bg-gradient-to-r from-[#1E1B4B] via-[#312E81] to-[#4338CA] p-[32px] flex flex-col md:flex-row md:items-center justify-between shadow-lg">
              {/* Elemento de background decorativo */}
              <div className="absolute top-0 right-0 w-[400px] h-[400px] bg-white opacity-5 rounded-full blur-[80px] -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
              
              <div className="flex items-center gap-[20px] relative z-10">
                <div className="w-[56px] h-[56px] bg-white/10 backdrop-blur-md rounded-[16px] flex items-center justify-center border border-white/20">
                  <Cloud className="w-[28px] h-[28px] text-white" strokeWidth={1.5} />
                </div>
                <div className="flex flex-col">
                  <h1 className="text-[28px] font-[800] text-white tracking-tight leading-none mb-[8px]">
                    Clientes Asaas
                  </h1>
                  <p className="text-[14px] text-[#C7D2FE] font-[500] max-w-[500px] leading-snug">
                    Sincronize, classifique e atribua comissões aos clientes pré-existentes no Asaas.
                  </p>
                </div>
              </div>

              <div className="flex items-center gap-[12px] mt-[20px] md:mt-0 relative z-10">
                <button className="flex items-center gap-[8px] px-[16px] py-[10px] bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 transition-all rounded-[10px] text-white text-[13px] font-[600] shadow-sm">
                  <History className="w-[16px] h-[16px]" />
                  Auditoria Retroativa
                </button>
                <button 
                  onClick={handleSyncAsaas}
                  disabled={isSyncing}
                  className="flex items-center gap-[8px] px-[16px] py-[10px] bg-white text-[#312E81] hover:bg-[#F8FAFC] transition-all rounded-[10px] text-[13px] font-[700] shadow-sm disabled:opacity-70 disabled:cursor-not-allowed">
                  <RefreshCw className={`w-[16px] h-[16px] ${isSyncing ? 'animate-spin' : ''}`} />
                  {isSyncing ? 'Sincronizando...' : 'Sincronizar com Asaas'}
                </button>
              </div>
            </div>

            {/* KPI CARDS REDESIGN */}
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-[16px]">
              <div className="bg-white rounded-[12px] p-[20px] border border-[#E2E8F0] shadow-sm flex flex-col justify-center items-center text-center hover:border-[#6366F1] transition-colors cursor-default">
                <h3 className="text-[32px] font-[800] text-[#1E293B] leading-none mb-[6px]">{kpis.total || 0}</h3>
                <span className="text-[11px] font-[700] text-[#64748B] tracking-widest uppercase">TOTAL</span>
              </div>
              <div className="bg-white rounded-[12px] p-[20px] border border-[#E2E8F0] shadow-sm flex flex-col justify-center items-center text-center hover:border-[#10B981] transition-colors cursor-default">
                <h3 className="text-[32px] font-[800] text-[#10B981] leading-none mb-[6px]">{kpis.ativos || 0}</h3>
                <span className="text-[11px] font-[700] text-[#10B981] tracking-widest uppercase">ATIVOS</span>
              </div>
              <div className="bg-white rounded-[12px] p-[20px] border border-[#E2E8F0] shadow-sm flex flex-col justify-center items-center text-center hover:border-[#F59E0B] transition-colors cursor-default">
                <h3 className="text-[32px] font-[800] text-[#F59E0B] leading-none mb-[6px]">{kpis.churn || 0}</h3>
                <span className="text-[11px] font-[700] text-[#F59E0B] tracking-widest uppercase">CHURN</span>
              </div>
              <div className="bg-white rounded-[12px] p-[20px] border border-[#E2E8F0] shadow-sm flex flex-col justify-center items-center text-center hover:border-[#EF4444] transition-colors cursor-default">
                <h3 className="text-[32px] font-[800] text-[#EF4444] leading-none mb-[6px]">{kpis.cancelados || 0}</h3>
                <span className="text-[11px] font-[700] text-[#EF4444] tracking-widest uppercase">CANCELADOS</span>
              </div>
              <div className="bg-white rounded-[12px] p-[20px] border border-[#E2E8F0] shadow-sm flex flex-col justify-center items-center text-center hover:border-[#8B5CF6] transition-colors cursor-default">
                <h3 className="text-[32px] font-[800] text-[#8B5CF6] leading-none mb-[6px]">{kpis.sem_vendedor || 0}</h3>
                <span className="text-[11px] font-[700] text-[#8B5CF6] tracking-widest uppercase">SEM VENDEDOR</span>
              </div>
            </div>

            {/* TABELA COM CONTROLES MODERNOS */}
            <div className="bg-white rounded-[16px] border border-[#E2E8F0] shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden flex flex-col">
              
              {/* TABS E BUSCA */}
              <div className="flex flex-col border-b border-[#E2E8F0] p-[16px_24px] gap-[20px]">
                
                {/* Tabs Modernas */}
                <div className="flex items-center gap-[8px] overflow-x-auto pb-[4px] hide-scrollbar w-full border-b border-[#E2E8F0] pb-[16px]">
                  {[
                    { id: "todos", label: "Todos", count: kpis.total || 0, icon: Users },
                    { id: "ativos", label: "Ativos", count: kpis.ativos || 0, icon: CheckCircle2 },
                    { id: "churn", label: "Churn", count: kpis.churn || 0, icon: AlertTriangle },
                    { id: "cancelados", label: "Cancelados", count: kpis.cancelados || 0, icon: XCircle },
                    { id: "sem_vendedor", label: "Sem Vendedor", count: kpis.sem_vendedor || 0, icon: UserX }
                  ].map((tab) => (
                    <button
                      key={tab.id}
                      onClick={() => { setActiveTab(tab.id as Tab); setCurrentPage(1); }}
                      className={`flex items-center gap-[6px] px-[14px] py-[8px] rounded-full transition-all whitespace-nowrap text-[13px] font-[600] ${
                        activeTab === tab.id 
                          ? "bg-[#EEF2FF] text-[#4F46E5] shadow-sm" 
                          : "text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#334155]"
                      }`}
                    >
                      <tab.icon className={`w-[14px] h-[14px] ${activeTab === tab.id ? "text-[#4F46E5]" : "text-[#94A3B8]"}`} />
                      {tab.label}
                      <span className={`px-[6px] py-[2px] rounded-full text-[11px] font-[700] ml-[4px] ${
                        activeTab === tab.id ? "bg-[#C7D2FE] text-[#3730A3]" : "bg-[#E2E8F0] text-[#64748B]"
                      }`}>
                        {tab.count}
                      </span>
                    </button>
                  ))}
                </div>

                {/* Filtros */}
                <div className="flex flex-wrap items-end gap-[16px] w-full">
                  
                  {/* Busca */}
                  <div className="flex flex-col gap-[6px]">
                    <label className="text-[13px] font-[600] text-[#4B5563] uppercase">
                      BUSCAR
                    </label>
                    <div className="relative">
                      <Search className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-[12px] top-1/2 -translate-y-1/2" />
                      <input 
                        type="text" 
                        value={busca}
                        onChange={(e) => setBusca(e.target.value)}
                        placeholder="Nome, CPF, email..." 
                        className="pl-[36px] pr-[12px] w-full lg:w-[220px] h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                      />
                    </div>
                  </div>
                  
                  {/* Vendedor */}
                  <div className="flex flex-col gap-[6px] w-full lg:w-[160px]">
                    <label className="text-[13px] font-[600] text-[#4B5563] uppercase">
                      VENDEDOR
                    </label>
                    <CustomSelect 
                      options={vendedorOptions}
                      value={vendedorFilter}
                      onChange={(val) => { setVendedorFilter(val); setCurrentPage(1); }}
                      triggerClassName="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none hover:border-[#D1D5DB]"
                    />
                  </div>

                  {/* Tipo Cobrança */}
                  <div className="flex flex-col gap-[6px] w-full lg:w-[160px]">
                    <label className="text-[13px] font-[600] text-[#4B5563] uppercase">
                      TIPO COBRANÇA
                    </label>
                    <CustomSelect 
                      options={tipoOptions}
                      value={tipoFilter}
                      onChange={(val) => { setTipoFilter(val); setCurrentPage(1); }}
                      triggerClassName="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none hover:border-[#D1D5DB]"
                    />
                  </div>

                  {/* Botões Ação */}
                  <div className="flex items-center gap-[8px] h-[40px]">
                    <button onClick={fetchClientes} className="flex items-center justify-center px-[16px] h-full bg-[#6D28D9] hover:bg-[#5B21B6] text-white rounded-[8px] text-[14px] font-[600] transition-colors shadow-sm">
                      <Filter className="w-[14px] h-[14px] mr-[6px]" />
                      Filtrar
                    </button>
                    <button 
                      onClick={() => { setBusca(""); setVendedorFilter("todos"); setTipoFilter("todas"); setCurrentPage(1); }}
                      className="flex items-center justify-center px-[16px] h-full bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] text-[#4B5563] hover:text-[#111827] rounded-[8px] text-[14px] font-[600] transition-colors shadow-sm"
                    >
                      Limpar
                    </button>
                  </div>

                </div>
              </div>

              {/* BARRA DE AÇÕES EM MASSA (Elegante) */}
              {selectedClients.length > 0 && (
                <div className="bg-[#EEF2FF] border-b border-[#C7D2FE] p-[12px_24px] flex items-center justify-between animate-in fade-in slide-in-from-top-2 duration-200">
                  <div className="flex items-center gap-[12px]">
                    <div className="flex items-center justify-center w-[24px] h-[24px] bg-[#6366F1] text-white rounded-full text-[12px] font-[700]">
                      {selectedClients.length}
                    </div>
                    <span className="text-[14px] font-[600] text-[#3730A3]">clientes selecionados</span>
                  </div>
                  
                  <div className="flex items-center gap-[12px]">
                    <select className="px-[12px] py-[8px] bg-white border border-[#C7D2FE] rounded-[8px] text-[13px] text-[#3730A3] font-[500] outline-none focus:border-[#6366F1] w-[200px]">
                      <option value="">— Selecionar Vendedor —</option>
                      {vendedores.map(v => (
                        <option key={v.id} value={v.id}>{v.nome}</option>
                      ))}
                    </select>
                    <button className="flex items-center gap-[6px] px-[16px] py-[8px] bg-[#6366F1] hover:bg-[#4F46E5] text-white rounded-[8px] text-[13px] font-[600] transition-colors shadow-sm">
                      <UserCheck className="w-[14px] h-[14px]" />
                      Atribuir Vendedor
                    </button>
                  </div>
                </div>
              )}

              {/* TABELA */}
              <div className="overflow-x-auto relative">
                {loading && (
                   <div className="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-10">
                     <div className="w-[30px] h-[30px] border-[3px] border-[#6D28D9] border-t-transparent rounded-full animate-spin"></div>
                   </div>
                )}
                <table className="w-full text-left border-collapse">
                  <thead>
                    <tr className="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                      <th className="p-[16px_24px] w-[50px]">
                        <input 
                          type="checkbox" 
                          checked={clients.length > 0 && selectedClients.length === clients.length}
                          onChange={toggleSelectAll}
                          className="w-[16px] h-[16px] rounded-[4px] border-[#CBD5E1] text-[#6366F1] focus:ring-[#6366F1] cursor-pointer"
                        />
                      </th>
                      <th className="p-[16px_24px] text-[12px] font-[700] text-[#64748B] uppercase tracking-wider">Cliente</th>
                      <th className="p-[16px_24px] text-[12px] font-[700] text-[#64748B] uppercase tracking-wider">Status</th>
                      <th className="p-[16px_24px] text-[12px] font-[700] text-[#64748B] uppercase tracking-wider">Vendedor</th>
                      <th className="p-[16px_24px] text-[12px] font-[700] text-[#64748B] uppercase tracking-wider">Datas</th>
                      <th className="p-[16px_24px] text-[12px] font-[700] text-[#64748B] uppercase tracking-wider text-right">Ações</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-[#E2E8F0]">
                    {clients.map((client) => {
                      const st = client.diagnostico_status?.toLowerCase() || 'pendente';
                      const statusLabel = client.diagnostico_status || 'PENDENTE';
                      return (
                      <tr key={client.id} className="hover:bg-[#F8FAFC] transition-colors group">
                        <td className="p-[16px_24px]">
                          <input 
                            type="checkbox" 
                            checked={selectedClients.includes(client.id)}
                            onChange={() => toggleSelect(client.id)}
                            className="w-[16px] h-[16px] rounded-[4px] border-[#CBD5E1] text-[#6366F1] focus:ring-[#6366F1] cursor-pointer"
                          />
                        </td>
                        <td className="p-[16px_24px]">
                          <div className="flex flex-col">
                            <span className="text-[14px] font-[700] text-[#1E293B] mb-[2px]">{client.nome || client.nome_igreja}</span>
                            <span className="text-[12px] font-[500] text-[#64748B]">{client.documento}</span>
                            <span className="text-[12px] text-[#94A3B8]">{client.email}</span>
                          </div>
                        </td>
                        <td className="p-[16px_24px]">
                          <div className="flex flex-col items-start gap-[4px]">
                            <div className={`px-[8px] py-[4px] rounded-[6px] text-[11px] font-[700] uppercase tracking-wide inline-flex items-center gap-[4px] ${
                              st === 'ativo' ? 'bg-[#D1FAE5] text-[#059669]' :
                              st === 'churn' ? 'bg-[#FFEDD5] text-[#D97706]' :
                              st === 'cancelado' ? 'bg-[#FEE2E2] text-[#DC2626]' :
                              'bg-[#F1F5F9] text-[#64748B]'
                            }`}>
                              {st === 'ativo' && <CheckCircle2 className="w-[12px] h-[12px]" />}
                              {st === 'churn' && <AlertTriangle className="w-[12px] h-[12px]" />}
                              {st === 'cancelado' && <XCircle className="w-[12px] h-[12px]" />}
                              {statusLabel}
                            </div>
                          </div>
                        </td>
                        <td className="p-[16px_24px]">
                          <span className="text-[13px] font-[500] text-[#475569]">{client.vendedor_nome || '—'}</span>
                        </td>
                        <td className="p-[16px_24px]">
                          <div className="flex flex-col gap-[2px]">
                            <div className="flex items-center gap-[6px]">
                              <span className="text-[11px] font-[600] text-[#94A3B8] w-[45px]">1º PAG:</span>
                              <span className="text-[13px] font-[600] text-[#334155]">{formatDate(client.primeiro_pagamento_at)}</span>
                            </div>
                            <div className="flex items-center gap-[6px]">
                              <span className="text-[11px] font-[600] text-[#94A3B8] w-[45px]">ÚLTIMO:</span>
                              <span className="text-[13px] font-[500] text={!client.ultimo_pagamento_at ? '#EF4444' : '#64748B'}">
                                {formatDate(client.ultimo_pagamento_at)}
                              </span>
                            </div>
                          </div>
                        </td>
                        <td className="p-[16px_24px] text-right">
                          <div className="flex items-center justify-end gap-[8px] opacity-0 group-hover:opacity-100 transition-opacity">
                            <Link 
                              href={`/configuracoes/clientes-asaas/${client.id}`}
                              title="Detalhes"
                              className="p-[8px] bg-white border border-[#E2E8F0] hover:border-[#6366F1] hover:text-[#6366F1] text-[#64748B] rounded-[8px] transition-colors shadow-sm inline-block"
                            >
                              <Eye className="w-[16px] h-[16px]" />
                            </Link>
                            <Link 
                              href={`/configuracoes/clientes-asaas/${client.id}/editar`}
                              title="Editar"
                              className="p-[8px] bg-white border border-[#E2E8F0] hover:border-[#F59E0B] hover:text-[#F59E0B] text-[#64748B] rounded-[8px] transition-colors shadow-sm inline-block"
                            >
                              <Edit2 className="w-[16px] h-[16px]" />
                            </Link>
                          </div>
                        </td>
                      </tr>
                      );
                    })}
                    {clients.length === 0 && !loading && (
                      <tr>
                        <td colSpan={6} className="p-[32px] text-center text-[#64748B] text-[14px]">
                          Nenhum cliente encontrado.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
              
              {/* Paginação */}
              <Pagination
                currentPage={currentPage}
                total={totalItems}
                pageSize={15}
                onPageChange={setCurrentPage}
                onPageSizeChange={() => {}}
              />

            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
