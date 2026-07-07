"use client";
import { ClientesService } from "@/services/clientes.service";


import React, { useState, useEffect } from "react";
import Link from "next/link";
import Topbar from "@/components/Topbar";
import Pagination from "@/components/Pagination";
import { useTranslation } from "react-i18next";
import {
  Contact,
  Search,
  Eye,
} from "lucide-react";



export default function ClientesPage() {
  const { t } = useTranslation();
  const [buscaNome, setBuscaNome] = useState("");
  const [buscaVendedor, setBuscaVendedor] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(15);
  const [clientes, setClientes] = useState<any[]>([]);
  const [totalItems, setTotalItems] = useState(0);

  useEffect(() => {
    ClientesService.listar({ page: currentPage, search: buscaNome }).then((res: any) => {
      setClientes(res.data || []);
      setTotalItems(res.total || res.meta?.total || 0);
    });
  }, [currentPage, buscaNome]);

  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  return (
    <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col h-full">

      {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

            {/* CABEÇALHO DENTRO DO CARD */}
            <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <Contact className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Clientes")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Consulte e gerencie a carteira de clientes do seu negócio.")}</p>
              </div>
            </div>

            {/* Toolbar: Botão e Buscas */}
            <div className="p-[24px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[16px]">
              <div className="flex items-center gap-[12px]">
                <span className="text-[13px] font-[500] text-[#6B7280] hidden sm:inline-block">
                  {totalItems} {totalItems === 1 ? t("cliente encontrado") : t("clientes encontrados")}
                </span>
              </div>
              
              <div className="flex items-center gap-[10px] w-full xl:w-auto">
                <div className="relative flex items-center w-full xl:w-[220px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaNome}
                    onChange={(e) => {
                      setBuscaNome(e.target.value);
                      setCurrentPage(1);
                    }}
                    placeholder={t("Buscar por Cliente")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
                <div className="relative flex items-center w-full xl:w-[220px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaVendedor}
                    onChange={(e) => {
                      setBuscaVendedor(e.target.value);
                      setCurrentPage(1);
                    }}
                    placeholder={t("Buscar por Vendedor")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
              </div>
            </div>

            {/* Tabela */}
            <div className="flex-1 flex flex-col overflow-x-auto">

              {/* Cabeçalho */}
              <div className="grid grid-cols-[1.8fr_1fr_1.2fr_120px_100px_90px] items-center px-[24px] h-[40px] border-t border-b border-[#F1F1F4] bg-[#FCFCFD] min-w-[800px]">
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Cliente")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Responsável")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Vendedor")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Financeiro")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Status")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("Ações")}</span>
              </div>

              {/* Linhas */}
              {clientes.map((c) => (
                <div key={c.id} className="grid grid-cols-[1.8fr_1fr_1.2fr_120px_100px_90px] items-center px-[24px] h-[52px] bg-white border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0 min-w-[800px]">
                  
                  <div className="flex flex-col justify-center truncate pr-4">
                    <span className="text-[12px] font-[600] text-[#111827] truncate">{c.nome}</span>
                    <span className="text-[10px] font-[500] text-[#9CA3AF] mt-0.5">{c.cpfCnpj}</span>
                  </div>
                  
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{c.responsavel}</span>
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{c.vendedor}</span>
                  
                  <div>
                    <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                      c.financeiro === "Em dia" ? "bg-[#ECFDF5] text-[#059669]" : 
                      c.financeiro === "Em Atraso" ? "bg-[#FEF3C7] text-[#D97706]" : 
                      "bg-[#FEE2E2] text-[#DC2626]"
                    }`}>
                      {c.financeiro}
                    </span>
                  </div>

                  <div>
                    <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                      c.status === "Ativo"
                        ? "bg-[#D1FAE5] text-[#059669]"
                        : "bg-[#FEE2E2] text-[#DC2626]"
                    }`}>
                      {c.status === "Ativo" ? t("Ativo") : t("Inativo")}
                    </span>
                  </div>
                  
                  <div className="flex items-center justify-center">
                    <Link href={`/gestao-comercial/clientes/${c.id}`} title="Ver detalhes" className="w-[30px] h-[30px] rounded-[8px] border border-[#E5E7EB] bg-white flex items-center justify-center hover:bg-[#F3F4F6] transition-colors">
                      <Eye className="w-[14px] h-[14px] text-[#6B7280]" strokeWidth={2.2} />
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
                total={totalItems}
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
