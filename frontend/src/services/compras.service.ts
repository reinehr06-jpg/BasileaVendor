import { api } from "@/lib/api";

export const ComprasService = {
  listar: async (params?: { page?: number; search?: string; status?: string }) => {
    const queryParams = new URLSearchParams();
    if (params?.page) queryParams.append('page', params.page.toString());
    if (params?.search) queryParams.append('search', params.search);
    if (params?.status) queryParams.append('status', params.status);
    const queryString = queryParams.toString();
    
    return api.get(`/compras${queryString ? `?${queryString}` : ''}`);
  },
  obterPorId: async (id: number | string) => {
    return api.get(`/compras/${id}`);
  },
  criar: async (data: any) => {
    return api.post(`/compras`, data);
  },
  atualizar: async (id: number | string, data: any) => {
    return api.put(`/compras/${id}`, data);
  },
  excluir: async (id: number | string) => {
    return api.delete(`/compras/${id}`);
  }
};
