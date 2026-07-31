"use client";

import React, { useState, useEffect, useRef } from "react";
import { ShieldCheck, Check, FileText } from "lucide-react";
import { createPortal } from "react-dom";

export default function LGPDTermsModal() {
  const [isOpen, setIsOpen] = useState(false);
  const [timeLeft, setTimeLeft] = useState(30);
  const [hasScrolledToBottom, setHasScrolledToBottom] = useState(false);
  const [hasCheckedTerms, setHasCheckedTerms] = useState(false);
  const contentRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // Verificar se já aceitou
    const accepted = localStorage.getItem("termos_aceitos");
    if (!accepted) {
      setIsOpen(true);
    }
  }, []);

  useEffect(() => {
    let timer: NodeJS.Timeout;
    if (isOpen && timeLeft > 0) {
      timer = setInterval(() => {
        setTimeLeft((prev) => prev - 1);
      }, 1000);
    }
    return () => clearInterval(timer);
  }, [isOpen, timeLeft]);

  const handleScroll = () => {
    if (contentRef.current) {
      const { scrollTop, scrollHeight, clientHeight } = contentRef.current;
      // Considera no final se estiver a 10px do fim
      if (scrollTop + clientHeight >= scrollHeight - 10) {
        setHasScrolledToBottom(true);
      }
    }
  };

  const handleAccept = () => {
    localStorage.setItem("termos_aceitos", "true");
    setIsOpen(false);
    window.dispatchEvent(new Event("termos_aceitos"));
  };

  if (!isOpen) return null;

  const canCheck = timeLeft === 0 && hasScrolledToBottom;
  const canAccept = canCheck && hasCheckedTerms;

  const modalContent = (
    <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-[#0F172A]/80 backdrop-blur-md animate-in fade-in duration-300 p-4">
      <div className="bg-white w-full max-w-[750px] h-[85vh] max-h-[850px] rounded-[24px] shadow-2xl flex flex-col overflow-hidden animate-in zoom-in-95 duration-500 ease-out border border-[#E2E8F0]">
        
        {/* HEADER */}
        <div className="relative px-[32px] py-[32px] overflow-hidden shrink-0 bg-gradient-to-br from-[#4C1D95] to-[#7C3AED]">
          {/* Background decoration */}
          <div className="absolute top-0 right-0 -mr-[40px] -mt-[40px] w-[160px] h-[160px] rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
          <div className="absolute bottom-0 left-0 -ml-[40px] -mb-[40px] w-[120px] h-[120px] rounded-full bg-black/10 blur-xl pointer-events-none"></div>
          
          <div className="relative z-10 flex items-center gap-[20px]">
            <div className="w-[56px] h-[56px] bg-white/20 backdrop-blur-md rounded-[16px] flex items-center justify-center shrink-0 border border-white/20 shadow-inner">
              <ShieldCheck className="w-[28px] h-[28px] text-white" />
            </div>
            <div className="flex flex-col">
              <h2 className="text-[22px] font-[800] text-white leading-tight tracking-tight">Termos de Uso & Privacidade</h2>
              <p className="text-[14px] text-white/80 mt-[4px] font-[400]">
                Atualização da Política de Proteção de Dados (LGPD)
              </p>
            </div>
          </div>
        </div>

        {/* BODY TEXT */}
        <div 
          ref={contentRef}
          onScroll={handleScroll}
          className="flex-1 overflow-y-auto p-[32px] custom-scrollbar text-[14px] text-[#334155] leading-[1.8] bg-[#F8FAFC]"
        >
          <div className="prose prose-sm max-w-none prose-headings:text-[#0F172A] prose-headings:font-[800] prose-headings:tracking-tight prose-headings:mt-6 prose-headings:mb-3 prose-p:mb-4">
            <h3 className="text-[16px] flex items-center gap-2">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 1. Aceitação dos Termos
            </h3>
            <p>Ao acessar e utilizar esta plataforma, você concorda em cumprir e estar vinculado aos seguintes Termos de Uso. Se você não concordar com qualquer parte destes termos, não deverá utilizar nossos serviços.</p>

            <h3 className="text-[16px] flex items-center gap-2 mt-6">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 2. Coleta de Dados (LGPD)
            </h3>
            <p>Em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018), informamos que coletamos dados pessoais necessários para a prestação de nossos serviços, incluindo, mas não se limitando a: nome, e-mail, telefone, CPF/CNPJ e dados de faturamento. Estes dados são armazenados de forma segura e utilizados estritamente para as finalidades do negócio.</p>

            <h3 className="text-[16px] flex items-center gap-2 mt-6">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 3. Uso das Informações
            </h3>
            <p>As informações coletadas são utilizadas para:</p>
            <ul className="list-disc pl-5 space-y-2 text-[#475569]">
              <li>Criar e gerenciar sua conta de usuário;</li>
              <li>Processar pagamentos e gerar notas fiscais;</li>
              <li>Enviar comunicados operacionais importantes;</li>
              <li>Prevenir fraudes e manter a segurança do sistema;</li>
              <li>Cumprir obrigações legais e regulatórias.</li>
            </ul>

            <h3 className="text-[16px] flex items-center gap-2 mt-6">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 4. Compartilhamento de Dados
            </h3>
            <p>Não vendemos ou alugamos seus dados pessoais. O compartilhamento ocorre apenas com fornecedores essenciais (como gateways de pagamento e serviços de nuvem), que também estão sujeitos à LGPD, ou mediante ordem judicial.</p>

            <h3 className="text-[16px] flex items-center gap-2 mt-6">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 5. Responsabilidades do Usuário
            </h3>
            <p>O usuário é inteiramente responsável por manter a confidencialidade de suas credenciais de acesso (login e senha). Qualquer ação realizada através de sua conta será de sua total responsabilidade.</p>

            <h3 className="text-[16px] flex items-center gap-2 mt-6">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 6. Armazenamento e Exclusão
            </h3>
            <p>Seus dados serão mantidos enquanto sua conta estiver ativa. Você tem o direito de solicitar a exclusão de seus dados pessoais, ressalvadas as informações que somos obrigados a reter por força de lei (ex: registros financeiros).</p>
            
            <h3 className="text-[16px] flex items-center gap-2 mt-6">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 7. Propriedade Intelectual
            </h3>
            <p>Todo o conteúdo, design, código-fonte e logotipos presentes na plataforma são de propriedade exclusiva. É vedada a cópia, reprodução ou modificação não autorizada.</p>
            
            <h3 className="text-[16px] flex items-center gap-2 mt-6">
              <FileText className="w-4 h-4 text-[#6D28D9]" /> 8. Atualizações dos Termos
            </h3>
            <p>Reservamo-nos o direito de modificar estes termos a qualquer momento. Alterações significativas serão notificadas através da própria plataforma.</p>

            <div className="mt-12 text-center">
              <span className="inline-block px-4 py-1 rounded-full bg-[#E2E8F0] text-[#64748B] text-xs font-bold uppercase tracking-widest">
                Fim do Documento
              </span>
            </div>
          </div>
        </div>

        {/* FOOTER */}
        <div className="p-[32px] bg-white shrink-0 flex flex-col gap-[24px] shadow-[0_-4px_24px_rgba(0,0,0,0.02)] z-10 relative">
          
          {/* Checkbox Section */}
          <label 
            className={`flex items-start gap-[16px] p-[20px] rounded-[16px] border-2 transition-all duration-300
              ${canCheck 
                ? "border-[#E0E7FF] bg-[#F5F3FF] cursor-pointer hover:border-[#C4B5FD]" 
                : "border-[#F1F5F9] bg-[#F8FAFC] opacity-80 cursor-not-allowed"
              }
            `}
          >
            <div className="mt-1">
              <input 
                type="checkbox" 
                checked={hasCheckedTerms}
                onChange={(e) => canCheck && setHasCheckedTerms(e.target.checked)}
                disabled={!canCheck}
                className={`w-[20px] h-[20px] rounded-[6px] border-2 text-[#6D28D9] focus:ring-[#6D28D9] transition-all
                  ${canCheck ? "border-[#8B5CF6] cursor-pointer" : "border-[#CBD5E1] cursor-not-allowed"}
                `}
              />
            </div>
            <div className="flex flex-col">
              <span className={`text-[15px] font-[800] leading-tight ${canCheck ? "text-[#4C1D95]" : "text-[#94A3B8]"}`}>
                Li e concordo com os Termos de Uso e Política de Privacidade.
              </span>
              <span className="text-[13px] text-[#64748B] mt-[4px]">
                {!hasScrolledToBottom 
                  ? "Você precisa rolar até o final do documento para habilitar."
                  : timeLeft > 0 
                    ? `Aguarde o tempo mínimo de leitura obrigatória (${timeLeft}s).`
                    : "Você pode aceitar os termos marcando a caixa acima."}
              </span>
            </div>
          </label>

          {/* Action Button */}
          <div className="flex justify-end">
            <button 
              onClick={handleAccept}
              disabled={!canAccept}
              className={`flex items-center justify-center gap-[8px] w-full sm:w-auto px-[40px] py-[16px] rounded-[12px] text-[15px] font-[800] tracking-wide transition-all duration-300 transform
                ${canAccept 
                  ? "bg-[#6D28D9] hover:bg-[#5B21B6] text-white shadow-[0_8px_24px_rgba(109,40,217,0.35)] hover:-translate-y-1" 
                  : "bg-[#F1F5F9] text-[#94A3B8] cursor-not-allowed"
                }
              `}
            >
              {canAccept && <Check className="w-[20px] h-[20px]" strokeWidth={3} />}
              ACEITAR TERMOS E ACESSAR PLATAFORMA
            </button>
          </div>
          
        </div>
      </div>
    </div>
  );

  if (typeof window !== "undefined") {
    return createPortal(modalContent, document.body);
  }
  return null;
}
