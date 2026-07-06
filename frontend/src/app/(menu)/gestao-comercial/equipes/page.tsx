"use client";
import { EquipesService } from "@/services/equipes.service";


import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Pagination from "@/components/Pagination";
import ModalDesativar from "@/components/ModalDesativar";
import { useTranslation } from "react-i18next";
import {
  Network,
  Plus,
  Search,
  Pencil,
  Trash2,
} from "lucide-react";



export default function EquipesPage() {
  const { t } = useTranslation();
  const [buscaNome, setBuscaNome] = useState("");
  const [buscaGestor, setBuscaGestor] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [equipes, setEquipes] = useState<any[]>([]);

  useEffect(() => {
    EquipesService.listar().then(setEquipes);
  }, []);
  const [modalDesativar, setModalDesativar] = useState<{ isOpen: boolean; id: number | null; nome: string }>({ isOpen: false, id: null, nome: "" });

  const filteredEquipes = equipes.filter(e =>
    e.nome.toLowerCase().includes(buscaNome.toLowerCase()) &&
    e.gestor.toLowerCase().includes(buscaGestor.toLowerCase())
  );
  const paginatedEquipes = filteredEquipes.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  
  const handlePageChange = (page: number) => setCurrentPage(page);
  const handlePageSizeChange = (size: number) => { setPageSize(size); setCurrentPage(1); };

  const abrirModalDesativar = (id: number, nome: string) => {
    setModalDesativar({ isOpen: true, id, nome });
  };

  const handleConfirmarDesativacao = (motivo: string) => {
    if (modalDesativar.id) {
      setEquipes(prev => prev.map(e => e.id === modalDesativar.id ? { ...e, status: "Inativa" } : e));
      alert(`Equipe desativada com sucesso!\nMotivo salvo no histórico: ${motivo}`);
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
                <Network className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Equipes")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Cadastre e gerencie as equipes da sua operação.")}</p>
              </div>
            </div>

            {/* Toolbar: Botão e Buscas */}
            <div className="p-[24px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[16px]">
              <div className="flex items-center gap-[12px]">
                <Link href="/gestao-comercial/equipes/nova" className="flex items-center gap-[6px] px-[16px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm uppercase tracking-wide shrink-0">
                  <Plus className="w-[16px] h-[16px]" strokeWidth={2.4} />
                  {t("NOVA EQUIPE")}
                </Link>
                {/* Removido o botão de importação conforme solicitado */}
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
                    value={buscaGestor}
                    onChange={(e) => setBuscaGestor(e.target.value)}
                    placeholder={t("Buscar por Gestor")}
                    className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                  />
                </div>
              </div>
            </div>

            {/* Tabela */}
            <div className="flex-1 flex flex-col overflow-x-auto">

              {/* Cabeçalho */}
              <div className="grid grid-cols-[80px_1.5fr_1.5fr_120px_120px_90px] items-center px-[24px] h-[40px] border-t border-b border-[#F1F1F4] bg-[#FCFCFD]">
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Status")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Nome da equipe")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Gestor")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Criada em")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280]">{t("Meta")}</span>
                <span className="text-[12px] font-[700] text-[#6B7280] text-center">{t("Ações")}</span>
              </div>

              {/* Linhas */}
              {paginatedEquipes.map((equipe) => (
                <div key={equipe.id} className="grid grid-cols-[80px_1.5fr_1.5fr_120px_120px_90px] items-center px-[24px] h-[42px] bg-white border-b border-[#F1F1F4] hover:bg-[#FAFAFC] transition-colors last:border-b-0">
                  <div>
                    <span className={`inline-flex items-center px-[8px] py-[2px] text-[11px] font-[700] rounded-full ${
                      equipe.status === "Ativa"
                        ? "bg-[#D1FAE5] text-[#059669]"
                        : "bg-[#FEE2E2] text-[#DC2626]"
                    }`}>
                      {equipe.status === "Ativa" ? t("Ativa") : t("Inativa")}
                    </span>
                  </div>
                  <Link href={`/gestao-comercial/equipes/${equipe.id}`} className="text-[12px] font-[600] text-[#6D28D9] truncate pr-4 cursor-pointer hover:underline">
                    {equipe.nome}
                  </Link>
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{equipe.gestor}</span>
                  <span className="text-[12px] font-[500] text-[#4B5563] truncate pr-4">{equipe.criadoEm}</span>
                  <span className="text-[12px] font-[600] text-[#111827] truncate pr-4">{equipe.meta}</span>
                  <div className="flex items-center justify-center gap-[6px]">
                    <Link href={`/gestao-comercial/equipes/${equipe.id}`} className="w-[30px] h-[30px] rounded-[8px] border border-[#E5E7EB] bg-white flex items-center justify-center hover:bg-[#F3F4F6] transition-colors">
                      <Pencil className="w-[14px] h-[14px] text-[#6B7280]" strokeWidth={2.2} />
                    </Link>
                    <button 
                      onClick={() => abrirModalDesativar(equipe.id, equipe.nome)}
                      className="w-[30px] h-[30px] rounded-[8px] bg-[#EF4444]/[0.08] flex items-center justify-center hover:bg-[#EF4444]/[0.16] transition-colors border border-transparent"
                    >
                      <Trash2 className="w-[14px] h-[14px] text-[#EF4444]" strokeWidth={2.2} />
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
                total={filteredEquipes.length}
              />
            </div>
          </div>

          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[22px] pb-[12px]">
            <p className="text-[14px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Vendor OS")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
      </div>

      <ModalDesativar 
        isOpen={modalDesativar.isOpen}
        itemName={modalDesativar.nome}
        title="Desativar Equipe"
        description="Ao desativar esta equipe, ela será paralisada no sistema. O histórico será mantido, mas ela não poderá registrar novas metas e nem receber novos membros."
        onClose={() => setModalDesativar({ isOpen: false, id: null, nome: "" })}
        onConfirm={handleConfirmarDesativacao}
      />
    </div>
  );
}
