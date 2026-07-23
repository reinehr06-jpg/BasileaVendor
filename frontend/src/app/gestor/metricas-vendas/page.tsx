"use client";

import React, { useState, useEffect, useMemo } from "react";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import { MetricasService, MetricasVendas } from "@/services/metricas.service";
import { VendedoresService } from "@/services/vendedores.service";
import { EquipesService } from "@/services/equipes.service";
import {
  PieChart,
  DollarSign,
  TrendingUp,
  TrendingDown,
  CreditCard,
  Filter,
} from "lucide-react";

const formatCurrency = (val: number) =>
  new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(val || 0);

export default function MetricasVendasPage() {
  const { t } = useTranslation();

  const [vendedorFiltro, setVendedorFiltro] = useState("");
  const [equipeFiltro, setEquipeFiltro] = useState("");
  const [data, setData] = useState<MetricasVendas | null>(null);
  const [loading, setLoading] = useState(true);

  const [vendedores, setVendedores] = useState<{ label: string; value: string }[]>([]);
  const [equipes, setEquipes] = useState<{ label: string; value: string }[]>([]);

  useEffect(() => {
    VendedoresService.listar()
      .then((vs: any[]) =>
        setVendedores([
          { label: "Todos os Vendedores", value: "" },
          ...(vs || []).map((v: any) => ({ label: v.nome ?? v.name ?? `#${v.id}`, value: String(v.id) })),
        ])
      )
      .catch(() => setVendedores([{ label: "Todos os Vendedores", value: "" }]));
    EquipesService.listar()
      .then((es: any[]) =>
        setEquipes([
          { label: "Todas as Equipes", value: "" },
          ...(es || []).map((e: any) => ({ label: e.nome ?? e.name ?? `#${e.id}`, value: String(e.id) })),
        ])
      )
      .catch(() => setEquipes([{ label: "Todas as Equipes", value: "" }]));
  }, []);

  useEffect(() => {
    setLoading(true);
    MetricasService.obter({ vendedor_id: vendedorFiltro || undefined, equipe_id: equipeFiltro || undefined })
      .then((res) => setData(res))
      .catch(() => setData(null))
      .finally(() => setLoading(false));
  }, [vendedorFiltro, equipeFiltro]);

  const maxReceita = useMemo(
    () => Math.max(1, ...(data?.receitaMensal?.map((m) => m.total) ?? [1])),
    [data]
  );

  const resumo = data?.resumo;

  return (
    <main className="p-[24px_28px_20px_28px] flex-1 flex flex-col h-full">
      <div className="bg-white rounded-[18px] flex-1 border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">
        {/* CABEÇALHO */}
        <div className="p-[16px_24px_0_24px] flex items-center gap-[12px]">
          <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
            <PieChart className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
          </div>
          <div className="flex flex-col justify-center">
            <h1 className="text-[20px] font-[700] text-[#1A1A2E] leading-tight">{t("Métricas de Vendas")}</h1>
            <p className="text-[12px] text-[#6B7280] mt-0.5">{t("Visão geral e desempenho do seu time comercial.")}</p>
          </div>
        </div>

        {/* Filtros */}
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
                options={equipes}
                value={equipeFiltro}
                onChange={setEquipeFiltro}
                triggerClassName="h-[36px] bg-white min-w-[180px] text-[12px]"
                placeholder="Equipes"
              />
            </div>
            <div className="w-full sm:w-auto">
              <CustomSelect
                options={vendedores}
                value={vendedorFiltro}
                onChange={setVendedorFiltro}
                searchable={true}
                triggerClassName="h-[36px] bg-white min-w-[200px] text-[12px]"
                placeholder="Vendedores"
              />
            </div>
          </div>
        </div>

        {/* CONTEÚDO */}
        <div className="p-[24px] flex-1 bg-[#FCFCFD] overflow-y-auto">
          {loading && <div className="text-[#6B7280] text-[14px]">{t("Carregando métricas...")}</div>}

          {!loading && resumo && (
            <>
              {/* KPIs */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[20px] mb-[24px]">
                <Kpi titulo={t("Total Vendido")} valor={formatCurrency(resumo.receitaTotal)} cor="#059669" bg="#ECFDF5" icon={<DollarSign className="w-[16px] h-[16px] text-[#059669]" />} />
                <Kpi titulo={t("Vendas Confirmadas")} valor={String(resumo.totalVendas)} cor="#7C3AED" bg="#F3E8FF" icon={<CreditCard className="w-[16px] h-[16px] text-[#7C3AED]" />} />
                <Kpi titulo={t("Ticket Médio")} valor={formatCurrency(resumo.ticketMedio)} cor="#2563EB" bg="#EFF6FF" icon={<PieChart className="w-[16px] h-[16px] text-[#2563EB]" />} />
                <Kpi titulo={t("Churn Rate")} valor={`${resumo.churn}%`} cor="#DC2626" bg="#FEF2F2" icon={<TrendingDown className="w-[16px] h-[16px] text-[#DC2626]" />} />
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-3 gap-[20px] flex-1">
                {/* Gráfico de receita mensal */}
                <div className="lg:col-span-2 bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] p-[24px] flex flex-col">
                  <h3 className="text-[16px] font-[700] text-[#111827] mb-6">{t("Receita ao Longo do Tempo")}</h3>
                  <div className="flex-1 flex items-end gap-[2%] pt-8 min-h-[220px]">
                    {(data?.receitaMensal ?? []).map((m, i) => {
                      const h = (m.total / maxReceita) * 100;
                      return (
                        <div key={i} className="flex-1 flex flex-col items-center gap-2 group">
                          <div className="w-full bg-[#E0E7FF] rounded-t-[4px] relative overflow-hidden" style={{ height: `${Math.max(h, 2)}%` }}>
                            <div className="absolute bottom-0 w-full bg-[#7C3AED]" style={{ height: "100%" }}></div>
                          </div>
                          <span className="text-[10px] font-[600] text-[#9CA3AF]">{m.name}</span>
                        </div>
                      );
                    })}
                    {(data?.receitaMensal ?? []).length === 0 && (
                      <span className="text-[13px] text-[#9CA3AF]">{t("Sem dados no período.")}</span>
                    )}
                  </div>
                </div>

                {/* Top vendedores */}
                <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] p-[24px] flex flex-col">
                  <h3 className="text-[16px] font-[700] text-[#111827] mb-6">{t("Top Vendedores")}</h3>
                  <div className="flex flex-col gap-5 flex-1 justify-center">
                    {(data?.topVendedores ?? []).map((v, i) => (
                      <div key={i} className="flex flex-col gap-2">
                        <div className="flex items-center justify-between">
                          <span className="text-[13px] font-[600] text-[#374151]">{v.name}</span>
                          <span className="text-[13px] font-[700] text-[#111827]">{formatCurrency(v.total)}</span>
                        </div>
                        <div className="h-[6px] w-full bg-[#F3F4F6] rounded-full overflow-hidden">
                          <div className="h-full bg-[#7C3AED] rounded-full transition-all duration-500" style={{ width: `${v.percent}%` }}></div>
                        </div>
                      </div>
                    ))}
                    {(data?.topVendedores ?? []).length === 0 && (
                      <span className="text-[13px] text-[#9CA3AF]">{t("Sem vendedores no período.")}</span>
                    )}
                  </div>
                </div>
              </div>
            </>
          )}

          {!loading && !resumo && (
            <div className="text-[14px] text-[#B42318] bg-[#FEF3F2] p-[16px] rounded-[12px]">
              {t("Não foi possível carregar as métricas.")}
            </div>
          )}
        </div>
      </div>

      <div className="mt-[24px] pb-[12px] flex justify-center">
        <p className="text-[14px] text-[#6B7280]">
          {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Vendor OS")}</span>
          {t(", Todos os direitos reservados")}
        </p>
      </div>
    </main>
  );
}

function Kpi({ titulo, valor, cor, bg, icon }: { titulo: string; valor: string; cor: string; bg: string; icon: React.ReactNode }) {
  return (
    <div className="bg-white rounded-[16px] p-[20px] border border-[#E5E7EB] shadow-[0_2px_12px_rgba(0,0,0,0.03)] flex flex-col justify-between">
      <div className="flex items-center justify-between mb-4">
        <span className="text-[13px] font-[600] text-[#6B7280]">{titulo}</span>
        <div className="w-[32px] h-[32px] rounded-full flex items-center justify-center" style={{ backgroundColor: bg }}>
          {icon}
        </div>
      </div>
      <h3 className="text-[28px] font-[700] text-[#111827]" style={{ color: cor === "#7C3AED" ? "#111827" : "#111827" }}>
        {valor}
      </h3>
    </div>
  );
}
