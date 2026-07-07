"use client";

import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Pagination from "@/components/Pagination";
import { useTranslation } from "react-i18next";
import {
  ClipboardCheck,
  Search,
  CheckCircle2,
  XCircle,
  Eye,
  Clock
} from "lucide-react";

import { AprovacoesService } from "@/services/aprovacoes.service";

export default function AprovacoesPage() {
  const { t } = useTranslation();
  const [buscaVendedor, setBuscaVendedor] = useState("");
  const [buscaCliente, setBuscaCliente] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [aprovacoes, setAprovacoes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  React.useEffect(() => {
    carregarAprovacoes();
  }, []);

  const carregarAprovacoes = async () => {
    try {
      setLoading(true);
      const res: any = await AprovacoesService.listar();
      setAprovacoes(res.data.data || []);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const filteredAprovacoes = aprovacoes.filter(a =>
    a.vendedor.toLowerCase().includes(buscaVendedor.toLowerCase()) &&
    a.cliente.toLowerCase().includes(buscaCliente.toLowerCase())
  );
  
  const paginatedAprovacoes = filteredAprovacoes.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  const kpis = {
    pendentes: aprovacoes.filter(a => a.status === "Pendente").length,
    aprovadas: aprovacoes.filter(a => a.status === "Aprovado").length,
    rejeitadas: aprovacoes.filter(a => a.status === "Rejeitado").length,
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT */}
        <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

          {/* KPIs Resumo (Pequenos e discretos antes do card principal) */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px] mb-[20px]">
            
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#FEF3C7] flex items-center justify-center">
                <Clock className="w-[16px] h-[16px] text-[#D97706]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#D97706] leading-none mb-1">{kpis.pendentes}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Pendentes")}</p>
            </div>
            
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#D1FAE5] flex items-center justify-center">
                <CheckCircle2 className="w-[16px] h-[16px] text-[#059669]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#059669] leading-none mb-1">{kpis.aprovadas}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Aprovadas")}</p>
            </div>

            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#FEE2E2] flex items-center justify-center">
                <XCircle className="w-[16px] h-[16px] text-[#DC2626]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#DC2626] leading-none mb-1">{kpis.rejeitadas}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Rejeitadas")}</p>
            </div>

          </div>

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

            {/* CABEÇALHO DENTRO DO CARD */}
            <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <ClipboardCheck className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Aprovações Comerciais")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Vendas que precisam de aprovação por desconto ou plano especial.")}</p>
              </div>
            </div>

            {/* Toolbar: Buscas */}
            <div className="p-[24px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[16px]">
              <div className="flex items-center gap-[12px]">
                {/* Aqui não tem botão "Nova", é apenas visualização */}
                <span className="text-[13px] font-[500] text-[#6B7280] hidden sm:inline-block">
                  {filteredAprovacoes.length} {filteredAprovacoes.length === 1 ? t("registro encontrado") : t("registros encontrados")}
                </span>
              </div>
              
              <div className="flex flex-wrap items-center gap-[10px] w-full xl:w-auto">
                <div className="relative flex items-center w-full xl:w-[220px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaVendedor}
                    onChange={(e) => setBuscaVendedor(e.target.value)}
                    placeholder={t("Buscar por Vendedor")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
                <div className="relative flex items-center w-full xl:w-[220px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaCliente}
                    onChange={(e) => setBuscaCliente(e.target.value)}
                    placeholder={t("Buscar por Cliente")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
              </div>
            </div>

            {/* Tabela */}
            <div className="flex-1 flex flex-col overflow-x-auto">
              
              {/* Cabeçalho */}
              <div className="grid grid-cols-[90px_1.5fr_1.5fr_140px_110px_110px_1.2fr_110px_80px] items-center px-[24px] h-[40px] border-t border-b border-[#F1F1F4] bg-[#FCFCFD] min-w-[1000px]">
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Venda #")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Vendedor")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Cliente")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Tipo")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Valor Solicitado")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Status")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Por")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Data")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("Ações")}</span>
              </div>

              {/* Linhas */}
              {paginatedAprovacoes.length > 0 ? paginatedAprovacoes.map((a, i) => (
                <div key={i} className="grid grid-cols-[90px_1.5fr_1.5fr_140px_110px_110px_1.2fr_110px_80px] items-center px-[24px] h-[52px] bg-white border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0 min-w-[1000px]">
                  
                  <span className="text-[12px] font-[700] text-[#111827]">{a.id}</span>
                  
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{a.vendedor}</span>
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{a.cliente}</span>
                  
                  <div>
                    <span className="inline-flex items-center px-[8px] py-[2px] text-[10px] font-[700] rounded-full bg-[#FEF3C7] text-[#D97706] uppercase tracking-wide whitespace-nowrap">
                      {a.tipo}
                    </span>
                  </div>

                  <span className="text-[12px] font-[600] text-[#111827]">{a.valor}</span>
                  
                  <div>
                    <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                      a.status === "Aprovado" ? "bg-[#D1FAE5] text-[#059669]" : 
                      a.status === "Pendente" ? "bg-[#F3F4F6] text-[#4B5563]" : 
                      "bg-[#FEE2E2] text-[#DC2626]"
                    }`}>
                      {a.status}
                    </span>
                  </div>

                  <span className="text-[12px] font-[500] text-[#6B7280] truncate pr-4">{a.por}</span>
                  <span className="text-[12px] font-[500] text-[#6B7280]">{a.data.split(" ")[0]} <br/> <span className="text-[10px]">{a.data.split(" ")[1]}</span></span>
                  
                  <div className="flex items-center justify-center gap-[6px]">
                    <Link href={`/gestao-comercial/aprovacoes/${a.id.replace('#', '')}`} title="Ver detalhes" className="w-[30px] h-[30px] rounded-[8px] border border-[#E5E7EB] bg-white flex items-center justify-center hover:bg-[#F3F4F6] transition-colors">
                      <Eye className="w-[14px] h-[14px] text-[#6B7280]" strokeWidth={2.2} />
                    </Link>
                    {a.status === "Pendente" && (
                      <>
                        <button title="Aprovar" className="w-[30px] h-[30px] rounded-[8px] bg-[#ECFDF5] flex items-center justify-center hover:bg-[#D1FAE5] transition-colors">
                          <CheckCircle2 className="w-[14px] h-[14px] text-[#059669]" strokeWidth={2.2} />
                        </button>
                        <button title="Rejeitar" className="w-[30px] h-[30px] rounded-[8px] bg-[#FEF2F2] flex items-center justify-center hover:bg-[#FEE2E2] transition-colors">
                          <XCircle className="w-[14px] h-[14px] text-[#DC2626]" strokeWidth={2.2} />
                        </button>
                      </>
                    )}
                  </div>
                  
                </div>
              )) : (
                <div className="flex-1 flex flex-col items-center justify-center p-8">
                  <div className="w-[64px] h-[64px] rounded-full bg-[#F3F4F6] flex items-center justify-center mb-4">
                    <CheckCircle2 className="w-[32px] h-[32px] text-[#9CA3AF]" />
                  </div>
                  <h3 className="text-[16px] font-[600] text-[#111827]">{t("Nenhuma aprovação pendente")}</h3>
                  <p className="text-[13px] text-[#6B7280] mt-1">{t("Todas as vendas estão dentro das regras comerciais.")}</p>
                </div>
              )}
            </div>

            {/* Paginação */}
            {paginatedAprovacoes.length > 0 && (
              <div className="p-[12px_24px] border-t border-[#E5E7EB]">
                <Pagination
                  currentPage={currentPage}
                  onPageChange={handlePageChange}
                  pageSize={pageSize}
                  onPageSizeChange={handlePageSizeChange}
                  total={filteredAprovacoes.length}
                />
              </div>
            )}

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
