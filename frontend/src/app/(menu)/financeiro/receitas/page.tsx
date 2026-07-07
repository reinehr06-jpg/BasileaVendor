/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: RECEITAS (Listagem + Dashboard)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /receitas
 * 📁 ARQUIVO: src/app/receitas/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Exibir todas as receitas (entradas financeiras) da instituição com duas visões:
 *      1. DASHBOARD: KPIs visuais + gráfico de evolução + gráfico de categorias
 *      2. LISTA: Tabela completa com filtros, paginação e ações por registro
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/receitas?page=1&limit=10&status=todas → Lista paginada de receitas
 *    2. GET /api/receitas/resumo → KPIs (total recebido, a receber, vencidas, qtd fontes)
 *    3. GET /api/receitas/evolucao?periodo=30d → Dados do gráfico de evolução (dízimos vs ofertas)
 *    4. GET /api/receitas/categorias → Dados do gráfico de barras por categoria
 *    5. POST /api/receitas → Criar nova receita (botão "Nova Receita" redireciona para /receitas/nova)
 *    6. GET /api/receitas/exportar?formato=csv → Exportar receitas em CSV/PDF
 *
 * 📌 REGRAS DE NEGÓCIO:
 *    - Receita pode ter status: "Recebido" (verde), "Aguardando recebimento" (amarelo), "Parcialmente recebido" (verde claro)
 *    - A tabela deve permitir filtrar por abas: Todas, Recebidas, Pendentes
 *    - O toggle Dashboard/Lista alterna entre visão gráfica e visão tabular
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
  Plus, 
  Search, 
  Filter, 
  ArrowUpCircle, 
  Download,
  LayoutDashboard,
  List,
  Calendar,
  MoreVertical,
  ChevronLeft,
  ChevronRight,
  TrendingUp,
  CreditCard,
  Building2,
  CalendarDays,
  FileText
} from "lucide-react";
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, Cell, PieChart, Pie, Legend, ComposedChart, Line } from "recharts";
import { ReceitasService } from "@/services/receitas.service";

/*
 * 📊 MOCK: Dados do gráfico de evolução de receitas (Dízimos vs Ofertas)
 * 🔗 BACK-END: GET /api/receitas/evolucao?periodo=30d
 */
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
  { name: "Dízimos", value: 45000 },
  { name: "Ofertas Gerais", value: 12000 },
  { name: "Ofertas Especiais", value: 8000 },
  { name: "Cantina/Eventos", value: 4500 },
].sort((a, b) => a.value - b.value);

const COLORS = ["#6D28D9", "#8B5CF6", "#A78BFA", "#C4B5FD"];

export default function ReceitasPage() {
  const [viewMode, setViewMode] = useState<"dashboard" | "lista">("dashboard");
  const [activeTab, setActiveTab] = useState("Todas");
  const [receitas, setReceitas] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    carregarReceitas();
  }, []);

  const carregarReceitas = async () => {
    try {
      setLoading(true);
      const res = await ReceitasService.listar();
      setReceitas(res.data.data);
    } catch (error) {
      console.error("Erro ao carregar receitas", error);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateStr: string) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('pt-BR').format(date);
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
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                <ArrowUpCircle className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Receitas</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gestão completa de todas as entradas e recebimentos do caixa.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              
              {/* VIEW TOGGLE */}
              <div className="flex items-center bg-[#F3F4F6] p-1 rounded-[8px] mr-2">
                <button 
                  onClick={() => setViewMode("dashboard")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "dashboard" ? 'bg-white text-[#6D28D9] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <LayoutDashboard className="w-[14px] h-[14px]" /> Dashboard
                </button>
                <button 
                  onClick={() => setViewMode("lista")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "lista" ? 'bg-white text-[#6D28D9] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <List className="w-[14px] h-[14px]" /> Lista
                </button>
              </div>

              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Download className="w-[14px] h-[14px]" /> Exportar
              </button>
              <Link href="/financeiro/receitas/nova" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
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
                    <span className="text-[11px] font-[600] text-[#6D28D9] mt-1 flex items-center gap-1">
                      <TrendingUp className="w-[12px] h-[12px]" strokeWidth={3} /> +15% vs mês passado
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                    <ArrowUpCircle className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">A Receber (Previsão)</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">12.450<span className="text-[14px]">,00</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#F59E0B] mt-1 flex items-center gap-1">
                      3 pendências aguardando
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#FFFBEB] flex items-center justify-center shrink-0">
                    <CalendarDays className="w-[20px] h-[20px] text-[#F59E0B]" strokeWidth={2.4} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Ticket Médio (Dízimo)</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">450<span className="text-[14px]">,00</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#6D28D9] mt-1 flex items-center gap-1">
                      <TrendingUp className="w-[12px] h-[12px]" strokeWidth={3} /> +5% vs mês passado
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                    <CreditCard className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Maior Fonte</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[18px] font-[800] text-[#1A1A2E] truncate">Congregação Sede</span>
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
                        <Bar dataKey="dízimos" name="Dízimos" fill="#6D28D9" radius={[4, 4, 0, 0]} maxBarSize={20} />
                        <Line type="monotone" dataKey="ofertas" name="Ofertas" stroke="#A78BFA" strokeWidth={3} dot={{ fill: '#A78BFA', strokeWidth: 2, r: 4 }} activeDot={{ r: 6 }} />
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
                  <button onClick={() => setViewMode("lista")} className="text-[12px] font-[600] text-[#6D28D9] hover:underline">Ver todas</button>
                </div>
                <div className="flex-1 flex flex-col gap-3 min-h-0 pr-2 overflow-y-auto">
                  {receitas.slice(0, 3).map((receita) => (
                    <div key={receita.id} className="bg-white border border-[#E5E7EB] rounded-[10px] p-3 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:border-[#D1D5DB] transition-colors cursor-pointer group">
                      <div className="flex justify-between items-start mb-2">
                        <div>
                          <span className="text-[13px] font-[700] text-[#1A1A2E] block">{receita.descricao}</span>
                          <span className="text-[11px] text-[#6B7280]">{receita.origem || '-'}</span>
                        </div>
                        <span className="text-[13px] font-[800] text-[#1A1A2E]">R$ {parseFloat(receita.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
                      </div>
                      <div className="flex justify-between items-end mt-2 pt-2 border-t border-[#F1F1F4]">
                        <div className="flex items-center gap-2">
                          <div className={`inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-[700] uppercase tracking-wide
                            ${receita.status === 'Recebido' ? 'bg-[#10B981]/10 text-[#10B981]' : 
                              receita.status === 'Aguardando recebimento' ? 'bg-[#F59E0B]/10 text-[#F59E0B]' : 
                              'bg-[#3B82F6]/10 text-[#3B82F6]'}
                          `}>
                            <div className={`w-1 h-1 rounded-full 
                              ${receita.status === 'Recebido' ? 'bg-[#10B981]' : 
                                receita.status === 'Aguardando recebimento' ? 'bg-[#F59E0B]' : 
                                'bg-[#3B82F6]'}
                            `}></div>
                            {receita.status}
                          </div>
                          <span className="text-[11px] text-[#9CA3AF] flex items-center gap-1"><Calendar className="w-3 h-3" /> {formatDate(receita.data)}</span>
                        </div>
                        <button className="text-[11px] font-[600] text-[#6D28D9] hover:underline opacity-0 group-hover:opacity-100 transition-opacity">Detalhes</button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

            </div>
          ) : (
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0 animate-in fade-in duration-300">
              
              {/* TABS & TOOLS */}
              <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
                
                <div className="flex items-center gap-1 bg-[#F3F4F6] p-1 rounded-[8px]">
                  {["Todas", "Recebidas", "Aguardando recebimento", "Parcialmente recebidas", "Vencidas"].map((tab) => (
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
                      placeholder="Buscar por descrição, origem ou NF..." 
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
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Status</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {receitas.map((receita) => (
                      <tr key={receita.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                        <td className="py-3 px-5">
                          <span className="text-[13px] font-[600] text-[#4B5563]">{formatDate(receita.data)}</span>
                        </td>
                        <td className="py-3 px-5">
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{receita.descricao}</span>
                            {receita.nf && <span className="text-[11px] text-[#6B7280] flex items-center gap-1 mt-0.5"><FileText className="w-[10px] h-[10px]" /> NF: {receita.nf}</span>}
                          </div>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] text-[#4B5563]">{receita.origem}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[11px] font-[600] bg-[#F3F4F6] text-[#4B5563]">{receita.categoria}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] text-[#6B7280]">{receita.conta}</span>
                        </td>
                        <td className="py-3 px-5 text-right">
                          <span className="text-[14px] font-[800] text-[#6D28D9]">R$ {parseFloat(receita.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
                        </td>
                        <td className="py-3 px-5 text-center">
                          <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-[700] ${
                            receita.status === 'Recebido' ? 'bg-[#ECFDF5] text-[#6D28D9]' : 
                            receita.status === 'Aguardando recebimento' ? 'bg-[#EFF6FF] text-[#3B82F6]' : 
                            receita.status === 'Parcialmente recebido' ? 'bg-[#FEF08A] text-[#854D0E]' : 
                            receita.status === 'Vencido' ? 'bg-[#FEF2F2] text-[#EF4444]' : 
                            receita.status === 'Cancelado' || receita.status === 'Estornado' ? 'bg-[#F3F4F6] text-[#6B7280]' : 
                            'bg-[#F3F4F6] text-[#6B7280]'
                          }`}>
                            {receita.status}
                          </span>
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
                    <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#E5E7EB] hover:text-[#374151] transition-colors disabled:opacity-50" disabled>
                      <ChevronLeft className="w-[14px] h-[14px]" />
                    </button>
                    <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] bg-[#ECFDF5] text-[#6D28D9] font-[700] text-[12px] transition-colors">
                      1
                    </button>
                    <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] text-[#9CA3AF] hover:bg-[#E5E7EB] hover:text-[#374151] transition-colors disabled:opacity-50" disabled>
                      <ChevronRight className="w-[14px] h-[14px]" />
                    </button>
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
