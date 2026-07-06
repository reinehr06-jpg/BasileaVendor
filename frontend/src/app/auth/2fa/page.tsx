"use client";

import React, { useState, useRef, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useTranslation } from "react-i18next";
import { toast } from "sonner";
import { ShieldCheck, Loader2, ArrowLeft } from "lucide-react";

export default function TwoFactorPage() {
  const { t } = useTranslation();
  const router = useRouter();
  
  const [code, setCode] = useState(["", "", "", "", "", ""]);
  const [isLoading, setIsLoading] = useState(false);
  const inputsRef = useRef<(HTMLInputElement | null)[]>([]);

  // Focus inicial
  useEffect(() => {
    if (inputsRef.current[0]) inputsRef.current[0].focus();
  }, []);

  const handleChange = (index: number, value: string) => {
    if (value.length > 1) value = value.slice(-1); // pega apenas 1 dígito
    if (!/^\d*$/.test(value)) return; // aceita apenas números

    const newCode = [...code];
    newCode[index] = value;
    setCode(newCode);

    // Auto-focus no próximo
    if (value !== "" && index < 5) {
      inputsRef.current[index + 1]?.focus();
    }
  };

  const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Backspace" && code[index] === "" && index > 0) {
      inputsRef.current[index - 1]?.focus();
    }
  };

  const handleVerify = async () => {
    const fullCode = code.join("");
    if (fullCode.length < 6) {
      toast.error(t("Preencha todos os 6 dígitos."));
      return;
    }

    setIsLoading(true);
    // Simulando delay de verificação
    setTimeout(() => {
      setIsLoading(false);
      toast.success(t("Código verificado com sucesso!"));
      router.push("/auth/termos");
    }, 1000);
  };

  return (
    <div className="w-full flex flex-col animation-fade-in">
      <button 
        onClick={() => router.back()}
        className="self-start mb-[24px] flex items-center gap-[6px] text-[13px] font-[600] text-[#6B7280] hover:text-[#111827] transition-colors"
      >
        <ArrowLeft className="w-[16px] h-[16px]" />
        {t("Voltar")}
      </button>

      <div className="mb-[32px] flex flex-col items-center text-center">
        <div className="w-[56px] h-[56px] bg-[#ECFDF5] rounded-full flex items-center justify-center mb-[16px]">
          <ShieldCheck className="w-[28px] h-[28px] text-[#10B981]" strokeWidth={2.2} />
        </div>
        <h2 className="text-[24px] font-[700] text-[#111827] mb-[8px]">{t("Verificação em 2 Passos")}</h2>
        <p className="text-[14px] text-[#6B7280]">
          {t("Enviamos um código de 6 dígitos para o seu e-mail e aplicativo autenticador.")}
        </p>
      </div>

      <div className="flex justify-center gap-[12px] mb-[32px]">
        {code.map((digit, idx) => (
          <input
            key={idx}
            ref={(el) => { inputsRef.current[idx] = el; }}
            type="text"
            inputMode="numeric"
            maxLength={1}
            value={digit}
            onChange={(e) => handleChange(idx, e.target.value)}
            onKeyDown={(e) => handleKeyDown(idx, e)}
            className="w-[48px] h-[56px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[12px] text-center text-[24px] font-[700] text-[#111827] outline-none focus:bg-white focus:border-[#7C3AED] focus:ring-4 focus:ring-[#7C3AED]/10 transition-all"
          />
        ))}
      </div>

      <button
        onClick={handleVerify}
        disabled={isLoading || code.join("").length < 6}
        className="w-full h-[48px] bg-[#7C3AED] hover:bg-[#6D28D9] text-white rounded-[12px] text-[15px] font-[600] flex items-center justify-center gap-[8px] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {isLoading ? <Loader2 className="w-[18px] h-[18px] animate-spin" /> : t("Verificar Código")}
      </button>

      <div className="mt-[24px] text-center">
        <p className="text-[13px] text-[#6B7280]">
          {t("Não recebeu o código?")}{" "}
          <button className="font-[600] text-[#7C3AED] hover:text-[#6D28D9] transition-colors">
            {t("Reenviar")}
          </button>
        </p>
      </div>
    </div>
  );
}
