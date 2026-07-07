import { api } from "@/lib/api";

export interface DashboardData {
  success: boolean;
  kpis: {
    total_vendas: number;
    vendas_ativas: number;
    receita_bruta: number;
    comissao_total: number;
    total_clientes: number;
  };
  charts: {
    receita_mensal: {
      labels: string[];
      data: number[];
    };
  };
  recent_sales: Array<{
    id: number;
    cliente_nome: string;
    valor: number;
    status: string;
    data: string;
  }>;
}

export const DashboardService = {
  obterDados: async (): Promise<DashboardData> => {
    try {
      const data = await api.get<DashboardData>('/dashboard');
      return data;
    } catch (error) {
      console.error("Erro ao carregar dashboard:", error);
      throw error;
    }
  }
};
