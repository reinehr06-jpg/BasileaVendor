"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import CustomDatePicker from "@/components/CustomDatePicker";
import { useTranslation } from "react-i18next";
import {
  Target,
  Trophy,
  ArrowDown,
  DollarSign,
  CheckCircle2,
  TrendingUp,
  Plus,
  FolderOpen,
  Users,
  CreditCard
} from "lucide-react";

export default function MetasPage() {
  const { t } = useTranslation();
  const [mesFiltro, setMesFiltro] = useState<string>("2026-07-01");
  const [vendedorFiltro, setVendedorFiltro] = useState("");

  const kpis = {
    totalMetas: 0,
    metasBatidas: 0,
    abaixoMeta: 0,
    volumeEsperado: "R$ 0",
    volumeRealizado: "R$ 0",
    atingimentoMedio: "0%"
  };

  return (
    <div className="flex h-screen font-inter bg-[#F5F5F7] overflow-hidden">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT - Agora com overflow-y-auto e p-[16px] para caber tudo */}
        <main className="p-[16px] flex-1 flex flex-col overflow-y-auto">

          {/* KPIs ultra-compactos numa linha só no desktop */}
          <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-[10px] mb-[12px]">
            
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[12px_16px] flex flex-col relative">
              <div className="absolute top-[12px] right-[12px] w-[24px] h-[24px] rounded-full bg-[#F4EEFF] flex items-center justify-center">
                <Target className="w-[12px] h-[12px] text-[#7C3AED]" />
              </div>
              <h3 className="text-[22px] font-[700] text-[#111827] leading-none mb-1">{kpis.totalMetas}</h3>
              <p className="text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Total de Metas")}</p>
            </div>
            
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[12px_16px] flex flex-col relative">
              <div className="absolute top-[12px] right-[12px] w-[24px] h-[24px] rounded-full bg-[#D1FAE5] flex items-center justify-center">
                <Trophy className="w-[12px] h-[12px] text-[#059669]" />
              </div>
              <h3 className="text-[22px] font-[700] text-[#059669] leading-none mb-1">{kpis.metasBatidas}</h3>
              <p className="text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Metas Batidas")}</p>
            </div>

            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[12px_16px] flex flex-col relative">
              <div className="absolute top-[12px] right-[12px] w-[24px] h-[24px] rounded-full bg-[#FEE2E2] flex items-center justify-center">
                <ArrowDown className="w-[12px] h-[12px] text-[#DC2626]" />
              </div>
              <h3 className="text-[22px] font-[700] text-[#DC2626] leading-none mb-1">{kpis.abaixoMeta}</h3>
              <p className="text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Abaixo da Meta")}</p>
            </div>

            <div className="bg-[#5B21B6] rounded-[12px] border border-[#4C1D95] shadow-[0_2px_8px_rgba(0,0,0,0.05)] p-[12px_16px] flex flex-col relative overflow-hidden">
              <div className="absolute top-[12px] right-[12px] w-[24px] h-[24px] rounded-full bg-white/[0.1] flex items-center justify-center backdrop-blur-sm z-10">
                <DollarSign className="w-[12px] h-[12px] text-[#FBBF24]" />
              </div>
              <h3 className="text-[20px] font-[700] text-white leading-none mb-1.5 mt-0.5 z-10">{kpis.volumeEsperado}</h3>
              <p className="text-[10px] font-[700] text-white/[0.7] uppercase tracking-wider z-10">{t("Volume Esperado")}</p>
            </div>

            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[12px_16px] flex flex-col relative">
              <div className="absolute top-[12px] right-[12px] w-[24px] h-[24px] rounded-full bg-[#ECFDF5] flex items-center justify-center">
                <CheckCircle2 className="w-[12px] h-[12px] text-[#10B981]" />
              </div>
              <h3 className="text-[20px] font-[700] text-[#10B981] leading-none mb-1.5 mt-0.5">{kpis.volumeRealizado}</h3>
              <p className="text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Volume Realizado")}</p>
            </div>

            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[12px_16px] flex flex-col relative">
              <div className="absolute top-[12px] right-[12px] w-[24px] h-[24px] rounded-full bg-[#EFF6FF] flex items-center justify-center">
                <TrendingUp className="w-[12px] h-[12px] text-[#2563EB]" />
              </div>
              <h3 className="text-[22px] font-[700] text-[#111827] leading-none mb-1">{kpis.atingimentoMedio}</h3>
              <p className="text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Atingimento Médio")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL (METAS) REDUZIDO */}
          <div className="bg-white rounded-[14px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col mb-[12px]">
            <div className="p-[12px_20px] flex items-center justify-between border-b border-[#F1F1F4]">
              <div className="flex items-center gap-[10px]">
                <div className="w-[32px] h-[32px] rounded-[8px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                  <Target className="w-[16px] h-[16px] text-[#7C3AED]" strokeWidth={2.2} />
                </div>
                <div>
                  <h1 className="text-[16px] font-[700] text-[#1A1A2E] leading-tight">{t("Metas Comerciais")}</h1>
                  <p className="text-[11px] text-[#6B7280]">{t("Acompanhamento de objetivos")}</p>
                </div>
              </div>

              {/* Controles Menores */}
              <div className="flex items-center gap-[10px]">
                <CustomDatePicker
                  value={mesFiltro}
                  onChange={setMesFiltro}
                  placeholder={t("Mês")}
                  className="w-[130px] h-[32px] text-[11px] bg-white"
                />
                <CustomSelect
                  options={[{ label: t("Todos Vendedores"), value: "" }]}
                  value={vendedorFiltro}
                  onChange={setVendedorFiltro}
                  placeholder={t("Vendedor")}
                  triggerClassName="h-[32px] min-w-[150px] bg-white text-[11px]"
                />
                <Link href="/financeiro/metas/nova" className="flex items-center gap-[6px] px-[12px] py-[8px] bg-[#6D28D9] text-white text-[11px] font-[600] rounded-[6px] hover:bg-[#5B21B6] transition-colors shadow-sm uppercase tracking-wide whitespace-nowrap">
                  <Plus className="w-[14px] h-[14px]" strokeWidth={2.4} />
                  {t("NOVA META")}
                </Link>
              </div>
            </div>

            {/* EMPTY STATE */}
            <div className="flex flex-col items-center justify-center p-[40px_20px]">
              <FolderOpen className="w-[28px] h-[28px] text-[#D1D5DB] mb-2" strokeWidth={1.5} />
              <h3 className="text-[14px] font-[600] text-[#374151]">{t("Nenhuma meta encontrada")}</h3>
            </div>
          </div>

          {/* CARDS SECUNDÁRIOS LADO A LADO */}
          <div className="grid grid-cols-1 xl:grid-cols-2 gap-[12px] flex-1 min-h-0">
            
            {/* Vendas por Vendedor */}
            <div className="bg-white rounded-[14px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col h-full">
              <div className="p-[10px_20px] border-b border-[#F1F1F4] flex items-center gap-[8px] bg-[#FAFAFC]">
                <Users className="w-[14px] h-[14px] text-[#6B7280]" />
                <h2 className="text-[12px] font-[700] text-[#374151]">{t("Vendas por Vendedor")}</h2>
              </div>
              <div className="flex-1 overflow-x-auto overflow-y-auto">
                <div className="grid grid-cols-[1.5fr_80px_100px_80px_60px] items-center px-[20px] h-[36px] bg-white min-w-[500px]">
                  <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase">{t("Vendedor")}</span>
                  <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase text-center">{t("Vendas")}</span>
                  <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase text-right">{t("Vendido")}</span>
                  <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase text-center">{t("Meta")}</span>
                  <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase text-center">{t("% Meta")}</span>
                </div>
                <div className="p-[20px] flex justify-center text-[12px] text-[#6B7280]">
                  {t("Nenhum dado disponível")}
                </div>
              </div>
            </div>

            {/* Formas de Pagamento */}
            <div className="bg-white rounded-[14px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col h-full">
              <div className="p-[10px_20px] border-b border-[#F1F1F4] flex items-center gap-[8px] bg-[#FAFAFC]">
                <CreditCard className="w-[14px] h-[14px] text-[#6B7280]" />
                <h2 className="text-[12px] font-[700] text-[#374151]">{t("Formas de Pagamento")}</h2>
              </div>
              <div className="flex-1 overflow-y-auto">
                <div className="grid grid-cols-[1fr_1fr] items-center px-[20px] h-[36px] bg-white">
                  <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase">{t("Forma")}</span>
                  <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase text-right">{t("Quantidade")}</span>
                </div>
                <div className="p-[20px] flex justify-center text-[12px] text-[#6B7280]">
                  {t("Nenhum dado disponível")}
                </div>
              </div>
            </div>

          </div>

          {/* RODAPÉ REMOVIDO PARA ECONOMIZAR ESPAÇO */}

        </main>
      </div>
    </div>
  );
}
