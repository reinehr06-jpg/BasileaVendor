"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import { 
  ChevronUp, 
  ChevronDown, 
  UsersRound,
  Settings
} from "lucide-react";
import Link from "next/link";

export default function NovoDepartamentoPage() {
  const { t } = useTranslation();

  // Estados dos Accordions
  const [openAccordion, setOpenAccordion] = useState<string | null>("info");
  
  // Estados do Formulário
  const [status, setStatus] = useState("Ativo");

  return (
    <div className="flex min-h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col overflow-y-auto custom-scrollbar">
          
          {/* HEADER TELA DE CADASTRO */}
          <div className="flex items-center gap-[14px] py-[16px]">
            <Link href="/cadastros/departamentos" className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
              <ChevronLeftIcon className="w-[18px] h-[18px]" />
            </Link>
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
              <UsersRound className="w-[20px] h-[20px] text-[#9333EA]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Novo Departamento / Ministério</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Cadastre e organize os grupos que compõem sua estrutura.</p>
            </div>
          </div>

          {/* FORMULÁRIO */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col p-[20px]">
            
            <div className="flex flex-col gap-[16px] flex-1">
              
              {/* SEÇÃO 1: DADOS DO DEPARTAMENTO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "info" ? null : "info")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "info" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <UsersRound className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      1. Informações Básicas
                    </span>
                  </div>
                  {openAccordion === "info" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "info" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-12 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Nome do Departamento ou Ministério <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input type="text" placeholder="Ex: Ministério de Louvor..." className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-12 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Descrição e Propósito</label>
                        <textarea 
                          placeholder="Detalhes sobre as atividades realizadas por este grupo..." 
                          className="w-full h-[80px] border border-[#E5E7EB] rounded-[8px] p-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9] resize-none"
                        ></textarea>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 2: GESTÃO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "gestao" ? null : "gestao")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "gestao" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Settings className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      2. Líderes e Status
                    </span>
                  </div>
                  {openAccordion === "gestao" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "gestao" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Líder / Responsável <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Buscar membro cadastrado..."
                          searchable={true}
                          options={[{value: "1", label: "Ana Silva"}, {value: "2", label: "Pr. João"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Status do Departamento
                        </label>
                        <CustomSelect
                          value={status}
                          onChange={setStatus}
                          placeholder="Status..."
                          options={[{value: "Ativo", label: "Ativo"}, {value: "Inativo", label: "Inativo"}]}
                          className="h-[38px]"
                        />
                      </div>
                    </div>

                  </div>
                </div>
              </div>

            </div>

            {/* RODAPÉ DE AÇÕES DENTRO DO CARD */}
            <div className="border-t border-[#F1F1F4] mt-[32px] pt-[24px] flex items-center justify-end gap-[12px]">
              <Link href="/cadastros/departamentos" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors text-[#4B5563] text-[12px] font-[700] rounded-[8px]">
                CANCELAR
              </Link>
              <button type="button" className="px-[20px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white text-[12px] font-[700] uppercase tracking-wider rounded-[8px] shadow-sm shadow-[#6D28D9]/20">
                SALVAR E CONCLUIR
              </button>
            </div>
          </div>
          
          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[24px] pb-[24px]">
            <p className="text-[12px] font-[500] text-[#9CA3AF]">
              COPYRIGHT © 2026 <span className="font-[700] text-[#6D28D9]">Basiléia</span>, Todos os direitos reservados
            </p>
          </div>
          
        </main>
      </div>
    </div>
  );
}

// Icon helper
function ChevronLeftIcon(props: React.SVGProps<SVGSVGElement>) {
  return (
    <svg
      {...props}
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="m15 18-6-6 6-6" />
    </svg>
  );
}
