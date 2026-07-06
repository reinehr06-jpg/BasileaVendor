/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: CENTROS DE CUSTO
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /centros-de-custo
 * 📁 ARQUIVO: src/app/centros-de-custo/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Organizar e visualizar os centros de custo (projetos, congregações, departamentos).
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/centros-de-custo?page=1&limit=10 → Lista de centros de custo
 *    2. POST /api/centros-de-custo → Criar novo centro de custo
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { 
  Building2, Plus, Search, MoreVertical, Layers, Filter
} from "lucide-react";

export default function CentrosDeCustoPage() {
  const [statusFilter, setStatusFilter] = useState("");

  const mockCentros = [
    { id: 1, nome: "Sede Administrativa", codigo: "ADM-01", responsavel: "Pr. Marcos", orcamento: "R$ 15.000,00", status: "Ativo" },
    { id: 2, nome: "Ministério de Jovens", codigo: "MIN-JOV", responsavel: "Lucas Almeida", orcamento: "R$ 2.500,00", status: "Ativo" },
    { id: 3, nome: "Construção Novo Templo", codigo: "OBRA-01", responsavel: "Pr. Marcos", orcamento: "R$ 50.000,00", status: "Em Progresso" },
  ];

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1400px] mx-auto gap-4 overflow-hidden relative">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#E0F2FE] flex items-center justify-center shrink-0">
                <Building2 className="w-[20px] h-[20px] text-[#0284C7]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Centros de Custo</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Organize despesas e orçamentos por projetos, departamentos ou ministérios.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Link href="/contabilidade/centros-de-custo/nova" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Novo Centro de Custo
              </Link>
            </div>
          </div>

          {/* FILTERS ROW */}
          <div className="flex items-center gap-3 shrink-0 bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-3 mb-4">
            <div className="flex items-center gap-2 px-2 border-r border-[#E5E7EB] shrink-0">
              <Filter className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.5} />
              <h2 className="text-[13px] font-[700] text-[#1A1A2E] pr-2">Filtros</h2>
            </div>
            
            <div className="flex-1 flex items-center gap-3">
              <div className="relative w-full max-w-[300px]">
                <input type="text" placeholder="Buscar centro de custo..." className="w-full h-[36px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[13px] text-[#111827] placeholder-[#9CA3AF] outline-none hover:border-[#D1D5DB] focus:bg-white focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
                <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-[12px] top-1/2 -translate-y-1/2" strokeWidth={2.5} />
              </div>

              <div className="w-[160px]">
                <CustomSelect 
                  options={[
                    {label: "Status: Todos", value: ""},
                    {label: "Ativo", value: "ativo"},
                    {label: "Inativo", value: "inativo"}
                  ]}
                  value={statusFilter}
                  onChange={setStatusFilter}
                  placeholder="Status"
                  className="h-[36px]"
                />
              </div>
            </div>

            <button className="h-[36px] px-4 rounded-[8px] text-[12px] font-[600] text-[#6B7280] hover:text-[#1A1A2E] hover:bg-[#F3F4F6] transition-colors shrink-0 border border-transparent hover:border-[#E5E7EB]">
              Limpar
            </button>
          </div>

          <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">

            <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Nome / Departamento</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Código</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Responsável</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Orçamento Anual Mensal</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Status</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {mockCentros.map((item) => (
                    <tr key={item.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                      <td className="py-4 px-5">
                        <div className="flex items-center gap-3">
                          <div className="w-[36px] h-[36px] rounded-[8px] bg-[#F3F4F6] flex items-center justify-center shrink-0">
                            <Layers className="w-[16px] h-[16px] text-[#6B7280]" />
                          </div>
                          <span className="text-[13px] font-[700] text-[#111827]">{item.nome}</span>
                        </div>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[12px] font-[600] text-[#4B5563] bg-[#F3F4F6] px-2 py-1 rounded-[6px]">{item.codigo}</span>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[13px] font-[600] text-[#374151]">{item.responsavel}</span>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[13px] font-[700] text-[#0284C7]">{item.orcamento}</span>
                      </td>
                      <td className="py-4 px-5 text-center">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-[700] ${item.status === 'Ativo' ? 'bg-[#ECFDF5] text-[#10B981]' : 'bg-[#FEF3C7] text-[#D97706]'}`}>
                          {item.status}
                        </span>
                      </td>
                      <td className="py-4 px-5 text-right">
                        <div className="flex items-center justify-end gap-2">
                          <Link href="/contabilidade/centros-de-custo/1" className="w-[32px] h-[32px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#6D28D9] hover:bg-[#F3E8FF] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                          </Link>
                          <button className="w-[32px] h-[32px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#111827] hover:bg-[#E5E7EB] transition-colors">
                            <MoreVertical className="w-[16px] h-[16px]" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
          
        </main>
      </div>
    </div>
  );
}
