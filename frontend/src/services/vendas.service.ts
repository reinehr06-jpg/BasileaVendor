import { api } from "@/lib/api";

export interface Venda {
  id: number;
  cliente_id: number;
  vendedor_id?: number | null;
  plano?: string | null;
  status: string;
  valor: string | number;
  valor_final: string | number;
  forma_pagamento?: string | null;
  modo_cobranca?: string | null;
  observacao?: string | null;
  data_venda?: string;
  created_at?: string;
  updated_at?: string;
  cliente?: {
    id: number;
    nome: string;
    nome_igreja?: string;
  };
  vendedor?: {
    id: number;
    user?: {
      id: number;
      name: string;
    };
  };
}

export const VendasService = {
  listar: async (params?: { page?: number; search?: string; status?: string }): Promise<{ data: Venda[], meta: any }> => {
    let url = "/vendas";
    if (params) {
      const searchParams = new URLSearchParams();
      if (params.page) searchParams.append("page", String(params.page));
      if (params.search) searchParams.append("search", params.search);
      if (params.status) searchParams.append("status", params.status);
      const qs = searchParams.toString();
      if (qs) url += `?${qs}`;
    }
    const response = await api.get<any>(url);
    return response;
  },
  
  obter: async (id: number): Promise<Venda> => {
    const response = await api.get<any>(`/vendas/${id}`);
    return response.data;
  },

  criar: async (data: any): Promise<Venda> => {
    const response = await api.post<any>("/vendas", data);
    return response.data;
  },

  metricas: async (): Promise<any> => {
    const response = await api.get<any>("/metricas-vendas");
    return response;
  }
};
