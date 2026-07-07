"use client";

import React, { useState, useEffect, use } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  ShoppingCart,
  Pencil,
  Search,
  Edit2,
  CheckCircle2,
  FileText,
  DollarSign,
  ExternalLink,
  Ban,
  Clock,
  Check,
  AlertTriangle
} from "lucide-react";
import { ComprasService } from "@/services/compras.service";
import { useRouter } from "next/navigation";

export default function CompraHistoricoPage({ params: paramsPromise }: { params: Promise<{ id: string }> }) {
  const { t } = useTranslation();
  const params = use(paramsPromise);
  const router = useRouter();
  
  const [activeFilter, setActiveFilter] = useState("Todos");
  const [timelinePeriod, setTimelinePeriod] = useState("todo");
  const [compra, setCompra] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [events, setEvents] = useState<any[]>([]);

  const [isClosingModalOpen, setIsClosingModalOpen] = useState(false);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [obsInativacao, setObsInativacao] = useState("");

  const filters = ["Todos", "Fluxo de aprovação", "Cotações", "Alterações", "Recebimento"];

  useEffect(() => {
    carregarCompra();
  }, [params.id]);

  const carregarCompra = async () => {
    try {
      setLoading(true);
      const res = await ComprasService.obterPorId(params.id);
      setCompra(res.data);
      const resHistorico: any = await ComprasService.historico(params.id);
      setEvents(resHistorico.data.data || []);
    } catch (error) {
      console.error("Erro ao carregar compra", error);
    } finally {
      setLoading(false);
    }
  };

  const handleAprovar = async () => {
    try {
      await ComprasService.atualizar(params.id, { status: "Aprovada" });
      carregarCompra();
    } catch (error) {
      console.error("Erro ao aprovar compra", error);
      alert("Erro ao aprovar compra.");
    }
  };

  const handleReprovar = async () => {
    try {
      await ComprasService.atualizar(params.id, { status: "Reprovada" });
      setIsClosingModalOpen(false);
      carregarCompra();
    } catch (error) {
      console.error("Erro ao reprovar compra", error);
      alert("Erro ao reprovar compra.");
    }
  };

  const formatCurrency = (valor: number) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor || 0);
  };

  const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('pt-BR').format(date);
  };

  const filteredEvents = activeFilter === "Todos" ? events : events.filter(e => e.type === activeFilter);

  if (loading) {
    return (
      <div className="flex min-h-screen font-inter bg-[#F8F9FA]">
        <Sidebar />
        <div className="flex-1 ml-[240px] flex items-center justify-center">
          <p className="text-gray-500">Carregando dados da compra...</p>
        </div>
      </div>
    );
  }

  if (!compra) {
    return (
      <div className="flex min-h-screen font-inter bg-[#F8F9FA]">
        <Sidebar />
        <div className="flex-1 ml-[240px] flex items-center justify-center">
          <p className="text-red-500">Compra não encontrada.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen font-inter bg-[#F8F9FA]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 overflow-x-hidden">
        <Topbar />
        <main className="p-[20px_32px_20px_32px] flex-1 flex flex-col max-w-[1400px] mx-auto w-full h-[calc(100vh-64px)] overflow-hidden">
          
          <div className="flex flex-col gap-4 mb-4 shrink-0">
            <div className="flex items-center justify-between">
              <div className="flex flex-col gap-1">
                <div className="flex items-center gap-2 text-[13px] font-[500] text-[#6B7280]">
                  <Link href="/gestao-financeira/compras" className="hover:text-[#1A1A2E] transition-colors">Compras</Link>
                  <span>/</span>
                  <span className="text-[#1A1A2E]">Pedido {compra.numero}</span>
                </div>
                <div className="flex items-center gap-3 mt-1">
                  <div className="w-[32px] h-[32px] rounded-[8px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                    <FileText className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                  <h1 className="text-[24px] font-[800] text-[#1A1A2E] tracking-tight">Histórico do pedido de compra</h1>
                </div>
                <p className="text-[13px] text-[#6B7280] font-[400] mt-0.5">Acompanhe cotações, fluxo de aprovação e recebimento deste pedido.</p>
              </div>
              
              {compra.status === "Aguardando aprovação" && (
                <div className="flex items-center gap-3">
                  <button onClick={() => setIsClosingModalOpen(true)} className="flex items-center gap-2 px-[20px] py-[10px] bg-white border border-[#C4B5FD] text-[#6D28D9] text-[13px] font-[600] rounded-[8px] hover:bg-[#F3E8FF] transition-colors">
                    <Ban className="w-[14px] h-[14px]" strokeWidth={2.4} />
                    Reprovar
                  </button>
                  <button onClick={handleAprovar} className="flex items-center gap-2 px-[20px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[700] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm">
                    <Check className="w-[14px] h-[14px]" strokeWidth={2.4} />
                    Aprovar pedido
                  </button>
                </div>
              )}
            </div>
          </div>

          <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-4 shadow-[0_2px_12px_rgba(0,0,0,0.02)] mb-4 flex flex-col xl:flex-row gap-5 shrink-0 items-center">
            <div className="flex items-center gap-4 flex-1 border-b xl:border-b-0 xl:border-r border-[#F1F1F4] pb-4 xl:pb-0 xl:pr-4">
              <div className="w-[56px] h-[56px] rounded-[14px] bg-[#F3E8FF] flex items-center justify-center shrink-0 border border-[#E9D5FF]">
                <ShoppingCart className="w-[28px] h-[28px] text-[#9333EA]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col flex-1">
                <div className="flex items-center gap-2 mb-1.5">
                  <h2 className="text-[16px] font-[800] text-[#1A1A2E] leading-none">{compra.numero}</h2>
                  <div className={`flex items-center gap-1.5 px-2 py-0.5 rounded-full ${
                    compra.status === 'Aprovada' ? 'bg-[#D1FAE5]' :
                    compra.status === 'Reprovada' ? 'bg-[#FEE2E2]' :
                    'bg-[#FEF3C7]'
                  }`}>
                    <div className={`w-[5px] h-[5px] rounded-full ${
                      compra.status === 'Aprovada' ? 'bg-[#059669]' :
                      compra.status === 'Reprovada' ? 'bg-[#DC2626]' :
                      'bg-[#F59E0B]'
                    }`}></div>
                    <span className={`text-[10px] font-[700] uppercase tracking-wide ${
                      compra.status === 'Aprovada' ? 'text-[#047857]' :
                      compra.status === 'Reprovada' ? 'text-[#B91C1C]' :
                      'text-[#D97706]'
                    }`}>{compra.status}</span>
                  </div>
                </div>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-y-1.5 gap-x-3">
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]"><span className="text-[#9CA3AF]">Solicitante:</span> <span className="font-[600] text-[#1A1A2E]">{compra.solicitante}</span></div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]"><span className="text-[#9CA3AF]">Fornecedor:</span> <span className="font-[600] text-[#1A1A2E]">{compra.fornecedor?.nome || '-'}</span></div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]"><span className="text-[#9CA3AF]">Data:</span> <span className="font-[600] text-[#1A1A2E]">{formatDate(compra.data_solicitacao)}</span></div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]"><span className="text-[#9CA3AF]">Centro Custo:</span> <span className="font-[600] text-[#1A1A2E]">-</span></div>
                </div>
              </div>
            </div>
            <div className="flex gap-3 xl:w-[340px] shrink-0 h-[68px]">
              <div className="flex-1 bg-[#FAFAFC] rounded-[10px] p-3 flex flex-col justify-center border border-[#F1F1F4] relative overflow-hidden group hover:border-[#9333EA]/30 transition-colors">
                <DollarSign className="absolute right-[-8px] bottom-[-8px] w-[32px] h-[32px] text-[#9333EA]/10 group-hover:text-[#9333EA]/20 transition-colors" strokeWidth={2} />
                <p className="text-[10px] font-[700] text-[#6B7280] uppercase tracking-wider mb-0.5">Valor total</p>
                <p className="text-[15px] font-[800] text-[#1A1A2E] leading-none">{formatCurrency(compra.valor)}</p>
              </div>
              <div className={`flex-1 rounded-[10px] p-3 flex flex-col justify-center border relative overflow-hidden group transition-colors ${
                compra.status === 'Aprovada' ? 'bg-[#ECFDF5] border-[#D1FAE5] hover:border-[#059669]/30' :
                compra.status === 'Reprovada' ? 'bg-[#FEF2F2] border-[#FEE2E2] hover:border-[#DC2626]/30' :
                'bg-[#FFFBEB] border-[#FEF3C7] hover:border-[#F59E0B]/30'
              }`}>
                <AlertTriangle className={`absolute right-[-8px] bottom-[-8px] w-[32px] h-[32px] transition-colors ${
                  compra.status === 'Aprovada' ? 'text-[#059669]/10 group-hover:text-[#059669]/20' :
                  compra.status === 'Reprovada' ? 'text-[#DC2626]/10 group-hover:text-[#DC2626]/20' :
                  'text-[#F59E0B]/10 group-hover:text-[#F59E0B]/20'
                }`} strokeWidth={2} />
                <p className={`text-[10px] font-[700] uppercase tracking-wider mb-0.5 ${
                  compra.status === 'Aprovada' ? 'text-[#047857]' :
                  compra.status === 'Reprovada' ? 'text-[#B91C1C]' :
                  'text-[#B45309]'
                }`}>Status</p>
                <p className={`text-[15px] font-[800] leading-none ${
                  compra.status === 'Aprovada' ? 'text-[#064E3B]' :
                  compra.status === 'Reprovada' ? 'text-[#7F1D1D]' :
                  'text-[#92400E]'
                }`}>{compra.status}</p>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2 mb-4 shrink-0 overflow-x-auto scrollbar-hide flex-nowrap pb-1 max-w-full">
            {filters.map(f => (
              <button key={f} onClick={() => setActiveFilter(f)} className={`px-[14px] py-[6px] rounded-full text-[12px] font-[600] transition-colors border whitespace-nowrap ${activeFilter === f ? 'bg-[#6D28D9] text-white border-[#6D28D9] shadow-sm' : 'bg-white text-[#6B7280] border-[#E5E7EB] hover:bg-[#FAFAFC]'}`}>{f}</button>
            ))}
          </div>

          <div className="flex flex-col xl:flex-row gap-5 flex-1 min-h-0">
            <div className="flex-1 flex flex-col min-h-0 min-w-0">
              <div className="flex items-center gap-4 mb-4 shrink-0">
                <div className="relative flex items-center flex-1 h-[42px] bg-white border border-[#E5E7EB] rounded-[10px] px-[14px] shadow-sm transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px]" />
                  <input type="text" placeholder="Pesquisar no histórico..." className="bg-transparent border-none outline-none text-[13px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full" />
                </div>
                <div className="shrink-0 w-[160px]">
                  <CustomSelect options={[{value:"30dias",label:"Últimos 30 dias"},{value:"6meses",label:"Últimos 6 meses"},{value:"1ano",label:"Último ano"},{value:"todo",label:"Todo período"}]} value={timelinePeriod} onChange={setTimelinePeriod} className="h-[42px]" />
                </div>
              </div>
              <div className="relative flex-1 overflow-y-auto overflow-x-hidden pr-4 custom-scrollbar pb-10">
                <div className="absolute left-[104px] top-2 bottom-0 w-[2px] bg-[#F1F1F4] border-l border-dashed border-[#D1D5DB]"></div>
                {filteredEvents.map(event => (
                  <div key={event.id} className="relative flex items-start mb-6">
                    <div className="w-[104px] pr-5 text-right mt-1 shrink-0 relative z-10">
                      <p className={`text-[12px] font-[600] ${event.isWarning ? 'text-[#F59E0B]' : 'text-[#1A1A2E]'}`}>{event.date}</p>
                      <p className={`text-[11px] font-[500] ${event.isWarning ? 'text-[#D97706]' : 'text-[#6B7280]'}`}>{event.time}</p>
                    </div>
                    <div className="absolute left-[104px] -ml-[12px] w-[24px] h-[24px] rounded-full bg-white border-2 flex items-center justify-center shadow-sm z-20 mt-1" style={{ borderColor: event.color }}>
                      {event.icon === "clock" && <Clock className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                      {event.icon === "edit" && <Edit2 className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                      {event.icon === "check" && <CheckCircle2 className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                    </div>
                    <div className="flex-1 pl-6">
                      <div className={`w-full bg-white border rounded-[10px] p-3 shadow-sm hover:shadow-[0_2px_8px_rgba(0,0,0,0.04)] transition-shadow ${event.isWarning ? 'bg-[#FFFBEB] border-[#FDE68A]' : 'border-[#E5E7EB]'}`}>
                        <div className="flex items-center justify-between mb-1.5">
                          <div className="flex items-center gap-2.5">
                            <span className="px-2 py-0.5 rounded-[4px] text-[9px] font-[800] uppercase tracking-wide border" style={{ backgroundColor: event.bgTag, color: event.textTag, borderColor: event.isWarning ? '#FDE68A' : 'transparent' }}>{event.type}</span>
                            <h3 className={`text-[13px] font-[800] ${event.isWarning ? 'text-[#92400E]' : 'text-[#1A1A2E]'}`}>{event.title}</h3>
                          </div>
                          <button className="text-[11px] font-[700] hover:underline shrink-0 ml-2" style={{ color: event.isWarning ? '#B45309' : '#6D28D9' }}>Ver detalhes</button>
                        </div>
                        <div className="flex items-start justify-between">
                          <p className={`text-[12px] leading-relaxed max-w-[80%] ${event.isWarning ? 'text-[#B45309]' : 'text-[#4B5563]'}`}>{event.desc}</p>
                          <div className="text-right shrink-0 ml-4">
                            <p className={`text-[11px] font-[700] ${event.isWarning ? 'text-[#92400E]' : 'text-[#374151]'}`}>{event.author}</p>
                            <p className={`text-[10px] font-[500] ${event.isWarning ? 'text-[#B45309]' : 'text-[#9CA3AF]'}`}>{event.authorName}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
                {filteredEvents.length === 0 && (
                  <div className="flex flex-col items-center justify-center p-10 text-center border-2 border-dashed border-[#E5E7EB] rounded-[12px] mt-4 bg-[#FAFAFC]">
                    <Search className="w-[24px] h-[24px] text-[#9CA3AF] mb-3" />
                    <p className="text-[14px] font-[700] text-[#1A1A2E]">Nenhum registro encontrado</p>
                    <p className="text-[12px] text-[#6B7280] mt-1">Não há dados para o filtro &quot;{activeFilter}&quot;.</p>
                  </div>
                )}
              </div>
            </div>
            <div className="w-full xl:w-[300px] shrink-0 flex flex-col gap-5">
              <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-5 shadow-sm">
                <h3 className="text-[14px] font-[800] text-[#1A1A2E] mb-4">Resumo do pedido</h3>
                <div className="flex flex-col gap-3">
                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]"><div className="flex items-center gap-2 text-[13px] text-[#4B5563]"><FileText className="w-[14px] h-[14px] text-[#9CA3AF]" />Total de registros</div><span className="font-[700] text-[#1A1A2E]">3</span></div>
                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]"><div className="flex items-center gap-2 text-[13px] text-[#4B5563]"><Clock className="w-[14px] h-[14px] text-[#F59E0B]" />Etapas de aprovação</div><span className="font-[700] text-[#1A1A2E]">1/2</span></div>
                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]"><div className="flex items-center gap-2 text-[13px] text-[#4B5563]"><Edit2 className="w-[14px] h-[14px] text-[#3B82F6]" />Cotações recebidas</div><span className="font-[700] text-[#1A1A2E]">1</span></div>
                </div>
                <button className="mt-4 flex items-center gap-1.5 text-[12px] font-[700] text-[#6D28D9] hover:underline">Ver relatório completo <ExternalLink className="w-[12px] h-[12px]" /></button>
              </div>
              {/* Itens do Pedido */}
              <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-5 shadow-sm">
                <h3 className="text-[14px] font-[800] text-[#1A1A2E] mb-3">Itens do pedido</h3>
                <div className="flex flex-col gap-2.5">
                  <div className="flex justify-between items-center text-[12px]">
                    <span className="text-[#4B5563]">Verba Aprovada</span>
                    <span className="font-[700] text-[#1A1A2E]">{formatCurrency(compra.valor)}</span>
                  </div>
                  <div className="border-t border-[#F1F1F4] pt-2 mt-1 flex justify-between items-center">
                    <span className="text-[12px] font-[700] text-[#6B7280] uppercase">Total</span>
                    <span className="text-[14px] font-[800] text-[#9333EA]">{formatCurrency(compra.valor)}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>

      {isClosingModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-8 animate-in fade-in duration-200">
          <div className="bg-white rounded-[16px] w-full max-w-[480px] p-6 shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col gap-6">
            <div className="flex flex-col gap-2">
              <h2 className="text-[18px] font-[800] text-[#1A1A2E]">Reprovar Pedido?</h2>
              <p className="text-[14px] text-[#4B5563] leading-relaxed">
                Você está prestes a reprovar o pedido <strong>{compra.numero}</strong>. Por favor, informe o motivo abaixo para notificar o solicitante.
              </p>
            </div>
            
            <div className="flex flex-col gap-4">
              <div className="flex flex-col gap-[6px]">
                <label className="text-[13px] font-[600] text-[#4B5563]">
                  {t("Motivo da Reprovação")} <span className="text-[#EF4444] ml-0.5">*</span>
                </label>
                <CustomSelect
                  value={motivoInativacao}
                  onChange={setMotivoInativacao}
                  placeholder="Selecione o motivo"
                  searchable={false}
                  className="h-[42px]"
                  options={[
                    { value: "orcamento", label: "Fora do Orçamento" },
                    { value: "prioridade", label: "Baixa Prioridade no Momento" },
                    { value: "incompleto", label: "Cotações Incompletas/Faltando Dados" },
                    { value: "outros", label: "Outros" }
                  ]}
                />
              </div>

              {motivoInativacao && (
                <div className="flex flex-col gap-[6px] animate-in fade-in slide-in-from-top-2 duration-300">
                  <label className="text-[13px] font-[600] text-[#4B5563]">
                    {t("Observação")} <span className="text-[#EF4444] ml-0.5">*</span>
                  </label>
                  <textarea 
                    value={obsInativacao}
                    onChange={(e) => setObsInativacao(e.target.value)}
                    placeholder="Descreva detalhadamente o motivo da reprovação (mínimo 15 caracteres)..."
                    className={`w-full min-h-[100px] bg-white border rounded-[8px] p-3 text-[13px] text-[#111827] placeholder-[#9CA3AF] outline-none transition-all resize-none ${obsInativacao.length > 0 && obsInativacao.length < 15 ? 'border-[#EF4444] focus:border-[#EF4444] focus:ring-1 focus:ring-[#EF4444]' : 'border-[#E5E7EB] hover:border-[#D1D5DB] focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9]'}`}
                  />
                  {obsInativacao.length > 0 && obsInativacao.length < 15 && (
                    <span className="text-[11px] font-[500] text-[#EF4444]">
                      A observação deve ter no mínimo 15 caracteres. Faltam {15 - obsInativacao.length}.
                    </span>
                  )}
                  {obsInativacao.length >= 15 && (
                    <span className="text-[11px] font-[500] text-[#10B981]">
                      Observação válida.
                    </span>
                  )}
                </div>
              )}
            </div>

            <div className="flex items-center justify-end gap-3 mt-2">
              <button 
                onClick={() => {
                  setIsClosingModalOpen(false);
                  setMotivoInativacao("");
                  setObsInativacao("");
                }} 
                className="px-5 py-2.5 rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F3F4F6] transition-colors"
              >
                Cancelar
              </button>
              <button 
                disabled={!motivoInativacao || obsInativacao.length < 15}
                onClick={handleReprovar} 
                className="px-5 py-2.5 rounded-[8px] bg-[#6D28D9] hover:bg-[#5B21B6] disabled:bg-[#C4B5FD] disabled:cursor-not-allowed text-white text-[13px] font-[700] transition-colors shadow-sm"
              >
                Reprovar Pedido
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
