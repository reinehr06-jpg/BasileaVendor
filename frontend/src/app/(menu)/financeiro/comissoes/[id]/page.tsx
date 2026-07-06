"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import {
  Download,
  Target,
  TrendingUp,
  Percent,
  Wallet,
  Receipt,
  Users,
  BarChart3,
  CreditCard,
  Briefcase,
  Mail,
  Calendar
} from "lucide-react";

export default function ComissaoHistoricoPage() {
  const { t } = useTranslation();
  const [activeTab, setActiveTab] = useState("Performance");
  const [mes, setMes] = useState("2026-07");

  const tabs = ["Performance", "Formas de Pagto", "Negociação", "Detalhamento"];

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1200px] mx-auto">
            
            {/* Breadcrumb */}
            <div className="flex items-center text-[13px] text-[#6B7280] mb-[20px]">
              <Link href="/financeiro/comissoes" className="hover:text-[#6D28D9] transition-colors">{t("Comissões")}</Link>
              <span className="mx-[8px]">/</span>
              <span className="text-[#1A1A2E] font-[600]">Histórico de Anthony Cardoso</span>
            </div>

            {/* HEADER DA PÁGINA (Padrão Novo) */}
            <div className="flex items-center justify-between mb-[24px]">
              <div className="flex items-center gap-[16px]">
                <div className="w-[48px] h-[48px] rounded-[12px] bg-[#F4EEFF] flex items-center justify-center shrink-0 border border-[#E9D5FF] shadow-sm">
                  <BarChart3 className="w-[24px] h-[24px] text-[#7C3AED]" strokeWidth={2.2} />
                </div>
                <div className="flex flex-col justify-center">
                  <h1 className="text-[24px] font-[800] text-[#1A1A2E] leading-tight tracking-tight">{t("Histórico de Comissões")}</h1>
                  <p className="text-[14px] text-[#6B7280] mt-1">{t("Acompanhe o desempenho de vendas e comissionamento deste vendedor.")}</p>
                </div>
              </div>
            </div>

            {/* PROFILE CARD HORIZONTAL (Padrão Novo) */}
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] p-[24px] mb-[32px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col lg:flex-row items-center justify-between gap-[24px]">
              
              {/* Info Left */}
              <div className="flex items-center gap-[20px]">
                <div className="w-[80px] h-[80px] rounded-[16px] bg-[#6D28D9] flex items-center justify-center border-4 border-[#F9FAFB] shadow-sm text-[28px] font-[800] text-white">
                  A
                </div>
                <div className="flex flex-col">
                  <div className="flex items-center gap-[12px] mb-[8px]">
                    <h2 className="text-[20px] font-[800] text-[#1A1A2E]">Anthony Cardoso</h2>
                  </div>
                  
                  <div className="flex flex-wrap items-center gap-x-[32px] gap-y-[8px]">
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <Mail className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      anthony.cardoso@basileia.global
                    </div>
                  </div>
                </div>
              </div>

              {/* Metrics Right (Principais) */}
              <div className="flex items-center gap-[16px] shrink-0">
                <div className="bg-[#F4EEFF] border border-[#E9D5FF] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#6D28D9] uppercase tracking-wider mb-[4px]">{t("Meta de Vendas")}</p>
                  <p className="text-[18px] font-[800] text-[#5B21B6]">R$ 0,00</p>
                </div>
                <div className="bg-[#F0FDF4] border border-[#DCFCE7] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#059669] uppercase tracking-wider mb-[4px]">{t("Total Vendido")}</p>
                  <p className="text-[18px] font-[800] text-[#059669]">R$ 0,00</p>
                </div>
                <div className="bg-[#FFFBEB] border border-[#FEF3C7] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#D97706] uppercase tracking-wider mb-[4px]">{t("Comissão Total")}</p>
                  <p className="text-[18px] font-[800] text-[#D97706]">R$ 0,00</p>
                </div>
              </div>

            </div>

            {/* FILTERS & CONTENT AREA */}
            <div className="flex flex-col lg:flex-row gap-[32px] items-start">
              
              {/* Left Column: Tabs & Tables */}
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

                {/* SESSÃO: Performance de Vendas */}
                {activeTab === "Performance" && (
                  <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden shadow-sm">
                    <div className="flex flex-col">
                      <div className="flex justify-between items-center px-[24px] h-[48px] border-b border-[#F1F1F4] bg-[#FCFCFD]">
                        <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("INDICADOR")}</span>
                        <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider text-right">{t("VALOR")}</span>
                      </div>
                      <div className="flex justify-between items-center px-[24px] py-[16px] border-b border-[#F1F1F4] hover:bg-[#FAFAFC]">
                        <span className="text-[13px] font-[600] text-[#4B5563]">{t("Total de Vendas")}</span>
                        <span className="text-[14px] font-[800] text-[#1A1A2E]">0</span>
                      </div>
                      <div className="flex justify-between items-center px-[24px] py-[16px] border-b border-[#F1F1F4] hover:bg-[#FAFAFC]">
                        <span className="text-[13px] font-[600] text-[#4B5563]">{t("Valor Total Vendido")}</span>
                        <span className="text-[14px] font-[800] text-[#1A1A2E]">R$ 0,00</span>
                      </div>
                      <div className="flex justify-between items-center px-[24px] py-[16px] border-b border-[#F1F1F4] hover:bg-[#FAFAFC]">
                        <span className="text-[13px] font-[600] text-[#4B5563]">{t("Valor Recebido")}</span>
                        <span className="text-[14px] font-[800] text-[#059669]">R$ 0,00</span>
                      </div>
                      <div className="flex justify-between items-center px-[24px] py-[16px] border-b border-[#F1F1F4] hover:bg-[#FAFAFC]">
                        <span className="text-[13px] font-[600] text-[#4B5563]">{t("Clientes Ativos")}</span>
                        <span className="text-[14px] font-[800] text-[#1A1A2E]">0</span>
                      </div>
                      <div className="flex justify-between items-center px-[24px] py-[16px] border-b border-[#F1F1F4] hover:bg-[#FEF2F2] transition-colors">
                        <span className="text-[13px] font-[600] text-[#EF4444]">{t("Cancelamentos")}</span>
                        <span className="text-[14px] font-[800] text-[#EF4444]">0</span>
                      </div>
                      <div className="flex justify-between items-center px-[24px] py-[16px] hover:bg-[#FEF2F2] transition-colors">
                        <span className="text-[13px] font-[600] text-[#EF4444]">{t("Valor Cancelado")}</span>
                        <span className="text-[14px] font-[800] text-[#EF4444]">R$ 0,00</span>
                      </div>
                    </div>
                  </div>
                )}

                {/* SESSÃO: Formas de Pagto */}
                {activeTab === "Formas de Pagto" && (
                  <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden shadow-sm">
                    <div className="grid grid-cols-[1fr_1fr_1fr] px-[24px] h-[48px] border-b border-[#F1F1F4] bg-[#FCFCFD]">
                      <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider flex items-center">{t("FORMA")}</span>
                      <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider flex items-center justify-center">{t("QUANTIDADE")}</span>
                      <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider flex items-center justify-end">{t("VALOR TOTAL")}</span>
                    </div>
                    <div className="flex items-center justify-center py-[48px]">
                      <p className="text-[13px] text-[#6B7280]">{t("Nenhuma venda encontrada para a forma de pagamento.")}</p>
                    </div>
                  </div>
                )}

                {/* SESSÃO: Negociação */}
                {activeTab === "Negociação" && (
                  <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden shadow-sm">
                    <div className="grid grid-cols-[1fr_1fr_1fr] px-[24px] h-[48px] border-b border-[#F1F1F4] bg-[#FCFCFD]">
                      <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider flex items-center">{t("TIPO")}</span>
                      <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider flex items-center justify-center">{t("QUANTIDADE")}</span>
                      <span className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider flex items-center justify-end">{t("VALOR TOTAL")}</span>
                    </div>
                    <div className="flex items-center justify-center py-[48px]">
                      <p className="text-[13px] text-[#6B7280]">{t("Nenhuma venda encontrada para o tipo de negociação.")}</p>
                    </div>
                  </div>
                )}

                {/* SESSÃO: Detalhamento */}
                {activeTab === "Detalhamento" && (
                  <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden shadow-sm">
                    <div className="flex flex-col">
                      <div className="flex items-center justify-center py-[48px]">
                        <p className="text-[13px] text-[#6B7280]">{t("Nenhum detalhamento encontrado para o período.")}</p>
                      </div>
                    </div>
                  </div>
                )}

              </div>

              {/* Right Column: Sidebar (Resumo e Ações) */}
              <div className="w-full lg:w-[320px] shrink-0 flex flex-col gap-[16px]">
                
                {/* Controles de Filtro e Exportação */}
                <div className="bg-white border border-[#E5E7EB] rounded-[16px] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                  <h3 className="text-[16px] font-[800] text-[#1A1A2E] mb-[16px]">{t("Filtros do Relatório")}</h3>
                  
                  <div className="flex flex-col gap-[16px]">
                    <div className="flex flex-col">
                      <label className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider mb-[6px] flex items-center gap-[6px]">
                        <Calendar className="w-[12px] h-[12px]" />
                        {t("COMPETÊNCIA")}
                      </label>
                      <input 
                        type="month" 
                        value={mes}
                        onChange={(e) => setMes(e.target.value)}
                        className="w-full bg-white border border-[#E5E7EB] rounded-[8px] h-[40px] px-[12px] text-[13px] font-[500] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all"
                      />
                    </div>
                    
                    <button className="w-full h-[40px] bg-[#F4EEFF] text-[#6D28D9] font-[700] text-[13px] rounded-[8px] hover:bg-[#E9D5FF] transition-colors flex items-center justify-center gap-[8px]">
                      <Download className="w-[16px] h-[16px]" strokeWidth={2.5} />
                      {t("Exportar Dados")}
                    </button>
                  </div>
                </div>

                {/* Indicadores Adicionais (Complemento dos 6 KPI do Print) */}
                <div className="bg-white border border-[#E5E7EB] rounded-[16px] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                  <h3 className="text-[16px] font-[800] text-[#1A1A2E] mb-[20px]">{t("Mais Indicadores")}</h3>
                  
                  <div className="flex flex-col gap-[16px]">
                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <Percent className="w-[14px] h-[14px] text-[#3B82F6]" />
                        {t("% Meta")}
                      </div>
                      <span className="text-[14px] font-[800] text-[#3B82F6]">0%</span>
                    </div>

                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <Receipt className="w-[14px] h-[14px] text-[#0284C7]" />
                        {t("Ticket Médio")}
                      </div>
                      <span className="text-[14px] font-[800] text-[#0284C7]">R$ 0,00</span>
                    </div>

                    <div className="flex items-center justify-between pt-[4px]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <Users className="w-[14px] h-[14px] text-[#6D28D9]" />
                        {t("Clientes Ativos")}
                      </div>
                      <span className="text-[14px] font-[800] text-[#1A1A2E]">0</span>
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
