"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import { 
  ChevronUp, 
  ChevronDown, 
  ArrowRightLeft,
  DollarSign,
  AlertCircle
} from "lucide-react";
import Link from "next/link";

export default function NovaTransferenciaPage() {
  const { t } = useTranslation();

  // Estados dos Accordions
  const [openAccordion, setOpenAccordion] = useState<string | null>("origem");
  
  // Estados do Formulário
  const [hasTaxa, setHasTaxa] = useState(false);

  return (
    <div className="flex min-h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col overflow-y-auto custom-scrollbar">
          
          {/* HEADER TELA DE CADASTRO */}
          <div className="flex items-center gap-[14px] py-[16px]">
            <Link href="/financeiro/transferencias" className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
              <ChevronLeftIcon className="w-[18px] h-[18px]" />
            </Link>
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F5F3FF] flex items-center justify-center shrink-0">
              <ArrowRightLeft className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Nova Transferência</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Movimente valores entre contas bancárias e caixas.</p>
            </div>
          </div>

          {/* FORMULÁRIO */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col p-[20px]">
            
            <div className="flex flex-col gap-[16px] flex-1">
              
              {/* SEÇÃO 1: ORIGEM E DESTINO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "origem" ? null : "origem")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "origem" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <ArrowRightLeft className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      1. Origem e Destino
                    </span>
                  </div>
                  {openAccordion === "origem" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "origem" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-5 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Conta de Origem (Saída) <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="De onde o dinheiro sai..."
                          options={[{value: "1", label: "Itaú - CC 1234"}]}
                          className="h-[38px]"
                        />
                        <span className="text-[11px] font-[600] text-[#9CA3AF]">Saldo Atual: <span className="text-[#10B981]">R$ 10.000,00</span></span>
                      </div>

                      <div className="col-span-2 flex items-center justify-center pt-5">
                        <div className="w-[40px] h-[40px] rounded-full bg-[#F3F4F6] flex items-center justify-center border border-[#E5E7EB]">
                          <ArrowRightLeft className="w-[16px] h-[16px] text-[#9CA3AF]" />
                        </div>
                      </div>

                      <div className="col-span-5 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Conta de Destino (Entrada) <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Para onde o dinheiro vai..."
                          options={[{value: "2", label: "Caixa Físico"}]}
                          className="h-[38px]"
                        />
                        <span className="text-[11px] font-[600] text-[#9CA3AF]">Saldo Atual: <span className="text-[#0284C7]">R$ 3.250,00</span></span>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 2: VALOR E TAXAS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "valor" ? null : "valor")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "valor" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <DollarSign className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      2. Detalhes do Valor
                    </span>
                  </div>
                  {openAccordion === "valor" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "valor" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Valor da Transferência <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <div className="relative">
                          <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[700] text-[#9CA3AF]">R$</span>
                          <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[14px] font-[700] text-[#6D28D9] outline-none focus:border-[#6D28D9]" />
                        </div>
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data da Movimentação <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input type="date" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-12 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Descrição / Motivo</label>
                        <input type="text" placeholder="Ex: Suprimento para o Caixa Físico" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-12 pt-[12px] border-t border-[#F1F1F4]">
                        <label className="flex items-center gap-2 cursor-pointer w-fit">
                          <div className={`w-[44px] h-[24px] rounded-full p-1 transition-colors ${hasTaxa ? 'bg-[#6D28D9]' : 'bg-[#D1D5DB]'}`} onClick={() => setHasTaxa(!hasTaxa)}>
                            <div className={`w-[16px] h-[16px] bg-white rounded-full shadow-sm transition-transform ${hasTaxa ? 'translate-x-[20px]' : 'translate-x-0'}`}></div>
                          </div>
                          <span className="text-[12px] font-[600] text-[#4B5563]">Houve cobrança de taxa bancária nesta operação?</span>
                        </label>
                      </div>

                      {hasTaxa && (
                        <div className="col-span-12 flex items-center gap-[16px] p-[16px] bg-[#F3E8FF] border border-[#C4B5FD] rounded-[8px] mt-[-8px]">
                          <div className="flex flex-col gap-[8px] w-[200px]">
                            <label className="text-[12px] font-[600] text-[#6D28D9]">Valor da Taxa</label>
                            <div className="relative">
                              <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[700] text-[#9CA3AF]">R$</span>
                              <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#C4B5FD] rounded-[8px] pl-[36px] pr-[12px] text-[14px] font-[700] text-[#6D28D9] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] bg-white" />
                            </div>
                          </div>
                          <div className="flex items-center gap-[8px] text-[#6D28D9] text-[12px] font-[600] mt-[24px]">
                            <AlertCircle className="w-[14px] h-[14px]" />
                            A taxa será lançada como uma despesa separada na conta de origem.
                          </div>
                        </div>
                      )}
                    </div>

                  </div>
                </div>
              </div>

            </div>

            {/* RODAPÉ DE AÇÕES DENTRO DO CARD */}
            <div className="border-t border-[#F1F1F4] mt-[32px] pt-[24px] flex items-center justify-end gap-[12px]">
              <Link href="/financeiro/transferencias" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors text-[#4B5563] text-[12px] font-[700] rounded-[8px]">
                CANCELAR
              </Link>
              <button type="button" className="px-[20px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white text-[12px] font-[700] uppercase tracking-wider rounded-[8px] shadow-sm shadow-[#6D28D9]/20">
                EFETIVAR TRANSFERÊNCIA
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
