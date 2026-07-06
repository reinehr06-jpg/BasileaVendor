"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Mail,
  Contact,
  Lock,
  Send,
  ChevronDown,
  ChevronUp,
  X,
  Save,
  Key
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";
import Link from "next/link";

type SectionType = "contato" | "api" | "teste" | null;

export default function EmailIntegracaoPage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<SectionType>("contato");
  
  // Contato State
  const [emailSistema, setEmailSistema] = useState("");
  const [emailClientes, setEmailClientes] = useState("");
  const [emailSuporte, setEmailSuporte] = useState("");
  const [whatsappSuporte, setWhatsappSuporte] = useState("");

  // API State
  const [clientId, setClientId] = useState("");
  const [clientSecret, setClientSecret] = useState("");
  const [emailEnvios, setEmailEnvios] = useState("");
  const [ativarGmail, setAtivarGmail] = useState(false);

  // Teste State
  const [emailTeste, setEmailTeste] = useState("");

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Integração de Email salva com sucesso!");
  };

  const handleSendTest = () => {
    if (!emailTeste) {
      toast.error("Preencha o email para teste.");
      return;
    }
    toast.info("Enviando e-mail de teste...");
    setTimeout(() => {
      toast.success("E-mail de teste enviado com sucesso!");
    }, 1500);
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
                <Mail className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Integrações de Email</h1>
                  <p className="text-[14px] text-[#6B7280]">Configure os canais de comunicação e API de envio.</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* ACORDEÃO 1: CONTATO E SUPORTE */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("contato")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Contact className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Contato e Suporte <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Emails e números visíveis para clientes e sistema.</p>
                    </div>
                  </div>
                  {openSection === "contato" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "contato" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* Email Sistema */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Email Remetente (Sistema)
                        </label>
                        <input
                          type="email"
                          value={emailSistema}
                          onChange={(e) => setEmailSistema(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>

                      {/* Email Clientes */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Email Remetente (Clientes)
                        </label>
                        <input
                          type="email"
                          value={emailClientes}
                          onChange={(e) => setEmailClientes(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>

                      {/* Email Suporte */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Email Suporte
                        </label>
                        <input
                          type="email"
                          value={emailSuporte}
                          onChange={(e) => setEmailSuporte(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>

                      {/* WhatsApp Suporte */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          WhatsApp Suporte (Somente números)
                        </label>
                        <input
                          type="text"
                          value={whatsappSuporte}
                          onChange={(e) => setWhatsappSuporte(e.target.value)}
                          placeholder="5599999999999"
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>
                    </div>
                  </div>
                )}
              </div>

              {/* ACORDEÃO 2: API DE ENVIO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("api")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Lock className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        API de Envio (Gmail) <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Credenciais do provedor de e-mail.</p>
                    </div>
                  </div>
                  {openSection === "api" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "api" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[24px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* Client ID */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Client ID
                        </label>
                        <input
                          type="password"
                          value={clientId}
                          onChange={(e) => setClientId(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] font-mono"
                        />
                      </div>

                      {/* Client Secret */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Client Secret
                        </label>
                        <input
                          type="password"
                          value={clientSecret}
                          onChange={(e) => setClientSecret(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] font-mono"
                        />
                      </div>
                    </div>

                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Email para Envios
                      </label>
                      <input
                        type="email"
                        value={emailEnvios}
                        onChange={(e) => setEmailEnvios(e.target.value)}
                        placeholder="contato@empresa.com"
                        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                      />
                    </div>

                    {/* TOGGLE GMAIL API */}
                    <div className="flex items-center gap-[16px] pt-[8px]">
                      <button
                        type="button"
                        onClick={() => setAtivarGmail(!ativarGmail)}
                        className={`w-[44px] h-[24px] rounded-full transition-colors flex items-center shrink-0 ${ativarGmail ? 'bg-[#8B5CF6]' : 'bg-[#E5E7EB]'}`}
                      >
                        <div className={`w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform transform ${ativarGmail ? 'translate-x-[22px]' : 'translate-x-[2px]'}`}></div>
                      </button>
                      <div className="flex flex-col">
                        <span className="text-[14px] font-[700] text-[#111827] leading-tight mb-[2px]">Ativar Gmail API</span>
                        <span className="text-[13px] text-[#6B7280] leading-tight">Habilita o envio de emails via integração oficial do Google.</span>
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* ACORDEÃO 3: TESTE DE ENVIO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("teste")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Send className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Teste de Envio
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Envie um e-mail teste para garantir que a integração está funcionando.</p>
                    </div>
                  </div>
                  {openSection === "teste" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "teste" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Email para Teste
                      </label>
                      <div className="flex items-center gap-[12px] w-full max-w-[600px]">
                        <input
                          type="email"
                          value={emailTeste}
                          onChange={(e) => setEmailTeste(e.target.value)}
                          placeholder="email@exemplo.com"
                          className="flex-1 h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                        <button 
                          type="button"
                          onClick={handleSendTest}
                          className="h-[40px] px-[20px] bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors rounded-[8px] flex items-center justify-center gap-[8px] text-white text-[13px] font-[600]"
                        >
                          <Send className="w-[16px] h-[16px]" /> Enviar E-mail de Teste
                        </button>
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
