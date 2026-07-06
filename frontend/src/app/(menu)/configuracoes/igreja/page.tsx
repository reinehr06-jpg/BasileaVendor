/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: Dados da Igreja
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /configuracoes/igreja
 * 📁 ARQUIVO: src/app/(menu)/configuracoes/igreja/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Informações gerais da instituição e contatos (Tenant Sede).
 *
 * ⚙️ INTEGRAÇÃO BACKEND (GUIA PARA O DEV):
 *    - GET /api/tenant -> Busca os dados da igreja sede (razão social, cnpj, endereço, logo).
 *    - PUT /api/tenant -> Atualiza os dados cadastrais e de endereço.
 *    - POST /api/tenant/logo -> Upload da imagem da logomarca (multipart/form-data).
 * 
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { ArrowLeft, Building2, Mail, Phone, MapPin, Upload, Save, FileText } from "lucide-react";

export default function IgrejaPage() {
  // 🗄️ ESTADO LOCAL
  // BACKEND: Carregar isso no useEffect vindo de GET /api/tenant
  const [formData, setFormData] = useState({
    razaoSocial: "Igreja Evangélica Basiléia",
    nomeFantasia: "Igreja Basiléia Sede",
    cnpj: "12.345.678/0001-90",
    telefone: "(11) 3214-5678",
    email: "contato@igrejabasileia.com",
    cep: "01001-000",
    endereco: "Praça da Sé, Centro",
    numero: "100",
    cidade: "São Paulo - SP"
  });

  // 🖱️ AÇÃO: Salvar
  const handleSalvar = () => {
    alert("Backend: PUT /api/tenant com os dados do formulário");
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
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Dados da Instituição</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Informações cadastrais, logotipo e endereço da Igreja Sede.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button onClick={handleSalvar} className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Salvar Dados
              </button>
            </div>
          </div>

          <div className="bg-white border border-[#E5E7EB] shadow-sm rounded-[12px] p-6 flex flex-col gap-[32px]">
             
             {/* 🖼️ LOGOTIPO */}
             <div className="flex items-center gap-[24px] pb-[32px] border-b border-[#F1F1F4]">
               <div className="w-[80px] h-[80px] border border-[#E5E7EB] rounded-[12px] flex items-center justify-center bg-[#F9FAFB]">
                 <Building2 className="w-[32px] h-[32px] text-[#D1D5DB]" />
               </div>
               <div className="flex flex-col gap-[8px]">
                 <h3 className="text-[15px] font-[600] text-[#1A1A2E]">Logotipo da Igreja</h3>
                 <p className="text-[13px] text-[#6B7280]">Este logo aparecerá nos recibos e relatórios. Fundo transparente (PNG).</p>
                 <div className="flex items-center gap-[12px] mt-[4px]">
                   {/* Botão Enviar Logo -> BACKEND: POST /api/tenant/logo */}
                   <button onClick={() => alert("Backend: POST /api/tenant/logo")} className="px-[16px] py-[8px] bg-white border border-[#E5E7EB] hover:bg-[#F9FAFB] rounded-[8px] text-[13px] font-[600] text-[#374151] flex items-center gap-[8px] transition-colors">
                     <Upload className="w-[14px] h-[14px]" />
                     Enviar Logo
                   </button>
                 </div>
               </div>
             </div>

             {/* 📝 DADOS CADASTRAIS */}
             <div>
               <h3 className="text-[15px] font-[600] text-[#1A1A2E] mb-[16px]">Dados Principais</h3>
               <div className="grid grid-cols-2 gap-[20px]">
                 <div className="col-span-2 md:col-span-1 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Razão Social</label>
                   <input type="text" value={formData.razaoSocial} onChange={(e) => setFormData({...formData, razaoSocial: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                 </div>
                 <div className="col-span-2 md:col-span-1 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Nome Fantasia</label>
                   <input type="text" value={formData.nomeFantasia} onChange={(e) => setFormData({...formData, nomeFantasia: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                 </div>
                 <div className="col-span-2 md:col-span-1 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">CNPJ</label>
                   <div className="relative">
                     <input type="text" value={formData.cnpj} onChange={(e) => setFormData({...formData, cnpj: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] pl-[40px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                     <FileText className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-[16px] top-1/2 -translate-y-1/2" />
                   </div>
                 </div>
                 <div className="col-span-2 md:col-span-1 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">E-mail</label>
                   <div className="relative">
                     <input type="email" value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] pl-[40px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                     <Mail className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-[16px] top-1/2 -translate-y-1/2" />
                   </div>
                 </div>
                 <div className="col-span-2 md:col-span-1 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Telefone</label>
                   <div className="relative">
                     <input type="text" value={formData.telefone} onChange={(e) => setFormData({...formData, telefone: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] pl-[40px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                     <Phone className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-[16px] top-1/2 -translate-y-1/2" />
                   </div>
                 </div>
               </div>
             </div>

             {/* 📍 ENDEREÇO */}
             <div className="pt-[32px] border-t border-[#F1F1F4]">
               <h3 className="text-[15px] font-[600] text-[#1A1A2E] mb-[16px]">Endereço</h3>
               <div className="grid grid-cols-6 gap-[20px]">
                 <div className="col-span-6 md:col-span-2 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">CEP</label>
                   {/* BACKEND: Recomenda-se integrar a API do ViaCEP no onBlur ou useEffect quando CEP atinge 8 dígitos */}
                   <input type="text" value={formData.cep} onChange={(e) => setFormData({...formData, cep: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                 </div>
                 <div className="col-span-6 md:col-span-4 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Endereço / Logradouro</label>
                   <div className="relative">
                     <input type="text" value={formData.endereco} onChange={(e) => setFormData({...formData, endereco: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] pl-[40px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                     <MapPin className="w-[16px] h-[16px] text-[#9CA3AF] absolute left-[16px] top-1/2 -translate-y-1/2" />
                   </div>
                 </div>
                 <div className="col-span-6 md:col-span-2 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Número / Complemento</label>
                   <input type="text" value={formData.numero} onChange={(e) => setFormData({...formData, numero: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                 </div>
                 <div className="col-span-6 md:col-span-4 flex flex-col gap-[8px]">
                   <label className="text-[13px] font-[600] text-[#374151]">Cidade / Estado</label>
                   <input type="text" value={formData.cidade} onChange={(e) => setFormData({...formData, cidade: e.target.value})} className="w-full h-[42px] border border-[#E5E7EB] rounded-[8px] px-[16px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all bg-[#F9FAFB]" disabled />
                 </div>
               </div>
             </div>

          </div>

        </main>
      </div>
    </div>
  );
}
