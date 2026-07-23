"use client";

import React, { useState, useMemo } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import { VendasService } from "@/services/vendas.service";
import { MetricasService } from "@/services/metricas.service";
import { VendedoresService } from "@/services/vendedores.service";
import { EquipesService } from "@/services/equipes.service";
import {
  PieChart,
  DollarSign,
  TrendingUp,
  TrendingDown,
  CreditCard,
  Calendar,
  Filter
} from "lucide-react";

export default function MetricasVendasPage() {
  const { t } = useTranslation();
  
  // States for filters
  const [periodo, setPeriodo] = useState("Este Ano");
  const [vendedorFiltro, setVendedorFiltro] = useState("");
  const [equipeFiltro, setEquipeFiltro] = useState("");

  const [metrics, setMetrics] = useState<{
    total: string;
    confirmed: number;
    ticket: string;
    churn: string;
    chartData: number[];
    topSellers: any[];
  }>({
    total: "R$ 0,00",
    confirmed: 0,
    ticket: "R$ 0,00",
    churn: "0.0%",
    chartData: [],
    topSellers: []
  });

  const [vendedorOpts, setVendedorOpts] = useState<{ label: string; value: string }[]>([{ label: "Todos os Vendedores", value: "" }]);
  const [equipeOpts, setEquipeOpts] = useState<{ label: string; value: string }[]>([{ label: "Todas as Equipes", value: "" }]);

  React.useEffect(() => {
    VendedoresService.listar()
      .then((vs: any[]) => setVendedorOpts([{ label: "Todos os Vendedores", value: "" }, ...(vs || []).map((v: any) => ({ label: v.nome ?? v.name ?? `#${v.id}`, value: String(v.id) }))]))
      .catch(() => {});
    EquipesService.listar()
      .then((es: any[]) => setEquipeOpts([{ label: "Todas as Equipes", value: "" }, ...(es || []).map((e: any) => ({ label: e.nome ?? e.name ?? `#${e.id}`, value: String(e.id) }))]))
      .catch(() => {});
  }, []);

  React.useEffect(() => {
    MetricasService.obter({ vendedor_id: vendedorFiltro || undefined, equipe_id: equipeFiltro || undefined }).then((data) => {
      const formatCurrency = (val: number) =>
        new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

      setMetrics({
        total: formatCurrency(data.resumo?.receitaTotal || 0),
        confirmed: data.resumo?.totalVendas || 0,
        ticket: formatCurrency(data.resumo?.ticketMedio || 0),
        churn: `${data.resumo?.churn ?? 0}%`,
        chartData: data.receitaMensal?.map((m: any) => m.total) || [],
        topSellers: data.topVendedores?.map((s: any) => ({
          nome: s.name,
          valor: formatCurrency(s.total).replace(",00", "k").replace("R$ ", "R$ ").replace(".", ""),
          percent: s.total > 0 ? (s.total / (data.resumo.receitaTotal || 1)) * 100 : 0
        })) || []
      });
    });
  }, [periodo, vendedorFiltro, equipeFiltro]);
  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT */}
        <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col">

          {/* CARD PRINCIPAL (Padrão do Sistema) */}
          <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

            {/* CABEÇALHO DENTRO DO CARD */}
            <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <PieChart className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col justify-center">
                <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Métricas de Vendas")}</h1>
                <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Visão geral e desempenho do seu time comercial.")}</p>
              </div>
            </div>

            {/* Toolbar de Filtros */}
            <div className="p-[24px] flex flex-col xl:flex-row items-start xl:items-center justify-between gap-[16px] border-b border-[#F1F1F4]">
              <div className="flex items-center gap-[10px]">
                <div className="w-[36px] h-[36px] rounded-[10px] bg-[#F3F4F6] flex items-center justify-center border border-[#E5E7EB]">
                  <Filter className="w-[16px] h-[16px] text-[#6B7280]" />
                </div>
                <span className="text-[14px] font-[600] text-[#4B5563]">{t("Filtros Ativos")}</span>
              </div>
              
              <div className="flex flex-wrap items-center gap-[10px] w-full xl:w-auto">
                <div className="w-full sm:w-auto">
                  <CustomSelect
                    options={[
                      { label: "Este Ano", value: "Este Ano" },
                      { label: "Ano Passado", value: "Ano Passado" },
                      { label: "Este Mês", value: "Este Mês" },
                      { label: "Últimos 30 dias", value: "Últimos 30 dias" }
                    ]}
                    value={periodo}
                    onChange={setPeriodo}
                    triggerClassName="h-[36px] bg-white min-w-[160px] text-[12px]"
                    placeholder="Período"
                  />
                </div>

                <div className="w-full sm:w-auto">
                  <CustomSelect
                    options={equipeOpts}
                    value={equipeFiltro}
                    onChange={setEquipeFiltro}
                    triggerClassName="h-[36px] bg-white min-w-[180px] text-[12px]"
                    placeholder="Equipes"
                  />
                </div>

                <div className="w-full sm:w-auto">
                  <CustomSelect
                    options={vendedorOpts}
                    value={vendedorFiltro}
                    onChange={setVendedorFiltro}
                    searchable={true}
                    triggerClassName="h-[36px] bg-white min-w-[200px] text-[12px]"
                    placeholder="Vendedores"
                  />
                </div>
              </div>
            </div>

            {/* CONTEÚDO DO DASHBOARD (Fundo levemente cinza para destacar os cards internos) */}
            <div className="p-[24px] flex-1 bg-[#FCFCFD] overflow-y-auto">
              {/* KPIs Grid */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[20px] mb-[24px]">
                
                {/* Card 1 */}
                <div className="bg-white rounded-[16px] p-[20px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                  <div className="flex items-center justify-between mb-4">
                    <span className="text-[13px] font-[600] text-[#6B7280]">{t("Total Vendido")}</span>
                    <div className="w-[32px] h-[32px] rounded-full bg-[#ECFDF5] flex items-center justify-center">
                      <DollarSign className="w-[16px] h-[16px] text-[#059669]" />
                    </div>
                  </div>
                  <div>
                    <h3 className="text-[28px] font-[700] text-[#111827]">{metrics.total}</h3>
                    <p className="text-[12px] font-[500] text-[#059669] flex items-center gap-1 mt-1">
                      <TrendingUp className="w-[14px] h-[14px]" /> +12.5% vs período anterior
                    </p>
                  </div>
                </div>

                {/* Card 2 */}
                <div className="bg-white rounded-[16px] p-[20px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                  <div className="flex items-center justify-between mb-4">
                    <span className="text-[13px] font-[600] text-[#6B7280]">{t("Vendas Confirmadas")}</span>
                    <div className="w-[32px] h-[32px] rounded-full bg-[#F3E8FF] flex items-center justify-center">
                      <CreditCard className="w-[16px] h-[16px] text-[#7C3AED]" />
                    </div>
                  </div>
                  <div>
                    <h3 className="text-[28px] font-[700] text-[#111827]">{metrics.confirmed}</h3>
                    <p className="text-[12px] font-[500] text-[#7C3AED] flex items-center gap-1 mt-1">
                      <TrendingUp className="w-[14px] h-[14px]" /> Dentro da expectativa
                    </p>
                  </div>
                </div>

                {/* Card 3 */}
                <div className="bg-white rounded-[16px] p-[20px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                  <div className="flex items-center justify-between mb-4">
                    <span className="text-[13px] font-[600] text-[#6B7280]">{t("Ticket Médio")}</span>
                    <div className="w-[32px] h-[32px] rounded-full bg-[#EFF6FF] flex items-center justify-center">
                      <PieChart className="w-[16px] h-[16px] text-[#2563EB]" />
                    </div>
                  </div>
                  <div>
                    <h3 className="text-[28px] font-[700] text-[#111827]">{metrics.ticket}</h3>
                    <p className="text-[12px] font-[500] text-[#6B7280] flex items-center gap-1 mt-1">
                      Média por transação
                    </p>
                  </div>
                </div>

                {/* Card 4 */}
                <div className="bg-white rounded-[16px] p-[20px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                  <div className="flex items-center justify-between mb-4">
                    <span className="text-[13px] font-[600] text-[#6B7280]">{t("Churn Rate")}</span>
                    <div className="w-[32px] h-[32px] rounded-full bg-[#FEF2F2] flex items-center justify-center">
                      <TrendingDown className="w-[16px] h-[16px] text-[#DC2626]" />
                    </div>
                  </div>
                  <div>
                    <h3 className="text-[28px] font-[700] text-[#111827]">{metrics.churn}</h3>
                    <p className="text-[12px] font-[500] text-[#DC2626] flex items-center gap-1 mt-1">
                      <TrendingDown className="w-[14px] h-[14px]" /> Taxa acompanhada
                    </p>
                  </div>
                </div>
                
              </div>

              {/* Gráfico e ranking */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-[20px] flex-1">
                
                {/* Gráfico de receita */}
                <div className="lg:col-span-2 bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] p-[24px] flex flex-col">
                  <div className="flex items-center justify-between mb-6">
                    <h3 className="text-[16px] font-[700] text-[#111827]">{t("Receita ao Longo do Tempo")}</h3>
                    <div className="flex items-center gap-2 text-[12px] font-[500] text-[#6B7280] border border-[#E5E7EB] rounded-[8px] px-3 py-1.5 cursor-not-allowed bg-gray-50">
                      <Calendar className="w-[14px] h-[14px]" /> {periodo}
                    </div>
                  </div>
                  
                  <div className="flex-1 flex items-end gap-[2%] pt-8">
                    {/* Barras do Gráfico Simuladas (Tailwind) */}
                    {metrics.chartData.map((h, i) => (
                      <div key={i} className="flex-1 flex flex-col items-center gap-2 group">
                        <div 
                          className="w-full bg-[#E0E7FF] rounded-t-[4px] relative overflow-hidden transition-all duration-300 group-hover:bg-[#C7D2FE]" 
                          style={{ height: `${Math.min(h, 100)}%` }}
                        >
                          <div className="absolute bottom-0 w-full bg-[#7C3AED] transition-all duration-300 group-hover:bg-[#6D28D9]" style={{ height: `${Math.min(h * 0.7, 100)}%` }}></div>
                        </div>
                        <span className="text-[10px] font-[600] text-[#9CA3AF]">
                          {["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"][i]}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Ranking Vendedores */}
                <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] p-[24px] flex flex-col">
                  <h3 className="text-[16px] font-[700] text-[#111827] mb-6">{t("Top Vendedores")}</h3>
                  
                  <div className="flex flex-col gap-5 flex-1 justify-center">
                    {metrics.topSellers.map((v, i) => (
                      <div key={i} className="flex flex-col gap-2">
                        <div className="flex items-center justify-between">
                          <span className="text-[13px] font-[600] text-[#374151]">{v.nome}</span>
                          <span className="text-[13px] font-[700] text-[#111827]">{v.valor}</span>
                        </div>
                        <div className="h-[6px] w-full bg-[#F3F4F6] rounded-full overflow-hidden">
                          <div className="h-full bg-[#7C3AED] rounded-full transition-all duration-500" style={{ width: `${v.percent}%` }}></div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

              </div>
            </div>

          </div>

          {/* RODAPÉ COPYRIGHT */}
          <div className="mt-[24px] pb-[12px] flex justify-center">
            <p className="text-[14px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Vendor OS")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
      </div>
    </div>
  );
}
