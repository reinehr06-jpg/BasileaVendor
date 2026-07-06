"use client";

import React, { useState } from "react";
import Link from "next/link";
import { ArrowLeft, Save, Wallet } from "lucide-react";
import { useTranslation } from "react-i18next";
import { toast } from "sonner";

export default function VendedorSplitPage() {
  const { t } = useTranslation();
  const [nome, setNome] = useState("");
  const [walletId, setWalletId] = useState("");

  const handleSave = () => {
    if (!walletId.trim()) {
      toast.error(t("Preencha o Wallet ID do Asaas."));
      return;
    }
    toast.success(t("Wallet ID salvo com sucesso! O comissionamento automático foi configurado."));
  };

  return (
    <main className="p-4 flex-1 flex flex-col w-full max-w-[1200px] mx-auto gap-4 overflow-y-auto custom-scrollbar">
          
          {/* 🏷️ CABEÇALHO PADRÃO */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
            <div className="flex items-center gap-3">
              <Link href="/vendedor/configuracoes" className="w-[40px] h-[40px] rounded-[10px] bg-[#F3F4F6] hover:bg-[#E5E7EB] flex items-center justify-center shrink-0 transition-colors">
                <ArrowLeft className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
              </Link>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Split Asaas</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Configure o recebimento automático das suas comissões via Asaas.</p>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-[24px]">

            {/* CARD PRINCIPAL */}
            <div className="bg-white border border-[#E5E7EB] rounded-[16px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[32px] flex flex-col gap-[24px]">
              
              <div className="flex items-center gap-[16px] pb-[20px] border-b border-[#F3F4F6]">
                <div className="w-[48px] h-[48px] rounded-full bg-[#ECFDF5] flex items-center justify-center shrink-0">
                  <Wallet className="w-[24px] h-[24px] text-[#059669]" />
                </div>
                <div className="flex flex-col">
                  <h2 className="text-[16px] font-[700] text-[#111827]">{t("Integração de Comissionamento")}</h2>
                  <span className="text-[13px] text-[#6B7280]">{t("As comissões das vendas serão enviadas diretamente para a sua conta Asaas.")}</span>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                <div className="flex flex-col gap-[6px]">
                  <label className="text-[13px] font-[600] text-[#374151]">{t("Nome da Carteira")}</label>
                  <input 
                    type="text" 
                    value={nome}
                    onChange={(e) => setNome(e.target.value)}
                    placeholder="Ex: Minha Conta Asaas"
                    className="h-[42px] px-[12px] bg-white border border-[#D1D5DB] rounded-[8px] text-[14px] text-[#111827] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all"
                  />
                </div>

                <div className="flex flex-col gap-[6px]">
                  <label className="text-[13px] font-[600] text-[#374151]">{t("Wallet ID Asaas")}</label>
                  <input 
                    type="text" 
                    value={walletId}
                    onChange={(e) => setWalletId(e.target.value)}
                    placeholder="Ex: wal_XXXXXXXXXXXXXX"
                    className="h-[42px] px-[12px] bg-white border border-[#D1D5DB] rounded-[8px] text-[14px] text-[#111827] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all font-mono"
                  />
                </div>
              </div>

              <div className="pt-[24px] border-t border-[#F3F4F6] flex justify-end">
                <button 
                  onClick={handleSave}
                  className="flex items-center gap-[8px] h-[40px] px-[20px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm"
                >
                  <Save className="w-[16px] h-[16px]" />
                  {t("Salvar Wallet ID")}
                </button>
              </div>

            </div>

          </div>
        </main>
  );
}
