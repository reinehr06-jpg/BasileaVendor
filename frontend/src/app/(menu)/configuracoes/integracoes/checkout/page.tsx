"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { 
  ArrowLeft,
  ShoppingCart,
  Key,
  Copy,
  Eye,
  EyeOff,
  Link as LinkIcon,
  ChevronDown,
  ChevronUp,
  X,
  Save,
  CheckCircle2,
  AlertCircle,
  Info
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";

type SectionType = "credenciais" | "urls" | "testes" | null;

export default function CheckoutExternoPage() {
  const { t } = useTranslation();
  
  const [openSection, setOpenSection] = useState<SectionType>("credenciais");
  const [webhookSecret, setWebhookSecret] = useState("whsec_GImjnPj7_tkEHRcWUfnd8NX8tloQOr7u");
  const [apiKey, setApiKey] = useState("ck_live_8f7d6a5s4d3f2g1h");
  const [showApiKey, setShowApiKey] = useState(false);
  const [baseUrl, setBaseUrl] = useState("https://secure.basileia.global");
  
  const webhookEndpoint = "https://vendor.basileia.global/api/webhook/checkout";

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const handleCopy = (text: string, message: string) => {
    navigator.clipboard.writeText(text);
    toast.success(message);
  };

  const handleGenerateSecret = () => {
    toast.info("Gerando nova chave secreta...");
    setTimeout(() => {
      setWebhookSecret("whsec_" + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15));
      toast.success("Nova chave gerada! Não se esqueça de salvar.");
    }, 800);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Integração de Checkout salva com sucesso!");
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* 🏷️ NAVEGAÇÃO DE VOLTA (Adicionado ao topo para facilidade) */}
            <Link 
              href="/configuracoes/integracoes"
              className="flex items-center gap-[8px] text-[14px] font-[600] text-[#6B7280] hover:text-[#111827] transition-colors w-fit mb-[16px]"
            >
              <ArrowLeft className="w-[16px] h-[16px]" />
              Voltar para Integrações
            </Link>

            {/* CABEÇALHO DA PÁGINA (ESTILO PADRÃO IGUAL AO ASAAS) */}
            <div className="flex items-start justify-between mb-[24px]">
              <div className="flex items-start gap-[12px]">
                <ShoppingCart className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Integração Externa de Checkout</h1>
                  <p className="text-[14px] text-[#6B7280]">Siga os 6 passos abaixo para conectar qualquer Checkout ao Basileia.</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* SEÇÃO 1: CREDENCIAIS DE SEGURANÇA */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("credenciais")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Key className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Credenciais de Segurança <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Chaves de autenticação para comunicação entre os sistemas.</p>
                    </div>
                  </div>
                  {openSection === "credenciais" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "credenciais" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[32px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* Webhook Secret */}
                    <div className="flex flex-col gap-[12px]">
                      <div className="flex items-center gap-[12px]">
                        <span className="px-[8px] py-[2px] bg-[#6D28D9] text-white text-[11px] font-[700] rounded-[6px] tracking-wider uppercase">Passos 1 e 2</span>
                        <h3 className="text-[15px] font-[700] text-[#374151]">Webhook Secret</h3>
                      </div>
                      <p className="text-[13px] text-[#6B7280]">Esta chave protege seu sistema para que apenas o seu Checkout possa enviar pagamentos pagos para cá.</p>
                      
                      <div className="flex items-center gap-[8px] w-full">
                        <input 
                          type="text" 
                          value={webhookSecret}
                          readOnly
                          className="flex-1 h-[40px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#111827] font-mono outline-none"
                        />
                        <button 
                          type="button"
                          onClick={handleGenerateSecret}
                          className="h-[40px] px-[16px] bg-[#F3F4F6] hover:bg-[#E5E7EB] border border-[#E5E7EB] transition-colors rounded-[8px] flex items-center justify-center gap-[8px] text-[#4B5563] text-[13px] font-[600]"
                        >
                          <Key className="w-[16px] h-[16px]" /> Gerar
                        </button>
                        <button 
                          type="button"
                          onClick={() => handleCopy(webhookSecret, "Webhook Secret copiado!")}
                          className="w-[40px] h-[40px] bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors rounded-[8px] flex items-center justify-center shrink-0 shadow-sm text-white"
                        >
                          <Copy className="w-[18px] h-[18px]" strokeWidth={2.5} />
                        </button>
                      </div>
                      <p className="text-[12px] font-[600] text-[#EF4444] flex items-center gap-[4px]">
                        <AlertCircle className="w-[14px] h-[14px]" /> Após gerar, clique no botão Copiar e cole no seu sistema de Checkout.
                      </p>
                    </div>

                    {/* API Key do Checkout */}
                    <div className="flex flex-col gap-[12px]">
                      <div className="flex items-center gap-[12px]">
                        <span className="px-[8px] py-[2px] bg-[#6D28D9] text-white text-[11px] font-[700] rounded-[6px] tracking-wider uppercase">Passo 1.5</span>
                        <h3 className="text-[15px] font-[700] text-[#374151]">API Key do Checkout</h3>
                      </div>
                      <p className="text-[13px] text-[#6B7280]">Copie a ck_live_... do seu Checkout e cole aqui. Esta chave autentica requisições ativas.</p>
                      
                      <div className="flex items-center gap-[8px] w-full">
                        <input 
                          type={showApiKey ? "text" : "password"} 
                          value={apiKey}
                          onChange={(e) => setApiKey(e.target.value)}
                          className="flex-1 h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#111827] font-mono outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                        <button 
                          type="button"
                          onClick={() => setShowApiKey(!showApiKey)}
                          className="w-[40px] h-[40px] bg-[#F3F4F6] hover:bg-[#E5E7EB] border border-[#E5E7EB] transition-colors rounded-[8px] flex items-center justify-center text-[#4B5563]"
                        >
                          {showApiKey ? <EyeOff className="w-[18px] h-[18px]" /> : <Eye className="w-[18px] h-[18px]" />}
                        </button>
                        <button 
                          type="button"
                          onClick={() => handleCopy(apiKey, "API Key copiada!")}
                          className="w-[40px] h-[40px] bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors rounded-[8px] flex items-center justify-center shrink-0 shadow-sm text-white"
                        >
                          <Copy className="w-[18px] h-[18px]" strokeWidth={2.5} />
                        </button>
                      </div>
                      <p className="text-[12px] text-[#9CA3AF] flex items-center gap-[4px]">
                        <Info className="w-[14px] h-[14px]" /> A API Key permite consultar, cancelar e buscar transações diretamente.
                      </p>
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 2: CONFIGURAÇÕES DE URL */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("urls")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <LinkIcon className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Configurações de URL
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Endpoints para comunicação de pagamentos e redirecionamentos.</p>
                    </div>
                  </div>
                  {openSection === "urls" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "urls" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[32px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* Endpoint do Webhook */}
                    <div className="flex flex-col gap-[12px]">
                      <div className="flex items-center gap-[12px]">
                        <span className="px-[8px] py-[2px] bg-[#6D28D9] text-white text-[11px] font-[700] rounded-[6px] tracking-wider uppercase">Passos 3 e 4</span>
                        <h3 className="text-[15px] font-[700] text-[#374151]">Endpoint do Webhook</h3>
                      </div>
                      <p className="text-[13px] text-[#6B7280]">Para onde o seu Checkout deve enviar o sinal de "PAGO" ou "RECUSADO"?</p>
                      
                      <div className="flex items-center gap-[8px] w-full">
                        <div className="flex-1 h-[44px] bg-[#F0F9FF] border border-[#BAE6FD] rounded-[8px] px-[16px] flex items-center text-[#0369A1] font-[600] font-mono text-[14px] overflow-hidden truncate">
                          {webhookEndpoint}
                        </div>
                        <button 
                          type="button"
                          onClick={() => handleCopy(webhookEndpoint, "Endpoint copiado!")}
                          className="w-[44px] h-[44px] bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors rounded-[8px] flex items-center justify-center shrink-0 shadow-sm text-white"
                        >
                          <Copy className="w-[18px] h-[18px]" strokeWidth={2.5} />
                        </button>
                      </div>
                      <p className="text-[12px] font-[600] text-[#EF4444] flex items-center gap-[4px]">
                        <AlertCircle className="w-[14px] h-[14px]" /> Cole esta URL no cadastro de webhook do seu Checkout.
                      </p>
                    </div>

                    {/* URL Base */}
                    <div className="flex flex-col gap-[12px]">
                      <div className="flex items-center gap-[12px]">
                        <span className="px-[8px] py-[2px] bg-[#6D28D9] text-white text-[11px] font-[700] rounded-[6px] tracking-wider uppercase">Passo 5</span>
                        <h3 className="text-[15px] font-[700] text-[#374151]">URL Base do Checkout</h3>
                      </div>
                      <p className="text-[13px] text-[#6B7280]">Para onde redirecionar o cliente na hora da compra? (Ex: `https://pay.seucheckout.com/`)</p>
                      
                      <div className="flex items-center gap-[8px] w-full">
                        <input 
                          type="text" 
                          value={baseUrl}
                          onChange={(e) => setBaseUrl(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#111827] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 3: TESTES E VALIDAÇÃO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("testes")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <CheckCircle2 className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Testes e Validação
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Verifique se a integração foi concluída com sucesso.</p>
                    </div>
                  </div>
                  {openSection === "testes" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "testes" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[24px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* Guia de Teste */}
                    <div className="bg-[#F0F9FF] border border-[#BAE6FD] rounded-[12px] p-[20px] flex flex-col gap-[12px]">
                      <div className="flex items-center gap-[8px]">
                        <span className="px-[8px] py-[2px] bg-[#0284C7] text-white text-[11px] font-[700] rounded-[6px] tracking-wider uppercase">Passo 6</span>
                        <h3 className="text-[14px] font-[700] text-[#0369A1]">Como testar a automação:</h3>
                      </div>
                      
                      <ol className="text-[13px] text-[#0369A1] flex flex-col gap-[8px] ml-[8px]">
                        <li>1. Crie uma <strong>Venda</strong> no sistema (O link de checkout será gerado instantaneamente).</li>
                        <li>2. Copie e abra o link gerado (Sua tela de checkout deve abrir montada).</li>
                        <li>3. Faça um pagamento teste (PIX ou Boleto).</li>
                        <li>4. Aguarde e veja se essa Venda muda para "PAGO" no seu painel.</li>
                      </ol>
                    </div>

                    {/* Botões de Ação de Teste */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
                      <div className="border border-[#E5E7EB] rounded-[12px] p-[20px] flex flex-col gap-[16px]">
                        <div className="flex flex-col gap-[4px]">
                          <div className="flex items-center gap-[8px]">
                            <Key className="w-[16px] h-[16px] text-[#8B5CF6]" />
                            <h4 className="text-[14px] font-[700] text-[#111827]">Testar API Key</h4>
                          </div>
                          <p className="text-[13px] text-[#6B7280]">Verifica se a API Key do Checkout está válida e acessível.</p>
                        </div>
                        <button 
                          type="button"
                          onClick={() => toast.success("API Key validada com sucesso!")}
                          className="w-fit bg-[#8B5CF6] hover:bg-[#7C3AED] transition-colors text-white px-[16px] py-[8px] rounded-[8px] text-[13px] font-[600] flex items-center gap-[8px]"
                        >
                          <Key className="w-[14px] h-[14px]" /> Testar API Key
                        </button>
                      </div>

                      <div className="border border-[#E5E7EB] rounded-[12px] p-[20px] flex flex-col gap-[16px]">
                        <div className="flex flex-col gap-[4px]">
                          <div className="flex items-center gap-[8px]">
                            <LinkIcon className="w-[16px] h-[16px] text-[#0EA5E9]" />
                            <h4 className="text-[14px] font-[700] text-[#111827]">Testar Webhook</h4>
                          </div>
                          <p className="text-[13px] text-[#6B7280]">Envia um evento simulado para validar o endpoint do webhook.</p>
                        </div>
                        <button 
                          type="button"
                          onClick={() => toast.success("Evento de webhook enviado com sucesso!")}
                          className="w-fit bg-[#0EA5E9] hover:bg-[#0284C7] transition-colors text-white px-[16px] py-[8px] rounded-[8px] text-[13px] font-[600] flex items-center gap-[8px]"
                        >
                          <LinkIcon className="w-[14px] h-[14px]" /> Testar Webhook
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
