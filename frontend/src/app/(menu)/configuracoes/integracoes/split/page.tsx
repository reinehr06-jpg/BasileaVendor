"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { 
  ArrowLeft,
  DollarSign,
  ChevronDown,
  ChevronUp,
  X,
  Save,
  Settings2
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";

export default function SplitRepassePage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<boolean>(true);
  const [splitGlobal, setSplitGlobal] = useState<boolean>(true);
  const [juros, setJuros] = useState<string>("1.99");
  const [multa, setMulta] = useState<string>("2.00");

  const toggleSection = () => {
    setOpenSection(!openSection);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Configurações financeiras salvas com sucesso!");
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA (ESTILO PADRÃO - ÍCONE SEM CAIXA) */}
            <div className="flex items-start justify-between mb-[24px]">
              <div className="flex items-start gap-[12px]">
                <DollarSign className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Split & Repasse Automático</h1>
                  <p className="text-[14px] text-[#6B7280]">Configure as regras de comissão e taxas padrões.</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* SEÇÃO ÚNICA: REGRAS GERAIS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={toggleSection}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Settings2 className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Regras de Comissionamento e Taxas <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Definições globais de split para a plataforma.</p>
                    </div>
                  </div>
                  {openSection ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[28px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* TOGGLE SPLIT GLOBAL */}
                    <div className="flex items-center gap-[16px]">
                      <button
                        type="button"
                        onClick={() => setSplitGlobal(!splitGlobal)}
                        className={`w-[44px] h-[24px] rounded-full transition-colors flex items-center shrink-0 ${splitGlobal ? 'bg-[#8B5CF6]' : 'bg-[#E5E7EB]'}`}
                      >
                        <div className={`w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform transform ${splitGlobal ? 'translate-x-[22px]' : 'translate-x-[2px]'}`}></div>
                      </button>
                      <div className="flex flex-col">
                        <span className="text-[14px] font-[700] text-[#111827] leading-tight mb-[4px]">Ativar Split Global</span>
                        <span className="text-[13px] text-[#6B7280] leading-tight">Vendedores com Wallet ID configurado receberão suas comissões automaticamente via Asaas.</span>
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                      {/* Juros Padrão */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Juros Padrão (% ao mês) <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input
                          type="number"
                          step="0.01"
                          value={juros}
                          onChange={(e) => setJuros(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>

                      {/* Multa Padrão */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Multa Padrão (%) <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input
                          type="number"
                          step="0.01"
                          value={multa}
                          onChange={(e) => setMulta(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* FIXED BOTTOM ACTION BAR */}
              <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] flex items-center justify-between px-[32px] z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <Link 
                  href="/configuracoes/integracoes"
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
                  {t("Salvar Integração")}
                </button>
              </div>

            </form>
          </div>
        </main>
      </div>
    </div>
  );
}
