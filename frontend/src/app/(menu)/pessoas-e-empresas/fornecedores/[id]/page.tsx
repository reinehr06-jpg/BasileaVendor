"use client";

import React, { useState, useEffect, use } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  Building2,
  Pencil,
  Search,
  Edit2,
  CheckCircle2,
  FileText,
  CreditCard,
  Phone,
  Mail,
  MapPin,
  ExternalLink,
  AlertTriangle,
  Clock,
  DollarSign,
  Ban
} from "lucide-react";
import { FornecedoresService } from "@/services/fornecedores.service";
import { useRouter } from "next/navigation";

export default function FornecedorHistoricoPage({ params: paramsPromise }: { params: Promise<{ id: string }> }) {
  const { t } = useTranslation();
  const params = use(paramsPromise);
  const router = useRouter();

  const [activeFilter, setActiveFilter] = useState("Todos");
  const [timelinePeriod, setTimelinePeriod] = useState("30dias");
  const [fornecedor, setFornecedor] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [events, setEvents] = useState<any[]>([]);

  const [isClosingModalOpen, setIsClosingModalOpen] = useState(false);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [obsInativacao, setObsInativacao] = useState("");

  const filters = [
    "Todos", "Alterações cadastrais", "Lançamentos", "Notas Fiscais", "Contatos"
  ];

  useEffect(() => {
    carregarFornecedor();
  }, [params.id]);

  const carregarFornecedor = async () => {
    try {
      setLoading(true);
      const res = await FornecedoresService.obterPorId(params.id);
      setFornecedor(res.data);
      const resHistorico: any = await FornecedoresService.historico(params.id);
      setEvents(resHistorico.data.data || []);
    } catch (error) {
      console.error("Erro ao carregar fornecedor", error);
    } finally {
      setLoading(false);
    }
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
          <p className="text-gray-500">Carregando dados do fornecedor...</p>
        </div>
      </div>
    );
  }

  if (!fornecedor) {
    return (
      <div className="flex min-h-screen font-inter bg-[#F8F9FA]">
        <Sidebar />
        <div className="flex-1 ml-[240px] flex items-center justify-center">
          <p className="text-red-500">Fornecedor não encontrado.</p>
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
          
          {/* BREADCRUMBS & TOP ACTIONS */}
          <div className="flex flex-col gap-4 mb-4 shrink-0">
            <div className="flex items-center justify-between">
              <div className="flex flex-col gap-1">
                <div className="flex items-center gap-2 text-[13px] font-[500] text-[#6B7280]">
                  <Link href="/pessoas-e-empresas/fornecedores" className="hover:text-[#1A1A2E] transition-colors">Fornecedores</Link>
                  <span className="text-[#D1D5DB]">/</span>
                  <span className="text-[#1A1A2E]">{fornecedor.nome}</span>
                </div>
                
                <div className="flex items-center gap-3 mt-1">
                  <div className="w-[32px] h-[32px] rounded-[8px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                    <FileText className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                  <h1 className="text-[24px] font-[800] text-[#1A1A2E] tracking-tight">Histórico do fornecedor</h1>
                </div>
                <p className="text-[13px] text-[#6B7280] font-[400] mt-0.5">Acompanhe todas as alterações, lançamentos e movimentações relacionadas a este fornecedor.</p>
              </div>

              <div className="flex items-center gap-3">
                <button disabled className="flex items-center gap-2 px-[20px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] opacity-50 transition-colors shadow-sm shadow-[#6D28D9]/20">
                  <Pencil className="w-[14px] h-[14px]" strokeWidth={2.4} />
                  Editar fornecedor
                </button>
              </div>
            </div>
          </div>

          {/* PERFIL SUPERIOR (Compacto) */}
          <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-4 shadow-[0_2px_12px_rgba(0,0,0,0.02)] mb-4 flex flex-col xl:flex-row gap-5 shrink-0 items-center">
            
            {/* Info do Fornecedor */}
            <div className="flex items-center gap-4 flex-1 border-b xl:border-b-0 xl:border-r border-[#F1F1F4] pb-4 xl:pb-0 xl:pr-4">
              <div className="w-[56px] h-[56px] rounded-[14px] bg-[#FEF3C7] flex items-center justify-center shrink-0 border border-[#FDE68A]">
                <Building2 className="w-[28px] h-[28px] text-[#F59E0B]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col flex-1">
                <div className="flex items-center gap-2 mb-1.5">
                  <h2 className="text-[16px] font-[800] text-[#1A1A2E] leading-none">{fornecedor.nome}</h2>
                  <div className={`flex items-center gap-1.5 px-2 py-0.5 rounded-full ${fornecedor.status === 'Ativo' ? 'bg-[#10B981]/10' : 'bg-[#F3F4F6]'}`}>
                    <div className={`w-[5px] h-[5px] rounded-full ${fornecedor.status === 'Ativo' ? 'bg-[#10B981]' : 'bg-[#6B7280]'}`}></div>
                    <span className={`text-[10px] font-[700] uppercase tracking-wide ${fornecedor.status === 'Ativo' ? 'text-[#10B981]' : 'text-[#4B5563]'}`}>{fornecedor.status}</span>
                  </div>
                </div>
                
                <div className="grid grid-cols-2 md:grid-cols-3 gap-y-1.5 gap-x-3">
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]">
                    <Phone className="w-[12px] h-[12px] text-[#9CA3AF]" />
                    {fornecedor.telefone || '-'}
                  </div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]">
                    <span className="text-[#9CA3AF]">CNPJ/CPF:</span> <span className="font-[600] text-[#1A1A2E]">{fornecedor.documento || '-'}</span>
                  </div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]">
                    <Mail className="w-[12px] h-[12px] text-[#9CA3AF]" />
                    {fornecedor.email || '-'}
                  </div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]">
                    <span className="text-[#9CA3AF]">Responsável:</span> <span className="font-[600] text-[#1A1A2E]">{fornecedor.contato_responsavel || '-'}</span>
                  </div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]">
                    <MapPin className="w-[12px] h-[12px] text-[#9CA3AF]" />
                    {fornecedor.endereco || '-'}
                  </div>
                  <div className="flex items-center gap-1.5 text-[12px] text-[#4B5563]">
                    <span className="text-[#9CA3AF]">Desde:</span> <span className="font-[600] text-[#1A1A2E]">{formatDate(fornecedor.created_at)}</span>
                  </div>
                </div>
              </div>
            </div>

            {/* KPIs */}
            <div className="flex gap-3 xl:w-[460px] shrink-0 h-[68px]">
              <div className="flex-1 bg-[#FAFAFC] rounded-[10px] p-3 flex flex-col justify-center border border-[#F1F1F4] relative overflow-hidden group hover:border-[#DC2626]/30 transition-colors">
                <DollarSign className="absolute right-[-8px] bottom-[-8px] w-[32px] h-[32px] text-[#DC2626]/10 group-hover:text-[#DC2626]/20 transition-colors" strokeWidth={2} />
                <p className="text-[10px] font-[700] text-[#6B7280] uppercase tracking-wider mb-0.5">Total pago</p>
                <p className="text-[15px] font-[800] text-[#DC2626] leading-none">R$ 0,00</p>
              </div>
              <div className="flex-1 bg-[#FAFAFC] rounded-[10px] p-3 flex flex-col justify-center border border-[#F1F1F4] relative overflow-hidden group hover:border-[#3B82F6]/30 transition-colors">
                <CreditCard className="absolute right-[-8px] bottom-[-8px] w-[32px] h-[32px] text-[#3B82F6]/10 group-hover:text-[#3B82F6]/20 transition-colors" strokeWidth={2} />
                <p className="text-[10px] font-[700] text-[#6B7280] uppercase tracking-wider mb-0.5">Lançamentos</p>
                <p className="text-[15px] font-[800] text-[#1A1A2E] leading-none">0</p>
              </div>
              <div className="flex-1 bg-[#FFFBEB] rounded-[10px] p-3 flex flex-col justify-center border border-[#FEF3C7] relative overflow-hidden group hover:border-[#F59E0B]/30 transition-colors">
                <AlertTriangle className="absolute right-[-8px] bottom-[-8px] w-[32px] h-[32px] text-[#F59E0B]/10 group-hover:text-[#F59E0B]/20 transition-colors" strokeWidth={2} />
                <p className="text-[10px] font-[700] text-[#B45309] uppercase tracking-wider mb-0.5">Pendentes</p>
                <div className="flex items-center gap-2">
                  <p className="text-[15px] font-[800] text-[#92400E] leading-none">0</p>
                </div>
              </div>
            </div>

          </div>

          {/* FILTROS EM PÍLULAS */}
          <div className="flex items-center gap-2 mb-4 shrink-0 overflow-x-auto scrollbar-hide flex-nowrap pb-1 max-w-full">
            {filters.map(f => (
              <button 
                key={f}
                onClick={() => setActiveFilter(f)}
                className={`px-[14px] py-[6px] rounded-full text-[12px] font-[600] transition-colors border whitespace-nowrap ${
                  activeFilter === f 
                    ? 'bg-[#6D28D9] text-white border-[#6D28D9] shadow-sm' 
                    : 'bg-white text-[#6B7280] border-[#E5E7EB] hover:bg-[#FAFAFC]'
                }`}
              >
                {f}
              </button>
            ))}
          </div>

          {/* MAIN CONTENT SPLIT */}
          <div className="flex flex-col xl:flex-row gap-5 flex-1 min-h-0">
            
            {/* ESQUERDA: TIMELINE */}
            <div className="flex-1 flex flex-col min-h-0 min-w-0">
              
              {/* Busca e Data */}
              <div className="flex items-center gap-4 mb-4 shrink-0">
                <div className="relative flex items-center flex-1 h-[42px] bg-white border border-[#E5E7EB] rounded-[10px] px-[14px] shadow-sm transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px]" />
                  <input
                    type="text"
                    placeholder="Pesquisar no histórico..."
                    className="bg-transparent border-none outline-none text-[13px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
                <div className="shrink-0 w-[160px]">
                  <CustomSelect
                    options={[
                      { value: "30dias", label: "Últimos 30 dias" },
                      { value: "6meses", label: "Últimos 6 meses" },
                      { value: "1ano", label: "Último ano" },
                      { value: "todo", label: "Todo período" },
                    ]}
                    value={timelinePeriod}
                    onChange={setTimelinePeriod}
                    className="h-[42px]"
                  />
                </div>
              </div>

              {/* Linha do Tempo (Scrollable) */}
              <div className="relative flex-1 overflow-y-auto overflow-x-hidden pr-4 custom-scrollbar pb-10">
                <div className="absolute left-[104px] top-2 bottom-0 w-[2px] bg-[#F1F1F4] border-l border-dashed border-[#D1D5DB]"></div>

                {filteredEvents.map(event => (
                  <div key={event.id} className="relative flex items-start mb-6">
                    {/* Timestamp */}
                    <div className="w-[104px] pr-5 text-right mt-1 shrink-0 relative z-10">
                      <p className={`text-[12px] font-[600] text-[#1A1A2E]`}>{event.date}</p>
                      <p className={`text-[11px] font-[500] text-[#6B7280]`}>{event.time}</p>
                    </div>

                    {/* Ícone */}
                    <div className="absolute left-[104px] -ml-[12px] w-[24px] h-[24px] rounded-full bg-white border-2 flex items-center justify-center shadow-sm z-20 mt-1" style={{ borderColor: event.color }}>
                      {event.icon === "clock" && <Clock className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                      {event.icon === "dollar" && <DollarSign className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                      {event.icon === "edit" && <Edit2 className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                      {event.icon === "check" && <CheckCircle2 className="w-[10px] h-[10px]" style={{ color: event.color }} strokeWidth={3} />}
                    </div>

                    {/* Card de Conteúdo */}
                    <div className="flex-1 pl-6">
                      <div className={`w-full bg-white border rounded-[10px] p-3 shadow-sm hover:shadow-[0_2px_8px_rgba(0,0,0,0.04)] transition-shadow border-[#E5E7EB]`}>
                        <div className="flex items-center justify-between mb-1.5">
                          <div className="flex items-center gap-2.5">
                            <span className="px-2 py-0.5 rounded-[4px] text-[9px] font-[800] uppercase tracking-wide border" style={{ backgroundColor: event.bgTag, color: event.textTag, borderColor: 'transparent' }}>
                              {event.type}
                            </span>
                            <h3 className={`text-[13px] font-[800] text-[#1A1A2E]`}>{event.title}</h3>
                          </div>
                          <button className="text-[11px] font-[700] hover:underline shrink-0 ml-2" style={{ color: '#6D28D9' }}>Ver detalhes</button>
                        </div>
                        <div className="flex items-start justify-between">
                          <p className={`text-[12px] leading-relaxed max-w-[80%] text-[#4B5563]`}>{event.desc}</p>
                          <div className="text-right shrink-0 ml-4">
                            <p className={`text-[11px] font-[700] text-[#374151]`}>{event.author}</p>
                            <p className={`text-[10px] font-[500] text-[#9CA3AF]`}>{event.authorName}</p>
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
                    <p className="text-[12px] text-[#6B7280] mt-1">Não há movimentações para o filtro &quot;{activeFilter}&quot;.</p>
                  </div>
                )}
              </div>
            </div>

            {/* DIREITA: PAINEL DE RESUMO E NOTAS */}
            <div className="w-full xl:w-[300px] shrink-0 flex flex-col gap-5">
              
              <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-5 shadow-sm">
                <h3 className="text-[14px] font-[800] text-[#1A1A2E] mb-4">Resumo operacional</h3>
                
                <div className="flex flex-col gap-3">
                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]">
                    <div className="flex items-center gap-2 text-[13px] text-[#4B5563]">
                      <FileText className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      Total de compras
                    </div>
                    <span className="font-[700] text-[#1A1A2E]">0</span>
                  </div>

                  <div className="flex justify-between items-center pb-3 border-b border-[#F1F1F4]">
                    <div className="flex items-center gap-2 text-[13px] text-[#4B5563]">
                      <Clock className="w-[14px] h-[14px] text-[#F59E0B]" />
                      Dias médio p/ pgto
                    </div>
                    <span className="font-[700] text-[#1A1A2E]">0</span>
                  </div>
                </div>

                <button className="mt-4 flex items-center gap-1.5 text-[12px] font-[700] text-[#6D28D9] hover:underline">
                  Ver relatório financeiro
                  <ExternalLink className="w-[12px] h-[12px]" />
                </button>
              </div>

            </div>
          </div>
          
        </main>
      </div>
    </div>
  );
}
