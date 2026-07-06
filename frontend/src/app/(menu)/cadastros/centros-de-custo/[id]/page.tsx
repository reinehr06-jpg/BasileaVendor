"use client";

import React, { useState, use } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  Layers,
  Pencil,
  Search,
  Edit2,
  CheckCircle2,
  FileText,
  DollarSign,
  ExternalLink,
  Ban,
  TrendingUp
} from "lucide-react";

export default function CentroDeCustoHistoricoPage({ params: paramsPromise }: { params: Promise<{ id: string }> }) {
  const { t } = useTranslation();
  const params = use(paramsPromise);
  const [activeFilter, setActiveFilter] = useState("Todos");
  const [timelinePeriod, setTimelinePeriod] = useState("30dias");

  const [isClosingModalOpen, setIsClosingModalOpen] = useState(false);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [obsInativacao, setObsInativacao] = useState("");

  const filters = ["Todos", "Alterações cadastrais", "Orçamento", "Despesas vinculadas"];

  const MOCK_EVENTS = [
    { id: 1, type: "Orçamento", date: "15/06/2024", time: "16:20", title: "Orçamento atualizado", desc: "Limite alterado de R$ 10.000,00 para R$ 15.000,00 pelo Pr. Marcos.", author: "Pr. Marcos", authorName: "Pastor responsável", icon: "edit", color: "#8B5CF6", bgTag: "#F4EEFF", textTag: "#6D28D9" },
    { id: 2, type: "Despesas vinculadas", date: "01/06/2024", time: "10:30", title: "Despesa vinculada: Conta de Luz Maio/24", desc: "Lançamento #1035 de R$ 780,00 alocado neste centro de custo.", author: "Sistema", authorName: "Registro automático", icon: "dollar", color: "#DC2626", bgTag: "#FEE2E2", textTag: "#DC2626" },
    { id: 3, type: "Alterações cadastrais", date: "10/02/2024", time: "09:00", title: "Centro de Custo criado", desc: "Estrutura cadastrada no sistema para organização de despesas da sede.", author: "Admin", authorName: "Usuário principal", icon: "check", color: "#3B82F6", bgTag: "#EFF6FF", textTag: "#2563EB" }
  ];

  const filteredEvents = activeFilter === "Todos" ? MOCK_EVENTS : MOCK_EVENTS.filter(e => e.type === activeFilter);

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
                  <Link href="/cadastros/centros-de-custo" className="hover:text-[#1A1A2E] transition-colors">Centros de Custo</Link>
                  <span>/</span>
                  <span className="text-[#1A1A2E]">Sede Administrativa</span>
                </div>
                <div className="flex items-center gap-3 mt-1">
                  <div className="w-[32px] h-[32px] rounded-[8px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                    <FileText className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                  <h1 className="text-[24px] font-[800] text-[#1A1A2E] tracking-tight">Histórico do centro de custo</h1>
                </div>
                <p className="text-[13px] text-[#6B7280] font-[400] mt-0.5">Acompanhe alterações, orçamentos e despesas vinculadas a este centro.</p>
              </div>
              <div className="flex items-center gap-3">
                <Link href={`/centros-de-custo/${params.id || 1}/editar`} className="flex items-center gap-2 px-[20px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm shadow-[#6D28D9]/20">
                  <Pencil className="w-[14px] h-[14px]" strokeWidth={2.4} />
                  Editar centro de custo
                </Link>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-4 shadow-[0_2px_12px_rgba(0,0,0,0.02)] mb-4 flex flex-col xl:flex-row gap-5 shrink-0 items-center">
            <div className="flex items-center gap-4 flex-1 border-b xl:border-b-0 xl:border-r border-[#F1F1F4] pb-4 xl:pb-0 xl:pr-4">
              <div className="w-[56px] h-[56px] rounded-[14px] bg-[#E0F2FE] flex items-center justify-center shrink-0 border border-[#BAE6FD]">
                <Layers className="w-[28px] h-[28px] text-[#0284C7]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col flex-1">
                <div className="flex items-center gap-2 mb-1.5">
                  <h2 className="text-[16px] font-[800] text-[#1A1A2E] leading-none">Sede Administrativa</h2>
                  <div className="flex items-center gap-1.5 px-2 py-0.5 bg-[#10B981]/10 rounded-full">
                    <div className="w-[5px] h-[5px] rounded-full bg-[#10B981]"></div>
                    <span className="text-[10px] font-[700] text-[#10B981] uppercase tracking-wide">Ativo</span>
                  </div>
                </div>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-y-1.5 gap-x-3">
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]"><span className="text-[#9CA3AF]">Código:</span> <span className="font-[600] text-[#1A1A2E]">ADM-01</span></div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]"><span className="text-[#9CA3AF]">Responsável:</span> <span className="font-[600] text-[#1A1A2E]">Pr. Marcos</span></div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]"><span className="text-[#9CA3AF]">Desde:</span> <span className="font-[600] text-[#1A1A2E]">10/02/2024</span></div>
                </div>
              </div>
            </div>
            <div className="flex gap-3 xl:w-[340px] shrink-0 h-[68px]">
              <div className="flex-1 bg-[#FAFAFC] rounded-[10px] p-3 flex flex-col justify-center border border-[#F1F1F4] relative overflow-hidden group hover:border-[#0284C7]/30 transition-colors">
                <DollarSign className="absolute right-[-8px] bottom-[-8px] w-[32px] h-[32px] text-[#0284C7]/10 group-hover:text-[#0284C7]/20 transition-colors" strokeWidth={2} />
                <p className="text-[10px] font-[700] text-[#6B7280] uppercase tracking-wider mb-0.5">Orçamento</p>
                <p className="text-[15px] font-[800] text-[#0284C7] leading-none">R$ 15.000,00</p>
              </div>
              <div className="flex-1 bg-[#FAFAFC] rounded-[10px] p-3 flex flex-col justify-center border border-[#F1F1F4] relative overflow-hidden group hover:border-[#DC2626]/30 transition-colors">
                <TrendingUp className="absolute right-[-8px] bottom-[-8px] w-[32px] h-[32px] text-[#DC2626]/10 group-hover:text-[#DC2626]/20 transition-colors" strokeWidth={2} />
                <p className="text-[10px] font-[700] text-[#6B7280] uppercase tracking-wider mb-0.5">Gasto atual</p>
                <div className="flex items-baseline gap-1">
                  <p className="text-[15px] font-[800] text-[#DC2626] leading-none">R$ 8.200,00</p>
                  <p className="text-[10px] font-[600] text-[#6B7280]">55%</p>
                </div>
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
                      <p className="text-[12px] font-[600] text-[#1A1A2E]">{event.date}</p>
                      <p className="text-[11px] font-[500] text-[#6B7280]">{event.time}</p>
                    </div>
                    <div className="absolute left-[104px] -ml-[12px] w-[24px] h-[24px] rounded-full bg-white border-2 flex items-center justify-center shadow-sm z-20 mt-1" style={{ borderColor: event.color }}>
                      {event.icon === "edit" && <Edit2 className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                      {event.icon === "check" && <CheckCircle2 className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                      {event.icon === "dollar" && <DollarSign className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                    </div>
                    <div className="flex-1 pl-6">
                      <div className="w-full bg-white border rounded-[10px] p-3 shadow-sm hover:shadow-[0_2px_8px_rgba(0,0,0,0.04)] transition-shadow border-[#E5E7EB]">
                        <div className="flex items-center justify-between mb-1.5">
                          <div className="flex items-center gap-2.5">
                            <span className="px-2 py-0.5 rounded-[4px] text-[9px] font-[800] uppercase tracking-wide border" style={{ backgroundColor: event.bgTag, color: event.textTag, borderColor: 'transparent' }}>{event.type}</span>
                            <h3 className="text-[13px] font-[800] text-[#1A1A2E]">{event.title}</h3>
                          </div>
                          <button className="text-[11px] font-[700] hover:underline shrink-0 ml-2 text-[#6D28D9]">Ver detalhes</button>
                        </div>
                        <div className="flex items-start justify-between">
                          <p className="text-[12px] leading-relaxed max-w-[80%] text-[#4B5563]">{event.desc}</p>
                          <div className="text-right shrink-0 ml-4">
                            <p className="text-[11px] font-[700] text-[#374151]">{event.author}</p>
                            <p className="text-[10px] font-[500] text-[#9CA3AF]">{event.authorName}</p>
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
                <h3 className="text-[14px] font-[800] text-[#1A1A2E] mb-4">Resumo do histórico</h3>
                <div className="flex flex-col gap-3">
                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]"><div className="flex items-center gap-2 text-[13px] text-[#4B5563]"><FileText className="w-[14px] h-[14px] text-[#9CA3AF]" />Total de registros</div><span className="font-[700] text-[#1A1A2E]">15</span></div>
                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]"><div className="flex items-center gap-2 text-[13px] text-[#4B5563]"><Edit2 className="w-[14px] h-[14px] text-[#8B5CF6]" />Alterações</div><span className="font-[700] text-[#1A1A2E]">3</span></div>
                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]"><div className="flex items-center gap-2 text-[13px] text-[#4B5563]"><DollarSign className="w-[14px] h-[14px] text-[#DC2626]" />Despesas vinculadas</div><span className="font-[700] text-[#1A1A2E]">12</span></div>
                </div>
                <button className="mt-4 flex items-center gap-1.5 text-[12px] font-[700] text-[#6D28D9] hover:underline">Ver relatório completo <ExternalLink className="w-[12px] h-[12px]" /></button>
              </div>
              <button onClick={() => setIsClosingModalOpen(true)} className="flex items-center justify-center gap-2.5 p-3 border rounded-[10px] transition-colors text-[13px] font-[700] bg-white border-[#C4B5FD] text-[#6D28D9] hover:bg-[#F3E8FF] cursor-pointer">
                <Ban className="w-[16px] h-[16px]" strokeWidth={2.4} />
                Inativar centro de custo
              </button>
            </div>
          </div>
        </main>
      </div>

      {isClosingModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-8 animate-in fade-in duration-200">
          <div className="bg-white rounded-[16px] w-full max-w-[480px] p-6 shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col gap-6">
            <div className="flex flex-col gap-2">
              <h2 className="text-[18px] font-[800] text-[#1A1A2E]">Inativar Centro de Custo?</h2>
              <p className="text-[14px] text-[#4B5563] leading-relaxed">
                Você está prestes a inativar o centro de custo <strong>Sede Administrativa</strong>. Por favor, informe o motivo abaixo.
              </p>
            </div>
            
            <div className="flex flex-col gap-4">
              <div className="flex flex-col gap-[6px]">
                <label className="text-[13px] font-[600] text-[#4B5563]">
                  {t("Motivo da Inativação")} <span className="text-[#EF4444] ml-0.5">*</span>
                </label>
                <CustomSelect
                  value={motivoInativacao}
                  onChange={setMotivoInativacao}
                  placeholder="Selecione o motivo"
                  searchable={false}
                  className="h-[42px]"
                  options={[
                    { value: "reestruturacao", label: "Reestruturação/Mudança Organizacional" },
                    { value: "fim_projeto", label: "Encerramento do Projeto/Atividade" },
                    { value: "unificacao", label: "Unificação com outro Centro de Custo" },
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
                    placeholder="Descreva detalhadamente o motivo da inativação (mínimo 15 caracteres)..."
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
                onClick={() => setIsClosingModalOpen(false)} 
                className="px-5 py-2.5 rounded-[8px] bg-[#6D28D9] hover:bg-[#5B21B6] disabled:bg-[#C4B5FD] disabled:cursor-not-allowed text-white text-[13px] font-[700] transition-colors shadow-sm"
              >
                Inativar Centro de Custo
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
