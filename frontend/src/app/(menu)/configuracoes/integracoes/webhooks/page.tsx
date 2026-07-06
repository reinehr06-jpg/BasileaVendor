"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Code,
  Link as LinkIcon,
  Globe,
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

type SectionType = "configuracao" | "endpoints" | null;

export default function WebhooksIntegracaoPage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<SectionType>("configuracao");
  
  const [webhookUrl, setWebhookUrl] = useState("");
  const [securityToken, setSecurityToken] = useState("");

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const handleCopy = (text: string, message: string) => {
    navigator.clipboard.writeText(text);
    toast.success(message);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Integração de Webhooks salva com sucesso!");
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
                <Globe className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Basileia Church Sync</h1>
                  <p className="text-[14px] text-[#6B7280]">Sincronize membros e status com o sistema da Igreja.</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* ACORDEÃO 1: CONFIGURAÇÃO DE WEBHOOK */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("configuracao")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Code className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Configuração de Conexão <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Defina a URL de disparo e o token de segurança.</p>
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
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                      
                      {/* Webhook URL */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Church Webhook URL
                        </label>
                        <input
                          type="url"
                          value={webhookUrl}
                          onChange={(e) => setWebhookUrl(e.target.value)}
                          placeholder="https://"
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>

                      {/* Security Token */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Security Token
                        </label>
                        <input
                          type="password"
                          value={securityToken}
                          onChange={(e) => setSecurityToken(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] font-mono"
                        />
                      </div>

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
                        Endpoints Disponíveis
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Rotas prontas para receber sincronizações.</p>
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
                        <h3 className="text-[15px] font-[700] text-[#0369A1]">Rotas do Sistema</h3>
                      </div>

                      <div className="flex flex-col gap-[16px]">
                        
                        {/* Church Sync Endpoint */}
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-[12px] p-[12px] bg-white rounded-[8px] border border-[#E0F2FE]">
                          <div className="flex flex-col">
                            <div className="flex items-center gap-[8px]">
                              <span className="text-[12px] font-[700] text-[#0284C7] bg-[#E0F2FE] px-[8px] py-[2px] rounded-[4px]">GET/POST</span>
                              <span className="text-[14px] font-[600] text-[#0369A1]">/webhook/basileia-church/sync</span>
                            </div>
                            <span className="text-[12px] text-[#64748B] mt-[4px]">Sincronização de status</span>
                          </div>
                          
                          <div className="flex items-center gap-[8px]">
                            <button 
                              type="button" 
                              onClick={() => handleCopy("/webhook/basileia-church/sync", "Rota de Sync copiada!")}
                              className="p-[6px] bg-[#0284C7] hover:bg-[#0369A1] text-white rounded-[6px] transition-colors flex items-center gap-[6px] px-[12px]"
                            >
                              <Copy className="w-[14px] h-[14px]" />
                              <span className="text-[12px] font-[600]">Copiar</span>
                            </button>
                          </div>
                        </div>

                        {/* Asaas Escuta Principal */}
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-[12px] p-[12px] bg-white rounded-[8px] border border-[#E0F2FE]">
                          <div className="flex flex-col">
                            <div className="flex items-center gap-[8px]">
                              <span className="text-[12px] font-[700] text-[#0284C7] bg-[#E0F2FE] px-[8px] py-[2px] rounded-[4px]">POST</span>
                              <span className="text-[14px] font-[600] text-[#0369A1]">/webhook/asaas</span>
                            </div>
                            <span className="text-[12px] text-[#64748B] mt-[4px]">Escuta principal do Gateway</span>
                          </div>
                          
                          <div className="flex items-center gap-[8px]">
                            <button 
                              type="button" 
                              onClick={() => handleCopy("/webhook/asaas", "Rota do Asaas copiada!")}
                              className="p-[6px] bg-[#0284C7] hover:bg-[#0369A1] text-white rounded-[6px] transition-colors flex items-center gap-[6px] px-[12px]"
                            >
                              <Copy className="w-[14px] h-[14px]" />
                              <span className="text-[12px] font-[600]">Copiar</span>
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
                  {t("Atualizar Church Sync")}
                </button>
              </div>

            </form>
          </div>
        </main>
      </div>
    </div>
  );
}
