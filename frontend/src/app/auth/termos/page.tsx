"use client";

import React, { useState } from "react";
import { useRouter } from "next/navigation";
import { useTranslation } from "react-i18next";
import { toast } from "sonner";
import { FileText, Loader2, Check } from "lucide-react";

export default function TermosPage() {
  const { t } = useTranslation();
  const router = useRouter();
  
  const [accepted, setAccepted] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const handleContinue = () => {
    setIsLoading(true);
    // Simula salvamento da aceitação
    setTimeout(() => {
      setIsLoading(false);
      toast.success(t("Acesso liberado!"));
      // Direcionar para a página principal (ex: gestor)
      router.push("/gestor");
    }, 1000);
  };

  return (
    <div className="w-full flex flex-col animation-fade-in max-w-[500px] mx-auto">
      <div className="mb-[24px] flex items-center gap-[16px]">
        <div className="w-[48px] h-[48px] bg-[#EEF2FF] rounded-[14px] flex items-center justify-center shrink-0">
          <FileText className="w-[24px] h-[24px] text-[#4F46E5]" strokeWidth={2} />
        </div>
        <div>
          <h2 className="text-[20px] font-[700] text-[#111827]">{t("Termos de Uso")}</h2>
          <p className="text-[13px] text-[#6B7280]">
            {t("Por favor, leia e aceite os termos antes de acessar a plataforma.")}
          </p>
        </div>
      </div>

      {/* Caixa de Texto Rolável */}
      <div className="w-full h-[240px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[12px] p-[16px] overflow-y-auto mb-[24px] custom-scrollbar text-[13px] text-[#4B5563] leading-[1.6]">
        <h3 className="font-[700] text-[#111827] mb-[8px]">1. Aceitação dos Termos</h3>
        <p className="mb-[12px]">
          Ao acessar e utilizar o Basiléia Vendor OS, você concorda em cumprir e estar vinculado aos seguintes Termos de Serviço e Condições de Uso. Caso não concorde com qualquer parte destes termos, o acesso à plataforma será restrito.
        </p>

        <h3 className="font-[700] text-[#111827] mb-[8px]">2. Confidencialidade e Dados</h3>
        <p className="mb-[12px]">
          Todos os dados financeiros, de clientes e de comissões aqui processados são estritamente confidenciais. O usuário compromete-se a não extrair, duplicar ou compartilhar informações com terceiros não autorizados.
        </p>

        <h3 className="font-[700] text-[#111827] mb-[8px]">3. Uso da Plataforma</h3>
        <p className="mb-[12px]">
          O sistema deve ser utilizado exclusivamente para fins corporativos e comerciais autorizados. Qualquer tentativa de fraude, manipulação de resultados ou acesso não autorizado será investigada sob o rigor das políticas internas.
        </p>

        <h3 className="font-[700] text-[#111827] mb-[8px]">4. Atualizações</h3>
        <p>
          Reservamo-nos o direito de modificar estes termos a qualquer momento. Mudanças significativas serão notificadas, e o uso contínuo implicará na aceitação das novas condições.
        </p>
      </div>

      {/* Checkbox */}
      <label className="flex items-start gap-[12px] cursor-pointer mb-[32px] group">
        <div className="relative flex items-center justify-center mt-0.5">
          <input 
            type="checkbox" 
            className="peer sr-only"
            checked={accepted}
            onChange={(e) => setAccepted(e.target.checked)}
          />
          <div className="w-[20px] h-[20px] bg-white border-[2px] border-[#D1D5DB] rounded-[6px] peer-checked:border-[#7C3AED] peer-checked:bg-[#7C3AED] transition-colors" />
          <Check className="w-[14px] h-[14px] text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity" strokeWidth={3} />
        </div>
        <span className="text-[14px] text-[#374151] font-[500] select-none group-hover:text-[#111827] transition-colors">
          {t("Li e concordo com os Termos de Uso e Políticas de Privacidade.")}
        </span>
      </label>

      {/* Submit */}
      <button
        onClick={handleContinue}
        disabled={!accepted || isLoading}
        className="w-full h-[48px] bg-[#111827] hover:bg-[#1F2937] text-white rounded-[12px] text-[15px] font-[600] flex items-center justify-center gap-[8px] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {isLoading ? <Loader2 className="w-[18px] h-[18px] animate-spin" /> : t("Aceitar e Continuar")}
      </button>
    </div>
  );
}
