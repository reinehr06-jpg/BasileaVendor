/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: EXTRATO CONSOLIDADO
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /extratos
 * 📁 ARQUIVO: src/app/extratos/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Exibir o histórico de movimentações (entradas e saídas) das contas financeiras.
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/extratos?conta_id=1&periodo=... → Retorna as transações e saldos consolidados
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
  FileText, Search, Filter, Download, ArrowUpRight, ArrowDownRight, RefreshCw, Wallet, Calendar
} from "lucide-react";

export default function ExtratosPage() {
  const mockExtrato = [
    { id: 1, data: "20/05/2024", historico: "Dízimos e Ofertas (Culto Domingo)", doc: "REC-001", cat: "Dízimos", tipo: "receita", valor: "R$ 4.500,00", saldo: "R$ 14.500,00" },
    { id: 2, data: "20/05/2024", historico: "Conta de Energia (Enel)", doc: "NF-0442", cat: "Energia", tipo: "despesa", valor: "R$ 450,00", saldo: "R$ 14.050,00" },
    { id: 3, data: "21/05/2024", historico: "Transferência para Caixa Físico", doc: "TRF-991", cat: "Transferência", tipo: "despesa", valor: "R$ 1.000,00", saldo: "R$ 13.050,00" },
    { id: 4, data: "22/05/2024", historico: "Doação Anônima", doc: "REC-002", cat: "Ofertas", tipo: "receita", valor: "R$ 200,00", saldo: "R$ 13.250,00" },
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
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#E0F2FE] flex items-center justify-center shrink-0">
                <FileText className="w-[20px] h-[20px] text-[#0284C7]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Extrato Consolidado</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Movimentações diárias de todas as contas financeiras e caixas.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Download className="w-[14px] h-[14px]" /> Exportar PDF/Excel
              </button>
            </div>
          </div>

          <div className="flex flex-col flex-1 overflow-hidden min-h-0 gap-4">
            
            {/* FILTROS E RESUMO (GRID) */}
            <div className="grid grid-cols-12 gap-4 shrink-0">
              
              {/* FILTRO DE CONTA (Esquerda) */}
              <div className="col-span-12 lg:col-span-4 bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm p-5 flex flex-col justify-center gap-3">
                <div className="flex flex-col gap-1">
                  <label className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Conta Financeira</label>
                  <CustomSelect
                    value="1"
                    onChange={() => {}}
                    placeholder="Selecione a conta..."
                    options={[
                      {value: "1", label: "Itaú - CC 1234"},
                      {value: "2", label: "Caixa Físico"},
                      {value: "todas", label: "Todas as Contas (Consolidado)"}
                    ]}
                    className="h-[40px]"
                  />
                </div>
                
                <div className="flex flex-col gap-1 mt-2">
                  <label className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Período de Análise</label>
                  <div className="flex items-center gap-2">
                    <input type="date" className="flex-1 h-[40px] border border-[#E5E7EB] rounded-[8px] px-3 text-[13px] text-[#111827] outline-none focus:border-[#0284C7]" defaultValue="2024-05-01" />
                    <span className="text-[#9CA3AF] text-[12px]">até</span>
                    <input type="date" className="flex-1 h-[40px] border border-[#E5E7EB] rounded-[8px] px-3 text-[13px] text-[#111827] outline-none focus:border-[#0284C7]" defaultValue="2024-05-31" />
                  </div>
                </div>
              </div>

              {/* DASHBOARD DE RESUMO (Direita) */}
              <div className="col-span-12 lg:col-span-8 bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm p-5 flex items-center justify-between">
                
                <div className="flex flex-col gap-1 flex-1 px-4">
                  <div className="flex items-center gap-1.5 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">
                    <Wallet className="w-[14px] h-[14px]" /> Saldo Inicial
                  </div>
                  <span className="text-[20px] font-[800] text-[#111827]">R$ 10.000,00</span>
                </div>

                <div className="w-[1px] h-[48px] bg-[#E5E7EB]"></div>

                <div className="flex flex-col gap-1 flex-1 px-4">
                  <div className="flex items-center gap-1.5 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">
                    <ArrowUpRight className="w-[14px] h-[14px] text-[#10B981]" strokeWidth={3} /> Entradas
                  </div>
                  <span className="text-[20px] font-[800] text-[#10B981]">+ R$ 4.700,00</span>
                </div>

                <div className="w-[1px] h-[48px] bg-[#E5E7EB]"></div>

                <div className="flex flex-col gap-1 flex-1 px-4">
                  <div className="flex items-center gap-1.5 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">
                    <ArrowDownRight className="w-[14px] h-[14px] text-[#DC2626]" strokeWidth={3} /> Saídas
                  </div>
                  <span className="text-[20px] font-[800] text-[#DC2626]">- R$ 1.450,00</span>
                </div>

                <div className="w-[1px] h-[48px] bg-[#E5E7EB]"></div>

                <div className="flex flex-col gap-1 flex-1 pl-4">
                  <div className="flex items-center gap-1.5 text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">
                    <Wallet className="w-[14px] h-[14px] text-[#0284C7]" /> Saldo Final
                  </div>
                  <span className="text-[24px] font-[800] text-[#0284C7]">R$ 13.250,00</span>
                </div>

              </div>

            </div>

            {/* TABELA DE EXTRATO */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden">
              
              <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
                <div className="relative">
                  <input 
                    type="text" 
                    placeholder="Buscar no histórico..." 
                    className="w-[320px] h-[36px] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[13px] outline-none focus:border-[#0284C7]"
                  />
                  <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                </div>
                <button className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[36px]">
                  <Filter className="w-[14px] h-[14px] text-[#9CA3AF]" />
                  Filtros Adicionais
                </button>
              </div>

              <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
                <table className="w-full text-left border-collapse">
                  <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                    <tr>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Data</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Histórico e Categoria</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Doc.</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Valor</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Saldo</th>
                    </tr>
                  </thead>
                  <tbody>
                    {mockExtrato.map((item) => (
                      <tr key={item.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                        <td className="py-3 px-5">
                          <span className="text-[13px] font-[600] text-[#4B5563] flex items-center gap-1.5">
                            <Calendar className="w-[14px] h-[14px] text-[#9CA3AF]" /> {item.data}
                          </span>
                        </td>
                        <td className="py-3 px-5">
                          <div className="flex flex-col">
                            <span className="text-[14px] font-[700] text-[#111827]">{item.historico}</span>
                            <span className="text-[12px] text-[#6B7280] mt-0.5">{item.cat}</span>
                          </div>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[12px] text-[#9CA3AF]">{item.doc}</span>
                        </td>
                        <td className="py-3 px-5 text-right">
                          <span className={`text-[14px] font-[800] flex items-center justify-end gap-1 ${item.tipo === 'receita' ? 'text-[#10B981]' : 'text-[#DC2626]'}`}>
                            {item.tipo === 'receita' ? <ArrowUpRight className="w-[14px] h-[14px]" strokeWidth={3} /> : <ArrowDownRight className="w-[14px] h-[14px]" strokeWidth={3} />}
                            {item.valor}
                          </span>
                        </td>
                        <td className="py-3 px-5 text-right">
                          <span className="text-[14px] font-[800] text-[#0284C7] bg-[#F0F9FF] px-2 py-1 rounded-[6px]">{item.saldo}</span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

          </div>
          
        </main>
      </div>
    </div>
  );
}
