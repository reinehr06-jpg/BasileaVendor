"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Building2, 
  Plus, 
  Search,
  MapPin,
  MoreVertical,
  Users
} from "lucide-react";

export default function FiliaisPage() {
  const mockFiliais = [
    { id: 1, nome: "Igreja Sede - Central", doc: "00.000.000/0001-00", local: "São Paulo, SP", contas: 3, responsaveis: 2, status: "Ativa" },
    { id: 2, nome: "Filial Zona Sul", doc: "00.000.000/0002-00", local: "São Paulo, SP", contas: 1, responsaveis: 1, status: "Ativa" },
    { id: 3, nome: "Missão Nordeste", doc: "-", local: "Recife, PE", contas: 1, responsaveis: 1, status: "Inativa" },
  ];

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1600px] mx-auto gap-4 overflow-hidden relative">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                <Building2 className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Filiais e Centros de Custo</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gerencie a estrutura de igrejas, filiais e seus respectivos responsáveis.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Link href="/configuracoes/filiais/nova" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Nova Filial
              </Link>
            </div>
          </div>

          {/* GRID SECTION */}
          <div className="flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-4 pb-10">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              
              {mockFiliais.map((filial) => (
                <div key={filial.id} className={`bg-white rounded-[12px] border ${filial.status === 'Ativa' ? 'border-[#E5E7EB] hover:border-[#C4B5FD]' : 'border-[#F3F4F6] opacity-75'} shadow-sm p-5 transition-colors relative group`}>
                  
                  <div className="absolute top-4 right-4 flex items-center gap-2">
                    <span className={`inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-[700] ${filial.status === 'Ativa' ? 'bg-[#ECFDF5] text-[#10B981]' : 'bg-[#F3F4F6] text-[#9CA3AF]'}`}>
                      {filial.status}
                    </span>
                    <button className="text-[#9CA3AF] hover:text-[#111827]">
                      <MoreVertical className="w-[16px] h-[16px]" />
                    </button>
                  </div>

                  <div className="flex items-center gap-3 mb-4">
                    <div className={`w-[48px] h-[48px] rounded-[10px] flex items-center justify-center shrink-0 ${filial.status === 'Ativa' ? 'bg-[#F5F3FF]' : 'bg-[#F9FAFB]'}`}>
                      <Building2 className={`w-[24px] h-[24px] ${filial.status === 'Ativa' ? 'text-[#8B5CF6]' : 'text-[#D1D5DB]'}`} strokeWidth={2} />
                    </div>
                    <div className="flex flex-col pr-12">
                      <h2 className="text-[16px] font-[800] text-[#111827] leading-tight truncate">{filial.nome}</h2>
                      <span className="text-[12px] text-[#6B7280] mt-0.5">CNPJ: {filial.doc}</span>
                    </div>
                  </div>

                  <div className="flex flex-col gap-2 mb-4">
                    <div className="flex items-center gap-2 text-[13px] text-[#4B5563]">
                      <MapPin className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      <span>{filial.local}</span>
                    </div>
                    <div className="flex items-center gap-2 text-[13px] text-[#4B5563]">
                      <Users className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      <span>{filial.responsaveis} Responsáveis financeiros</span>
                    </div>
                  </div>

                  <div className="border-t border-[#F1F1F4] pt-4 mt-2 flex items-center justify-between">
                    <span className="text-[12px] font-[600] text-[#6B7280]">{filial.contas} Contas vinculadas</span>
                    <button className="text-[12px] font-[700] text-[#6D28D9] hover:underline">Ver detalhes</button>
                  </div>
                </div>
              ))}

            </div>
          </div>
          
        </main>
      </div>
    </div>
  );
}
