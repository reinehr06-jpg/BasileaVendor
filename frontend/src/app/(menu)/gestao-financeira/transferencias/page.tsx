/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: TRANSFERÊNCIAS (Entre Contas)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /transferencias
 * 📁 ARQUIVO: src/app/transferencias/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Registrar movimentações financeiras entre contas da mesma instituição
 *    (Ex: Banco do Brasil -> Caixa Físico), sem afetar DRE (não é receita nem despesa).
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/transferencias?page=1&limit=10 → Lista de transferências
 *    2. POST /api/transferencias → Criar transferência entre contas
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client";

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  ArrowRightLeft, Plus, Search, Filter, MoreVertical, Calendar, 
  ArrowUpRight, ArrowDownRight, TrendingUp, HelpCircle
} from "lucide-react";
import { TransferenciasService } from "@/services/transferencias.service";
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, Cell, PieChart, Pie, Legend, ComposedChart, Line } from "recharts";

// Mock Data Transferências Dashboard
const transferenciasBancosData = [
  { name: "Itaú", saídas: 14000, entradas: 8000 },
  { name: "Bradesco", saídas: 3000, entradas: 12000 },
  { name: "Caixa Físico", saídas: 1000, entradas: 3500 },
  { name: "Nubank", saídas: 2000, entradas: 500 },
];

const transferenciasHistoricoData = [
  { name: "Semana 1", volume: 4000 },
  { name: "Semana 2", volume: 3000 },
  { name: "Semana 3", volume: 6000 },
  { name: "Semana 4", volume: 2780 },
];

export default function TransferenciasPage() {
  const [viewMode, setViewMode] = useState<"dashboard" | "lista">("dashboard");
  const [transferencias, setTransferencias] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    carregarTransferencias();
  }, []);

  const carregarTransferencias = async () => {
    try {
      setLoading(true);
      const res = await TransferenciasService.listar();
      setTransferencias(res.data.data);
    } catch (error) {
      console.error("Erro ao carregar transferências", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F5F5F7]">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="p-4 flex-1 flex flex-col w-full max-w-[1600px] mx-auto gap-4 overflow-hidden relative">
          
          {/* HEADER */}
          <div className="flex items-center justify-between shrink-0 bg-white rounded-[12px] p-4 border border-[#E5E7EB] shadow-sm">
            <div className="flex items-center gap-3">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F5F3FF] flex items-center justify-center shrink-0">
                <ArrowRightLeft className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Transferências</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Histórico de remanejamento de saldos entre contas da igreja.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              
              {/* VIEW TOGGLE */}
              <div className="flex items-center bg-[#F3F4F6] p-1 rounded-[8px] mr-2">
                <button 
                  onClick={() => setViewMode("dashboard")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "dashboard" ? 'bg-white text-[#6D28D9] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <ArrowRightLeft className="w-[14px] h-[14px]" /> Dashboard
                </button>
                <button 
                  onClick={() => setViewMode("lista")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "lista" ? 'bg-white text-[#6D28D9] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <ArrowRightLeft className="w-[14px] h-[14px]" /> Lista
                </button>
              </div>

              <Link href="/gestao-financeira/transferencias/nova" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Nova Transferência
              </Link>
            </div>
          </div>

          {/* DYNAMIC CONTENT */}
          {viewMode === "dashboard" ? (
            <div className="flex flex-col flex-1 gap-4 overflow-y-auto custom-scrollbar pb-4 animate-in fade-in duration-300">
              
              {/* KPIs */}
              <div className="grid grid-cols-4 gap-4 shrink-0">
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Volume Movimentado no Mês</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">24.500<span className="text-[14px]">,00</span></span>
                    </div>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#F5F3FF] flex items-center justify-center shrink-0">
                    <ArrowRightLeft className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Total de Operações</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">18</span>
                    </div>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#EFF6FF] flex items-center justify-center shrink-0">
                    <Calendar className="w-[20px] h-[20px] text-[#3B82F6]" strokeWidth={2.4} />
                  </div>
                </div>
              </div>

              {/* CHARTS */}
              <div className="flex gap-4 shrink-0 h-[280px]">
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 w-[380px] flex flex-col shadow-sm">
                  <span className="text-[14px] font-[700] text-[#1A1A2E] mb-4 shrink-0">Balanço de Contas</span>
                  <div className="flex-1 w-full min-h-0 ml-[-20px]">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={transferenciasBancosData} layout="vertical" margin={{ top: 5, right: 0, left: 0, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="#f3f4f6" />
                        <XAxis type="number" hide />
                        <YAxis dataKey="name" type="category" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9CA3AF' }} width={100} />
                        <Tooltip cursor={{fill: '#F4EEFF'}} contentStyle={{ borderRadius: '8px', border: 'none', fontSize: '11px' }} formatter={(value) => `R$ ${value}`} />
                        <Bar dataKey="saídas" name="Saídas" stackId="a" fill="#C4B5FD" radius={[0, 0, 0, 0]} maxBarSize={20} />
                        <Bar dataKey="entradas" name="Entradas" stackId="a" fill="#6D28D9" radius={[0, 4, 4, 0]} maxBarSize={20} />
                        <Legend verticalAlign="top" align="right" wrapperStyle={{ fontSize: '11px', paddingBottom: '10px' }} iconType="circle" />
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex-1 flex flex-col shadow-sm overflow-hidden">
                  <div className="flex justify-between items-center mb-4 shrink-0">
                    <span className="text-[14px] font-[700] text-[#1A1A2E]">Últimas Transferências</span>
                    <button onClick={() => setViewMode("lista")} className="text-[12px] font-[600] text-[#6D28D9] hover:underline">Ver todas</button>
                  </div>
                  <div className="flex flex-col gap-2 overflow-y-auto custom-scrollbar">
                    {transferencias.slice(0, 3).map((item) => (
                      <div key={item.id} className="flex items-center justify-between p-3 rounded-[8px] bg-[#F9FAFB] hover:bg-[#F3F4F6] transition-colors border border-[#F1F1F4]">
                        <div className="flex items-center gap-3">
                          <div className="w-[36px] h-[36px] rounded-full bg-white flex items-center justify-center shrink-0 shadow-sm border border-[#E5E7EB]">
                            <ArrowRightLeft className="w-[16px] h-[16px] text-[#6B7280]" />
                          </div>
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{item.descricao || 'Transferência'}</span>
                            <div className="flex items-center gap-1.5 text-[11px] text-[#6B7280]">
                              <span>{item.origem_nome || item.origem?.nome}</span>
                              <ArrowRightLeft className="w-[10px] h-[10px]" />
                              <span>{item.destino_nome || item.destino?.nome}</span>
                            </div>
                          </div>
                        </div>
                        <div className="flex flex-col items-end">
                          <span className="text-[13px] font-[700] text-[#111827]">R$ {Number(item.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                          <span className="text-[11px] font-[500] text-[#6B7280]">{item.data ? new Date(item.data).toLocaleDateString('pt-BR') : '-'}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0 animate-in fade-in duration-300">
              
              <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
                <div className="flex items-center gap-2">
                  <div className="relative">
                    <input 
                      type="text" 
                      placeholder="Buscar transferência..." 
                      className="w-[280px] h-[36px] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[13px] outline-none focus:border-[#6D28D9]"
                    />
                    <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                  </div>
                  <button className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[36px]">
                    <Filter className="w-[14px] h-[14px] text-[#9CA3AF]" />
                    Filtros
                  </button>
                </div>
              </div>

              <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
                <table className="w-full text-left border-collapse">
                  <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                    <tr>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Data</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Descrição</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Origem</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Destino</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Valor</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Status</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {transferencias.map((item) => (
                      <tr key={item.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                        <td className="py-4 px-5">
                          <span className="text-[13px] font-[500] text-[#6B7280]">{item.data ? new Date(item.data).toLocaleDateString('pt-BR') : '-'}</span>
                        </td>
                        <td className="py-4 px-5">
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{item.descricao || 'Transferência'}</span>
                          </div>
                        </td>
                        <td className="py-4 px-5">
                          <span className="text-[13px] font-[600] text-[#4B5563]">{item.origem_nome || item.origem?.nome}</span>
                        </td>
                        <td className="py-4 px-5">
                          <span className="text-[13px] font-[600] text-[#4B5563]">{item.destino_nome || item.destino?.nome}</span>
                        </td>
                        <td className="py-4 px-5 text-right">
                          <span className="text-[13px] font-[700] text-[#111827]">R$ {Number(item.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                        </td>
                        <td className="py-4 px-5 text-center">
                          <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-[700] ${item.status === 'Concluída' ? 'bg-[#ECFDF5] text-[#10B981]' : 'bg-[#FEF3C7] text-[#D97706]'}`}>
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
          )}
          
        </main>
      </div>
    </div>
  );
}
