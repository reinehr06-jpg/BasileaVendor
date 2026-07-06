/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: CONTAS A PAGAR (Dashboard de Vencimentos)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /contas-a-pagar
 * 📁 ARQUIVO: src/app/contas-a-pagar/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Oferecer uma visão priorizada dos compromissos financeiros (despesas) que estão
 *    vencidos, vencendo hoje ou nos próximos dias. Foco total em ação rápida de pagamento.
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/despesas/dashboard-vencimentos → Retorna as contas filtradas por data de vencimento
 *    2. PUT /api/despesas/{id}/pagar → Ação rápida para marcar uma conta como "Paga"
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  AlertTriangle, CalendarDays, Clock, Filter, Search, CheckCircle2, Download, Printer
} from "lucide-react";

export default function ContasAPagarPage() {
  const [activeFilter, setActiveFilter] = useState("Vencidas");

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1600px] mx-auto gap-4 overflow-hidden relative">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex flex-col">
              <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Contas a Pagar</h1>
              <p className="text-[12px] font-[500] text-[#6B7280]">Visão priorizada dos seus compromissos financeiros em aberto.</p>
            </div>
            
            <div className="flex items-center gap-3">
              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Printer className="w-[14px] h-[14px]" /> Imprimir Relatório
              </button>
              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Download className="w-[14px] h-[14px]" /> Exportar PDF
              </button>
            </div>
          </div>

          <div className="flex gap-4 flex-1 overflow-hidden">
            
            {/* SIDE PANEL FILTERS */}
            <div className="w-[260px] flex flex-col gap-3 shrink-0">
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm p-4 flex flex-col gap-1">
                <h3 className="text-[11px] font-[800] text-[#9CA3AF] uppercase tracking-wider mb-2">Visões Rápidas</h3>
                
                <button 
                  onClick={() => setActiveFilter('Vencidas')}
                  className={`flex items-center justify-between w-full p-2.5 rounded-[8px] transition-colors ${activeFilter === 'Vencidas' ? 'bg-[#FEF2F2] border border-[#FECACA]' : 'hover:bg-[#F9FAFB] border border-transparent'}`}
                >
                  <div className="flex items-center gap-2">
                    <AlertTriangle className={`w-[16px] h-[16px] ${activeFilter === 'Vencidas' ? 'text-[#DC2626]' : 'text-[#6B7280]'}`} />
                    <span className={`text-[13px] font-[700] ${activeFilter === 'Vencidas' ? 'text-[#DC2626]' : 'text-[#4B5563]'}`}>Vencidas</span>
                  </div>
                  <span className={`text-[12px] font-[800] ${activeFilter === 'Vencidas' ? 'text-[#DC2626]' : 'text-[#9CA3AF]'}`}>3</span>
                </button>

                <button 
                  onClick={() => setActiveFilter('Hoje')}
                  className={`flex items-center justify-between w-full p-2.5 rounded-[8px] transition-colors ${activeFilter === 'Hoje' ? 'bg-[#FFF7ED] border border-[#FED7AA]' : 'hover:bg-[#F9FAFB] border border-transparent'}`}
                >
                  <div className="flex items-center gap-2">
                    <Clock className={`w-[16px] h-[16px] ${activeFilter === 'Hoje' ? 'text-[#EA580C]' : 'text-[#6B7280]'}`} />
                    <span className={`text-[13px] font-[700] ${activeFilter === 'Hoje' ? 'text-[#EA580C]' : 'text-[#4B5563]'}`}>Vencem Hoje</span>
                  </div>
                  <span className={`text-[12px] font-[800] ${activeFilter === 'Hoje' ? 'text-[#EA580C]' : 'text-[#9CA3AF]'}`}>1</span>
                </button>

                <button 
                  onClick={() => setActiveFilter('Proximos')}
                  className={`flex items-center justify-between w-full p-2.5 rounded-[8px] transition-colors ${activeFilter === 'Proximos' ? 'bg-[#EFF6FF] border border-[#BFDBFE]' : 'hover:bg-[#F9FAFB] border border-transparent'}`}
                >
                  <div className="flex items-center gap-2">
                    <CalendarDays className={`w-[16px] h-[16px] ${activeFilter === 'Proximos' ? 'text-[#2563EB]' : 'text-[#6B7280]'}`} />
                    <span className={`text-[13px] font-[700] ${activeFilter === 'Proximos' ? 'text-[#2563EB]' : 'text-[#4B5563]'}`}>Próximos Dias</span>
                  </div>
                  <span className={`text-[12px] font-[800] ${activeFilter === 'Proximos' ? 'text-[#2563EB]' : 'text-[#9CA3AF]'}`}>5</span>
                </button>
              </div>

              <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm p-4 flex flex-col gap-3 flex-1 overflow-y-auto custom-scrollbar">
                <div className="flex items-center justify-between">
                  <h3 className="text-[11px] font-[800] text-[#9CA3AF] uppercase tracking-wider">Filtros Avançados</h3>
                  <Filter className="w-[12px] h-[12px] text-[#9CA3AF]" />
                </div>
                
                <div className="relative mt-1">
                  <input type="text" placeholder="Buscar credor..." className="w-full h-[36px] border border-[#E5E7EB] rounded-[8px] pl-8 pr-3 text-[12px] outline-none focus:border-[#6D28D9]" />
                  <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-2.5 top-1/2 -translate-y-1/2" />
                </div>

                <div className="flex flex-col gap-1 mt-2">
                  <span className="text-[11px] font-[700] text-[#6B7280]">Filial</span>
                  <select className="w-full h-[36px] border border-[#E5E7EB] rounded-[8px] px-2 text-[12px] outline-none focus:border-[#6D28D9] bg-white">
                    <option>Todas as Filiais</option>
                  </select>
                </div>

                <div className="flex flex-col gap-1 mt-2">
                  <span className="text-[11px] font-[700] text-[#6B7280]">Conta</span>
                  <select className="w-full h-[36px] border border-[#E5E7EB] rounded-[8px] px-2 text-[12px] outline-none focus:border-[#6D28D9] bg-white">
                    <option>Todas as Contas</option>
                  </select>
                </div>
              </div>
            </div>

            {/* MAIN CONTENT / CARDS */}
            <div className="flex-1 flex flex-col bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm overflow-hidden relative">
              <div className="p-5 border-b border-[#E5E7EB] bg-[#F9FAFB] flex items-center justify-between shrink-0">
                <h2 className="text-[16px] font-[800] text-[#1A1A2E]">
                  {activeFilter === 'Vencidas' && 'Contas Vencidas e em Atraso'}
                  {activeFilter === 'Hoje' && 'Vencimentos de Hoje'}
                  {activeFilter === 'Proximos' && 'Contas dos Próximos 7 Dias'}
                </h2>
                <div className="text-[14px] font-[800] text-[#DC2626]">
                  Total: R$ 3.450,00
                </div>
              </div>
              
              <div className="flex-1 overflow-y-auto custom-scrollbar p-5 flex flex-col gap-4">
                
                {/* CARD CONTA A PAGAR 1 */}
                <div className="border border-[#FECACA] bg-[#FEF2F2] rounded-[12px] p-5 flex items-start justify-between shadow-sm hover:shadow-md transition-shadow">
                  <div className="flex items-start gap-4">
                    <div className="w-[48px] h-[48px] rounded-[10px] bg-white border border-[#FCA5A5] flex flex-col items-center justify-center shrink-0">
                      <span className="text-[10px] font-[800] text-[#DC2626] uppercase">Mai</span>
                      <span className="text-[18px] font-[800] text-[#991B1B] leading-none">10</span>
                    </div>
                    <div className="flex flex-col">
                      <h3 className="text-[16px] font-[800] text-[#111827]">Compra de Microfones</h3>
                      <div className="flex items-center gap-3 mt-1.5 text-[12px] text-[#6B7280] font-[500]">
                        <span className="flex items-center gap-1.5"><span className="w-[6px] h-[6px] rounded-full bg-[#D1D5DB]"></span> AudioTech</span>
                        <span className="flex items-center gap-1.5"><span className="w-[6px] h-[6px] rounded-full bg-[#D1D5DB]"></span> Caixa Físico</span>
                        <span className="flex items-center gap-1.5"><span className="w-[6px] h-[6px] rounded-full bg-[#D1D5DB]"></span> NF-8921</span>
                      </div>
                      <div className="inline-flex mt-2 items-center px-2 py-0.5 rounded-[4px] text-[10px] font-[700] bg-[#FCA5A5] text-[#7F1D1D] w-fit">
                        Vencido há 14 dias
                      </div>
                    </div>
                  </div>
                  <div className="flex flex-col items-end gap-3">
                    <span className="text-[20px] font-[800] text-[#DC2626]">R$ 1.200,00</span>
                    <button className="flex items-center gap-1.5 px-4 py-2 bg-white border border-[#E5E7EB] text-[#374151] rounded-[8px] text-[12px] font-[700] hover:bg-[#F3F4F6] transition-colors shadow-sm">
                      <CheckCircle2 className="w-[14px] h-[14px] text-[#10B981]" /> Marcar como Paga
                    </button>
                  </div>
                </div>

                {/* CARD CONTA A PAGAR 2 */}
                <div className="border border-[#FECACA] bg-[#FEF2F2] rounded-[12px] p-5 flex items-start justify-between shadow-sm hover:shadow-md transition-shadow">
                  <div className="flex items-start gap-4">
                    <div className="w-[48px] h-[48px] rounded-[10px] bg-white border border-[#FCA5A5] flex flex-col items-center justify-center shrink-0">
                      <span className="text-[10px] font-[800] text-[#DC2626] uppercase">Mai</span>
                      <span className="text-[18px] font-[800] text-[#991B1B] leading-none">20</span>
                    </div>
                    <div className="flex flex-col">
                      <h3 className="text-[16px] font-[800] text-[#111827]">Conta de Água</h3>
                      <div className="flex items-center gap-3 mt-1.5 text-[12px] text-[#6B7280] font-[500]">
                        <span className="flex items-center gap-1.5"><span className="w-[6px] h-[6px] rounded-full bg-[#D1D5DB]"></span> Sabesp</span>
                        <span className="flex items-center gap-1.5"><span className="w-[6px] h-[6px] rounded-full bg-[#D1D5DB]"></span> Itaú - CC 1234</span>
                      </div>
                      <div className="inline-flex mt-2 items-center px-2 py-0.5 rounded-[4px] text-[10px] font-[700] bg-[#FCA5A5] text-[#7F1D1D] w-fit">
                        Vencido há 4 dias
                      </div>
                    </div>
                  </div>
                  <div className="flex flex-col items-end gap-3">
                    <span className="text-[20px] font-[800] text-[#DC2626]">R$ 250,00</span>
                    <button className="flex items-center gap-1.5 px-4 py-2 bg-white border border-[#E5E7EB] text-[#374151] rounded-[8px] text-[12px] font-[700] hover:bg-[#F3F4F6] transition-colors shadow-sm">
                      <CheckCircle2 className="w-[14px] h-[14px] text-[#10B981]" /> Marcar como Paga
                    </button>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </main>
      </div>
    </div>
  );
}
