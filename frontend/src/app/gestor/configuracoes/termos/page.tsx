"use client";

import React from "react";
import Link from "next/link";
import { ArrowLeft, Download, FileText } from "lucide-react";
import { useTranslation } from "react-i18next";
import { toast } from "sonner";

export default function VendedorTermosPage() {
  const { t } = useTranslation();

  const handleDownloadPDF = () => {
    toast.success(t("O download do contrato PDF foi iniciado."));
  };

  return (
    <main className="p-4 flex-1 flex flex-col w-full max-w-[1200px] mx-auto gap-4 overflow-y-auto custom-scrollbar">
          
          {/* 🏷️ CABEÇALHO PADRÃO */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
            <div className="flex items-center gap-3">
              <Link href="/gestor/configuracoes" className="w-[40px] h-[40px] rounded-[10px] bg-[#F3F4F6] hover:bg-[#E5E7EB] flex items-center justify-center shrink-0 transition-colors">
                <ArrowLeft className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
              </Link>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Termos de Uso</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Leia as diretrizes e regras do programa de parceria comercial.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button 
                onClick={handleDownloadPDF}
                className="hidden sm:flex items-center gap-[8px] h-[40px] px-[20px] bg-white border border-[#D1D5DB] text-[#374151] text-[13px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors"
              >
                <Download className="w-[16px] h-[16px]" />
                {t("Baixar PDF")}
              </button>
            </div>
          </div>

          <div className="flex flex-col gap-[24px]">

            {/* CARD PRINCIPAL (SCROLL DE TEXTO) */}
            <div className="bg-white border border-[#E5E7EB] rounded-[16px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] p-[32px] flex flex-col flex-1 min-h-[500px]">
              
              <div className="flex items-center gap-[16px] pb-[20px] border-b border-[#F3F4F6] shrink-0">
                <div className="w-[48px] h-[48px] rounded-full bg-[#F4EEFF] flex items-center justify-center shrink-0">
                  <FileText className="w-[24px] h-[24px] text-[#7C3AED]" />
                </div>
                <div className="flex flex-col">
                  <h2 className="text-[16px] font-[700] text-[#111827]">{t("Contrato de Parceria Comercial")}</h2>
                  <span className="text-[13px] text-[#6B7280]">{t("Última atualização: 01 de Junho de 2026")}</span>
                </div>
                
                {/* Botão Mobile */}
                <button 
                  onClick={handleDownloadPDF}
                  className="sm:hidden ml-auto flex items-center justify-center w-[40px] h-[40px] bg-white border border-[#D1D5DB] text-[#374151] rounded-[8px] hover:bg-[#F9FAFB]"
                >
                  <Download className="w-[18px] h-[18px]" />
                </button>
              </div>

              <div className="mt-[24px] flex-1 overflow-y-auto pr-[16px] custom-scrollbar text-[14px] leading-relaxed text-[#4B5563]">
                <h3 className="text-[16px] font-[700] text-[#111827] mb-[12px]">{t("1. Aceitação dos Termos")}</h3>
                <p className="mb-[20px]">
                  {t("Ao utilizar o sistema Basileia Vendor OS, o vendedor concorda com todos os termos descritos neste documento. A concordância é um requisito essencial para a continuidade da parceria comercial.")}
                </p>

                <h3 className="text-[16px] font-[700] text-[#111827] mb-[12px]">{t("2. Comissionamento")}</h3>
                <p className="mb-[20px]">
                  {t("As comissões serão repassadas automaticamente através do Split de Pagamentos, desde que o Wallet ID (Asaas) esteja configurado corretamente na aba apropriada. Em caso de estorno pelo cliente final, a comissão correspondente será estornada do próximo repasse.")}
                </p>

                <h3 className="text-[16px] font-[700] text-[#111827] mb-[12px]">{t("3. Confidencialidade")}</h3>
                <p className="mb-[20px]">
                  {t("O parceiro comercial compromete-se a manter em sigilo absoluto todas as informações de clientes, valores, métricas de negócio e dados do sistema, sob pena de bloqueio imediato do acesso e medidas legais cabíveis.")}
                </p>

                <h3 className="text-[16px] font-[700] text-[#111827] mb-[12px]">{t("4. Rescisão")}</h3>
                <p className="mb-[20px]">
                  {t("Qualquer uma das partes poderá rescindir esta parceria mediante aviso prévio formal. Após a rescisão, as comissões pendentes geradas até a data limite serão devidamente pagas no ciclo seguinte.")}
                </p>
                
                <p className="mt-[40px] italic text-[13px]">
                  {t("Este é um documento de demonstração para o protótipo do sistema.")}
                </p>
              </div>

            </div>

          </div>
        </main>
  );
}
