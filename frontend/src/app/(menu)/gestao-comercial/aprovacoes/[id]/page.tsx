"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import {
  FileText,
  User,
  CheckCircle2,
  XCircle,
  Clock,
  MessageSquare,
  DollarSign,
  Briefcase,
  ShieldAlert,
  ExternalLink
} from "lucide-react";

export default function AprovacaoDetalhePage() {
  const { t } = useTranslation();
  const [activeTab, setActiveTab] = useState("Detalhes");

  const tabs = ["Detalhes do Pedido", "Linha do Tempo"];

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1200px] mx-auto">
            
            {/* Breadcrumb */}
            <div className="flex items-center text-[13px] text-[#6B7280] mb-[20px]">
              <Link href="/gestao-comercial/aprovacoes" className="hover:text-[#6D28D9] transition-colors">{t("Aprovações")}</Link>
              <span className="mx-[8px]">/</span>
              <span className="text-[#1A1A2E] font-[600]">Venda #00012</span>
            </div>

            {/* HEADER DA PÁGINA */}
            <div className="flex items-center justify-between mb-[24px]">
              <div className="flex items-center gap-[16px]">
                <div className="w-[48px] h-[48px] rounded-[12px] bg-[#FFFBEB] flex items-center justify-center shrink-0 border border-[#FEF3C7] shadow-sm">
                  <ShieldAlert className="w-[24px] h-[24px] text-[#D97706]" strokeWidth={2.2} />
                </div>
                <div className="flex flex-col justify-center">
                  <h1 className="text-[24px] font-[800] text-[#1A1A2E] leading-tight tracking-tight">{t("Histórico da Aprovação")}</h1>
                  <p className="text-[14px] text-[#6B7280] mt-1">{t("Acompanhe o status e os motivos desta solicitação.")}</p>
                </div>
              </div>
            </div>

            {/* PROFILE CARD HORIZONTAL (Padrão) */}
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] p-[24px] mb-[32px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col lg:flex-row items-center justify-between gap-[24px]">
              
              {/* Info Left */}
              <div className="flex items-center gap-[20px]">
                <div className="w-[80px] h-[80px] rounded-[16px] bg-[#FFFBEB] flex items-center justify-center border-4 border-[#F9FAFB] shadow-sm">
                  <Clock className="w-[32px] h-[32px] text-[#D97706]" strokeWidth={2.5} />
                </div>
                <div className="flex flex-col">
                  <div className="flex items-center gap-[12px] mb-[8px]">
                    <h2 className="text-[20px] font-[800] text-[#1A1A2E]">Solicitação #00012</h2>
                    <span className="flex items-center gap-[4px] px-[8px] py-[2px] bg-[#FEF3C7] text-[#D97706] text-[10px] font-[800] uppercase tracking-wide rounded-[4px]">
                      <div className="w-[6px] h-[6px] rounded-full bg-[#D97706]"></div>
                      {t("Pendente")}
                    </span>
                    <span className="px-[8px] py-[2px] bg-[#F3F4F6] text-[#4B5563] text-[10px] font-[800] uppercase tracking-wide rounded-[4px]">
                      {t("Desconto Especial")}
                    </span>
                  </div>
                  
                  <div className="flex flex-wrap items-center gap-x-[32px] gap-y-[8px]">
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <User className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      Vendedor Padrão
                    </div>
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <Briefcase className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      Cliente: Acme Corp LTDA
                    </div>
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <FileText className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      Plano: Enterprise (Anual)
                    </div>
                  </div>
                </div>
              </div>

              {/* Metrics Right (Valores) */}
              <div className="flex items-center gap-[16px] shrink-0">
                <div className="bg-[#F9FAFB] border border-[#F1F1F4] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#6B7280] uppercase tracking-wider mb-[4px]">{t("Valor Original")}</p>
                  <p className="text-[16px] font-[800] text-[#9CA3AF] line-through">R$ 10.000</p>
                </div>
                <div className="bg-[#FEF2F2] border border-[#FEE2E2] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#DC2626] uppercase tracking-wider mb-[4px]">{t("Desconto")}</p>
                  <p className="text-[18px] font-[800] text-[#DC2626]">- 25%</p>
                </div>
                <div className="bg-[#F0FDF4] border border-[#DCFCE7] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#059669] uppercase tracking-wider mb-[4px]">{t("Valor Final")}</p>
                  <p className="text-[18px] font-[800] text-[#059669]">R$ 7.500</p>
                </div>
              </div>

            </div>

            {/* FILTERS & CONTENT AREA */}
            <div className="flex flex-col lg:flex-row gap-[32px] items-start">
              
              {/* Left Column: Timeline / Content */}
              <div className="flex-1 w-full flex flex-col">
                
                {/* Tabs */}
                <div className="flex items-center gap-[12px] mb-[24px] overflow-x-auto pb-2 scrollbar-hide">
                  {tabs.map((tab) => (
                    <button
                      key={tab}
                      onClick={() => setActiveTab(tab)}
                      className={`h-[36px] px-[16px] rounded-full text-[13px] font-[600] whitespace-nowrap transition-colors border ${
                        activeTab === tab 
                          ? 'bg-[#6D28D9] text-white border-[#6D28D9]' 
                          : 'bg-white text-[#4B5563] border-[#E5E7EB] hover:bg-[#F9FAFB]'
                      }`}
                    >
                      {tab}
                    </button>
                  ))}
                </div>

                {/* Aba: Detalhes do Pedido */}
                {activeTab === "Detalhes do Pedido" && (
                  <div className="flex flex-col gap-[24px]">
                    {/* MOTIVO BOX */}
                    <div className="bg-white rounded-[16px] border border-[#E5E7EB] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] relative overflow-hidden">
                      <div className="absolute top-0 left-0 w-[4px] h-full bg-[#6D28D9]"></div>
                      <h3 className="text-[14px] font-[700] text-[#1A1A2E] mb-[12px] flex items-center gap-[8px]">
                        <MessageSquare className="w-[16px] h-[16px] text-[#6D28D9]" />
                        {t("Motivo do Pedido")}
                      </h3>
                      <div className="bg-[#F9FAFB] border border-[#F1F1F4] rounded-[8px] p-[16px]">
                        <p className="text-[14px] text-[#4B5563] leading-relaxed italic">
                          "O cliente Acme Corp está negociando conosco há 3 meses. Eles fecharam o plano Anual Enterprise, mas exigiram 25% de desconto à vista para fecharmos o contrato hoje. Peço a aprovação pois o LTV desse cliente será muito alto."
                        </p>
                      </div>
                    </div>

                    {/* Bloco Envolvidos */}
                    <div className="bg-white rounded-[16px] border border-[#E5E7EB] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                      <h3 className="text-[14px] font-[700] text-[#1A1A2E] mb-[16px] flex items-center gap-[8px]">
                        <User className="w-[16px] h-[16px] text-[#3B82F6]" />
                        {t("Participantes")}
                      </h3>
                      <div className="flex flex-col gap-[16px]">
                        <div className="flex items-start gap-[12px]">
                          <div className="w-[32px] h-[32px] rounded-full bg-[#F3F4F6] flex items-center justify-center text-[12px] font-[700] text-[#4B5563]">VP</div>
                          <div className="flex flex-col">
                            <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase">{t("Quem Pediu")}</span>
                            <span className="text-[13px] font-[600] text-[#1A1A2E]">Vendedor Padrão</span>
                            <span className="text-[12px] text-[#6B7280]">Equipe: Vendas Internas</span>
                          </div>
                        </div>
                        <div className="flex items-start gap-[12px]">
                          <div className="w-[32px] h-[32px] rounded-full border border-dashed border-[#D1D5DB] flex items-center justify-center">
                            <User className="w-[14px] h-[14px] text-[#D1D5DB]" />
                          </div>
                          <div className="flex flex-col">
                            <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase">{t("Quem Aprovou")}</span>
                            <span className="text-[13px] font-[500] text-[#9CA3AF] italic">{t("Aguardando aprovação...")}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* Aba: Linha do Tempo */}
                {activeTab === "Linha do Tempo" && (
                  <div className="flex flex-col relative before:absolute before:inset-y-[8px] before:left-[108px] before:w-[2px] before:bg-[#E5E7EB]">
                    
                    {/* Item 1 */}
                    <div className="flex items-start gap-[16px] relative mb-[24px]">
                      <div className="w-[84px] shrink-0 text-right pt-[4px]">
                        <p className="text-[12px] font-[700] text-[#1A1A2E]">Hoje</p>
                        <p className="text-[11px] text-[#6B7280]">14:30</p>
                      </div>
                      
                      <div className="relative shrink-0 mt-[2px]">
                        <div className="w-[24px] h-[24px] rounded-full bg-white border-[2px] border-[#3B82F6] flex items-center justify-center z-10 relative">
                          <div className="w-[8px] h-[8px] rounded-full bg-[#3B82F6]"></div>
                        </div>
                      </div>

                      <div className="flex-1 bg-white border border-[#E5E7EB] rounded-[12px] p-[16px] shadow-sm">
                        <span className="px-[8px] py-[2px] bg-[#EFF6FF] text-[#2563EB] text-[10px] font-[800] uppercase tracking-wide rounded-[4px] mb-[8px] inline-block">
                          {t("Solicitação Criada")}
                        </span>
                        <p className="text-[13px] text-[#4B5563]">{t("Vendedor Padrão solicitou aprovação de desconto.")}</p>
                      </div>
                    </div>

                    {/* Item 2 */}
                    <div className="flex items-start gap-[16px] relative mb-[24px]">
                      <div className="w-[84px] shrink-0 text-right pt-[4px]">
                        <p className="text-[12px] font-[700] text-[#1A1A2E]">Hoje</p>
                        <p className="text-[11px] text-[#6B7280]">14:30</p>
                      </div>
                      
                      <div className="relative shrink-0 mt-[2px]">
                        <div className="w-[24px] h-[24px] rounded-full bg-white border-[2px] border-[#F59E0B] flex items-center justify-center z-10 relative shadow-[0_0_0_4px_rgba(253,230,138,0.4)]">
                          <div className="w-[8px] h-[8px] rounded-full bg-[#F59E0B]"></div>
                        </div>
                      </div>

                      <div className="flex-1 bg-white border border-[#E5E7EB] rounded-[12px] p-[16px] shadow-sm">
                        <span className="px-[8px] py-[2px] bg-[#FFFBEB] text-[#D97706] text-[10px] font-[800] uppercase tracking-wide rounded-[4px] mb-[8px] inline-block">
                          {t("Em Análise")}
                        </span>
                        <p className="text-[13px] text-[#4B5563]">{t("Aguardando revisão de um gestor comercial.")}</p>
                      </div>
                    </div>

                  </div>
                )}

              </div>

              {/* Right Column: Resumo e Ações */}
              <div className="w-full lg:w-[320px] shrink-0 flex flex-col gap-[16px]">
                
                {/* Ações Rápidas */}
                <div className="flex flex-col gap-[12px]">
                  <button className="w-full h-[48px] bg-[#059669] text-white font-[700] text-[14px] rounded-[10px] hover:bg-[#047857] transition-colors flex items-center justify-center gap-[8px] shadow-sm">
                    <CheckCircle2 className="w-[18px] h-[18px]" strokeWidth={2.5} />
                    {t("Aprovar Venda")}
                  </button>
                  <button className="w-full h-[48px] bg-white border border-[#EF4444] text-[#EF4444] font-[700] text-[14px] rounded-[10px] hover:bg-[#FEF2F2] transition-colors flex items-center justify-center gap-[8px] shadow-sm">
                    <XCircle className="w-[18px] h-[18px]" strokeWidth={2.5} />
                    {t("Recusar Pedido")}
                  </button>
                </div>

                <div className="w-full h-[1px] bg-[#E5E7EB] my-[4px]"></div>

                {/* Cliente Info Sidebar */}
                <div className="bg-white border border-[#E5E7EB] rounded-[16px] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                  <h3 className="text-[16px] font-[800] text-[#1A1A2E] mb-[20px]">{t("Resumo")}</h3>
                  
                  <div className="flex flex-col gap-[16px]">
                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <Briefcase className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Empresa")}
                      </div>
                      <span className="text-[13px] font-[700] text-[#6D28D9] cursor-pointer hover:underline">Acme Corp LTDA</span>
                    </div>

                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <FileText className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Plano")}
                      </div>
                      <span className="text-[13px] font-[600] text-[#1A1A2E]">Enterprise (Anual)</span>
                    </div>

                    <div className="flex items-center justify-between pt-[4px]">
                      <Link href="#" className="flex items-center gap-[6px] text-[13px] font-[700] text-[#6D28D9] hover:underline">
                        {t("Ver cliente no CRM")}
                        <ExternalLink className="w-[14px] h-[14px]" />
                      </Link>
                    </div>
                  </div>
                </div>

              </div>

            </div>

          </div>
        </main>
      </div>
    </div>
  );
}
