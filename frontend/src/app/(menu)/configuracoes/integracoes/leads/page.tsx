"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  MessageCircle,
  Key,
  Link as LinkIcon,
  ChevronDown,
  ChevronUp,
  X,
  Save,
  Copy,
  AlertCircle
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";
import Link from "next/link";

type SectionType = "chaves" | "endpoints" | null;

export default function LeadsIntegracaoPage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<SectionType>("chaves");
  
  const [googleKey, setGoogleKey] = useState("gads_k9x2mPqR7vLnT4wZ");
  const [metaToken, setMetaToken] = useState("meta_vt_x9kP2mQrLnW5");
  const [metaSecret, setMetaSecret] = useState("");

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const handleCopy = (text: string, message: string) => {
    navigator.clipboard.writeText(text);
    toast.success(message);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Integração de Leads salva com sucesso!");
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
                <MessageCircle className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Google & Meta Leads</h1>
                  <p className="text-[14px] text-[#6B7280]">Configure Google Ads e Meta Leads no mesmo painel.</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* ACORDEÃO 1: CHAVES DE AUTENTICAÇÃO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("chaves")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Key className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Chaves de Autenticação <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Tokens e segredos para validar os webhooks externos.</p>
                    </div>
                  </div>
                  {openSection === "chaves" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "chaves" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* Google Ads Webhook Key */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Google Ads Webhook Key
                      </label>
                      <input
                        type="text"
                        value={googleKey}
                        onChange={(e) => setGoogleKey(e.target.value)}
                        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] font-mono"
                      />
                      <p className="text-[12px] text-[#9CA3AF]">Usada para validar POST do Google Ads Lead Form.</p>
                    </div>

                    {/* Meta Verify Token */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Meta Verify Token
                      </label>
                      <input
                        type="text"
                        value={metaToken}
                        onChange={(e) => setMetaToken(e.target.value)}
                        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] font-mono"
                      />
                      <p className="text-[12px] text-[#9CA3AF]">Token para validação do webhook de Leads da Meta.</p>
                    </div>

                    {/* Meta App Secret */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Meta App Secret
                      </label>
                      <input
                        type="password"
                        value={metaSecret}
                        onChange={(e) => setMetaSecret(e.target.value)}
                        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] font-mono"
                      />
                      <p className="text-[12px] text-[#9CA3AF]">Assinatura HMAC do payload da Meta.</p>
                    </div>

                  </div>
                )}
              </div>

              {/* ACORDEÃO 2: ENDPOINTS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("endpoints")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <LinkIcon className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Endpoints para Configurar
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">URLs que devem ser cadastradas nos painéis do Google e Meta.</p>
                    </div>
                  </div>
                  {openSection === "endpoints" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "endpoints" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="bg-[#F0F9FF] border border-[#BAE6FD] rounded-[12px] p-[24px] flex flex-col gap-[20px]">
                      
                      <div className="flex items-center gap-[8px]">
                        <AlertCircle className="w-[18px] h-[18px] text-[#0369A1]" />
                        <h3 className="text-[15px] font-[700] text-[#0369A1]">Endpoints disponíveis</h3>
                      </div>

                      <div className="flex flex-col gap-[16px]">
                        
                        {/* Google Verify */}
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-[12px] p-[12px] bg-white rounded-[8px] border border-[#E0F2FE]">
                          <div className="flex items-center gap-[8px]">
                            <LinkIcon className="w-[16px] h-[16px] text-[#0284C7]" />
                            <span className="text-[14px] font-[600] text-[#0369A1]">Google Ads Verify (GET):</span>
                          </div>
                          <div className="flex items-center gap-[8px]">
                            <span className="text-[13px] font-mono text-[#4B5563] bg-[#F3F4F6] px-[12px] py-[4px] rounded-[6px] truncate max-w-[300px] xl:max-w-full">
                              https://vendor.basileia.global/api/leads/google-ads
                            </span>
                            <button 
                              type="button" 
                              onClick={() => handleCopy("https://vendor.basileia.global/api/leads/google-ads", "Endpoint Google Verify copiado!")}
                              className="p-[6px] bg-[#0284C7] hover:bg-[#0369A1] text-white rounded-[6px] transition-colors"
                            >
                              <Copy className="w-[14px] h-[14px]" />
                            </button>
                          </div>
                        </div>

                        {/* Google Lead POST */}
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-[12px] p-[12px] bg-white rounded-[8px] border border-[#E0F2FE]">
                          <div className="flex items-center gap-[8px]">
                            <LinkIcon className="w-[16px] h-[16px] text-[#0284C7]" />
                            <span className="text-[14px] font-[600] text-[#0369A1]">Google Ads Lead (POST):</span>
                          </div>
                          <div className="flex items-center gap-[8px]">
                            <span className="text-[13px] font-mono text-[#4B5563] bg-[#F3F4F6] px-[12px] py-[4px] rounded-[6px] truncate max-w-[300px] xl:max-w-full">
                              https://vendor.basileia.global/api/leads/google-ads
                            </span>
                            <button 
                              type="button" 
                              onClick={() => handleCopy("https://vendor.basileia.global/api/leads/google-ads", "Endpoint Google Lead copiado!")}
                              className="p-[6px] bg-[#0284C7] hover:bg-[#0369A1] text-white rounded-[6px] transition-colors"
                            >
                              <Copy className="w-[14px] h-[14px]" />
                            </button>
                          </div>
                        </div>

                        {/* Meta Leads POST */}
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-[12px] p-[12px] bg-white rounded-[8px] border border-[#E0F2FE]">
                          <div className="flex items-center gap-[8px]">
                            <LinkIcon className="w-[16px] h-[16px] text-[#0284C7]" />
                            <span className="text-[14px] font-[600] text-[#0369A1]">Meta Leads (POST):</span>
                          </div>
                          <div className="flex items-center gap-[8px]">
                            <span className="text-[13px] font-mono text-[#4B5563] bg-[#F3F4F6] px-[12px] py-[4px] rounded-[6px] truncate max-w-[300px] xl:max-w-full">
                              https://vendor.basileia.global/webhooks/chat/meta-leads
                            </span>
                            <button 
                              type="button" 
                              onClick={() => handleCopy("https://vendor.basileia.global/webhooks/chat/meta-leads", "Endpoint Meta Leads copiado!")}
                              className="p-[6px] bg-[#0284C7] hover:bg-[#0369A1] text-white rounded-[6px] transition-colors"
                            >
                              <Copy className="w-[14px] h-[14px]" />
                            </button>
                          </div>
                        </div>

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
