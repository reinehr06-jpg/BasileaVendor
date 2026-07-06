"use client";

import React, { useState } from "react";
import Link from "next/link";
import { ArrowLeft, ShieldCheck, KeyRound, SmartphoneNfc, RotateCw, PowerOff } from "lucide-react";
import { useTranslation } from "react-i18next";
import { toast } from "sonner";

export default function VendedorSegurancaPage() {
  const { t } = useTranslation();
  const [senhaAtual, setSenhaAtual] = useState("");
  const [novaSenha, setNovaSenha] = useState("");
  const [confirmarSenha, setConfirmarSenha] = useState("");

  const handleUpdatePassword = () => {
    if (novaSenha !== confirmarSenha) {
      toast.error(t("As senhas não coincidem."));
      return;
    }
    toast.success(t("Senha atualizada com sucesso!"));
    setSenhaAtual("");
    setNovaSenha("");
    setConfirmarSenha("");
  };

  const handleRotateKey = () => toast.success(t("Nova chave gerada. Atualize seu aplicativo autenticador."));
  const handleDisable2FA = () => toast.warning(t("Autenticação de 2 Fatores desativada."));

  return (
    <main className="p-4 flex-1 flex flex-col w-full max-w-[1200px] mx-auto gap-4 overflow-y-auto custom-scrollbar">
          
          {/* 🏷️ CABEÇALHO PADRÃO */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
            <div className="flex items-center gap-3">
              <Link href="/vendedor/configuracoes" className="w-[40px] h-[40px] rounded-[10px] bg-[#F3F4F6] hover:bg-[#E5E7EB] flex items-center justify-center shrink-0 transition-colors">
                <ArrowLeft className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
              </Link>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Segurança</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Proteja sua conta com senha forte e autenticação dupla.</p>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-[24px]">

            {/* ALTERAR SENHA */}
            <div className="bg-white border border-[#E5E7EB] rounded-[16px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[32px] flex flex-col gap-[24px]">
              <div className="flex items-center gap-[16px] pb-[20px] border-b border-[#F3F4F6]">
                <div className="w-[48px] h-[48px] rounded-full bg-[#FEF2F2] flex items-center justify-center shrink-0">
                  <KeyRound className="w-[24px] h-[24px] text-[#DC2626]" />
                </div>
                <div className="flex flex-col">
                  <h2 className="text-[16px] font-[700] text-[#111827]">{t("Alterar Senha")}</h2>
                  <span className="text-[13px] text-[#6B7280]">{t("Sua nova senha deve ter no mínimo 8 caracteres.")}</span>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-[20px] max-w-[800px]">
                <div className="flex flex-col gap-[6px]">
                  <label className="text-[13px] font-[600] text-[#374151]">{t("Senha Atual")}</label>
                  <input 
                    type="password" 
                    value={senhaAtual}
                    onChange={(e) => setSenhaAtual(e.target.value)}
                    className="h-[42px] px-[12px] bg-white border border-[#D1D5DB] rounded-[8px] text-[14px] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all"
                  />
                </div>
                <div className="flex flex-col gap-[6px]">
                  <label className="text-[13px] font-[600] text-[#374151]">{t("Nova Senha")}</label>
                  <input 
                    type="password" 
                    value={novaSenha}
                    onChange={(e) => setNovaSenha(e.target.value)}
                    className="h-[42px] px-[12px] bg-white border border-[#D1D5DB] rounded-[8px] text-[14px] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all"
                  />
                </div>
                <div className="flex flex-col gap-[6px]">
                  <label className="text-[13px] font-[600] text-[#374151]">{t("Confirmar Nova Senha")}</label>
                  <input 
                    type="password" 
                    value={confirmarSenha}
                    onChange={(e) => setConfirmarSenha(e.target.value)}
                    className="h-[42px] px-[12px] bg-white border border-[#D1D5DB] rounded-[8px] text-[14px] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all"
                  />
                </div>
              </div>

              <div className="pt-[24px] border-t border-[#F3F4F6]">
                <button 
                  onClick={handleUpdatePassword}
                  className="flex items-center justify-center gap-[8px] h-[40px] px-[20px] bg-[#111827] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#374151] transition-colors shadow-sm"
                >
                  <ShieldCheck className="w-[16px] h-[16px]" />
                  {t("Atualizar Senha")}
                </button>
              </div>
            </div>

            {/* AUTENTICAÇÃO DE 2 FATORES (2FA) */}
            <div className="bg-white border border-[#E5E7EB] rounded-[16px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[32px] flex flex-col gap-[24px]">
              <div className="flex items-center justify-between pb-[20px] border-b border-[#F3F4F6]">
                <div className="flex items-center gap-[16px]">
                  <div className="w-[48px] h-[48px] rounded-full bg-[#ECFDF5] flex items-center justify-center shrink-0">
                    <SmartphoneNfc className="w-[24px] h-[24px] text-[#059669]" />
                  </div>
                  <div className="flex flex-col">
                    <div className="flex items-center gap-[10px]">
                      <h2 className="text-[16px] font-[700] text-[#111827]">{t("Autenticação de Dois Fatores (2FA)")}</h2>
                      <span className="bg-[#D1FAE5] text-[#059669] px-[8px] py-[2px] rounded-full text-[10px] font-[800] uppercase tracking-wider">
                        {t("Ativo")}
                      </span>
                    </div>
                    <span className="text-[13px] text-[#6B7280] mt-1">{t("Aumente a segurança exigindo um código do app Google Authenticator ao logar.")}</span>
                  </div>
                </div>
              </div>

              <div className="flex flex-col sm:flex-row items-center gap-[12px] pt-[8px]">
                <button 
                  onClick={handleRotateKey}
                  className="w-full sm:w-auto flex items-center justify-center gap-[8px] h-[40px] px-[20px] bg-white border border-[#D1D5DB] text-[#374151] text-[13px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors"
                >
                  <RotateCw className="w-[16px] h-[16px]" />
                  {t("Rotacionar Chave")}
                </button>
                <button 
                  onClick={handleDisable2FA}
                  className="w-full sm:w-auto flex items-center justify-center gap-[8px] h-[40px] px-[20px] bg-[#FEF2F2] border border-[#FCA5A5] text-[#DC2626] text-[13px] font-[600] rounded-[8px] hover:bg-[#FEE2E2] transition-colors"
                >
                  <PowerOff className="w-[16px] h-[16px]" />
                  {t("Desativar")}
                </button>
              </div>
            </div>

          </div>
        </main>
  );
}
