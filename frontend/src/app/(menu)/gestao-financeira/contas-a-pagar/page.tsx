"use client";

import React, { useState } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  AlertTriangle, Calendar, CalendarDays, DollarSign, Search, SlidersHorizontal, 
  ChevronRight, Building, FileText, CheckCircle2, MoreVertical, LayoutDashboard, List, Download, Plus, Filter, Columns, ChevronLeft
} from "lucide-react";
import { BarChart, Bar, XAxis, Tooltip, ResponsiveContainer, Cell, YAxis, CartesianGrid } from "recharts";

const fluxoData = [
  { name: 'Hoje', value: 850 },
  { name: 'Qui', value: 620 },
  { name: 'Sex', value: 530 },
  { name: 'Sáb', value: 0 },
  { name: 'Dom', value: 0 },
  { name: 'Seg', value: 700 },
  { name: 'Ter', value: 450 },
];

export default function ContasAPagarPage() {
  const [viewMode, setViewMode] = useState<"dashboard" | "lista">("dashboard");
  const [activeTab, setActiveTab] = useState("Todas");

  const mockContas = [
    { id: 1, data: "10/05/2024", desc: "Compra de Microfones", fornecedor: "AudioTech", cat: "Equipamentos", conta: "Caixa Físico", valor: "R$ 1.200,00", status: "Vencida", diasAtraso: 14, nf: "NF-8921" },
    { id: 2, data: "20/05/2024", desc: "Conta de Água", fornecedor: "Sabesp", cat: "Despesas Fixas", conta: "Itaú - CC 1234", valor: "R$ 250,00", status: "Vencida", diasAtraso: 4, nf: "-" },
    { id: 3, data: "24/05/2024", desc: "Internet Corporativa", fornecedor: "Vivo Empresas", cat: "Despesas Fixas", conta: "Itaú - CC 1234", valor: "R$ 850,00", status: "Vence Hoje", diasAtraso: 0, nf: "NF-112" },
    { id: 4, data: "26/05/2024", desc: "Energia Elétrica", fornecedor: "Enel Distribuição", cat: "Despesas Fixas", conta: "Bradesco", valor: "R$ 620,00", status: "A Vencer", diasAtraso: 0, nf: "-" },
    { id: 5, data: "27/05/2024", desc: "Licença de Software", fornecedor: "Adobe Systems", cat: "Tecnologia", conta: "Cartão de Crédito", valor: "R$ 530,00", status: "A Vencer", diasAtraso: 0, nf: "IN-442" },
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
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#FEF2F2] flex items-center justify-center shrink-0">
                <AlertTriangle className="w-[20px] h-[20px] text-[#EF4444]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Contas a Pagar</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gestão de compromissos financeiros e vencimentos.</p>
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
              <button className="bg-[#7C3AED] hover:bg-[#6D28D9] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Nova Conta
              </button>
            </div>
          </div>

          {/* DYNAMIC CONTENT */}
          {viewMode === "dashboard" ? (
            <div className="flex flex-col flex-1 gap-4 overflow-y-auto custom-scrollbar pb-4 animate-in fade-in duration-300">
              
              {/* COMPACT KPIs */}
              <div className="grid grid-cols-4 gap-4 shrink-0">
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-4 flex items-center justify-between shadow-sm border-l-[4px] border-l-[#EF4444]">
                  <div className="flex flex-col">
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">Vencidas</span>
                    <div className="flex items-baseline gap-2 mt-1">
                      <span className="text-[20px] font-[800] text-[#EF4444]">R$ 1.450,00</span>
                      <span className="text-[12px] font-[600] text-[#EF4444] bg-[#FEF2F2] px-2 py-0.5 rounded-[4px]">2 contas</span>
                    </div>
                  </div>
                  <div className="w-[36px] h-[36px] rounded-full bg-[#FEF2F2] flex items-center justify-center shrink-0">
                    <AlertTriangle className="w-[16px] h-[16px] text-[#EF4444]" strokeWidth={2.5} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-4 flex items-center justify-between shadow-sm border-l-[4px] border-l-[#F59E0B]">
                  <div className="flex flex-col">
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">Para Hoje</span>
                    <div className="flex items-baseline gap-2 mt-1">
                      <span className="text-[20px] font-[800] text-[#F59E0B]">R$ 850,00</span>
                      <span className="text-[12px] font-[600] text-[#F59E0B] bg-[#FFFBEB] px-2 py-0.5 rounded-[4px]">1 conta</span>
                    </div>
                  </div>
                  <div className="w-[36px] h-[36px] rounded-full bg-[#FFFBEB] flex items-center justify-center shrink-0">
                    <Calendar className="w-[16px] h-[16px] text-[#F59E0B]" strokeWidth={2.5} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-4 flex items-center justify-between shadow-sm border-l-[4px] border-l-[#3B82F6]">
                  <div className="flex flex-col">
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">Próximos 7 Dias</span>
                    <div className="flex items-baseline gap-2 mt-1">
                      <span className="text-[20px] font-[800] text-[#3B82F6]">R$ 1.150,00</span>
                      <span className="text-[12px] font-[600] text-[#3B82F6] bg-[#EFF6FF] px-2 py-0.5 rounded-[4px]">2 contas</span>
                    </div>
                  </div>
                  <div className="w-[36px] h-[36px] rounded-full bg-[#EFF6FF] flex items-center justify-center shrink-0">
                    <CalendarDays className="w-[16px] h-[16px] text-[#3B82F6]" strokeWidth={2.5} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-4 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">Total em Aberto</span>
                    <div className="flex items-baseline gap-2 mt-1">
                      <span className="text-[20px] font-[800] text-[#1A1A2E]">R$ 3.450,00</span>
                      <span className="text-[12px] font-[600] text-[#6B7280] bg-[#F3F4F6] px-2 py-0.5 rounded-[4px]">5 contas</span>
                    </div>
                  </div>
                  <div className="w-[36px] h-[36px] rounded-full bg-[#F3F4F6] flex items-center justify-center shrink-0">
                    <DollarSign className="w-[16px] h-[16px] text-[#4B5563]" strokeWidth={2.5} />
                  </div>
                </div>
              </div>

              {/* CHARTS AND TOP LIST */}
              <div className="flex gap-4 flex-1 min-h-[300px]">
                
                {/* CHART */}
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex-1 flex flex-col shadow-sm">
                  <div className="flex justify-between items-center mb-6 shrink-0">
                    <div className="flex flex-col">
                      <span className="text-[15px] font-[800] text-[#1A1A2E]">Fluxo de Pagamentos</span>
                      <span className="text-[12px] font-[500] text-[#6B7280]">Previsão diária para os próximos 7 dias</span>
                    </div>
                    <div className="text-right">
                      <span className="block text-[16px] font-[800] text-[#7C3AED]">R$ 3.150,00</span>
                      <span className="text-[11px] font-[600] text-[#6B7280]">Total no período</span>
                    </div>
                  </div>
                  
                  <div className="flex-1 w-full min-h-0 relative -ml-4">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={fluxoData} margin={{ top: 10, right: 0, left: 0, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f3f4f6" />
                        <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: '#6B7280', fontWeight: 600 }} dy={5} />
                        <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: '#6B7280' }} tickFormatter={(val) => `R$ ${val}`} />
                        <Tooltip cursor={{fill: '#F8FAFC'}} contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)', fontSize: '12px', fontWeight: 600 }} formatter={(val) => `R$ ${val}`} />
                        <Bar dataKey="value" radius={[4, 4, 0, 0]} maxBarSize={32}>
                          {fluxoData.map((entry, index) => (
                            <Cell key={`cell-${index}`} fill={entry.value > 0 ? '#C4B5FD' : '#E5E7EB'} />
                          ))}
                        </Bar>
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </div>

                {/* PRIORITIES LIST */}
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] w-[450px] flex flex-col shadow-sm overflow-hidden">
                  <div className="p-4 border-b border-[#F1F1F4] flex justify-between items-center bg-[#F9FAFB] shrink-0">
                    <span className="text-[14px] font-[800] text-[#1A1A2E]">Contas Vencidas e em Atraso</span>
                    <button onClick={() => setViewMode("lista")} className="text-[12px] font-[600] text-[#7C3AED] hover:underline">Ver todas</button>
                  </div>
                  <div className="flex-1 overflow-auto custom-scrollbar p-0">
                    {mockContas.filter(c => c.status === "Vencida" || c.status === "Vence Hoje").map(conta => (
                      <div key={conta.id} className="p-4 border-b border-[#F1F1F4] last:border-0 hover:bg-[#F9FAFB] flex flex-col gap-2 transition-colors">
                        <div className="flex justify-between items-start">
                          <div className="flex flex-col">
                            <span className="text-[14px] font-[700] text-[#1A1A2E]">{conta.desc}</span>
                            <span className="text-[12px] text-[#6B7280]">{conta.fornecedor} • {conta.conta}</span>
                          </div>
                          <span className={`text-[15px] font-[800] ${conta.status === 'Vencida' ? 'text-[#EF4444]' : 'text-[#F59E0B]'}`}>{conta.valor}</span>
                        </div>
                        <div className="flex justify-between items-center mt-1">
                          {conta.status === 'Vencida' ? (
                            <span className="inline-flex items-center gap-1 text-[11px] font-[700] text-[#EF4444] bg-[#FEF2F2] px-2 py-0.5 rounded-[4px]">
                              <AlertTriangle className="w-[12px] h-[12px]" /> Vencida há {conta.diasAtraso} dias
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 text-[11px] font-[700] text-[#F59E0B] bg-[#FFFBEB] px-2 py-0.5 rounded-[4px]">
                              <Calendar className="w-[12px] h-[12px]" /> Vence Hoje
                            </span>
                          )}
                          <button className="flex items-center gap-1 text-[12px] font-[700] text-[#10B981] hover:underline">
                            <CheckCircle2 className="w-[14px] h-[14px]" /> Baixar
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

              </div>

            </div>
          ) : (
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0 animate-in fade-in duration-300">
              
              {/* TABS & TOOLS */}
              <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
                <div className="flex items-center gap-1 bg-[#F3F4F6] p-1 rounded-[8px]">
                  {["Todas", "Vencidas", "Para Hoje", "A Vencer", "Pagas"].map((tab) => (
                    <button
                      key={tab}
                      onClick={() => setActiveTab(tab)}
                      className={`px-4 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${activeTab === tab ? 'bg-white text-[#1A1A2E] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                    >
                      {tab}
                    </button>
                  ))}
                </div>
                
                <div className="flex items-center gap-2">
                  <div className="relative">
                    <input 
                      type="text" 
                      placeholder="Buscar por descrição, fornecedor ou NF..." 
                      className="w-[280px] h-[36px] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[13px] outline-none focus:border-[#7C3AED]"
                    />
                    <Search className="w-[14px] h-[14px] text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" />
                  </div>
                  <button className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[36px]">
                    <Filter className="w-[14px] h-[14px] text-[#9CA3AF]" /> Filtros
                  </button>
                  <button className="flex items-center gap-2 px-3 py-1.5 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#374151] hover:bg-[#F9FAFB] h-[36px]">
                    <Columns className="w-[14px] h-[14px] text-[#9CA3AF]" /> Colunas
                  </button>
                </div>
              </div>

              {/* TABLE */}
              <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
                <table className="w-full text-left border-collapse">
                  <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                    <tr>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Vencimento</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Descrição</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Fornecedor</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Categoria</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Conta</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Status</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Valor</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {mockContas.map((conta) => (
                      <tr key={conta.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                        <td className="py-3 px-5">
                          <span className={`text-[13px] font-[700] ${conta.status === 'Vencida' ? 'text-[#EF4444]' : conta.status === 'Vence Hoje' ? 'text-[#F59E0B]' : 'text-[#4B5563]'}`}>{conta.data}</span>
                        </td>
                        <td className="py-3 px-5">
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{conta.desc}</span>
                            {conta.nf !== "-" && <span className="text-[11px] text-[#6B7280] flex items-center gap-1 mt-0.5"><FileText className="w-[10px] h-[10px]" /> NF: {conta.nf}</span>}
                          </div>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] text-[#4B5563]">{conta.fornecedor}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[11px] font-[600] bg-[#F3F4F6] text-[#4B5563]">{conta.cat}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] text-[#6B7280]">{conta.conta}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className={`inline-flex items-center px-2 py-0.5 rounded-[4px] text-[11px] font-[700] ${
                            conta.status === 'Vencida' ? 'bg-[#FEF2F2] text-[#EF4444]' : 
                            conta.status === 'Vence Hoje' ? 'bg-[#FFFBEB] text-[#F59E0B]' : 
                            'bg-[#F3F4F6] text-[#4B5563]'
                          }`}>
                            {conta.status}
                          </span>
                        </td>
                        <td className="py-3 px-5 text-right">
                          <span className="text-[14px] font-[800] text-[#111827]">{conta.valor}</span>
                        </td>
                        <td className="py-3 px-5 text-right flex items-center justify-end gap-1">
                          <button className="h-[32px] px-3 rounded-[6px] flex items-center justify-center gap-1.5 text-[#10B981] font-[600] text-[12px] border border-[#10B981]/20 hover:bg-[#ECFDF5] transition-colors bg-white">
                            <CheckCircle2 className="w-[14px] h-[14px]" /> Baixar
                          </button>
                          <button className="w-[32px] h-[32px] rounded-[6px] flex items-center justify-center text-[#9CA3AF] hover:text-[#111827] hover:bg-[#E5E7EB] transition-colors">
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
                  </select>
                </div>
                <div className="text-[12px] font-[600] text-[#111827]">1-5 de 5</div>
                <div className="flex items-center gap-4">
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
