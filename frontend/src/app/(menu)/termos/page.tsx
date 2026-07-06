"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import {
  FileText,
  Plus,
  Download,
  Edit,
  X,
  Trash2,
  Search
} from "lucide-react";

export default function TermosPage() {
  const { t } = useTranslation();
  const [busca, setBusca] = useState("");

  const mockTermos = [
    {
      id: 1,
      tipo: "USO",
      titulo: "Contrato padrao",
      versao: "2.0",
      criadoEm: "05/05/2026",
      status: "Ativo"
    }
  ];

  const filteredTermos = mockTermos.filter(termo => 
    termo.titulo.toLowerCase().includes(busca.toLowerCase())
  );

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT */}
        <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

          {/* Cabeçalho da Página (Fora do Card Principal para dar destaque como no print) */}
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-[16px] mb-[24px]">
            <div className="flex items-center gap-[12px]">
              <div className="w-[36px] h-[36px] rounded-[10px] bg-white border border-[#E5E7EB] shadow-sm flex items-center justify-center shrink-0">
                <FileText className="w-[18px] h-[18px] text-[#6B7280]" strokeWidth={2.2} />
              </div>
              <h1 className="text-[22px] font-[700] text-[#1A1A2E] leading-tight">
                {t("Gerenciar Termos de Uso")}
              </h1>
            </div>
            
            <Link 
              href="/termos/novo"
              className="flex items-center gap-[6px] px-[18px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm uppercase tracking-wide shrink-0"
            >
              <Plus className="w-[16px] h-[16px]" strokeWidth={2.4} />
              {t("NOVO TERMO")}
            </Link>
          </div>

          {/* CARD PRINCIPAL */}
          <div className="bg-white rounded-[18px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col flex-1">
            
            {/* Título Interno do Card */}
            <div className="p-[24px_24px_16px_24px] border-b border-[#F1F1F4] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[16px]">
              <h2 className="text-[15px] font-[700] text-[#9CA3AF]">{t("Termos Cadastrados")}</h2>
              
              {/* Barra de Busca Extra (Melhoria de UX) */}
              <div className="relative flex items-center w-full sm:w-[260px] h-[36px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] transition-all">
                <Search className="text-[#9CA3AF] w-[16px] h-[16px] mr-[8px] shrink-0" strokeWidth={2.4} />
                <input
                  type="text"
                  value={busca}
                  onChange={(e) => setBusca(e.target.value)}
                  placeholder={t("Buscar termo...")}
                  className="bg-transparent border-none outline-none text-[12px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full"
                />
              </div>
            </div>

            {/* Lista de Termos */}
            <div className="p-[24px] flex flex-col gap-[16px] overflow-y-auto">
              
              {filteredTermos.map((termo) => (
                <div 
                  key={termo.id} 
                  className="flex flex-col md:flex-row items-start md:items-center justify-between gap-[16px] p-[20px] rounded-[12px] border border-[#F1F1F4] bg-[#FAFAFC] hover:border-[#E5E7EB] hover:bg-white hover:shadow-sm transition-all"
                >
                  
                  {/* Info do Termo (Esquerda) */}
                  <div className="flex flex-col gap-[10px]">
                    <div className="flex items-center gap-[10px]">
                      <span className="inline-flex items-center px-[8px] py-[3px] text-[10px] font-[800] rounded-full bg-[#5B21B6] text-white uppercase tracking-wider leading-none">
                        {termo.tipo}
                      </span>
                    </div>
                    <div className="flex flex-col">
                      <h3 className="text-[16px] font-[700] text-[#1A1A2E]">{termo.titulo}</h3>
                      <p className="text-[12px] font-[500] text-[#9CA3AF] mt-1">
                        {t("Versão")} {termo.versao} • {t("Criado em")} {termo.criadoEm}
                      </p>
                    </div>
                  </div>

                  {/* Ações e Status (Direita) */}
                  <div className="flex flex-col sm:flex-row items-start sm:items-center gap-[16px] md:gap-[24px] w-full md:w-auto">
                    
                    {/* Status Badge */}
                    <span className="inline-flex items-center px-[12px] py-[4px] text-[11px] font-[700] rounded-full bg-[#D1FAE5] text-[#059669] uppercase tracking-wide">
                      {termo.status}
                    </span>

                    {/* Botões de Ação */}
                    <div className="flex items-center gap-[8px]">
                      <button 
                        title={t("Baixar PDF")}
                        className="w-[34px] h-[34px] rounded-[8px] bg-[#F3F4F6] text-[#4B5563] flex items-center justify-center hover:bg-[#E5E7EB] hover:text-[#111827] transition-colors"
                      >
                        <Download className="w-[16px] h-[16px]" strokeWidth={2.2} />
                      </button>
                      
                      <button 
                        title={t("Editar")}
                        className="w-[34px] h-[34px] rounded-[8px] bg-[#F3F4F6] text-[#4B5563] flex items-center justify-center hover:bg-[#E5E7EB] hover:text-[#111827] transition-colors"
                      >
                        <Edit className="w-[16px] h-[16px]" strokeWidth={2.2} />
                      </button>

                      <button 
                        title={t("Desativar")}
                        className="w-[34px] h-[34px] rounded-[8px] bg-[#FEE2E2] text-[#DC2626] flex items-center justify-center hover:bg-[#FECACA] transition-colors"
                      >
                        <X className="w-[16px] h-[16px]" strokeWidth={2.5} />
                      </button>

                      <button 
                        title={t("Excluir")}
                        className="w-[34px] h-[34px] rounded-[8px] bg-[#FEE2E2] text-[#DC2626] flex items-center justify-center hover:bg-[#FECACA] transition-colors"
                      >
                        <Trash2 className="w-[16px] h-[16px]" strokeWidth={2.2} />
                      </button>
                    </div>

                  </div>
                </div>
              ))}

              {filteredTermos.length === 0 && (
                <div className="flex flex-col items-center justify-center p-[40px] bg-white border border-dashed border-[#E5E7EB] rounded-[12px]">
                  <FileText className="w-[32px] h-[32px] text-[#D1D5DB] mb-3" strokeWidth={1.5} />
                  <h3 className="text-[14px] font-[600] text-[#374151]">{t("Nenhum termo encontrado")}</h3>
                  <p className="text-[13px] text-[#9CA3AF] mt-1">{t("Crie um novo termo de uso para começar.")}</p>
                </div>
              )}

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
