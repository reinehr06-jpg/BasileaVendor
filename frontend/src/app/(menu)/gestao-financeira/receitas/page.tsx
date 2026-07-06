"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  ArrowUpCircle, Plus, Search, Filter, Columns, Download, MoreVertical, ChevronLeft, ChevronRight, FileText, LayoutDashboard, List, TrendingUp, CreditCard, Building2, CheckCircle2
} from "lucide-react";
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, Cell, PieChart, Pie, Legend, ComposedChart, Line } from "recharts";

const receitasEvolucaoData = [
  { name: "01/06", dízimos: 4000, ofertas: 1200 },
  { name: "05/06", dízimos: 5000, ofertas: 1398 },
  { name: "10/06", dízimos: 6000, ofertas: 2800 },
  { name: "15/06", dízimos: 4780, ofertas: 1908 },
  { name: "20/06", dízimos: 7890, ofertas: 3800 },
  { name: "25/06", dízimos: 6390, ofertas: 2800 },
  { name: "30/06", dízimos: 8490, ofertas: 4300 },
];

const receitasCategoriaData = [
  { name: "Contrib. Regulares", value: 45000 },
  { name: "Doações e Ofertas", value: 12000 },
  { name: "Campanhas", value: 8000 },
  { name: "Cantina/Eventos", value: 4500 },
].sort((a, b) => a.value - b.value);

const COLORS = ["#10B981", "#34D399", "#6EE7B7", "#A7F3D0"];

export default function ReceitasPage() {
  const [viewMode, setViewMode] = useState<"dashboard" | "lista">("dashboard");
  const [activeTab, setActiveTab] = useState("Contribuições");

  const mockReceitas = [
    { id: 1, data: "24/05/2024", desc: "Contribuição Mensal - Sede", conta: "Itaú - CC 1234", cat: "Contribuições", origem: "João Silva", valor: "R$ 1.500,00", nf: "-" },
    { id: 2, data: "24/05/2024", desc: "Doação - Evento Principal", conta: "Itaú - CC 1234", cat: "Doações", origem: "Evento Principal", valor: "R$ 845,50", nf: "-" },
    { id: 3, data: "25/05/2024", desc: "Venda de Livros - Conferência", conta: "Caixa Físico", cat: "Vendas", origem: "Livraria", valor: "R$ 320,00", nf: "NF-0012" },
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
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                <ArrowUpCircle className="w-[20px] h-[20px] text-[#10B981]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Receitas</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gestão exclusiva das entradas já consolidadas no caixa.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              
              <div className="flex items-center bg-[#F3F4F6] p-1 rounded-[8px] mr-2">
                <button 
                  onClick={() => setViewMode("dashboard")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "dashboard" ? 'bg-white text-[#7C3AED] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <LayoutDashboard className="w-[14px] h-[14px]" /> Dashboard
                </button>
                <button 
                  onClick={() => setViewMode("lista")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "lista" ? 'bg-white text-[#7C3AED] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <List className="w-[14px] h-[14px]" /> Lista
                </button>
              </div>

              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Download className="w-[14px] h-[14px]" /> Exportar
              </button>
              <Link href="/gestao-financeira/receitas/nova" className="bg-[#7C3AED] hover:bg-[#6D28D9] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Nova Receita
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
                    <span className="text-[12px] font-[600] text-[#6B7280]">Recebido no mês</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">69.500<span className="text-[14px]">,00</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#10B981] mt-1 flex items-center gap-1">
                      <TrendingUp className="w-[12px] h-[12px]" strokeWidth={3} /> +15% vs mês passado
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                    <ArrowUpCircle className="w-[20px] h-[20px] text-[#10B981]" strokeWidth={2.4} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Meta de Arrecadação</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">80%<span className="text-[14px]"></span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#10B981] mt-1 flex items-center gap-1">
                      R$ 80.000,00 previstos
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                    <CheckCircle2 className="w-[20px] h-[20px] text-[#10B981]" strokeWidth={2.4} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Ticket Médio (Contribuições)</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">450<span className="text-[14px]">,00</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#10B981] mt-1 flex items-center gap-1">
                      <TrendingUp className="w-[12px] h-[12px]" strokeWidth={3} /> +5% vs mês passado
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                    <CreditCard className="w-[20px] h-[20px] text-[#10B981]" strokeWidth={2.4} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Maior Fonte</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[18px] font-[800] text-[#1A1A2E] truncate">Matriz (Sede)</span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#6B7280] mt-1 flex items-center gap-1">
                      Responsável por 65% das entradas
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#EFF6FF] flex items-center justify-center shrink-0">
                    <Building2 className="w-[20px] h-[20px] text-[#3B82F6]" strokeWidth={2.4} />
                  </div>
                </div>
              </div>

              {/* CHARTS */}
              <div className="flex gap-4 shrink-0 h-[280px]">
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex-1 flex flex-col shadow-sm">
                  <div className="flex justify-between items-center mb-4 shrink-0">
                    <span className="text-[14px] font-[700] text-[#1A1A2E]">Evolução de Entradas</span>
                    <select className="text-[11px] border border-[#E5E7EB] px-2 py-1 rounded-[6px] text-[#4B5563] outline-none">
                      <option>Este Mês</option>
                    </select>
                  </div>
                  <div className="flex-1 w-full min-h-0 ml-[-20px]">
                    <ResponsiveContainer width="100%" height="100%">
                      <ComposedChart data={receitasEvolucaoData} margin={{ top: 5, right: 0, left: 0, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f3f4f6" />
                        <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9CA3AF' }} dy={5} />
                        <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9CA3AF' }} tickFormatter={(val) => `R$ ${val/1000}k`} />
                        <Tooltip contentStyle={{ borderRadius: '10px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)', fontSize: '11px', fontWeight: 500 }} />
                        <Legend verticalAlign="top" align="right" wrapperStyle={{ fontSize: '11px', paddingBottom: '10px' }} iconType="circle" />
                        <Bar dataKey="dízimos" name="Dízimos" fill="#10B981" radius={[4, 4, 0, 0]} maxBarSize={20} />
                        <Line type="monotone" dataKey="ofertas" name="Ofertas" stroke="#34D399" strokeWidth={3} dot={{ fill: '#34D399', strokeWidth: 2, r: 4 }} activeDot={{ r: 6 }} />
                      </ComposedChart>
                    </ResponsiveContainer>
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 w-[380px] flex flex-col shadow-sm">
                  <span className="text-[14px] font-[700] text-[#1A1A2E] mb-4 shrink-0">Receitas por Categoria</span>
                  <div className="flex-1 w-full min-h-0">
                    <ResponsiveContainer width="100%" height="100%">
                      <PieChart>
                        <Pie data={receitasCategoriaData} dataKey="value" nameKey="name" cx="50%" cy="50%" innerRadius={60} outerRadius={80} paddingAngle={5}>
                          {receitasCategoriaData.map((entry, index) => (
                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                          ))}
                        </Pie>
                        <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', fontSize: '11px' }} formatter={(value) => `R$ ${value}`} />
                        <Legend verticalAlign="middle" align="right" layout="vertical" iconType="circle" wrapperStyle={{ fontSize: '11px' }} />
                      </PieChart>
                    </ResponsiveContainer>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex flex-col flex-1 min-h-[200px] shadow-sm overflow-hidden">
                <div className="flex justify-between items-center mb-4 shrink-0">
                  <span className="text-[14px] font-[700] text-[#1A1A2E]">Maiores entradas recentes</span>
                  <button onClick={() => setViewMode("lista")} className="text-[12px] font-[600] text-[#10B981] hover:underline">Ver todas</button>
                </div>
                <div className="flex-1 overflow-auto custom-scrollbar">
                  <table className="w-full text-left">
                    <thead>
                      <tr className="border-b border-[#F1F1F4]">
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase sticky top-0 bg-white">Descrição</th>
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase sticky top-0 bg-white">Origem</th>
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase sticky top-0 bg-white text-right">Valor</th>
                      </tr>
                    </thead>
                    <tbody>
                      {mockReceitas.slice(0, 3).map((receita) => (
                        <tr key={receita.id} className="border-b border-[#F1F1F4] last:border-0 hover:bg-[#F9FAFB]">
                          <td className="py-2.5 text-[12px] font-[600] text-[#1A1A2E]">{receita.desc}</td>
                          <td className="py-2.5 text-[11px] font-[500] text-[#4B5563]">{receita.origem}</td>
                          <td className="py-2.5 text-[12px] font-[800] text-[#10B981] text-right">{receita.valor}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
          ) : (
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0 animate-in fade-in duration-300">
              
              {/* TABS & TOOLS */}
              <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
                
                <div className="flex items-center gap-1 bg-[#F3F4F6] p-1 rounded-[8px]">
                  {["Todas", "Contribuições", "Doações", "Eventos", "Vendas"].map((tab) => (
                    <button
                      key={tab}
                      onClick={() => setActiveTab(tab)}
                      className={`px-4 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${activeTab === tab ? 'bg-white text-[#7C3AED] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                    >
                      {tab}
                    </button>
                  ))}
                </div>
                
                <div className="flex items-center gap-2">
                  <div className="relative">
                    <input 
                      type="text" 
                      placeholder="Buscar por descrição, recibo ou origem..." 
                      className="w-[280px] h-[36px] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[13px] outline-none focus:border-[#7C3AED]"
                    />
                    <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                  </div>
                  <button className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[36px]">
                    <Filter className="w-[14px] h-[14px] text-[#9CA3AF]" />
                    Filtros
                  </button>
                  <button className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[36px]">
                    <Columns className="w-[14px] h-[14px] text-[#9CA3AF]" />
                    Colunas
                  </button>
                </div>
              </div>

              {/* TABLE */}
              <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
                <table className="w-full text-left border-collapse">
                  <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                    <tr>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Data</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Descrição</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Origem</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Categoria</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Conta</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Valor</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {mockReceitas.map((receita) => (
                      <tr key={receita.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                        <td className="py-3 px-5">
                          <span className="text-[13px] font-[600] text-[#4B5563]">{receita.data}</span>
                        </td>
                        <td className="py-3 px-5">
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{receita.desc}</span>
                            {receita.nf !== "-" && <span className="text-[11px] text-[#6B7280] flex items-center gap-1 mt-0.5"><FileText className="w-[10px] h-[10px]" /> NF: {receita.nf}</span>}
                          </div>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] text-[#4B5563]">{receita.origem}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[11px] font-[600] bg-[#F3F4F6] text-[#4B5563]">{receita.cat}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] text-[#6B7280]">{receita.conta}</span>
                        </td>
                        <td className="py-3 px-5 text-right">
                          <span className="text-[14px] font-[800] text-[#10B981]">{receita.valor}</span>
                        </td>
                        <td className="py-3 px-5 text-right">
                          <button className="w-[32px] h-[32px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#111827] hover:bg-[#E5E7EB] transition-colors ml-auto">
                            <MoreVertical className="w-[16px] h-[16px]" />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* TABLE FOOTER / PAGINATION */}
              <div className="border-t border-[#F1F1F4] p-4 flex items-center justify-between shrink-0 bg-white">
                
                <div className="flex items-center gap-2">
                  <span className="text-[12px] font-[600] text-[#6B7280]">Linhas por página</span>
                  <select className="bg-white border border-[#E5E7EB] rounded-[6px] px-2 py-1 text-[12px] font-[600] text-[#374151] outline-none">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                  </select>
                </div>

                <div className="text-[12px] font-[600] text-[#111827]">
                  1-3 de 3
                </div>

                <div className="flex items-center gap-4">
                  <div className="flex items-center gap-2">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Página</span>
                    <select className="bg-white border border-[#E5E7EB] rounded-[6px] px-2 py-1 text-[12px] font-[600] text-[#374151] outline-none">
                      <option>1</option>
                    </select>
                  </div>
                  
                  <div className="flex items-center gap-1">
                    <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] disabled:opacity-50" disabled><ChevronLeft className="w-[14px] h-[14px]" /></button>
                    <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] bg-[#F4F1FD] text-[#7C3AED] font-[700] text-[12px]">1</button>
                    <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] disabled:opacity-50" disabled><ChevronRight className="w-[14px] h-[14px]" /></button>
                  </div>
                </div>

              </div>

            </div>
          )}
          
        </main>
      </div>
    </div>
  );
}
