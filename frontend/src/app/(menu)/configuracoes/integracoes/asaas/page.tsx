"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { 
  CreditCard,
  Copy,
  Info,
  Link as LinkIcon,
  ChevronDown,
  ChevronUp,
  X,
  Save,
  Rocket
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";

type SectionType = "configuracoes-api" | "webhook" | null;

export default function AsaasGatewayPage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<SectionType>("configuracoes-api");
  const [apiKey, setApiKey] = useState("••••••••••••••••••••••••••••••••••••••••");
  const [webhookToken, setWebhookToken] = useState("••••••••••••••••••••••••••••••••");
  const webhookUrl = "https://vendor.basileia.global/api/webhook/asaas";

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const handleCopy = () => {
    navigator.clipboard.writeText(webhookUrl);
    toast.success("URL copiada para a área de transferência!");
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Configurações do Asaas salvas com sucesso!");
  };

  const handleTestConnection = () => {
    toast.info("Testando conexão com a API do Asaas...");
    setTimeout(() => {
      toast.success("Conexão bem-sucedida!");
    }, 1500);
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA (ESTILO PADRÃO) */}
            <div className="flex items-start justify-between mb-[24px]">
              <div className="flex items-start gap-[12px]">
                <CreditCard className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Asaas Gateway</h1>
                  <p className="text-[14px] text-[#6B7280]">Configure a API Key, Webhook Token e o ambiente de cobrança.</p>
                </div>
              </div>
              <button 
                type="button"
                onClick={handleTestConnection}
                className="px-[16px] py-[8px] bg-white border border-[#8B5CF6] text-[#8B5CF6] hover:bg-[#F5F3FF] transition-colors rounded-[8px] text-[13px] font-[600] shadow-sm flex items-center gap-[6px]"
              >
                <Rocket className="w-[14px] h-[14px]" />
                Testar Conexão
              </button>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* SEÇÃO 1: CONFIGURAÇÕES DA API */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("configuracoes-api")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <CreditCard className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Ambiente e Chaves de API <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Credenciais de acesso para a integração financeira.</p>
                    </div>
                  </div>
                  {openSection === "configuracoes-api" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "configuracoes-api" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* Ambiente Asaas */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Ambiente Asaas <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <select
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                          required
                        >
                          <option value="production">🚀 Produção (Real)</option>
                          <option value="sandbox">🛠️ Sandbox (Testes)</option>
                        </select>
                      </div>

                      {/* URL de Callback */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          URL de Callback
                        </label>
                        <input
                          type="text"
                          placeholder="https://..."
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* API Key */}
                      <div className="flex flex-col gap-[6px] md:col-span-2">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          API Key <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input 
                          type="password" 
                          value={apiKey}
                          onChange={(e) => setApiKey(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] tracking-wider font-mono"
                          required
                        />
                        <p className="text-[11px] text-[#9CA3AF]">Chave de acesso total à API do Asaas.</p>
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 2: DADOS DO WEBHOOK */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("webhook")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <LinkIcon className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Configuração de Webhook
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">URL de retorno para recebimento de eventos.</p>
                    </div>
                  </div>
                  {openSection === "webhook" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "webhook" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[24px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* Webhook Token */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Webhook Token
                      </label>
                      <input 
                        type="password" 
                        value={webhookToken}
                        onChange={(e) => setWebhookToken(e.target.value)}
                        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] tracking-wider font-mono"
                      />
                      <p className="text-[11px] text-[#9CA3AF]">Token para validação de segurança no webhook.</p>
                    </div>

                    <div className="bg-[#F0F9FF] border border-[#BAE6FD] rounded-[12px] p-[24px] flex flex-col gap-[16px]">
                      <div className="flex items-center gap-[8px]">
                        <LinkIcon className="w-[18px] h-[18px] text-[#0369A1]" strokeWidth={2.5} />
                        <h3 className="text-[15px] font-[700] text-[#0369A1]">URL do Webhook Asaas</h3>
                      </div>
                      
                      <p className="text-[13px] text-[#0369A1]">
                        Copie esta URL e cole no campo <strong>Webhook</strong> no painel do Asaas (Configurações {'>'} Integrações {'>'} Webhooks).
                      </p>

                      <div className="flex items-center gap-[8px] w-full">
                        <div className="flex-1 h-[44px] bg-[#E0F2FE] border border-[#BAE6FD] rounded-[8px] px-[16px] flex items-center text-[#0369A1] font-[600] font-mono text-[14px] overflow-hidden truncate">
                          {webhookUrl}
                        </div>
                        <button 
                          type="button"
                          onClick={handleCopy}
                          className="w-[44px] h-[44px] bg-[#0284C7] hover:bg-[#0369A1] transition-colors rounded-[8px] flex items-center justify-center shrink-0 shadow-sm text-white"
                          title="Copiar URL"
                        >
                          <Copy className="w-[18px] h-[18px]" strokeWidth={2.5} />
                        </button>
                      </div>

                      <div className="flex items-center gap-[6px]">
                        <Info className="w-[14px] h-[14px] text-[#0369A1]" />
                        <p className="text-[12px] text-[#0369A1]">
                          O sistema também aceita <strong>/api/webhook/assas</strong> caso haja erro de digitação.
                        </p>
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
