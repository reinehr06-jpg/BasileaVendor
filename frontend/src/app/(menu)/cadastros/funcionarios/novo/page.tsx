"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import { 
  ChevronUp, 
  ChevronDown, 
  User,
  Briefcase,
  DollarSign,
  Users
} from "lucide-react";
import Link from "next/link";

export default function NovoFuncionarioPage() {
  const { t } = useTranslation();

  // Estados dos Accordions
  const [openAccordion, setOpenAccordion] = useState<string | null>("pessoal");
  const [tipoContrato, setTipoContrato] = useState("clt");
  
  return (
    <div className="flex min-h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col overflow-y-auto custom-scrollbar">
          
          {/* HEADER TELA DE CADASTRO */}
          <div className="flex items-center gap-[14px] py-[16px]">
            <Link href="/cadastros/funcionarios" className="w-[32px] h-[32px] rounded-[8px] bg-white border border-[#E5E7EB] flex items-center justify-center hover:bg-[#F9FAFB] transition-colors shrink-0 shadow-sm text-[#6B7280]">
              <ChevronLeftIcon className="w-[18px] h-[18px]" />
            </Link>
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#DBEAFE] flex items-center justify-center shrink-0">
              <Users className="w-[20px] h-[20px] text-[#2563EB]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Novo Funcionário</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Cadastre um colaborador para gerenciar pagamentos de folha.</p>
            </div>
          </div>

          {/* FORMULÁRIO */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col p-[20px]">
            
            <div className="flex flex-col gap-[16px] flex-1">
              
              {/* SEÇÃO 1: DADOS PESSOAIS */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "pessoal" ? null : "pessoal")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "pessoal" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <User className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      1. Dados Pessoais
                    </span>
                  </div>
                  {openAccordion === "pessoal" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "pessoal" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Nome Completo <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input type="text" placeholder="Nome do funcionário" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          CPF <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input type="text" placeholder="000.000.000-00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-3 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data de Nascimento
                        </label>
                        <input type="date" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>
                      
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Telefone / WhatsApp
                        </label>
                        <input type="text" placeholder="(00) 00000-0000" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          E-mail
                        </label>
                        <input type="email" placeholder="email@exemplo.com" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 2: VÍNCULO PROFISSIONAL */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "vinculo" ? null : "vinculo")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "vinculo" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <Briefcase className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      2. Vínculo Profissional
                    </span>
                  </div>
                  {openAccordion === "vinculo" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "vinculo" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Filial / Igreja de Lotação <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Ex: Sede Principal"
                          options={[{value: "1", label: "Sede Principal"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          {tipoContrato === "pj" ? "Serviço Prestado" : "Cargo / Função"} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Ex: Zelador, Pastor..."
                          options={[{value: "pastor", label: "Pastor"}, {value: "zelador", label: "Zelador"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Tipo de Contrato
                        </label>
                        <CustomSelect
                          value={tipoContrato}
                          onChange={setTipoContrato}
                          placeholder="CLT, Prestador..."
                          options={[{value: "clt", label: "CLT"}, {value: "pj", label: "PJ"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Data de Admissão
                        </label>
                        <input type="date" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      {tipoContrato === "pj" && (
                        <div className="col-span-12 flex flex-col gap-[8px] mt-2">
                          <label className="text-[12px] font-[600] text-[#374151]">
                            Descrição Detalhada do Serviço Prestado
                          </label>
                          <textarea 
                            placeholder="Descreva as atividades e escopo do serviço prestado pelo PJ..." 
                            className="h-[80px] w-full border border-[#E5E7EB] rounded-[8px] p-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9] resize-none"
                          ></textarea>
                        </div>
                      )}
                    </div>

                  </div>
                </div>
              </div>

              {/* SEÇÃO 3: ACORDO FINANCEIRO E PAGAMENTO */}
              <div className="bg-white border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => setOpenAccordion(openAccordion === "financeiro" ? null : "financeiro")}
                  className={`w-full flex items-center justify-between p-[20px_24px] transition-colors text-left bg-white hover:bg-[#F9FAFB] ${openAccordion === "financeiro" ? "border-b border-[#E5E7EB]" : ""}`}
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[36px] h-[36px] rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0 shadow-sm">
                      <DollarSign className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2} />
                    </div>
                    <span className="text-[14px] font-[700] text-[#111827]">
                      3. Acordo Financeiro (Salário e Conta)
                    </span>
                  </div>
                  {openAccordion === "financeiro" ? (
                    <ChevronUp className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  ) : (
                    <ChevronDown className="w-[18px] h-[18px] text-[#9CA3AF]" strokeWidth={2.5} />
                  )}
                </button>
                
                <div className={`transition-all duration-300 ease-in-out bg-white ${openAccordion === "financeiro" ? "max-h-[800px] opacity-100" : "max-h-0 opacity-0 overflow-hidden"}`}>
                  <div className="p-[24px] flex flex-col gap-[24px]">
                    
                    <div className="grid grid-cols-12 gap-[24px]">
                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Salário Base (Bruto) <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <div className="relative">
                          <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[700] text-[#9CA3AF]">R$</span>
                          <input type="text" placeholder="0,00" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[14px] font-[700] text-[#2563EB] outline-none focus:border-[#6D28D9]" />
                        </div>
                      </div>

                      <div className="col-span-6 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">
                          Dia de Pagamento
                        </label>
                        <CustomSelect
                          value=""
                          onChange={() => {}}
                          placeholder="Dia do mês..."
                          options={[{value: "5", label: "Dia 5"}, {value: "10", label: "Dia 10"}]}
                          className="h-[38px]"
                        />
                      </div>

                      <div className="col-span-12 w-full h-[1px] bg-[#E5E7EB] my-[4px]"></div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Chave PIX</label>
                        <input type="text" placeholder="Telefone, CPF ou Aleatória" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-4 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Banco</label>
                        <input type="text" placeholder="Ex: Itaú" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-2 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Agência</label>
                        <input type="text" placeholder="0000" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>

                      <div className="col-span-2 flex flex-col gap-[8px]">
                        <label className="text-[12px] font-[600] text-[#374151]">Conta</label>
                        <input type="text" placeholder="00000-0" className="h-[38px] w-full border border-[#E5E7EB] rounded-[8px] px-[12px] text-[13px] text-[#111827] outline-none focus:border-[#6D28D9]" />
                      </div>
                    </div>

                  </div>
                </div>
              </div>

            </div>

            {/* RODAPÉ DE AÇÕES DENTRO DO CARD */}
            <div className="border-t border-[#F1F1F4] mt-[32px] pt-[24px] flex items-center justify-end gap-[12px]">
              <Link href="/cadastros/funcionarios" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] transition-colors text-[#4B5563] text-[12px] font-[700] rounded-[8px]">
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
