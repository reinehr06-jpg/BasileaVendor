"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  Users,
  User,
  BadgeCheck,
  Coins,
  ChevronDown,
  ChevronUp,
  Save,
  X,
  Lock
} from "lucide-react";

type SectionType = "dados-pessoais" | "funcao" | "comissoes" | null;

export default function NovoVendedorPage() {
  const { t } = useTranslation();
  const [openSection, setOpenSection] = useState<SectionType>("dados-pessoais");

  const [perfil, setPerfil] = useState("Vendedor");
  const [status, setStatus] = useState("Ativo");
  const [gestor, setGestor] = useState("");

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const InputField = ({ label, type = "text", placeholder = "", required = false, value, onChange, icon }: any) => (
    <div className="flex flex-col gap-[6px]">
      <label className="text-[13px] font-[600] text-[#4B5563]">
        {label} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
      </label>
      <div className="relative">
        <input 
          type={type} 
          placeholder={placeholder}
          defaultValue={value}
          onChange={onChange ? (e) => onChange(e.target.value) : undefined}
          className={`w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] ${icon ? 'pr-[36px]' : ''}`}
        />
        {icon && (
          <div className="absolute inset-y-0 right-[12px] flex items-center pointer-events-none text-[#6B7280] font-[600]">
            {icon}
          </div>
        )}
      </div>
    </div>
  );

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA (ESTILO PRINT 1) */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <Users className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Novo Vendedor")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Cadastre um novo vendedor e configure suas comissões.")}</p>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS (DROPDOWNS) */}
            <div className="flex flex-col gap-[16px]">

              {/* SEÇÃO 1: DADOS PESSOAIS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("dados-pessoais")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <User className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados Pessoais")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Informações de acesso e contato.")}</p>
                    </div>
                  </div>
                  {openSection === "dados-pessoais" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "dados-pessoais" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField 
                        label={t("Nome completo")} 
                        placeholder="Ex: João da Silva" 
                        required 
                      />
                      <InputField 
                        type="email"
                        label={t("E-mail (Acesso)")} 
                        placeholder="vendedor@email.com" 
                        required 
                      />
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Telefone")}
                        </label>
                        <div className="flex h-[40px]">
                          <div className="flex items-center justify-center bg-white border border-[#E5E7EB] border-r-0 rounded-l-[8px] px-[12px] shrink-0 text-[14px] text-[#6B7280] gap-[8px]">
                            <span role="img" aria-label="BR">🇧🇷</span>
                            <span>+55</span>
                          </div>
                          <input 
                            type="text" 
                            placeholder="(00) 00000-0000"
                            className="w-full h-full bg-white border border-[#E5E7EB] rounded-r-[8px] border-l-0 px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                          />
                        </div>
                      </div>
                      
                      {/* Senha Provisória Simples */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Senha Provisória")}
                        </label>
                        <div className="h-[40px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] px-[12px] flex items-center gap-[8px] text-[14px] text-[#4B5563] cursor-not-allowed">
                          <Lock className="w-[14px] h-[14px] text-[#9CA3AF]" />
                          <span className="font-[600]">Basileia123</span>
                          <span className="text-[12px] text-[#9CA3AF] ml-auto">{t("(Troca obrigatória)")}</span>
                        </div>
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 2: FUNÇÃO E EQUIPE */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("funcao")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <BadgeCheck className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Função e Equipe")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Defina o nível de acesso e o líder direto.")}</p>
                    </div>
                  </div>
                  {openSection === "funcao" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "funcao" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Perfil")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          options={[
                            { label: "Vendedor", value: "Vendedor" },
                            { label: "Gerente", value: "Gerente" }
                          ]}
                          value={perfil}
                          onChange={setPerfil}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                      
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Status")}
                        </label>
                        <CustomSelect
                          options={[
                            { label: "Ativo", value: "Ativo" },
                            { label: "Inativo", value: "Inativo" }
                          ]}
                          value={status}
                          onChange={setStatus}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                    </div>

                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        {t("Gestor Responsável")}
                      </label>
                      <CustomSelect
                        options={[
                          { label: "Nenhum (equipe do Admin)", value: "" }
                        ]}
                        value={gestor}
                        onChange={setGestor}
                        placeholder="Nenhum (equipe do Admin)"
                        triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                      />
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 3: COMISSÕES DO VENDEDOR */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden mb-[30px]">
                <button 
                  onClick={() => toggleSection("comissoes")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Coins className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Comissões")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Percentuais aplicados às vendas deste membro.")}</p>
                    </div>
                  </div>
                  {openSection === "comissoes" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "comissoes" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                      <div className="flex flex-col gap-[6px]">
                        <InputField 
                          type="number"
                          label={t("Comissão Inicial (%)")} 
                          placeholder="10" 
                          value="10"
                          required 
                          icon="%"
                        />
                      </div>

                      <div className="flex flex-col gap-[6px]">
                        <InputField 
                          type="number"
                          label={t("Comissão Recorrência (%)")} 
                          placeholder="5" 
                          value="5"
                          required 
                          icon="%"
                        />
                      </div>
                    </div>

                  </div>
                )}
              </div>

            </div>
          </div>
        </main>

        {/* BARRA INFERIOR FLUTUANTE (FIXA) PARA SALVAR */}
        <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] px-[32px] flex items-center justify-between z-40">
          <p className="text-[13px] text-[#6B7280] hidden md:block">
            {t("Preencha as informações obrigatórias (")} <span className="text-[#EF4444] font-[700]">*</span> {t(") antes de salvar.")}
          </p>
          <div className="flex items-center gap-[12px] ml-auto">
            <Link 
              href="/gestao-comercial/vendedores"
              className="h-[44px] px-[20px] bg-white border border-[#E5E7EB] text-[#374151] font-[600] text-[14px] rounded-[8px] hover:bg-[#F9FAFB] hover:text-[#111827] transition-colors flex items-center justify-center"
            >
              {t("Cancelar")}
            </Link>
            <button 
              className="h-[44px] px-[24px] bg-[#6D28D9] text-white font-[600] text-[14px] rounded-[8px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center gap-[8px]"
            >
              <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
              {t("Salvar Vendedor")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
