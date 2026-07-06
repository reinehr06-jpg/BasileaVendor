"use client";
import { FinanceiroService } from "@/services/financeiro.service";


import React, { useState, useEffect } from "react";
import Link from "next/link";
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
    c.cliente.toLowerCase().includes(busca.toLowerCase())
  );
  
  const paginatedComissoes = filteredComissoes.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  // KPIs mockados da imagem
  const kpis = {
    vendas: 3,
    totalComissao: "R$ 84,40",
    totalVendas: "R$ 844,00",
    comissaoMedia: "10%"
  };

  return (
    <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

      {/* KPIs Resumo */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-[16px] mb-[20px]">
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#F4EEFF] flex items-center justify-center">
                <ShoppingBag className="w-[16px] h-[16px] text-[#7C3AED]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#111827] leading-none mb-1">{kpis.vendas}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Vendas Comissionadas")}</p>
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
                    placeholder={t("Buscar por cliente...")}
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
              <div className="grid grid-cols-[100px_2fr_120px_120px_100px] items-center px-[24px] h-[40px] border-t border-b border-[#F1F1F4] bg-[#FCFCFD] min-w-[700px]">
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Data")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Cliente")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Venda")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Comissão")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Status")}</span>
              </div>

              {/* Linhas */}
              {paginatedComissoes.map((c) => (
                <div key={c.id} className="grid grid-cols-[100px_2fr_120px_120px_100px] items-center px-[24px] h-[52px] bg-white border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0 min-w-[700px]">
                  
                  <span className="text-[12px] font-[500] text-[#6B7280]">{c.data}</span>
                  
                  <span className="text-[13px] font-[600] text-[#111827] truncate pr-4">{c.cliente}</span>
                  
                  <span className="text-[12px] font-[500] text-[#4B5563]">{c.venda}</span>
                  
                  <span className="text-[12px] font-[700] text-[#059669]">{c.comissao}</span>
                  
                  <div>
                    <span className="inline-flex items-center px-[8px] py-[2px] text-[10px] font-[700] rounded-full bg-[#D1FAE5] text-[#059669] uppercase tracking-wide">
                      {c.status}
                    </span>
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
  );
}
