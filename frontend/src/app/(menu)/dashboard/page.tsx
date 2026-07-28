"use client";

import React, { useState, useEffect } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Sun,
  SunDim,
  Moon,
  DollarSign,
  ShoppingCart,
  Users,
  Megaphone,
  Rocket,
  LineChart,
  Tag,
  RefreshCw,
  UserMinus,
  Brain,
  ArrowRight
} from "lucide-react";
import { 
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer
} from "recharts";
import { useTranslation } from "react-i18next";
import { DashboardService, DashboardData } from "@/services/dashboard.service";
import { useAuth } from "@/context/AuthContext";


const desempenhoComercialData = [
  { name: "01/06", valor: 0 },
  { name: "05/06", valor: 150 },
  { name: "10/06", valor: 300 },
  { name: "15/06", valor: 200 },
  { name: "20/06", valor: 500 },
  { name: "25/06", valor: 400 },
  { name: "30/06", valor: 700 },
];

export default function DashboardPage() {
  const { t } = useTranslation();
  const { user } = useAuth();
  
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [greeting, setGreeting] = useState("Bom dia");
  
  useEffect(() => {
    const hour = new Date().getHours();
    if (hour >= 5 && hour < 12) setGreeting("Bom dia");
    else if (hour >= 12 && hour < 18) setGreeting("Boa tarde");
    else setGreeting("Boa noite");
    
    DashboardService.obterDados().then(res => {
      setData(res);
      setLoading(false);
    }).catch(err => {
      console.error(err);
      setLoading(false);
    });
  }, []);

  const getGreetingConfig = () => {
    if (greeting === "Bom dia") {
      return {
        icon: <Sun className="text-white drop-shadow-sm" strokeWidth={2} />,
        bg: "bg-gradient-to-br from-amber-400 to-orange-500 shadow-[0_4px_14px_rgba(245,158,11,0.3)]"
      }
    }
    if (greeting === "Boa tarde") {
      return {
        icon: <SunDim className="text-white drop-shadow-sm" strokeWidth={2} />,
        bg: "bg-gradient-to-br from-sky-400 to-blue-500 shadow-[0_4px_14px_rgba(56,187,248,0.3)]"
      }
    }
    return {
      icon: <Moon className="text-white drop-shadow-sm" strokeWidth={2.2} />,
      bg: "bg-gradient-to-br from-[#3B0764] to-[#0F172A] shadow-[0_4px_14px_rgba(59,7,100,0.3)]"
    }
  };
  const greetingConfig = getGreetingConfig();
  
  const formatCurrency = (val: number) => 
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

  // Fallback values se loading ou erro
  const receita = data?.kpis.receita_bruta || 0;
  const vendas = data?.kpis.total_vendas || 0;
  const clientes = data?.kpis.total_clientes || 0;
  
  const chartData = data?.charts.receita_mensal.labels.map((label, index) => ({
    name: label,
    valor: data.charts.receita_mensal.data[index]
  })) || [];

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />
        
        <main className="flex-1 flex flex-col p-[20px_32px] min-h-0 relative gap-[16px]">
          
          {/* HEADER DE SAUDAÇÃO */}
          <div className="bg-gradient-to-br from-[#6D28D9] to-[#5B21B6] rounded-[16px] p-[16px_24px] flex items-center justify-between shadow-[0_4px_16px_rgba(124,58,237,0.3)] shrink-0">
            <div className="flex items-center gap-[16px]">
              <div className={`w-[56px] h-[56px] rounded-[14px] flex items-center justify-center shrink-0 [&>svg]:!w-[28px] [&>svg]:!h-[28px] ${greetingConfig.bg}`}>
                {greetingConfig.icon}
              </div>
              <div className="flex flex-col">
                <h2 className="text-[24px] font-[700] text-white tracking-tight">
                <span className="fade-in inline-block">
                  {greeting}, {user?.name ? user.name.split(' ')[0] : 'Admin'}
                </span>
                <span className="wave-animation inline-block ml-2 origin-bottom-right">👋</span></h2>
                <p className="text-[14px] text-purple-200 mt-1">{t("Aqui está o resumo da sua operação comercial hoje.")}</p>
              </div>
            </div>
            
            {/* Opcional: Algum card ou ação no topo direito, ou pode ficar vazio */}
          </div>

          {/* 3 KPIs SUPERIORES */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px] shrink-0 mb-[24px]">
            
            {/* KPI 1: Faturamento */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[16px] flex flex-col shadow-sm hover:shadow-md transition-shadow">
              <div className="flex items-center justify-between mb-[8px]">
                <div className="flex items-center gap-[8px]">
                  <div className="w-[32px] h-[32px] rounded-[8px] bg-green-50 flex items-center justify-center">
                    <DollarSign className="w-[16px] h-[16px] text-green-600" />
                  </div>
                  <span className="text-[14px] font-[600] text-[#4B5563]">{t("Faturamento")}</span>
                </div>
              </div>
              <div className="flex flex-col">
                <span className="text-[24px] font-[700] text-[#111827]">
                  {loading ? '...' : formatCurrency(receita)}
                </span>
                <span className="text-[12px] text-green-600 font-[500] mt-1">+12% vs. mês anterior</span>
              </div>
            </div>

            {/* KPI 2: Vendas */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[16px] flex flex-col shadow-sm hover:shadow-md transition-shadow">
              <div className="flex items-center justify-between mb-[8px]">
                <div className="flex items-center gap-[8px]">
                  <div className="w-[32px] h-[32px] rounded-[8px] bg-blue-50 flex items-center justify-center">
                    <ShoppingCart className="w-[16px] h-[16px] text-blue-600" />
                  </div>
                  <span className="text-[14px] font-[600] text-[#4B5563]">{t("Vendas Realizadas")}</span>
                </div>
              </div>
              <div className="flex flex-col">
                <span className="text-[24px] font-[700] text-[#111827]">
                  {loading ? '...' : vendas}
                </span>
                <span className="text-[12px] text-green-600 font-[500] mt-1">+5% vs. mês anterior</span>
              </div>
            </div>

            {/* KPI 3: Clientes */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[16px] flex flex-col shadow-sm hover:shadow-md transition-shadow">
              <div className="flex items-center justify-between mb-[8px]">
                <div className="flex items-center gap-[8px]">
                  <div className="w-[32px] h-[32px] rounded-[8px] bg-purple-50 flex items-center justify-center">
                    <Users className="w-[16px] h-[16px] text-purple-600" />
                  </div>
                  <span className="text-[14px] font-[600] text-[#4B5563]">{t("Clientes Ativos")}</span>
                </div>
              </div>
              <div className="flex flex-col">
                <span className="text-[24px] font-[700] text-[#111827]">
                  {loading ? '...' : clientes}
                </span>
                <span className="text-[12px] text-purple-600 font-[500] mt-1">Estável</span>
              </div>
            </div>

          </div>

          {/* ÁREA PRINCIPAL: GRÁFICO (ESQUERDA) E CARDS LATERAIS (DIREITA) */}
          <div className="flex gap-[16px] flex-1 min-h-[400px]">
            
            {/* GRÁFICO PRINCIPAL */}
            <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-[20px] flex-1 flex flex-col shadow-sm">
              <div className="flex justify-between items-center mb-[20px] shrink-0">
                <div className="flex items-center gap-2">
                  <LineChart className="w-[20px] h-[20px] text-[#6D28D9]" />
                  <span className="text-[16px] font-[800] text-[#1A1A2E] tracking-tight">{t("Desempenho Comercial")}</span>
                </div>
                <div className="flex items-center gap-2">
                  <span className="text-[12px] text-[#6B7280] font-[500] uppercase tracking-wider">Período:</span>
                  <select className="text-[13px] font-[600] text-[#4C1D95] border border-[#E5E7EB] bg-[#F8F7FF] px-3 py-1.5 rounded-[8px] outline-none cursor-pointer">
                    <option>{t("Este Mês")}</option>
                  </select>
                </div>
              </div>
              
              <div className="flex-1 w-full min-h-0 ml-[-20px]">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart data={chartData.length > 0 ? chartData : desempenhoComercialData} margin={{ top: 10, right: 0, left: 0, bottom: 0 }}>
                    <defs>
                      <linearGradient id="colorValor" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#A78BFA" stopOpacity={0.4}/>
                        <stop offset="95%" stopColor="#A78BFA" stopOpacity={0}/>
                      </linearGradient>
                    </defs>
                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f3f4f6" />
                    <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: '#9CA3AF', fontWeight: 500 }} dy={10} />
                    <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: '#9CA3AF', fontWeight: 500 }} tickFormatter={(val) => `R$ ${val}`} />
                    <Tooltip contentStyle={{ borderRadius: '10px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)', fontSize: '12px', fontWeight: 600 }} />
                    <Area type="monotone" dataKey="valor" name="Valor" stroke="#A78BFA" strokeWidth={3} fillOpacity={1} fill="url(#colorValor)" activeDot={{ r: 6, fill: '#6D28D9', stroke: '#fff', strokeWidth: 2 }} />
                  </AreaChart>
                </ResponsiveContainer>
              </div>
            </div>

            {/* COLUNA DIREITA: CARDS EXTRAS */}
            <div className="w-[300px] flex flex-col gap-[16px] shrink-0">
              
              {/* Ticket Médio */}
              <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-[20px] shadow-sm flex items-center gap-[16px] hover:shadow-md transition-shadow">
                <div className="w-[46px] h-[46px] rounded-[12px] bg-sky-50 flex items-center justify-center shrink-0">
                  <Tag className="w-[24px] h-[24px] text-sky-500" strokeWidth={2.5} />
                </div>
                <div className="flex flex-col">
                  <span className="text-[10px] font-[800] text-[#9CA3AF] uppercase tracking-widest">{t("Ticket Médio")}</span>
                  <span className="text-[22px] font-[800] text-[#1A1A2E] leading-tight">
                    {loading ? '...' : formatCurrency(vendas > 0 ? (receita / vendas) : 0)}
                  </span>
                </div>
              </div>

              {/* Renovações */}
              <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-[20px] shadow-sm flex items-center gap-[16px] hover:shadow-md transition-shadow">
                <div className="w-[46px] h-[46px] rounded-[12px] bg-amber-50 flex items-center justify-center shrink-0">
                  <RefreshCw className="w-[24px] h-[24px] text-amber-500" strokeWidth={2.5} />
                </div>
                <div className="flex flex-col">
                  <span className="text-[10px] font-[800] text-[#9CA3AF] uppercase tracking-widest">{t("Renovações")}</span>
                  <span className="text-[22px] font-[800] text-[#1A1A2E] leading-tight">0 <span className="text-[14px] font-[700] text-[#6B7280]">UND</span></span>
                </div>
              </div>

              {/* Churn Rate */}
              <div className="bg-white rounded-[14px] border border-[#E5E7EB] p-[20px] shadow-sm flex items-center gap-[16px] hover:shadow-md transition-shadow">
                <div className="w-[46px] h-[46px] rounded-[12px] bg-red-50 flex items-center justify-center shrink-0">
                  <UserMinus className="w-[24px] h-[24px] text-red-500" strokeWidth={2.5} />
                </div>
                <div className="flex flex-col">
                  <span className="text-[10px] font-[800] text-[#9CA3AF] uppercase tracking-widest">{t("Churn Rate")}</span>
                  <span className="text-[22px] font-[800] text-[#1A1A2E] leading-tight">0.0%</span>
                </div>
              </div>



            </div>
          </div>
          
        </main>
      </div>
    </div>
  );
}
