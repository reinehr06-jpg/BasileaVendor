"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  FileText,
  Type,
  FileCode,
  Upload,
  PenLine,
  ChevronDown,
  ChevronUp,
  Save
} from "lucide-react";

type SectionType = "info" | "conteudo" | null;

export default function NovoTermoPage() {
  const { t } = useTranslation();
  const [openSection, setOpenSection] = useState<SectionType>("info");

  const [tipo, setTipo] = useState("termos_uso");
  const [titulo, setTitulo] = useState("");
  const [versao, setVersao] = useState("");
  const [conteudo, setConteudo] = useState("");

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const InputField = ({ label, type = "text", placeholder = "", required = false, value, onChange }: any) => (
    <div className="flex flex-col gap-[6px]">
      <label className="text-[13px] font-[600] text-[#4B5563] uppercase tracking-wide">
        {label} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
      </label>
      <input 
        type={type} 
        placeholder={placeholder}
        value={value}
        onChange={onChange ? (e) => onChange(e.target.value) : undefined}
        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
      />
    </div>
  );

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA (ESTILO PRINT 3) */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <FileText className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Novo Termo")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Crie e configure um novo documento de termos de uso ou política de privacidade.")}</p>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS */}
            <div className="flex flex-col gap-[16px]">

              {/* SEÇÃO 1: INFORMAÇÕES BÁSICAS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("info")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Type className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Informações Básicas")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Identificação e versionamento do documento.")}</p>
                    </div>
                  </div>
                  {openSection === "info" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "info" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563] uppercase tracking-wide">
                        {t("Tipo")} <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      <CustomSelect
                        options={[
                          { label: "Termos de Uso", value: "termos_uso" },
                          { label: "Política de Privacidade", value: "privacidade" },
                          { label: "Contrato de Adesão", value: "contrato" }
                        ]}
                        value={tipo}
                        onChange={setTipo}
                        placeholder="Selecione o tipo"
                        triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px] hover:border-[#D1D5DB]"
                      />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField 
                        label={t("Título")} 
                        placeholder="Ex: Termos de Uso Basileia Vendas" 
                        required 
                        value={titulo}
                        onChange={setTitulo}
                      />
                      <InputField 
                        label={t("Versão")} 
                        placeholder="Ex: 1.0.0" 
                        required 
                        value={versao}
                        onChange={setVersao}
                      />
                    </div>
                  </div>
                )}
              </div>

              {/* SEÇÃO 2: IMPORTAÇÃO E CONTEÚDO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden mb-[30px]">
                <button 
                  onClick={() => toggleSection("conteudo")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <FileCode className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Conteúdo do Termo")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Importe um arquivo ou digite o HTML diretamente.")}</p>
                    </div>
                  </div>
                  {openSection === "conteudo" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "conteudo" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* ZONA DE DROP (IMPORTAR ARQUIVO) */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563] uppercase tracking-wide">
                        {t("Importar de Arquivo (Opcional)")}
                      </label>
                      <div className="w-full border-2 border-dashed border-[#E5E7EB] bg-[#F9FAFB] rounded-[12px] flex flex-col items-center justify-center py-[32px] px-[24px] cursor-pointer hover:border-[#6D28D9] hover:bg-[#F5F3FF] transition-all group">
                        <div className="w-[40px] h-[40px] rounded-[8px] bg-[#EDE9FE] flex items-center justify-center mb-[12px] group-hover:bg-[#DDD6FE] transition-colors">
                          <Upload className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.5} />
                        </div>
                        <span className="text-[14px] font-[700] text-[#6D28D9] uppercase tracking-wider mb-[4px]">
                          Clique para selecionar PDF ou DOCX
                        </span>
                        <span className="text-[13px] text-[#9CA3AF]">
                          O sistema tentará formatar o texto automaticamente
                        </span>
                      </div>
                    </div>

                    {/* TEXTAREA HTML */}
                    <div className="flex flex-col gap-[6px] mt-[8px]">
                      <label className="text-[13px] font-[600] text-[#4B5563] uppercase tracking-wide">
                        {t("Conteúdo (HTML)")}
                      </label>
                      <textarea 
                        className="w-full min-h-[240px] bg-white border border-[#E5E7EB] rounded-[8px] p-[16px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] resize-y font-mono"
                        placeholder={`<h1>Termos de Uso</h1><p>Seu conteúdo aqui...</p>`}
                        value={conteudo}
                        onChange={(e) => setConteudo(e.target.value)}
                      ></textarea>
                      <div className="flex items-center gap-[6px] text-[#6D28D9] mt-[4px]">
                        <PenLine className="w-[14px] h-[14px]" />
                        <span className="text-[13px] font-[500]">
                          Se você subir um arquivo, o conteúdo acima será preenchido após o processamento.
                        </span>
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
              href="/termos"
              className="h-[44px] px-[20px] bg-white border border-[#E5E7EB] text-[#374151] font-[600] text-[14px] rounded-[8px] hover:bg-[#F9FAFB] hover:text-[#111827] transition-colors flex items-center justify-center"
            >
              {t("Cancelar")}
            </Link>
            <button 
              className="h-[44px] px-[24px] bg-[#6D28D9] text-white font-[600] text-[14px] rounded-[8px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center gap-[8px]"
            >
              <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
              {t("Salvar Termo")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
