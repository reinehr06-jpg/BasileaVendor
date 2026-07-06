/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: DEPARTAMENTOS E MINISTÉRIOS
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /departamentos
 * 📁 ARQUIVO: src/app/departamentos/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Organizar os grupos, ministérios e departamentos da igreja.
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/departamentos?page=1&limit=10 → Lista de departamentos
 *    2. POST /api/departamentos → Criar novo departamento
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  UsersRound, Plus, Search, MoreVertical, Users
} from "lucide-react";

export default function DepartamentosPage() {
  const mockDepartamentos = [
    { id: 1, nome: "Ministério de Louvor", lider: "Ana Silva", status: "Ativo", membros: 12 },
    { id: 2, nome: "Ministério Infantil", lider: "Carla Mendes", status: "Ativo", membros: 8 },
    { id: 3, nome: "Ação Social", lider: "Pr. João", status: "Ativo", membros: 5 },
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
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center shrink-0">
                <UsersRound className="w-[20px] h-[20px] text-[#9333EA]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Departamentos e Ministérios</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Organize os grupos, ministérios e departamentos da sua igreja.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Link href="/pessoas-e-empresas/departamentos/novo" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Novo Departamento
              </Link>
            </div>
          </div>

          <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0">
            
            <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
              <div className="relative">
                <input 
                  type="text" 
                  placeholder="Buscar departamento..." 
                  className="w-[280px] h-[36px] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[13px] outline-none focus:border-[#6D28D9]"
                />
                <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
              </div>
            </div>

            <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
              <table className="w-full text-left border-collapse">
                <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                  <tr>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Nome / Ministério</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Líder / Responsável</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Voluntários / Membros</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Status</th>
                    <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {mockDepartamentos.map((item) => (
                    <tr key={item.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                      <td className="py-4 px-5">
                        <div className="flex items-center gap-3">
                          <div className="w-[36px] h-[36px] rounded-[8px] bg-[#F3F4F6] flex items-center justify-center shrink-0">
                            <Users className="w-[16px] h-[16px] text-[#6B7280]" />
                          </div>
                          <span className="text-[13px] font-[700] text-[#111827]">{item.nome}</span>
                        </div>
                      </td>
                      <td className="py-4 px-5">
                        <span className="text-[13px] font-[600] text-[#374151]">{item.lider}</span>
                      </td>
                      <td className="py-4 px-5 text-center">
                        <span className="text-[13px] font-[600] text-[#6B7280]">{item.membros} pessoas</span>
                      </td>
                      <td className="py-4 px-5 text-center">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-[700] ${item.status === 'Ativo' ? 'bg-[#ECFDF5] text-[#10B981]' : 'bg-[#F3F4F6] text-[#6B7280]'}`}>
                          {item.status}
                        </span>
                      </td>
                      <td className="py-4 px-5 text-right">
                        <button className="w-[32px] h-[32px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#111827] hover:bg-[#E5E7EB] transition-colors ml-auto">
                          <MoreVertical className="w-[16px] h-[16px]" />
                        </button>
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
