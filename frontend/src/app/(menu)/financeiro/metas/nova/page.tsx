"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import { toast } from "sonner";
import { 
  Target, 
  User, 
  Calendar,
  DollarSign,
  AlignLeft,
  ChevronDown,
  ChevronUp,
  Save,
  X
} from "lucide-react";

type SectionType = "dados-principais" | "observacoes" | null;

export default function NovaMetaPage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<SectionType>("dados-principais");
  
  const [vendedor, setVendedor] = useState("");
  const [competencia, setCompetencia] = useState("");
  const [valorMeta, setValorMeta] = useState("");
  const [comissaoMeta, setComissaoMeta] = useState("");
  const [observacoes, setObservacoes] = useState("");

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

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!vendedor || !competencia || !valorMeta) {
      toast.error(t("Preencha os campos obrigatórios."));
      return;
    }
    toast.success(t("Meta cadastrada com sucesso!"));
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA (ESTILO PADRÃO) */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <Target className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Nova Meta Comercial")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Defina os objetivos de vendas e comissionamento para a equipe.")}</p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="flex flex-col gap-[16px]">
              
              {/* SEÇÃO 1: DADOS PRINCIPAIS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("dados-principais")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Target className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados Principais")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Informações vitais da meta comercial.")}</p>
                    </div>
                  </div>
                  {openSection === "dados-principais" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "dados-principais" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* Vendedor */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Vendedor")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <select
                          value={vendedor}
                          onChange={(e) => setVendedor(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                          required
                        >
                          <option value="" disabled>{t("Selecione um vendedor")}</option>
                          <option value="1">Anthony Cardoso</option>
                          <option value="2">Vendedor Padrão</option>
                          <option value="3">Equipe: Vendas Internas (Todos)</option>
                        </select>
                      </div>

                      {/* Competência */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Competência (Mês/Ano)")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input
                          type="month"
                          value={competencia}
                          onChange={(e) => setCompetencia(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                          required
                        />
                      </div>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* Valor Meta */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Valor da Meta de Vendas")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <div className="relative">
                          <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[600] text-[#9CA3AF]">R$</span>
                          <input 
                            type="text" 
                            placeholder="0,00"
                            value={valorMeta}
                            onChange={(e) => setValorMeta(e.target.value)}
                            className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] pl-[36px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                            required
                          />
                        </div>
                      </div>

                      {/* Expectativa */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Expectativa de Comissão")}
                        </label>
                        <div className="relative">
                          <span className="absolute left-[12px] top-1/2 -translate-y-1/2 text-[14px] font-[600] text-[#9CA3AF]">R$</span>
                          <input 
                            type="text" 
                            placeholder="0,00"
                            value={comissaoMeta}
                            onChange={(e) => setComissaoMeta(e.target.value)}
                            className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] pl-[36px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                          />
                        </div>
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 2: OBSERVAÇÕES */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("observacoes")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <AlignLeft className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Observações Internas")}
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Detalhes adicionais ou justificativas para a meta.")}</p>
                    </div>
                  </div>
                  {openSection === "observacoes" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "observacoes" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[20px]"></div>
                    <textarea 
                      placeholder={t("Ex: Meta extra focada em planos Enterprise.")}
                      value={observacoes}
                      onChange={(e) => setObservacoes(e.target.value)}
                      className="w-full bg-white border border-[#E5E7EB] rounded-[8px] min-h-[120px] p-[16px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] resize-y"
                    />
                  </div>
                )}
              </div>

              {/* FIXED BOTTOM ACTION BAR */}
              <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] flex items-center justify-between px-[32px] z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <Link 
                  href="/financeiro/metas"
                  className="flex items-center gap-[8px] px-[20px] py-[10px] text-[#4B5563] hover:text-[#111827] hover:bg-[#F3F4F6] transition-colors rounded-[8px] text-[14px] font-[600]"
                >
                  <X className="w-[18px] h-[18px]" strokeWidth={2.5} />
                  {t("Cancelar")}
                </Link>

                <button 
                  type="submit"
                  className="flex items-center gap-[8px] px-[24px] py-[12px] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors rounded-[8px] text-white text-[14px] font-[600] shadow-sm"
                >
                  <Save className="w-[18px] h-[18px]" strokeWidth={2.5} />
                  {t("Salvar Meta")}
                </button>
              </div>

            </form>
          </div>
        </main>
      </div>
    </div>
  );
}
