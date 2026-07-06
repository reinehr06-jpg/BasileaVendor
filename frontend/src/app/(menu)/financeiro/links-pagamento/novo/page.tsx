"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { 
  ArrowLeft,
  Package,
  Settings2,
  ChevronDown,
  ChevronUp,
  X,
  Rocket
} from "lucide-react";
import { toast } from "sonner";
import { useTranslation } from "react-i18next";
import { useRouter } from "next/navigation";

type SectionType = "produto" | "regras" | null;

export default function NovoLinkPage() {
  const { t } = useTranslation();
  const router = useRouter();
  
  const [openSection, setOpenSection] = useState<SectionType>("produto");
  
  // Informações do Produto
  const [titulo, setTitulo] = useState("");
  const [descricao, setDescricao] = useState("");
  const [valor, setValor] = useState("0,00");
  const [vagas, setVagas] = useState("10");

  // Regras e Configurações
  const [whatsapp, setWhatsapp] = useState("");
  const [expiracao, setExpiracao] = useState("");
  const [meiosPagamento, setMeiosPagamento] = useState("todos");
  const [tipoCobranca, setTipoCobranca] = useState("avulsa");
  const [ativarNotificacoes, setAtivarNotificacoes] = useState(true);
  const [exigirEndereco, setExigirEndereco] = useState(false);

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!titulo || !vagas || !whatsapp) {
      toast.error("Preencha todos os campos obrigatórios (*).");
      return;
    }
    toast.success("Link de pagamento criado com sucesso!");
    setTimeout(() => {
      router.push("/financeiro/links-pagamento");
    }, 1500);
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* NAVEGAÇÃO DE VOLTA */}
            <Link 
              href="/financeiro/links-pagamento"
              className="flex items-center gap-[8px] text-[14px] font-[600] text-[#6B7280] hover:text-[#111827] transition-colors w-fit mb-[16px]"
            >
              <ArrowLeft className="w-[16px] h-[16px]" />
              Voltar para Links
            </Link>

            {/* CABEÇALHO DA PÁGINA */}
            <div className="flex items-start justify-between mb-[24px]">
              <div className="flex items-start gap-[12px]">
                <Package className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
                <div className="flex flex-col">
                  <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">Criar Novo Link</h1>
                  <p className="text-[14px] text-[#6B7280]">Configure os detalhes do produto e regras de checkout.</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSave} className="flex flex-col gap-[16px]">
              
              {/* ACORDEÃO 1: INFORMAÇÕES DO PRODUTO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("produto")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Package className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        1. Informações do Produto <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Título, descrição e valores da cobrança.</p>
                    </div>
                  </div>
                  {openSection === "produto" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "produto" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* Título Comercial */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        TÍTULO COMERCIAL <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      <input
                        type="text"
                        value={titulo}
                        onChange={(e) => setTitulo(e.target.value)}
                        placeholder="Ex: Treinamento Liderança 2026"
                        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                      />
                    </div>

                    {/* Descrição Curta */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        DESCRIÇÃO CURTA
                      </label>
                      <textarea
                        value={descricao}
                        onChange={(e) => setDescricao(e.target.value)}
                        placeholder="Opcional: Aparece no topo do checkout..."
                        className="w-full h-[100px] bg-white border border-[#E5E7EB] rounded-[8px] p-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] resize-y"
                      />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* Valor Unitário */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          VALOR UNITÁRIO (R$)
                        </label>
                        <input
                          type="text"
                          value={valor}
                          onChange={(e) => setValor(e.target.value)}
                          placeholder="0,00 (aberto)"
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                        <p className="text-[12px] text-[#9CA3AF]">Deixe 0 para valor aberto.</p>
                      </div>

                      {/* Limite de Vagas */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          LIMITE DE VAGAS <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input
                          type="number"
                          value={vagas}
                          onChange={(e) => setVagas(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* ACORDEÃO 2: REGRAS E CONFIGURAÇÕES */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  type="button"
                  onClick={() => toggleSection("regras")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Settings2 className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        2. Regras e Configurações Asaas <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Meios de pagamento, vencimento e notificações.</p>
                    </div>
                  </div>
                  {openSection === "regras" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "regras" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* WhatsApp Suporte */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          WHATSAPP SUPORTE <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <input
                          type="text"
                          value={whatsapp}
                          onChange={(e) => setWhatsapp(e.target.value)}
                          placeholder="55..."
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        />
                      </div>

                      {/* Expiração do Link */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          EXPIRAÇÃO DO LINK
                        </label>
                        <input
                          type="date"
                          value={expiracao}
                          onChange={(e) => setExpiracao(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] text-[#6B7280]"
                        />
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      {/* Meios de Pagamento */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          MEIOS DE PAGAMENTO
                        </label>
                        <select
                          value={meiosPagamento}
                          onChange={(e) => setMeiosPagamento(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        >
                          <option value="todos">Todos (Pix, Cartão, Boleto)</option>
                          <option value="pix">Apenas Pix</option>
                          <option value="cartao">Apenas Cartão de Crédito</option>
                        </select>
                      </div>

                      {/* Tipo de Cobrança */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          TIPO DE COBRANÇA
                        </label>
                        <select
                          value={tipoCobranca}
                          onChange={(e) => setTipoCobranca(e.target.value)}
                          className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        >
                          <option value="avulsa">Cobrança Avulsa</option>
                          <option value="assinatura">Assinatura / Recorrente</option>
                        </select>
                      </div>
                    </div>

                    {/* Caixa de Checkboxes */}
                    <div className="bg-[#F8FAFC] border border-[#E2E8F0] rounded-[8px] p-[16px] flex flex-col gap-[12px] mt-[4px]">
                      <label className="flex items-center gap-[10px] cursor-pointer">
                        <div className="relative flex items-center justify-center">
                          <input 
                            type="checkbox" 
                            checked={ativarNotificacoes}
                            onChange={() => setAtivarNotificacoes(!ativarNotificacoes)}
                            className="w-[18px] h-[18px] appearance-none border border-[#CBD5E1] rounded-[4px] checked:bg-[#8B5CF6] checked:border-[#8B5CF6] transition-colors cursor-pointer"
                          />
                          {ativarNotificacoes && (
                            <svg className="w-[12px] h-[12px] text-white absolute pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                              <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                          )}
                        </div>
                        <span className="text-[14px] text-[#475569]">Ativar notificações de pagamento para o cliente</span>
                      </label>

                      <label className="flex items-center gap-[10px] cursor-pointer">
                        <div className="relative flex items-center justify-center">
                          <input 
                            type="checkbox" 
                            checked={exigirEndereco}
                            onChange={() => setExigirEndereco(!exigirEndereco)}
                            className="w-[18px] h-[18px] appearance-none border border-[#CBD5E1] rounded-[4px] checked:bg-[#8B5CF6] checked:border-[#8B5CF6] transition-colors cursor-pointer"
                          />
                          {exigirEndereco && (
                            <svg className="w-[12px] h-[12px] text-white absolute pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                              <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                          )}
                        </div>
                        <span className="text-[14px] text-[#475569]">Exigir endereço completo no checkout</span>
                      </label>
                    </div>

                  </div>
                )}
              </div>

              {/* FIXED BOTTOM ACTION BAR */}
              <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] flex items-center justify-between px-[32px] z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <Link 
                  href="/financeiro/links-pagamento"
                  className="flex items-center gap-[8px] px-[20px] py-[10px] text-[#4B5563] hover:text-[#111827] hover:bg-[#F3F4F6] transition-colors rounded-[8px] text-[14px] font-[600] border border-[#E5E7EB]"
                >
                  Cancelar
                </Link>

                <button 
                  type="submit"
                  className="flex items-center gap-[8px] px-[24px] py-[12px] bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors rounded-[8px] text-white text-[14px] font-[600] shadow-sm"
                >
                  <Rocket className="w-[16px] h-[16px]" />
                  Criar Link no Asaas
                </button>
              </div>

            </form>
          </div>
        </main>
      </div>
    </div>
  );
}
