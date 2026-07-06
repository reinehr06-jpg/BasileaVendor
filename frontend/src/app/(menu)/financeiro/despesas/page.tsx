/*
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🗺️ MAPA DO TESOURO — TELA: DESPESAS (Listagem + Dashboard)
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * 📍 ROTA: /despesas
 * 📁 ARQUIVO: src/app/despesas/page.tsx
 *
 * 🎯 OBJETIVO DESTA TELA:
 *    Exibir todas as despesas (saídas financeiras) da instituição com duas visões:
 *      1. DASHBOARD: KPIs visuais + gráfico de evolução + gráfico de categorias
 *      2. LISTA: Tabela completa com filtros, paginação e ações por registro
 *
 * 🔗 INTEGRAÇÕES COM O BACK-END:
 *    1. GET /api/despesas?page=1&limit=10&status=todas → Lista paginada de despesas
 *    2. GET /api/despesas/resumo → KPIs (total pago, pendente, vencido, qtd fornecedores)
 *    3. GET /api/despesas/evolucao?periodo=30d → Dados do gráfico de evolução (fixas vs variáveis)
 *    4. GET /api/despesas/categorias → Dados do gráfico de barras por categoria
 *    5. POST /api/despesas → Criar nova despesa (botão "Nova Despesa" redireciona para /despesas/nova)
 *    6. GET /api/despesas/exportar?formato=csv → Exportar despesas em CSV/PDF
 *    7. DELETE /api/despesas/{id} → Excluir uma despesa
 *    8. PUT /api/despesas/{id}/pagar → Marcar despesa como paga
 *
 * 📌 REGRAS DE NEGÓCIO:
 *    - Despesa pode ter 3 status: "Pago" (verde), "Agendado" (amarelo/roxo), "Vencido" (vermelho)
 *    - "Vencido" = data de vencimento passou e não foi pago
 *    - "Agendado" = despesa cadastrada mas ainda não venceu
 *    - "Pago" = despesa quitada (deve ter data de pagamento preenchida)
 *    - A tabela deve permitir filtrar por abas: Todas, Pagas, Pendentes, Vencidas
 *    - O toggle Dashboard/Lista alterna entre visão gráfica e visão tabular
 *    - Botão "Exportar" gera CSV ou PDF com os dados filtrados atualmente
 *    - Botão "Nova Despesa" redireciona para /despesas/nova (formulário de criação)
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

"use client"; // Obrigatório: componente interativo com estados

// ─── IMPORTAÇÕES ─────────────────────────────────────────────────────────────
import React, { useState } from "react";
import Link from "next/link";                 // Navegação Next.js (Link para /despesas/nova)
import Sidebar from "@/components/Sidebar";   // Menu lateral (navegação principal)
import Topbar from "@/components/Topbar";     // Barra superior (busca global)
import { 
  ArrowDownCircle, // Ícone do header (seta pra baixo = saída de dinheiro)
  Plus,            // Ícone "+" (botão Nova Despesa)
  Search,          // Ícone de lupa (busca na tabela)
  Filter,          // Ícone de filtro (botão filtros avançados)
  Columns,         // Ícone de colunas (personalizar colunas visíveis)
  Download,        // Ícone de download (botão Exportar)
  MoreVertical,    // Ícone 3 pontos (menu de ações por registro)
  ChevronLeft,     // Seta esquerda (paginação)
  ChevronRight,    // Seta direita (paginação)
  FileText,        // Ícone de documento (coluna de nota fiscal)
  LayoutDashboard, // Ícone de dashboard (toggle de visão)
  List,            // Ícone de lista (toggle de visão)
  TrendingDown,    // Ícone de tendência (variação % nos KPIs)
  Building2,       // Ícone de prédio (KPI de fornecedores)
  CalendarDays,    // Ícone de calendário (KPI de vencimentos)
  CreditCard       // Ícone de cartão (KPI de despesas pendentes)
} from "lucide-react";
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, Cell, PieChart, Pie, Legend, ComposedChart, Line } from "recharts";

/*
 * 📊 MOCK: Dados do gráfico de evolução de despesas (Fixas vs Variáveis)
 * 🔗 BACK-END: GET /api/despesas/evolucao?periodo=30d
 *    Resposta: Array de { name: "DD/MM", fixas: number, variaveis: number }
 */
const despesasEvolucaoData = [
  { name: "01/06", fixas: 2400, variaveis: 1200 },
  { name: "05/06", fixas: 1398, variaveis: 800 },
  { name: "10/06", fixas: 9800, variaveis: 2400 },
  { name: "15/06", fixas: 3908, variaveis: 1800 },
  { name: "20/06", fixas: 4800, variaveis: 2900 },
  { name: "25/06", fixas: 3800, variaveis: 1500 },
  { name: "30/06", fixas: 4300, variaveis: 2100 },
];

/*
 * 📊 MOCK: Dados do gráfico de despesas por categoria
 * 🔗 BACK-END: GET /api/despesas/categorias
 *    Resposta: Array de { name: "NomeCategoria", value: number }
 */
const despesasCategoriaData = [
  { name: "Manutenção", value: 8500 },
  { name: "Folha / Prebendas", value: 18000 },
  { name: "Eventos", value: 4500 },
  { name: "Taxas/Impostos", value: 2500 },
].sort((a, b) => a.value - b.value);

/* 🎨 Cores para o gráfico de barras (tons de roxo — identidade visual do sistema) */
const COLORS = ["#6D28D9", "#8B5CF6", "#A78BFA", "#C4B5FD"];

export default function DespesasPage() {
  const [viewMode, setViewMode] = useState<"dashboard" | "lista">("dashboard");
  const [activeTab, setActiveTab] = useState("Todas");

  const mockDespesas = [
    { id: 1, venc: "20/05/2024", pagto: "20/05/2024", desc: "Conta de Energia", conta: "Itaú - CC 1234", cat: "Energia", fornecedor: "Enel", valor: "R$ 450,00", status: "Pago", nf: "NF-0442" },
    { id: 2, venc: "28/05/2024", pagto: "-", desc: "Aluguel Prédio Sede", conta: "Itaú - CC 1234", cat: "Imóveis", fornecedor: "Imobiliária XP", valor: "R$ 4.500,00", status: "Agendado", nf: "-" },
    { id: 3, venc: "10/05/2024", pagto: "-", desc: "Compra de Microfones", conta: "Caixa Físico", cat: "Equipamentos", fornecedor: "AudioTech", valor: "R$ 1.200,00", status: "Vencido", nf: "NF-8921" },
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
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#FEE2E2] flex items-center justify-center shrink-0">
                <ArrowDownCircle className="w-[20px] h-[20px] text-[#DC2626]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[18px] font-[800] text-[#1A1A2E] leading-tight">Despesas</h1>
                <p className="text-[12px] font-[500] text-[#6B7280]">Gestão completa de todas as saídas, custos e despesas gerais.</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              
              {/* 🗺️ MAPA DO TESOURO: TOGGLE DE VISÃO (DASHBOARD vs LISTA)
                  Controla o estado 'viewMode'. Se 'dashboard', mostra KPIs e gráficos.
                  Se 'lista', mostra a tabela completa com paginação e filtros. */}
              <div className="flex items-center bg-[#F3F4F6] p-1 rounded-[8px] mr-2">
                <button 
                  onClick={() => setViewMode("dashboard")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "dashboard" ? 'bg-white text-[#DC2626] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <LayoutDashboard className="w-[14px] h-[14px]" /> Dashboard
                </button>
                <button 
                  onClick={() => setViewMode("lista")}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[13px] font-[600] transition-colors ${viewMode === "lista" ? 'bg-white text-[#DC2626] shadow-sm' : 'text-[#6B7280] hover:text-[#374151]'}`}
                >
                  <List className="w-[14px] h-[14px]" /> Lista
                </button>
              </div>

              <button className="flex items-center gap-2 px-4 py-2 border border-[#E5E7EB] rounded-[8px] text-[13px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-sm">
                <Download className="w-[14px] h-[14px]" /> Exportar
              </button>
              <Link href="/financeiro/despesas/nova" className="bg-[#6D28D9] hover:bg-[#5B21B6] transition-colors text-white px-4 py-2 rounded-[8px] text-[13px] font-[700] flex items-center gap-2 shadow-sm">
                <Plus className="w-[16px] h-[16px]" strokeWidth={2.5} />
                Nova Despesa
              </Link>
            </div>
          </div>

          {/* DYNAMIC CONTENT */}
          {viewMode === "dashboard" ? (
            <div className="flex flex-col flex-1 gap-4 overflow-y-auto custom-scrollbar pb-4 animate-in fade-in duration-300">
              
              {/* 🗺️ MAPA DO TESOURO: GRID DE KPIs
                  Exibidos apenas no modo 'dashboard'. */}
              <div className="grid grid-cols-4 gap-4 shrink-0">
                
                {/* 🗺️ MAPA DO TESOURO: KPI 1 - Total Pago no Mês
                    Soma de todas as despesas com status 'Pago' no mês atual.
                    🔗 BACK-END: GET /api/despesas/resumo -> total_pago */}
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Total Pago no Mês</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">33.500<span className="text-[14px]">,00</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#10B981] mt-1 flex items-center gap-1">
                      <TrendingDown className="w-[12px] h-[12px]" strokeWidth={3} /> -5% vs mês passado
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#FEE2E2] flex items-center justify-center shrink-0">
                    <ArrowDownCircle className="w-[20px] h-[20px] text-[#DC2626]" strokeWidth={2.4} />
                  </div>
                </div>

                {/* 🗺️ MAPA DO TESOURO: KPI 2 - A Pagar (Hoje)
                    Soma das despesas cujo vencimento é HOJE e status é 'Agendado'. */}
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">A Pagar (Hoje)</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">1.450<span className="text-[14px]">,00</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#A78BFA] mt-1 flex items-center gap-1">
                      Em 2 boletos
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#FFFBEB] flex items-center justify-center shrink-0">
                    <CalendarDays className="w-[20px] h-[20px] text-[#A78BFA]" strokeWidth={2.4} />
                  </div>
                </div>

                {/* 🗺️ MAPA DO TESOURO: KPI 3 - Atrasadas
                    Soma das despesas cujo vencimento já passou (ontem ou antes) e não estão pagas. */}
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Atrasadas</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">0<span className="text-[14px]">,00</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#10B981] mt-1 flex items-center gap-1">
                      Nenhuma pendência atrasada
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#ECFDF5] flex items-center justify-center shrink-0">
                    <Building2 className="w-[20px] h-[20px] text-[#10B981]" strokeWidth={2.4} />
                  </div>
                </div>

                {/* 🗺️ MAPA DO TESOURO: KPI 4 - Fixas vs Variáveis
                    Percentual comparativo do tipo de despesa no mês atual. */}
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex items-center justify-between shadow-sm">
                  <div className="flex flex-col">
                    <span className="text-[12px] font-[600] text-[#6B7280]">Fixas vs Variáveis</span>
                    <div className="flex items-baseline gap-1 mt-1">
                      <span className="text-[22px] font-[800] text-[#1A1A2E]">70%<span className="text-[14px]"> / 30%</span></span>
                    </div>
                    <span className="text-[11px] font-[600] text-[#6B7280] mt-1 flex items-center gap-1">
                      Proporção saudável
                    </span>
                  </div>
                  <div className="w-[42px] h-[42px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                    <CreditCard className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.4} />
                  </div>
                </div>
              </div>

              {/* 🗺️ MAPA DO TESOURO: GRÁFICOS */}
              <div className="flex gap-4 shrink-0 h-[280px]">
                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 flex-1 flex flex-col shadow-sm">
                  <div className="flex justify-between items-center mb-4 shrink-0">
                    <span className="text-[14px] font-[700] text-[#1A1A2E]">Evolução de Saídas</span>
                    <select className="text-[11px] border border-[#E5E7EB] px-2 py-1 rounded-[6px] text-[#4B5563] outline-none">
                      <option>Este Mês</option>
                    </select>
                  </div>
                  <div className="flex-1 w-full min-h-0 ml-[-20px]">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={despesasEvolucaoData} margin={{ top: 5, right: 0, left: 0, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f3f4f6" />
                        <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9CA3AF' }} dy={5} />
                        <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9CA3AF' }} tickFormatter={(val) => `R$ ${val/1000}k`} />
                        <Tooltip contentStyle={{ borderRadius: '10px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)', fontSize: '11px', fontWeight: 500 }} />
                        <Legend verticalAlign="top" align="right" wrapperStyle={{ fontSize: '11px', paddingBottom: '10px' }} iconType="circle" />
                        <Bar dataKey="fixas" name="Fixas" stackId="a" fill="#6D28D9" maxBarSize={30} />
                        <Bar dataKey="variaveis" name="Variáveis" stackId="a" fill="#C4B5FD" radius={[4, 4, 0, 0]} maxBarSize={30} />
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </div>

                <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-5 w-[380px] flex flex-col shadow-sm">
                  <span className="text-[14px] font-[700] text-[#1A1A2E] mb-4 shrink-0">Despesas por Categoria</span>
                  <div className="flex-1 w-full min-h-0">
                    <ResponsiveContainer width="100%" height="100%">
                      <PieChart>
                        <Pie data={despesasCategoriaData} dataKey="value" nameKey="name" cx="50%" cy="50%" innerRadius={60} outerRadius={80} paddingAngle={5}>
                          {despesasCategoriaData.map((entry, index) => (
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
                  <span className="text-[14px] font-[700] text-[#1A1A2E]">Próximos Vencimentos</span>
                  <button onClick={() => setViewMode("lista")} className="text-[12px] font-[600] text-[#DC2626] hover:underline">Ver todos</button>
                </div>
                <div className="flex-1 overflow-auto custom-scrollbar">
                  <table className="w-full text-left">
                    <thead>
                      <tr className="border-b border-[#F1F1F4]">
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase sticky top-0 bg-white">Vencimento</th>
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase sticky top-0 bg-white">Descrição</th>
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase sticky top-0 bg-white">Fornecedor</th>
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase sticky top-0 bg-white text-right">Valor</th>
                        <th className="pb-2 text-[10px] font-[700] text-[#9CA3AF] uppercase text-center sticky top-0 bg-white">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {mockDespesas.map((despesa) => (
                        <tr key={despesa.id} className="border-b border-[#F1F1F4] last:border-0 hover:bg-[#F9FAFB]">
                          <td className="py-2.5 text-[12px] font-[600] text-[#4B5563]">{despesa.venc}</td>
                          <td className="py-2.5 text-[12px] font-[600] text-[#1A1A2E]">{despesa.desc}</td>
                          <td className="py-2.5 text-[11px] font-[500] text-[#4B5563]">{despesa.fornecedor}</td>
                          <td className="py-2.5 text-[12px] font-[800] text-[#DC2626] text-right">{despesa.valor}</td>
                          <td className="py-2.5 text-center">
                            <span className={`inline-block px-2 py-0.5 rounded-[4px] text-[10px] font-[700] ${despesa.status === 'Pago' ? 'bg-[#ECFDF5] text-[#10B981]' : despesa.status === 'Vencido' ? 'bg-[#FEF2F2] text-[#6D28D9]' : 'bg-[#EFF6FF] text-[#3B82F6]'}`}>
                              {despesa.status}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
          ) : (
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-sm flex flex-col flex-1 overflow-hidden min-h-0 animate-in fade-in duration-300">
              
              {/* 🗺️ MAPA DO TESOURO: TABS & TOOLS (Apenas no modo Lista)
                  Controles para filtro rápido por status da despesa.
                  🔗 BACK-END: Recarrega GET /api/despesas passando o 'status' selecionado. */}
              <div className="p-4 border-b border-[#F1F1F4] flex items-center justify-between gap-3 shrink-0">
                
                <div className="flex items-center gap-1 bg-[#F3F4F6] p-1 rounded-[8px]">
                  {["Todas", "Vencidas", "Em aberto", "Agendadas", "Pagas", "Aguardando aprovação"].map((tab) => (
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
                      className="w-[280px] h-[36px] border border-[#E5E7EB] rounded-[8px] pl-9 pr-3 text-[13px] outline-none focus:border-[#DC2626]"
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

              {/* 🗺️ MAPA DO TESOURO: TABELA PRINCIPAL (Visão Lista)
                  Lista todas as despesas conforme filtro selecionado. */}
              <div className="flex-1 overflow-y-auto custom-scrollbar p-0 m-0 relative">
                <table className="w-full text-left border-collapse">
                  <thead className="sticky top-0 bg-[#F9FAFB] shadow-[0_1px_0_#F1F1F4] z-10">
                    <tr>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Vencimento / Pagto</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Descrição</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Fornecedor</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Categoria</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4]">Conta</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Valor</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-center">Status</th>
                      <th className="py-3 px-5 text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider border-b border-[#F1F1F4] text-right">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {mockDespesas.map((despesa) => (
                      <tr key={despesa.id} className="hover:bg-[#F9FAFB] transition-colors group border-b border-[#F1F1F4]">
                        <td className="py-3 px-5">
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">V: {despesa.venc}</span>
                            <span className="text-[11px] text-[#6B7280]">P: {despesa.pagto}</span>
                          </div>
                        </td>
                        <td className="py-3 px-5">
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[700] text-[#111827]">{despesa.desc}</span>
                            {despesa.nf !== "-" && <span className="text-[11px] text-[#6B7280] flex items-center gap-1 mt-0.5"><FileText className="w-[10px] h-[10px]" /> NF: {despesa.nf}</span>}
                          </div>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] font-[600] text-[#4B5563]">{despesa.fornecedor}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[11px] font-[600] bg-[#F3F4F6] text-[#4B5563]">{despesa.cat}</span>
                        </td>
                        <td className="py-3 px-5">
                          <span className="text-[13px] text-[#6B7280]">{despesa.conta}</span>
                        </td>
                        <td className="py-3 px-5 text-right">
                          <span className="text-[14px] font-[800] text-[#DC2626]">{despesa.valor}</span>
                        </td>
                        <td className="py-3 px-5 text-center">
                          <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-[700] ${
                            despesa.status === 'Pago' ? 'bg-[#ECFDF5] text-[#10B981]' : 
                            despesa.status === 'Vencido' ? 'bg-[#FEF2F2] text-[#6D28D9]' : 
                            despesa.status === 'Agendado' ? 'bg-[#EFF6FF] text-[#3B82F6]' : 
                            despesa.status === 'Cancelado' ? 'bg-[#F3F4F6] text-[#6B7280]' :
                            despesa.status === 'Aguardando aprovação' ? 'bg-[#FEF08A] text-[#854D0E]' :
                            despesa.status === 'Parcialmente pago' ? 'bg-[#D1FAE5] text-[#047857]' :
                            despesa.status === 'Em aberto' ? 'bg-[#FFF7ED] text-[#EA580C]' :
                            despesa.status === 'Contestação' ? 'bg-[#FEE2E2] text-[#B91C1C]' :
                            'bg-[#F3F4F6] text-[#6B7280]'
                          }`}>
                            {despesa.status}
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

              {/* 🗺️ MAPA DO TESOURO: FOOTER DA TABELA / PAGINAÇÃO
                  Controla limites e páginas usando requisições ao back-end. */}
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
                    <button className="w-[28px] h-[28px] flex items-center justify-center rounded-[6px] bg-[#FEF2F2] text-[#DC2626] font-[700] text-[12px] transition-colors">
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
