/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: Importação e Exportação
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /configuracoes/importacao
 * 📁 ARQUIVO: src/app/(menu)/configuracoes/importacao/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Configure padrões de arquivos CSV, XML e OFX e exportação.
 *
 * ⚙️ INTEGRAÇÃO BACKEND (GUIA PARA O DEV):
 *    - GET /api/tenant/preferences/integration -> Puxa os formatos padrões salvos.
 *    - PUT /api/tenant/preferences/integration -> Salva o formato OFX/CSV default.
 * 
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { ArrowLeft, Save, UploadCloud, DownloadCloud, FileSpreadsheet } from "lucide-react";
import CustomSelect from "@/components/CustomSelect";

export default function ImportacaoPage() {
  // 🗄️ ESTADOS
  const [padraoOFX, setPadraoOFX] = useState("auto");
  const [padraoCSV, setPadraoCSV] = useState("banco_brasil");
  const [exportacaoContabil, setExportacaoContabil] = useState("dominio");

  // 🖱️ AÇÃO: Salvar
  const handleSalvar = () => {
    alert("Backend: PUT /api/tenant/preferences/integration");
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1200px] mx-auto gap-4 overflow-y-auto custom-scrollbar">
          
          {/* 🏷️ CABEÇALHO */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <Link href="/configuracoes" className="w-[40px] h-[40px] rounded-[10px] bg-[#F3F4F6] hover:bg-[#E5E7EB] flex items-center justify-center shrink-0 transition-colors">
                <ArrowLeft className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
              </Link>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Importação e Exportação</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Configure padrões de leitura de extratos (OFX/CSV) e exportação para contabilidade.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button onClick={handleSalvar} className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Salvar Preferências
              </button>
            </div>
          </div>

          <div className="bg-white border border-[#E5E7EB] shadow-sm rounded-[12px] p-6 flex flex-col gap-[32px]">
             
             {/* 📥 IMPORTAÇÃO DE EXTRATOS (OFX / CSV) */}
             <div>
               <div className="flex items-center gap-[12px] mb-[16px]">
                 <div className="w-[32px] h-[32px] bg-[#F3E8FF] rounded-[8px] flex items-center justify-center text-[#6D28D9]">
                   <UploadCloud className="w-[16px] h-[16px]" />
                 </div>
                 <h3 className="text-[16px] font-[600] text-[#1A1A2E]">Importação Bancária</h3>
               </div>
               
               {/* BACKEND: Bind dos selects com a tabela de tenant_preferences */}
               <div className="grid grid-cols-2 gap-[24px]">
                 <div className="col-span-2 md:col-span-1">
                   <label className="text-[13px] font-[600] text-[#374151] mb-[6px] block">Padrão de Leitura OFX</label>
                   <CustomSelect 
                     value={padraoOFX} 
                     onChange={setPadraoOFX} 
                     searchable={false}
                     className="h-[42px]"
                     options={[
                       { value: "auto", label: "Automático (Recomendado)" },
                       { value: "itau", label: "Formato Itaú" },
                       { value: "bradesco", label: "Formato Bradesco" },
                     ]} 
                     placeholder="Selecione o padrão OFX" 
                   />
                   <p className="text-[12px] text-[#6B7280] mt-[6px]">A IA tentará identificar as colunas se estiver Automático.</p>
                 </div>
                 <div className="col-span-2 md:col-span-1">
                   <label className="text-[13px] font-[600] text-[#374151] mb-[6px] block">Padrão de Planilha CSV</label>
                   <CustomSelect 
                     value={padraoCSV} 
                     onChange={setPadraoCSV} 
                     searchable={false}
                     className="h-[42px]"
                     options={[
                       { value: "banco_brasil", label: "Banco do Brasil" },
                       { value: "nubank", label: "Nubank" },
                       { value: "sicredi", label: "Sicredi" },
                     ]} 
                     placeholder="Selecione o padrão CSV" 
                   />
                 </div>
               </div>

               <div className="mt-[20px] flex items-center gap-[12px]">
                 <input type="checkbox" id="auto-conciliar" className="w-[16px] h-[16px] rounded border-[#D1D5DB] text-[#6D28D9] focus:ring-[#6D28D9]" defaultChecked />
                 <label htmlFor="auto-conciliar" className="text-[14px] text-[#374151] cursor-pointer">Auto-conciliar lançamentos idênticos via IA durante a importação</label>
               </div>
             </div>

             {/* 📤 EXPORTAÇÃO CONTÁBIL */}
             <div className="pt-[32px] border-t border-[#F1F1F4]">
               <div className="flex items-center gap-[12px] mb-[16px]">
                 <div className="w-[32px] h-[32px] bg-[#F3F4F6] border border-[#E5E7EB] rounded-[8px] flex items-center justify-center text-[#6B7280]">
                   <DownloadCloud className="w-[16px] h-[16px]" />
                 </div>
                 <h3 className="text-[16px] font-[600] text-[#1A1A2E]">Exportação para Contabilidade</h3>
               </div>
               
               <div className="grid grid-cols-2 gap-[24px]">
                 <div className="col-span-2 md:col-span-1">
                   <label className="text-[13px] font-[600] text-[#374151] mb-[6px] block">Sistema Contábil</label>
                   <CustomSelect 
                     value={exportacaoContabil} 
                     onChange={setExportacaoContabil} 
                     searchable={false}
                     className="h-[42px]"
                     options={[
                       { value: "dominio", label: "Domínio Sistemas" },
                       { value: "alterdata", label: "Alterdata" },
                       { value: "fortes", label: "Fortes Tecnologia" },
                       { value: "csv", label: "Planilha CSV Genérica" },
                     ]} 
                     placeholder="Selecione o sistema" 
                   />
                 </div>
               </div>

               <div className="mt-[24px] bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#E5E7EB] flex items-start gap-[12px]">
                 <FileSpreadsheet className="w-[20px] h-[20px] text-[#6B7280] shrink-0 mt-[2px]" />
                 <div>
                   <h4 className="text-[14px] font-[600] text-[#1A1A2E]">Mapeamento do Plano de Contas</h4>
                   <p className="text-[13px] text-[#6B7280] mt-[4px] leading-[1.5]">Para a exportação funcionar corretamente, você precisa garantir que cada Categoria Financeira do Basiléia tenha o Código Contábil correspondente cadastrado.</p>
                   <Link href="/cadastros/categorias" className="text-[#6D28D9] font-[600] text-[13px] mt-[8px] inline-block hover:underline">
                     Ir para Categorias
                   </Link>
                 </div>
               </div>
             </div>

          </div>

        </main>
      </div>
    </div>
  );
}
