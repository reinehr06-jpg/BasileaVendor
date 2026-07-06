/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: Documentos e Recibos
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /configuracoes/documentos
 * 📁 ARQUIVO: src/app/(menu)/configuracoes/documentos/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Personalize os modelos de emissão, assinaturas e padrões de impressão.
 *
 * ⚙️ INTEGRAÇÃO BACKEND (GUIA PARA O DEV):
 *    - GET /api/tenant/documents -> Puxa layout do recibo, footer text, assinatura digital URL.
 *    - PUT /api/tenant/documents -> Salva texto do rodapé e prefixo.
 *    - POST /api/tenant/documents/signature -> Upload multipart/form-data da assinatura PNG.
 * 
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { ArrowLeft, Save, PenTool, Printer, FileCheck } from "lucide-react";
import CustomSelect from "@/components/CustomSelect";

export default function DocumentosPage() {
  const [formatoImpressao, setFormatoImpressao] = useState("a4");
  const [mensagemRodape, setMensagemRodape] = useState("Deus abençoe a sua oferta. Este documento tem validade de recibo financeiro.");

  const handleSalvar = () => {
    alert("Backend: Salvar configuração de Documentos via PUT");
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
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Documentos e Recibos</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Personalize os modelos de emissão, assinaturas e padrões de impressão.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button onClick={handleSalvar} className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Salvar Alterações
              </button>
            </div>
          </div>

          <div className="bg-white border border-[#E5E7EB] shadow-sm rounded-[12px] p-6 flex flex-col gap-[32px]">
             
             {/* ✍️ ASSINATURA DIGITAL */}
             <div>
               <div className="flex items-center gap-[12px] mb-[16px]">
                 <div className="w-[32px] h-[32px] bg-[#F3E8FF] rounded-[8px] flex items-center justify-center text-[#6D28D9]">
                   <PenTool className="w-[16px] h-[16px]" />
                 </div>
                 <h3 className="text-[16px] font-[600] text-[#1A1A2E]">Assinatura Padrão</h3>
               </div>
               
               <div className="flex items-center justify-between p-[24px] border border-[#E5E7EB] rounded-[12px] bg-[#F9FAFB]">
                 <div className="flex flex-col gap-[4px]">
                   <p className="text-[14px] font-[600] text-[#1A1A2E]">Assinatura do Tesoureiro / Pastor</p>
                   <p className="text-[13px] text-[#6B7280]">Imagem (PNG transparente) que sairá impressa no rodapé dos recibos.</p>
                 </div>
                 {/* BACKEND: POST /api/tenant/documents/signature */}
                 <button onClick={() => alert("Backend: Upload de PNG de assinatura")} className="bg-white border border-[#E5E7EB] hover:bg-[#F3F4F6] text-[#374151] px-[16px] py-[8px] rounded-[8px] text-[13px] font-[600] transition-colors">
                   Anexar Imagem
                 </button>
               </div>
             </div>

             {/* 🖨️ IMPRESSÃO E FORMATOS */}
             <div className="pt-[32px] border-t border-[#F1F1F4]">
               <div className="flex items-center gap-[12px] mb-[16px]">
                 <div className="w-[32px] h-[32px] bg-[#F3F4F6] border border-[#E5E7EB] rounded-[8px] flex items-center justify-center text-[#6B7280]">
                   <Printer className="w-[16px] h-[16px]" />
                 </div>
                 <h3 className="text-[16px] font-[600] text-[#1A1A2E]">Impressão e Textos</h3>
               </div>
               
               <div className="grid grid-cols-2 gap-[24px]">
                 <div className="col-span-2 md:col-span-1 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Formato de Impressão</label>
                   <CustomSelect 
                     value={formatoImpressao} 
                     onChange={setFormatoImpressao} 
                     searchable={false}
                     className="h-[42px]"
                     options={[
                       { value: "a4", label: "Papel A4 (Recibo Inteiro ou Meia Folha)" },
                       { value: "termica", label: "Bobina Térmica (58mm/80mm)" },
                     ]} 
                     placeholder="Selecione o formato" 
                   />
                 </div>
                 <div className="col-span-2 md:col-span-1 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Série do Recibo (Prefixo)</label>
                   <input type="text" placeholder="Ex: REC-" className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                 </div>
                 
                 <div className="col-span-2 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Mensagem de Rodapé</label>
                   <textarea 
                     value={mensagemRodape}
                     onChange={(e) => setMensagemRodape(e.target.value)}
                     rows={3} 
                     className="w-full border border-[#E5E7EB] rounded-[8px] p-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all resize-none"
                   />
                   <p className="text-[12px] text-[#9CA3AF]">Este texto aparecerá no final de todos os recibos emitidos pelo sistema.</p>
                 </div>
               </div>

               {/* BACKEND: Atualiza flag 'auto_receipt' na tabela do tenant */}
               <div className="mt-[24px] flex items-center justify-between p-[16px] bg-[#F3E8FF]/50 border border-[#6D28D9]/20 rounded-[12px]">
                 <div className="flex items-center gap-[12px]">
                   <FileCheck className="w-[20px] h-[20px] text-[#6D28D9]" />
                   <div>
                     <p className="text-[14px] font-[600] text-[#1A1A2E]">Gerar recibos automaticamente</p>
                     <p className="text-[13px] text-[#6B7280]">Ao aprovar um recebimento, o PDF já fica disponível no histórico.</p>
                   </div>
                 </div>
                 <div className="relative inline-block w-[40px] h-[24px] shrink-0 cursor-pointer" onClick={() => alert("Backend: Alterar flag auto_receipt no DB")}>
                   <input type="checkbox" className="peer sr-only" id="auto-recibo" defaultChecked readOnly />
                   <div className="w-[40px] h-[24px] bg-[#E5E7EB] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[20px] after:w-[20px] after:transition-all peer-checked:bg-[#6D28D9]"></div>
                 </div>
               </div>
             </div>

          </div>

        </main>
      </div>
    </div>
  );
}
