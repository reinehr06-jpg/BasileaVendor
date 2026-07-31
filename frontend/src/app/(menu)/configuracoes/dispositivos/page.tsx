"use client";
import React from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import { Smartphone, Plus, ArrowLeft } from "lucide-react";

export default function DispositivosPage() {
  const { t } = useTranslation();

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />
        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          <div className="w-full flex flex-col max-w-[1000px] mx-auto">
            <Link 
              href="/configuracoes"
              className="flex items-center gap-[8px] text-[14px] font-[600] text-[#6B7280] hover:text-[#111827] transition-colors w-fit mb-[16px]"
            >
              <ArrowLeft className="w-[16px] h-[16px]" />
              {t("Voltar para Configurações")}
            </Link>

            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-[16px] mb-[24px]">
              <div className="flex items-center gap-[12px]">
                <div className="w-[36px] h-[36px] rounded-[10px] bg-white border border-[#E5E7EB] shadow-sm flex items-center justify-center shrink-0">
                  <Smartphone className="w-[18px] h-[18px] text-[#6B7280]" strokeWidth={2.2} />
                </div>
                <h1 className="text-[22px] font-[700] text-[#1A1A2E] leading-tight">
                  {t("Meus Dispositivos")}
                </h1>
              </div>
              <Link 
                href="/configuracoes/dispositivos/novo"
                className="flex items-center gap-[6px] px-[18px] py-[10px] bg-[#6D28D9] text-white text-[13px] font-[600] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm uppercase tracking-wide shrink-0"
              >
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.4} />
                {t("NOVO DISPOSITIVO")}
              </Link>
            </div>

            <div className="bg-white rounded-[18px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 p-8 items-center justify-center">
              <Smartphone className="w-[48px] h-[48px] text-[#D1D5DB] mb-3" strokeWidth={1.5} />
              <h3 className="text-[15px] font-[700] text-[#111827] mb-1">Nenhum dispositivo cadastrado</h3>
              <p className="text-[13px] text-[#6B7280] text-center max-w-[400px]">
                Adicione um novo dispositivo para usar a autenticação de 2 fatores e acessar o sistema administrativo pelo celular.
              </p>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
