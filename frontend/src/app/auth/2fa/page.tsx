"use client";

import React, { useState, useRef, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useTranslation } from "react-i18next";
import { toast } from "sonner";
import { ShieldCheck, Loader2, ArrowLeft } from "lucide-react";
import { AuthSplitLayout } from "@/components/auth/AuthSplitLayout";
import { useAuth } from "@/context/AuthContext";
import { api } from "@/lib/api";

export default function TwoFactorPage() {
  const { t } = useTranslation();
  const router = useRouter();
  const { user } = useAuth();
  const [userId, setUserId] = useState("");
  
  useEffect(() => {
    const stored = localStorage.getItem("2fa_user_id");
    if (stored) {
      setUserId(stored);
    } else {
      router.push("/auth/login");
    }
  }, [router]);
  
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
    try {
      const res = await api.post("/auth/verify-2fa", { user_id: userId, code: fullCode });
      
      // Salvar token em cookie e redirecionar
      document.cookie = `auth_token=${res.token}; path=/; max-age=86400; SameSite=Lax`;
      localStorage.removeItem("2fa_user_id");
      
      toast.success(t("Código verificado com sucesso!"));
      
      // Force reload to update AuthContext state or push dashboard
      if (res.user?.perfil === 'gestor') {
        window.location.href = "/gestor";
      } else if (res.user?.perfil === 'vendedor') {
        window.location.href = "/vendedor";
      } else {
        window.location.href = "/dashboard";
      }
    } catch (error: any) {
      toast.error(t("Erro na verificação"), {
        description: error.message || t("Código inválido. Tente novamente.")
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AuthSplitLayout onBack={() => router.back()}>
      <div className="fade-in" style={{width: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center'}}>
        <div className="card-header" style={{ alignItems: 'center', textAlign: 'center', marginBottom: '32px', marginTop: '16px' }}>
          <div className="w-[56px] h-[56px] bg-[#ECFDF5] rounded-full flex items-center justify-center mb-[16px] mx-auto">
            <ShieldCheck className="w-[28px] h-[28px] text-[#10B981]" strokeWidth={2.2} />
          </div>
          <h1>{t("Verificação em 2 Passos")}</h1>
          <p>
            {t("Enviamos um código de 6 dígitos para o seu e-mail e aplicativo autenticador.")}
          </p>
        </div>

        <div className="flex justify-center gap-[12px]" style={{ marginBottom: '32px' }}>
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
          className="btn"
          style={{ width: '100%' }}
        >
          {isLoading ? <Loader2 className="w-[18px] h-[18px] animate-spin mx-auto" /> : t("Verificar Código")}
        </button>

        <div className="new-account" style={{ marginTop: '24px' }}>
          <span>{t("Não recebeu o código?")}</span>
          <a style={{cursor: 'pointer'}}>{t("Reenviar")}</a>
        </div>
      </div>
    </AuthSplitLayout>
  );
}
