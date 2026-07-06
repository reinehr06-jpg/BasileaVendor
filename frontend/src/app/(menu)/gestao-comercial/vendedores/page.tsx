"use client";
import { VendedoresService } from "@/services/vendedores.service";


import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Pagination from "@/components/Pagination";
import ModalDesativar from "@/components/ModalDesativar";
import { useTranslation } from "react-i18next";
import {
  Users,
  Plus,
  Upload,
  Search,
  Split,
  Pencil,
  Trash2,
} from "lucide-react";



export default function VendedoresPage() {
  const { t } = useTranslation();
  const [buscaNome, setBuscaNome] = useState("");
  const [buscaCpfCnpj, setBuscaCpfCnpj] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [vendedores, setVendedores] = useState<any[]>([]);

  useEffect(() => {
    VendedoresService.listar().then(setVendedores);
  }, []);
  
  const [modalDesativar, setModalDesativar] = useState<{ isOpen: boolean; id: number | null; nome: string }>({ isOpen: false, id: null, nome: "" });

  const filteredVendedores = vendedores.filter(v =>
    v.nome?.toLowerCase().includes(buscaNome.toLowerCase()) &&
    (v.cpfCnpj || "").includes(buscaCpfCnpj)
  );
  const paginatedVendedores = filteredVendedores.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  const abrirModalDesativar = (id: number, nome: string) => {
    setModalDesativar({ isOpen: true, id, nome });
  };

  const handleConfirmarDesativacao = (motivo: string) => {
    if (modalDesativar.id) {
      setVendedores(prev => prev.map(v => v.id === modalDesativar.id ? { ...v, status: "Inativo" } : v));
      // alert mock para feedback
      alert(`Vendedor desativado com sucesso!\nMotivo salvo no histórico: ${motivo}`);
    }
    setModalDesativar({ isOpen: false, id: null, nome: "" });
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT */}
        <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

            {/* CABEÇALHO DENTRO DO CARD */}
            <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <Users className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Vendedores")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Cadastre e gerencie os vendedores da sua operação.")}</p>
              </div>
            </div>

            {/* Toolbar: Botão e Buscas */}
            <div className="p-[24px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[16px]">
              <div className="flex items-center gap-[12px]">
                <Link href="/gestao-comercial/vendedores/novo" className="flex items-center gap-[6px] px-[16px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm uppercase tracking-wide shrink-0">
                  <Plus className="w-[16px] h-[16px]" strokeWidth={2.4} />
                  {t("NOVO VENDEDOR")}
                </Link>
              </div>
              
              <div className="flex items-center gap-[10px] w-full xl:w-auto">
                <div className="relative flex items-center w-full xl:w-[220px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaNome}
                    onChange={(e) => setBuscaNome(e.target.value)}
                    placeholder={t("Buscar por Nome")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
                <div className="relative flex items-center w-full xl:w-[220px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                  <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                  <input
                    type="text"
                    value={buscaCpfCnpj}
                    onChange={(e) => setBuscaCpfCnpj(e.target.value)}
                    placeholder={t("Buscar por CPF/CNPJ")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
              </div>
            </div>

            {/* Tabela */}
            <div className="flex-1 flex flex-col overflow-x-auto">

              {/* Cabeçalho */}
              <div className="grid grid-cols-[80px_1.8fr_120px_130px_115px_105px_105px_90px] items-center px-[24px] h-[40px] border-t border-b border-[#F1F1F4] bg-[#FCFCFD]">
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Status")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Nome")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("CPF/CNPJ")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Telefone")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Membro desde")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Criado em")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Split")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("Ações")}</span>
              </div>

              {/* Linhas */}
              {paginatedVendedores.map((v) => (
                <div key={v.id} className="grid grid-cols-[80px_1.8fr_120px_130px_115px_105px_105px_90px] items-center px-[24px] h-[42px] bg-white border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0">
                  <div>
                    <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                      v.status === "Ativo"
                        ? "bg-[#D1FAE5] text-[#059669]"
                        : "bg-[#FEE2E2] text-[#DC2626]"
                    }`}>
                      {v.status === "Ativo" ? t("Ativo") : t("Inativo")}
                    </span>
                  </div>
                  <Link href={`/gestao-comercial/vendedores/${v.id}`} className="text-[12px] font-[600] text-[#6D28D9] truncate pr-4 cursor-pointer hover:underline">
                    {v.nome}
                  </Link>
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{v.cpfCnpj}</span>
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{v.telefone}</span>
                  <span className="text-[12px] font-[500] text-[#4B5563]">{v.desde}</span>
                  <span className="text-[12px] font-[500] text-[#4B5563]">{v.criadoEm}</span>
                  <div>
                    <div className={`inline-flex items-center gap-[4px] px-[8px] py-[2px] rounded-full ${
                      v.split === "Ativo"
                        ? "bg-[#F4EEFF] border border-[#7C3AED]/[0.12]"
                        : "bg-[#F3F4F6] border border-[#D1D5DB]/[0.3]"
                    }`}>
                      <Split className={`w-[12px] h-[12px] ${v.split === "Ativo" ? "text-[#7C3AED]" : "text-[#9CA3AF]"}`} strokeWidth={2.4} />
                      <span className={`text-[11px] font-[600] leading-none ${v.split === "Ativo" ? "text-[#7C3AED]" : "text-[#9CA3AF]"}`}>
                        {v.split === "Ativo" ? t("Ativo") : t("Inativo")}
                      </span>
                    </div>
                  </div>
                  <div className="flex items-center gap-[4px]">
                    <Link href={`/gestao-comercial/vendedores/${v.id}`} className="w-[32px] h-[32px] rounded-[8px] flex items-center justify-center text-[#9CA3AF] hover:text-[#6D28D9] hover:bg-[#F4EEFF] transition-all">
                      <Pencil className="w-[16px] h-[16px]" />
                    </Link>
                    <button 
                      onClick={() => abrirModalDesativar(v.id, v.nome)}
                      className="w-[32px] h-[32px] rounded-[8px] flex items-center justify-center text-[#9CA3AF] hover:text-[#EF4444] hover:bg-[#FEF2F2] transition-all"
                    >
                      <Trash2 className="w-[16px] h-[16px]" />
                    </button>
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
                total={filteredVendedores.length}
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

      <ModalDesativar 
        isOpen={modalDesativar.isOpen}
        itemName={modalDesativar.nome}
        title="Desativar Vendedor"
        description="Ao desativar este vendedor, ele perderá imediatamente o acesso ao sistema. O histórico de vendas será mantido, mas ele não poderá registrar novas movimentações."
        onClose={() => setModalDesativar({ isOpen: false, id: null, nome: "" })}
        onConfirm={handleConfirmarDesativacao}
      />
    </div>
  );
}
