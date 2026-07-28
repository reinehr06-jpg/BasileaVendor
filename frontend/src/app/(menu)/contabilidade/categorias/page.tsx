/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: CATEGORIAS FINANCEIRAS
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /categorias
 * 📁 ARQUIVO: src/app/categorias/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Gerenciar as categorias e subcategorias usadas para classificar receitas e despesas.
 *    (Plano de Contas Gerencial).
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/categorias?page=1&limit=10 → Lista de categorias
 *    2. POST /api/categorias → Criar nova categoria
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
  Tags, Plus, Filter, Search, ChevronLeft, ChevronRight, Pencil, Trash2
} from "lucide-react";

export default function CategoriasPage() {
  const [statusFilter, setStatusFilter] = useState("");
  const [tipoFilter, setTipoFilter] = useState("");

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1600px] mx-auto gap-4 overflow-hidden">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-full bg-[#FEF3C7] flex items-center justify-center shrink-0">
                <Tags className="w-[20px] h-[20px] text-[#F59E0B]" strokeWidth={2.5} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Categorias</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gerenciamento de Categorias para Receitas e Despesas</p>
              </div>
            </div>
            <Link href="/contabilidade/categorias/nova" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[12px] font-[600] flex items-center gap-2 shadow-sm">
              <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
              NOVA CATEGORIA
            </Link>
          </div>

          {/* FILTERS ROW */}
          <div className="flex items-center gap-3 shrink-0 bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-3">
            <div className="flex items-center gap-2 px-2 border-r border-[#E5E7EB] shrink-0">
              <Filter className="w-[16px] h-[16px] text-[#6D28D9]" strokeWidth={2.5} />
              <h2 className="text-[13px] font-[700] text-[#1A1A2E] pr-2">Filtros</h2>
            </div>
            
            <div className="flex-1 flex items-center gap-3">
              <div className="relative w-full max-w-[300px]">
                <input type="text" placeholder="Buscar por nome..." className="w-full h-[36px] bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] pl-[36px] pr-[12px] text-[13px] text-[#111827] placeholder-[#9CA3AF] outline-none hover:border-[#D1D5DB] focus:bg-white focus:border-[#6D28D9] focus:ring-1 focus:ring-[#6D28D9] transition-all" />
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

              <div className="w-[180px]">
                <CustomSelect 
                  options={[
                    {label: "Tipo: Todos", value: ""},
                    {label: "Receitas", value: "receita"},
                    {label: "Despesas", value: "despesa"}
                  ]}
                  value={tipoFilter}
                  onChange={setTipoFilter}
                  placeholder="Tipo"
                  className="h-[36px]"
                />
              </div>
            </div>

            <button className="h-[36px] px-4 rounded-[8px] text-[12px] font-[600] text-[#6B7280] hover:text-[#1A1A2E] hover:bg-[#F3F4F6] transition-colors shrink-0 border border-transparent hover:border-[#E5E7EB]">
              Limpar
            </button>
          </div>

          {/* TABLE SECTION */}
          <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">
            
            <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-white shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-4 px-4 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Nome</th>
                    <th className="py-4 px-4 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Tipo</th>
                    <th className="py-4 px-4 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Status</th>
                    <th className="py-4 px-4 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4]">Descrição</th>
                    <th className="py-4 px-4 text-[11px] font-[700] text-[#9CA3AF] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ação</th>
                  </tr>
                </thead>
                <tbody>
                  
                  {/* Row 1 - Oferta */}
                  <tr className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                    <td className="py-4 px-4">
                      <span className="text-[13px] font-[600] text-[#6D28D9]">Oferta</span>
                    </td>
                    <td className="py-4 px-4">
                      <span className="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-[700] bg-[#10B981] text-white">receita</span>
                    </td>
                    <td className="py-4 px-4">
                      <span className="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-[700] bg-[#10B981] text-white">ativo</span>
                    </td>
                    <td className="py-4 px-4">
                      <span className="text-[13px] text-[#6B7280]"></span>
                    </td>
                    <td className="py-4 px-4 text-right">
                      <div className="flex items-center justify-end gap-3">
                        <Link href="/contabilidade/categorias/1/editar" className="text-[#9CA3AF] hover:text-[#6D28D9] transition-colors">
                          <Pencil className="w-[16px] h-[16px]" strokeWidth={2} />
                        </Link>
                        <button className="text-[#9CA3AF] hover:text-[#EF4444] transition-colors">
                          <Trash2 className="w-[16px] h-[16px]" strokeWidth={2} />
                        </button>
                      </div>
                    </td>
                  </tr>

                  {/* Row 2 - Aluguel */}
                  <tr className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                    <td className="py-4 px-4">
                      <span className="text-[13px] font-[600] text-[#6D28D9]">Aluguel</span>
                    </td>
                    <td className="py-4 px-4">
                      <span className="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-[700] bg-[#EF4444] text-white">despesa</span>
                    </td>
                    <td className="py-4 px-4">
                      <span className="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-[700] bg-[#10B981] text-white">ativo</span>
                    </td>
                    <td className="py-4 px-4">
                      <span className="text-[13px] text-[#6B7280]">Alguel do templo</span>
                    </td>
                    <td className="py-4 px-4 text-right">
                      <div className="flex items-center justify-end gap-3">
                        <Link href="/contabilidade/categorias/2/editar" className="text-[#9CA3AF] hover:text-[#6D28D9] transition-colors">
                          <Pencil className="w-[16px] h-[16px]" strokeWidth={2} />
                        </Link>
                        <button className="text-[#9CA3AF] hover:text-[#EF4444] transition-colors">
                          <Trash2 className="w-[16px] h-[16px]" strokeWidth={2} />
                        </button>
                      </div>
                    </td>
                  </tr>

                </tbody>
              </table>
            </div>

            {/* TABLE FOOTER / PAGINATION */}
            <div className="border-t border-[#F1F1F4] p-3 flex items-center justify-between shrink-0 bg-[#F9FAFB]">
              
              <div className="flex items-center gap-2">
                <span className="text-[11px] font-[600] text-[#6B7280]">Linhas por página:</span>
                <select className="bg-white border border-[#E5E7EB] rounded-[6px] px-2 py-1 text-[11px] font-[600] text-[#374151] outline-none">
                  <option>10</option>
                  <option>25</option>
                  <option>50</option>
                </select>
              </div>

              <div className="flex items-center gap-4">
                <span className="text-[11px] font-[600] text-[#6B7280]">1-2 de 2</span>
                
                <div className="flex items-center gap-2">
                  <span className="text-[11px] font-[600] text-[#6B7280]">Página</span>
                  <input type="number" min="1" defaultValue="1" className="w-[40px] h-[24px] bg-white border border-[#E5E7EB] rounded-[4px] px-1 text-[11px] text-center outline-none" />
                  <button className="h-[24px] px-2 bg-[#6D28D9] text-white text-[10px] font-[700] rounded-[4px] hover:bg-[#5B21B6] transition-colors">
                    IR
                  </button>
                </div>
                
                <div className="flex items-center gap-1">
                  <button className="w-[24px] h-[24px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#E5E7EB] hover:text-[#374151] transition-colors disabled:opacity-50" disabled>
                    <ChevronLeft className="w-[14px] h-[14px]" />
                  </button>
                  <button className="w-[24px] h-[24px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#E5E7EB] hover:text-[#374151] transition-colors disabled:opacity-50" disabled>
                    <ChevronRight className="w-[14px] h-[14px]" />
                  </button>
                </div>
              </div>

            </div>

          </div>
          
        </main>
      </div>
    </div>
  );
}
