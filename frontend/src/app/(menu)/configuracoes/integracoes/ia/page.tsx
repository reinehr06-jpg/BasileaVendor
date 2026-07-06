"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Bot,
  Brain,
  Settings2,
  ChevronDown,
  ChevronUp,
  X,
  Save,
  Activity
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";
import Link from "next/link";

type SectionType = "configuracao" | "status" | null;

export default function IAIntegracaoPage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<SectionType>("configuracao");
  
  const [provider, setProvider] = useState("ollama");
  const [ativarIA, setAtivarIA] = useState(true);
  const [endpoint, setEndpoint] = useState("https://longish-quaggy-carmen.ngrok-free.dev/v1");
  const [modelo, setModelo] = useState("gemma4:e4b");
  const [rateLimit, setRateLimit] = useState("100");

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Configurações da IA salvas com sucesso!");
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA */}
            <div className="flex items-start justify-between mb-[24px]">
              <div className="flex items-start gap-[12px]">
                <Bot className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Inteligência Artificial</h1>
                  <p className="text-[14px] text-[#6B7280]">Configure a IA local (Ollama) ou OpenAI para automação.</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* ACORDEÃO 1: CONFIGURAÇÃO DO MODELO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("configuracao")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Settings2 className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Parâmetros da IA <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Defina o modelo, limites e endpoint do LLM.</p>
                    </div>
                  </div>
                  {openSection === "configuracao" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "configuracao" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-[1fr_200px] gap-[24px]">
                      
                      {/* Provider de IA */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Provider de IA
                        </label>
                        <select
                          value={provider}
                          onChange={(e) => setProvider(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        >
                          <option value="ollama">Ollama (Local)</option>
                          <option value="openai">OpenAI (Cloud)</option>
                        </select>
                        <p className="text-[12px] text-[#9CA3AF]">Escolha entre Ollama (local) ou OpenAI (cloud).</p>
                      </div>

                      {/* Ativar IA */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Ativar IA
                        </label>
                        <div className="flex items-center gap-[12px] h-[40px]">
                          <button
                            type="button"
                            onClick={() => setAtivarIA(!ativarIA)}
                            className={`w-[44px] h-[24px] rounded-full transition-colors flex items-center shrink-0 ${ativarIA ? 'bg-[#8B5CF6]' : 'bg-[#E5E7EB]'}`}
                          >
                            <div className={`w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform transform ${ativarIA ? 'translate-x-[22px]' : 'translate-x-[2px]'}`}></div>
                          </button>
                        </div>
                        <p className="text-[12px] text-[#9CA3AF]">Ative ou desative a IA no sistema.</p>
                      </div>

                    </div>

                    {/* Endpoint */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Endpoint (Ollama via ngrok)
                      </label>
                      <input
                        type="text"
                        value={endpoint}
                        onChange={(e) => setEndpoint(e.target.value)}
                        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] font-mono"
                      />
                      <p className="text-[12px] text-[#9CA3AF]">URL do Ollama com /v1/chat/completions</p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                      
                      {/* Modelo */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Modelo
                        </label>
                        <input
                          type="text"
                          value={modelo}
                          onChange={(e) => setModelo(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                        <p className="text-[12px] text-[#9CA3AF]">Modelo a ser usado (ex: gemma4:e4b, llama3.2)</p>
                      </div>

                      {/* Rate Limit */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Rate Limit (chamadas/hora)
                        </label>
                        <input
                          type="number"
                          value={rateLimit}
                          onChange={(e) => setRateLimit(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                        <p className="text-[12px] text-[#9CA3AF]">Limite de chamadas por hora.</p>
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* ACORDEÃO 2: STATUS DA IA */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("status")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Brain className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Status da IA
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Monitoramento em tempo real do estado da inteligência artificial.</p>
                    </div>
                  </div>
                  {openSection === "status" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "status" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                      
                      {/* Box: Provider */}
                      <div className="bg-[#F9FAFB] border border-[#F3F4F6] rounded-[12px] p-[24px] flex flex-col items-center justify-center gap-[8px]">
                        <span className="text-[11px] font-[700] text-[#9CA3AF] tracking-wider uppercase">PROVIDER</span>
                        <span className="text-[20px] font-[700] text-[#374151] capitalize">{provider}</span>
                      </div>

                      {/* Box: Modelo */}
                      <div className="bg-[#F9FAFB] border border-[#F3F4F6] rounded-[12px] p-[24px] flex flex-col items-center justify-center gap-[8px]">
                        <span className="text-[11px] font-[700] text-[#9CA3AF] tracking-wider uppercase">MODELO</span>
                        <span className="text-[20px] font-[700] text-[#374151]">{modelo}</span>
                      </div>

                      {/* Box: Status */}
                      <div className="bg-[#F9FAFB] border border-[#F3F4F6] rounded-[12px] p-[24px] flex flex-col items-center justify-center gap-[8px]">
                        <span className="text-[11px] font-[700] text-[#9CA3AF] tracking-wider uppercase">STATUS</span>
                        {ativarIA ? (
                          <span className="text-[20px] font-[800] text-[#22C55E]">ATIVO</span>
                        ) : (
                          <span className="text-[20px] font-[800] text-[#EF4444]">INATIVO</span>
                        )}
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
                  {t("Salvar Configurações da IA")}
                </button>
              </div>

            </form>
          </div>
        </main>
      </div>
    </div>
  );
}
