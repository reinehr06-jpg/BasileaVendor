"use client";

import React, { useState, useEffect } from "react";
import { toast } from "sonner";
import Link from "next/link";
import Pagination from "@/components/Pagination";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  CreditCard,
  Search,
  ListTodo,
  CheckCircle2,
  Clock,
  DollarSign,
  Download
} from "lucide-react";

import { FinanceiroService } from "@/services/financeiro.service";

export default function PagamentosPage() {
  const { t } = useTranslation();
  const [busca, setBusca] = useState("");
  const [statusFiltro, setStatusFiltro] = useState("");
  const [formaFiltro, setFormaFiltro] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [pageSize, setPageSize] = useState(15);
  const [pagamentos, setPagamentos] = useState<any[]>([]);

  useEffect(() => {
    FinanceiroService.listarPagamentos({ page: currentPage, search: busca }).then((res) => {
      setPagamentos(res.data);
      if (res.meta) {
        setTotalPages(res.meta.last_page || 1);
        setTotalItems(res.meta.total || res.data.length);
      }
    });
  }, [currentPage, busca]);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  // KPIs mockados da imagem (idealmente devem vir de um endpoint meta ou de resumo do backend)
  const kpis = {
    total: totalItems,
    pagos: 0,
    pendentes: 0,
    recebido: "R$ 0,00"
  };

  return (
    <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

      {/* KPIs Resumo */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-[16px] mb-[20px]">
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#F3F4F6] flex items-center justify-center">
                <ListTodo className="w-[16px] h-[16px] text-[#6B7280]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#111827] leading-none mb-1">{kpis.total}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Total")}</p>
            </div>
            
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#D1FAE5] flex items-center justify-center">
                <CheckCircle2 className="w-[16px] h-[16px] text-[#059669]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#059669] leading-none mb-1">{kpis.pagos}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Pagos")}</p>
            </div>

            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#FEF3C7] flex items-center justify-center">
                <Clock className="w-[16px] h-[16px] text-[#D97706]" />
              </div>
              <h3 className="text-[36px] font-[700] text-[#D97706] leading-none mb-1">{kpis.pendentes}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Pendentes")}</p>
            </div>

            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[20px] flex flex-col relative">
              <div className="absolute top-[20px] right-[20px] w-[32px] h-[32px] rounded-full bg-[#ECFDF5] flex items-center justify-center">
                <DollarSign className="w-[16px] h-[16px] text-[#10B981]" />
              </div>
              <h3 className="text-[28px] font-[700] text-[#10B981] leading-none mb-2 mt-1">{kpis.recebido}</h3>
              <p className="text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Recebido")}</p>
            </div>
          </div>

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

            {/* CABEÇALHO DENTRO DO CARD */}
            <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <CreditCard className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Controle de Pagamentos")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Visão global de todas as cobranças e recebimentos.")}</p>
              </div>
            </div>

            {/* Toolbar: Buscas e Filtros */}
            <div className="p-[24px] flex flex-col xl:flex-row items-start xl:items-center justify-between gap-[16px]">
              
              {/* Lado Esquerdo: Contador */}
              <div className="flex items-center gap-[12px]">
                <span className="text-[13px] font-[500] text-[#6B7280] hidden sm:inline-block">
                  {totalItems} {totalItems === 1 ? t("registro encontrado") : t("registros encontrados")}
                </span>
              </div>
              
              {/* Lado Direito: Busca e Filtros */}
              <div className="flex flex-wrap items-center gap-[10px] w-full xl:w-auto">
                <div className="relative flex items-center w-full sm:w-auto h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={busca}
                    onChange={(e) => {
                      setBusca(e.target.value);
                      setCurrentPage(1);
                    }}
                    placeholder={t("Buscar por cliente...")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full sm:w-[220px]"
                  />
                </div>

                <div className="w-full sm:w-auto">
                  <CustomSelect
                    options={[
                      { label: t("Status: Todos"), value: "" },
                      { label: t("Status: Pago"), value: "Pago" },
                      { label: t("Status: Pendente"), value: "Pendente" }
                    ]}
                    value={statusFiltro}
                    onChange={setStatusFiltro}
                    placeholder={t("Status")}
                    triggerClassName="h-[36px] min-w-[150px] bg-white text-[12px]"
                  />
                </div>

                <div className="w-full sm:w-auto">
                  <CustomSelect
                    options={[
                      { label: t("Forma: Todas"), value: "" },
                      { label: t("Cartão de Crédito"), value: "Cartão de Crédito" },
                      { label: t("Pix"), value: "Pix" },
                      { label: t("Boleto"), value: "Boleto" }
                    ]}
                    value={formaFiltro}
                    onChange={setFormaFiltro}
                    placeholder={t("Forma de Pagto")}
                    triggerClassName="h-[36px] min-w-[150px] bg-white text-[12px]"
                  />
                </div>
              </div>
            </div>

            {/* Tabela */}
            <div className="flex-1 flex flex-col overflow-x-auto">
              <table className="w-full border-collapse">
                <thead>
                  <tr className="border-b border-[#E5E7EB]">
                    <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider text-left">{t("Cliente / Empresa")}</th>
                    <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider text-left">{t("Valor / Forma")}</th>
                    <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider text-left">{t("Status")}</th>
                    <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider text-left">{t("Data de Pagamento")}</th>
                    <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider text-center">{t("Ações")}</th>
                  </tr>
                </thead>
                <tbody>
                  {pagamentos.map((p, i) => (
                    <tr key={p.id || i} className="border-b border-[#F1F1F4] hover:bg-[#FAFAFC]">
                      <td className="p-[12px_24px] align-middle">
                        <span className="text-[13px] font-[600] text-[#111827]">{p.cliente?.nome || '-'}</span>
                      </td>
                      <td className="p-[12px_24px] align-middle">
                        <div className="flex flex-col gap-1">
                          <span className="text-[13px] font-[700] text-[#111827]">
                            {new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(p.valor || 0))}
                          </span>
                          <span className="inline-flex items-center px-[8px] py-[2px] text-[10px] font-[700] rounded-full bg-[#F3E8FF] text-[#7C3AED] uppercase tracking-wide w-fit">
                            {p.forma_pagamento_real || p.forma_pagamento || '-'}
                          </span>
                        </div>
                      </td>
                      <td className="p-[12px_24px] align-middle">
                        <span className={`inline-flex items-center px-[8px] py-[2px] text-[10px] font-[700] rounded-full uppercase tracking-wide ${
                          (p.status === "RECEIVED" || p.status === "RECEIVED_IN_CASH") ? "bg-[#D1FAE5] text-[#059669]" : 
                          p.status === "PENDING" ? "bg-[#FEF3C7] text-[#D97706]" : 
                          "bg-[#F3F4F6] text-[#6B7280]"
                        }`}>
                          {p.status || 'PENDENTE'}
                        </span>
                      </td>
                      <td className="p-[12px_24px] align-middle text-[13px] font-[500] text-[#6B7280]">
                        {p.data_pagamento ? new Date(p.data_pagamento).toLocaleDateString('pt-BR') : '-'}
                      </td>
                      <td className="p-[12px_24px] align-middle text-center">
                        <button title={t("Baixar comprovante")} onClick={() => toast.success(t("O download do comprovante foi iniciado!"))} className="w-[30px] h-[30px] rounded-[8px] border border-[#E5E7EB] bg-white flex items-center justify-center hover:bg-[#F3F4F6] hover:text-[#6D28D9] transition-colors mx-auto">
                          <Download className="w-[14px] h-[14px] text-[#6B7280] hover:text-[#6D28D9]" strokeWidth={2.2} />
                        </button>
                      </td>
                    </tr>
                  ))}
                  {pagamentos.length === 0 && (
                    <tr>
                      <td colSpan={5} className="text-center p-8 text-[#6B7280] text-[13px]">{t("Nenhum pagamento encontrado")}</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {/* Paginação */}
            {pagamentos.length > 0 && (
              <div className="p-[12px_24px] border-t border-[#E5E7EB]">
                <Pagination
                  currentPage={currentPage}
                  onPageChange={handlePageChange}
                  pageSize={pageSize}
                  onPageSizeChange={handlePageSizeChange}
                  total={totalItems}
                />
              </div>
            )}
          </div>
          {/* END CARD PRINCIPAL */}

          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[24px] pb-[12px] flex justify-center">
            <p className="text-[14px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Vendor OS")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
  );
}
