"use client";
import { ClientesService } from "@/services/clientes.service";


import React, { useState, useEffect } from "react";
import Link from "next/link";
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
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [clientes, setClientes] = useState<any[]>([]);

  useEffect(() => {
    ClientesService.listar().then(setClientes);
  }, []);

  const filteredClientes = clientes.filter(c =>
    c.nome.toLowerCase().includes(buscaNome.toLowerCase())
  );
  
  const paginatedClientes = filteredClientes.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  return (
    <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col min-h-0 relative">

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
              {filteredClientes.length} {filteredClientes.length === 1 ? t("cliente encontrado") : t("clientes encontrados")}
            </span>
          </div>
          
          <div className="flex items-center gap-[10px] w-full xl:w-auto">
            <div className="relative flex items-center w-full xl:w-[260px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
              <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
              <input
                type="text"
                value={buscaNome}
                onChange={(e) => setBuscaNome(e.target.value)}
                placeholder={t("Buscar clientes...")}
                className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
              />
            </div>
          </div>
        </div>

        {/* Tabela */}
        <div className="flex-1 flex flex-col overflow-x-auto">
          <table className="w-full text-left border-collapse min-w-[800px]">
            <thead>
              <tr className="border-b border-[#E5E7EB]">
                <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Cliente / Empresa")}</th>
                <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Responsável / Doc")}</th>
                <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Situação Financeira")}</th>
                <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Status")}</th>
                <th className="p-[12px_24px] text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider text-center">{t("Ações")}</th>
              </tr>
            </thead>
            <tbody>
              {paginatedClientes.map((c) => (
                <tr key={c.id} className="border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0">
                  <td className="p-[12px_24px] align-middle">
                    <span className="text-[13px] font-[600] text-[#111827]">{c.nome}</span>
                  </td>
                  <td className="p-[12px_24px] align-middle">
                    <div className="flex flex-col">
                      <span className="text-[13px] font-[500] text-[#374151]">{c.responsavel}</span>
                      <span className="text-[11px] text-[#9CA3AF] mt-0.5">{c.cpfCnpj}</span>
                    </div>
                  </td>
                  <td className="p-[12px_24px] align-middle">
                    <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                      c.financeiro === "Em dia" ? "bg-[#ECFDF5] text-[#059669]" : 
                      c.financeiro === "Em Atraso" ? "bg-[#FEF3C7] text-[#D97706]" : 
                      "bg-[#FEE2E2] text-[#DC2626]"
                    }`}>
                      {c.financeiro}
                    </span>
                  </td>
                  <td className="p-[12px_24px] align-middle">
                    <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                      c.status === "Ativo"
                        ? "bg-[#D1FAE5] text-[#059669]"
                        : "bg-[#FEE2E2] text-[#DC2626]"
                    }`}>
                      {c.status === "Ativo" ? t("Ativo") : t("Inativo")}
                    </span>
                  </td>
                  <td className="p-[12px_24px] align-middle text-center">
                    <Link href={`/gestao-comercial/clientes/${c.id}`} title="Ver detalhes" className="inline-flex w-[30px] h-[30px] rounded-[8px] border border-[#E5E7EB] bg-white items-center justify-center hover:bg-[#F3F4F6] transition-colors">
                      <Eye className="w-[14px] h-[14px] text-[#6B7280]" strokeWidth={2.2} />
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Paginação */}
        <div className="p-[12px_24px] border-t border-[#E5E7EB]">
          <Pagination
            currentPage={currentPage}
            onPageChange={handlePageChange}
            pageSize={pageSize}
            onPageSizeChange={handlePageSizeChange}
            total={filteredClientes.length}
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
