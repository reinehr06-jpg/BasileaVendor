"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import { FileSpreadsheet, Save, Eye } from "lucide-react";

export default function ModelosDocumentosPage() {
  const { t } = useTranslation();

  const [activeTab, setActiveTab] = useState<"recibo" | "prebenda">("recibo");

  // Mock states for templates
  const [reciboTemplate, setReciboTemplate] = useState(
    "Eu, [NOME_RECEBEDOR], inscrito(a) no CPF/CNPJ sob o nº [CPF_CNPJ], declaro ter recebido da empresa [NOME_EMPRESA] a quantia de R$ [VALOR] ([VALOR_EXTENSO]) referente a [DESCRICAO_SERVICO].\n\nPor ser verdade, firmo o presente.\n\n[CIDADE], [DATA]\n\n_________________________________________________\nAssinatura"
  );

  const [prebendaTemplate, setPrebendaTemplate] = useState(
    "RECIBO DE PREBENDA PASTORAL\n\nIgreja/Instituição: [NOME_IGREJA]\nCNPJ: [CNPJ_IGREJA]\n\nRecebi da instituição acima mencionada, a importância de R$ [VALOR] ([VALOR_EXTENSO]), referente ao sustento pastoral (Prebenda) do mês de [MES_ANO].\n\nNome do Ministro: [NOME_PASTOR]\nCPF: [CPF_PASTOR]\n\n[CIDADE], [DATA]\n\n_________________________________________________\nAssinatura do Ministro"
  );

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-6 flex-1 flex flex-col max-w-[1200px] mx-auto w-full gap-6 overflow-hidden">
          
          {/* HEADER */}
          <div className="flex items-center gap-[14px] shrink-0">
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
              <FileSpreadsheet className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">Modelos de Documentos</h1>
              <p className="text-[13px] font-[500] text-[#6B7280] mt-0.5">Configure os modelos padrão para impressão de recibos e prebendas.</p>
            </div>
          </div>

          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden">
            
            {/* TABS */}
            <div className="flex border-b border-[#E5E7EB] p-4 gap-2 bg-[#F9FAFB]">
              <button 
                onClick={() => setActiveTab("recibo")}
                className={`px-4 py-2 rounded-[8px] text-[13px] font-[600] transition-colors ${activeTab === "recibo" ? "bg-white text-[#7C3AED] shadow-sm border border-[#E5E7EB]" : "text-[#4B5563] hover:bg-[#F3F4F6]"}`}
              >
                Modelo de Recibo
              </button>
              <button 
                onClick={() => setActiveTab("prebenda")}
                className={`px-4 py-2 rounded-[8px] text-[13px] font-[600] transition-colors ${activeTab === "prebenda" ? "bg-white text-[#7C3AED] shadow-sm border border-[#E5E7EB]" : "text-[#4B5563] hover:bg-[#F3F4F6]"}`}
              >
                Modelo de Prebenda
              </button>
            </div>

            {/* EDITOR */}
            <div className="flex flex-col flex-1 p-6 overflow-y-auto">
              <div className="flex justify-between items-end mb-4">
                <div className="flex flex-col gap-1">
                  <h2 className="text-[16px] font-[700] text-[#1A1A2E]">
                    {activeTab === "recibo" ? "Corpo do Recibo Padrão" : "Corpo da Prebenda Pastoral"}
                  </h2>
                  <p className="text-[13px] text-[#6B7280]">
                    Você pode utilizar as tags dinâmicas como <code className="bg-[#F3F4F6] px-1 rounded text-[#7C3AED]">[VALOR]</code> para preenchimento automático.
                  </p>
                </div>
                <div className="flex gap-2">
                  <button className="flex items-center gap-2 px-4 py-2 bg-[#F3F4F6] text-[#4B5563] rounded-[8px] text-[13px] font-[600] hover:bg-[#E5E7EB] transition-colors">
                    <Eye className="w-[14px] h-[14px]" /> Pré-visualizar
                  </button>
                  <button className="flex items-center gap-2 px-4 py-2 bg-[#7C3AED] text-white rounded-[8px] text-[13px] font-[600] hover:bg-[#6D28D9] transition-colors shadow-sm">
                    <Save className="w-[14px] h-[14px]" /> Salvar Modelo
                  </button>
                </div>
              </div>

              <div className="flex-1 flex flex-col">
                <textarea 
                  value={activeTab === "recibo" ? reciboTemplate : prebendaTemplate}
                  onChange={(e) => activeTab === "recibo" ? setReciboTemplate(e.target.value) : setPrebendaTemplate(e.target.value)}
                  className="w-full flex-1 border border-[#E5E7EB] rounded-[12px] p-4 text-[14px] font-mono text-[#374151] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all resize-none leading-relaxed"
                />
              </div>

              {/* TAGS GUIDE */}
              <div className="mt-6 bg-[#F8FAFC] border border-[#E2E8F0] rounded-[12px] p-4">
                <h3 className="text-[13px] font-[700] text-[#475569] mb-3">Tags Disponíveis para Substituição Automática:</h3>
                <div className="flex flex-wrap gap-2">
                  {activeTab === "recibo" ? (
                    <>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[NOME_RECEBEDOR]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[CPF_CNPJ]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[NOME_EMPRESA]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[VALOR]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[VALOR_EXTENSO]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[DESCRICAO_SERVICO]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[DATA]</span>
                    </>
                  ) : (
                    <>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[NOME_IGREJA]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[CNPJ_IGREJA]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[VALOR]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[VALOR_EXTENSO]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[NOME_PASTOR]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[CPF_PASTOR]</span>
                      <span className="text-[12px] bg-white border border-[#E2E8F0] px-2 py-1 rounded text-[#64748B] font-mono">[MES_ANO]</span>
                    </>
                  )}
                </div>
              </div>

            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
