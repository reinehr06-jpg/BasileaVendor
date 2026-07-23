import { api } from "@/lib/api";

export type MetricasVendas = {
  resumo: {
    totalVendas: number;
    receitaTotal: number;
    ticketMedio: number;
    churn: number;
  };
  receitaMensal: { name: string; total: number }[];
  vendasPorStatus: { name: string; value: number; color: string }[];
  topVendedores: { name: string; total: number; percent: number }[];
};

export const MetricasService = {
  obter: async (params?: { vendedor_id?: string; equipe_id?: string }): Promise<MetricasVendas> => {
    const qs = new URLSearchParams();
    if (params?.vendedor_id) qs.append("vendedor_id", params.vendedor_id);
    if (params?.equipe_id) qs.append("equipe_id", params.equipe_id);
    const q = qs.toString();
    const res: any = await api.get(`/metricas-vendas${q ? `?${q}` : ""}`);
    return res?.data ?? res;
  },
};
