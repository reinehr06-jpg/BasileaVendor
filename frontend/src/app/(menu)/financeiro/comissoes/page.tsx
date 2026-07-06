"use client";
import { FinanceiroService } from "@/services/financeiro.service";


import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Pagination from "@/components/Pagination";
import { useTranslation } from "react-i18next";
import {
  Percent,
  Search,
  Users,
  DollarSign,
  ShoppingBag,
  TrendingUp,
  Eye
} from "lucide-react";
import CustomDatePicker from "@/components/CustomDatePicker";



export default function ComissoesPage() {
  const { t } = useTranslation();
  const [busca, setBusca] = useState("");
  const [mesFiltro, setMesFiltro] = useState<string>("2026-07-01");
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [comissoes, setComissoes] = useState<any[]>([]);

  useEffect(() => {
    FinanceiroService.listarComissoes().then(setComissoes);
  }, []);

  const filteredComissoes = comissoes.filter(c =>
    c.vendedor.toLowerCase().includes(busca.toLowerCase()) || 
    c.email.toLowerCase().includes(busca.toLowerCase())
  );
  
  const paginatedComissoes = filteredComissoes.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  // KPIs mockados da imagem
  const kpis = {
    vendedores: 9,
    totalComissao: "R$ 100,47",
    totalVendas: 0,
    comissaoMedia: "R$ 0,00"
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT */}
        <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

          {/* KPIs Resumo */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-[16px] mb-[20px]">
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#F4EEFF] flex items-center justify-center">
                <Users className="w-[16px] h-[16px] text-[#7C3AED]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#111827] leading-none mb-1">{kpis.vendedores}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Vendedores")}</p>
            </div>
            
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#D1FAE5] flex items-center justify-center">
                <DollarSign className="w-[16px] h-[16px] text-[#059669]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#059669] leading-none mb-1">{kpis.totalComissao}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Total Comissão")}</p>
            </div>

            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#EFF6FF] flex items-center justify-center">
                <ShoppingBag className="w-[16px] h-[16px] text-[#2563EB]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#111827] leading-none mb-1">{kpis.totalVendas}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Total Vendas")}</p>
            </div>

            {/* Este card tem fundo roxo escuro como na imagem de referência */}
            <div className="bg-[#5B21B6] rounded-[16px] border border-[#4C1D95] shadow-[0_2px_8px_rgba(0,0,0,0.05)] p-[20px] flex flex-col relative overflow-hidden">
              <div className="absolute -right-6 -top-6 opacity-20">
                <Percent className="w-[100px] h-[100px] text-white" />
              </div>
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-white/[0.1] flex items-center justify-center backdrop-blur-sm z-10">
                <TrendingUp className="w-[16px] h-[16px] text-white" />
              </div>
              <h3 className="text-[36px] font-[700] text-white leading-none mb-1 z-10">{kpis.comissaoMedia}</h3>
              <p className="text-[11px] font-[700] text-white/[0.7] uppercase tracking-wider z-10">{t("Comissão Média")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

            {/* CABEÇALHO DENTRO DO CARD */}
            <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <Percent className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Comissões")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Resumo de comissões por vendedor com histórico detalhado.")}</p>
              </div>
            </div>

            {/* Toolbar: Buscas e Filtros (no PADRÃO que você aprovou!) */}
            <div className="p-[24px] flex flex-col xl:flex-row items-start xl:items-center justify-between gap-[16px]">
              
              {/* Lado Esquerdo: Contador */}
              <div className="flex items-center gap-[12px]">
                <span className="text-[13px] font-[500] text-[#6B7280] hidden sm:inline-block">
                  {filteredComissoes.length} {filteredComissoes.length === 1 ? t("registro encontrado") : t("registros encontrados")}
                </span>
              </div>
              
              {/* Lado Direito: Busca e Filtros */}
              <div className="flex flex-wrap items-center gap-[10px] w-full xl:w-auto">
                <div className="relative flex items-center w-full sm:w-auto h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={busca}
                    onChange={(e) => setBusca(e.target.value)}
                    placeholder={t("Buscar por vendedor...")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full sm:w-[220px]"
                  />
                </div>

                <div className="h-[36px]">
                  <CustomDatePicker
                    value={mesFiltro}
                    onChange={setMesFiltro}
                    placeholder={t("Filtrar por Mês")}
                    className="w-full sm:w-[150px] h-[36px] text-[12px] bg-white"
                  />
                </div>
              </div>
            </div>

            {/* Tabela */}
            <div className="flex-1 flex flex-col overflow-x-auto">
              
              {/* Cabeçalho */}
              <div className="grid grid-cols-[1.5fr_100px_140px_100px_120px_140px] items-center px-[24px] h-[40px] border-t border-b border-[#F1F1F4] bg-[#FCFCFD] min-w-[900px]">
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Vendedor")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("Vendas")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Comissão Total")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("% Meta")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("Notas Fiscais")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-right">{t("Ações")}</span>
              </div>

              {/* Linhas */}
              {paginatedComissoes.map((c, i) => (
                <div key={i} className="grid grid-cols-[1.5fr_100px_140px_100px_120px_140px] items-center px-[24px] py-[12px] bg-white border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0 min-w-[900px]">
                  
                  {/* Avatar + Nome + Email */}
                  <div className="flex items-center gap-[12px] pr-4">
                    <div className={`w-[32px] h-[32px] rounded-full ${c.avatarColor} text-white flex items-center justify-center text-[12px] font-[600] shrink-0`}>
                      {c.vendedor.charAt(0).toUpperCase()}
                    </div>
                    <div className="flex flex-col truncate">
                      <span className="text-[13px] font-[600] text-[#111827] truncate">{c.vendedor}</span>
                      <span className="text-[11px] font-[500] text-[#9CA3AF] truncate mt-0.5">{c.email}</span>
                    </div>
                  </div>
                  
                  <span className="text-[13px] font-[700] text-[#374151] text-center">{c.vendas}</span>
                  
                  <span className="text-[13px] font-[700] text-[#059669]">{c.comissao}</span>
                  
                  <div className="flex justify-center">
                    <span className="inline-flex items-center px-[8px] py-[2px] text-[10px] font-[700] rounded-full bg-[#FEE2E2] text-[#DC2626]">
                      {c.meta}
                    </span>
                  </div>

                  <span className="text-[13px] font-[500] text-[#9CA3AF] text-center">{c.notas}</span>
                  
                  <div className="flex items-center justify-end">
                    <Link href={`/financeiro/comissoes/${c.id}`} className="flex items-center gap-[6px] px-[12px] py-[6px] bg-[#6D28D9] text-white text-[11px] font-[600] rounded-[6px] hover:bg-[#5B21B6] transition-colors shadow-sm">
                      <Eye className="w-[12px] h-[12px]" />
                      {t("Ver Histórico")}
                    </Link>
                  </div>
                  
                </div>
              ))}
            </div>

            {/* Paginação */}
            <div className="p-[12px_24px] border-t border-[#E5E7EB]">
              <Pagination
                currentPage={currentPage}
                onPageChange={handlePageChange}
                pageSize={pageSize}
                onPageSizeChange={handlePageSizeChange}
                total={filteredComissoes.length}
              />
            </div>

          </div>

          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[24px] pb-[12px] flex justify-center">
            <p className="text-[14px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Vendor OS")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
      </div>
    </div>
  );
}
