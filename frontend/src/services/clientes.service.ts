import { api } from "@/lib/api";

export const ClientesService = {
  listar: async (params?: { page?: number; search?: string }) => {
    const queryParams = new URLSearchParams();
    if (params?.page) queryParams.append('page', params.page.toString());
    if (params?.search) queryParams.append('search', params.search);
    const queryString = queryParams.toString();
    
    return api.get(`/clientes${queryString ? `?${queryString}` : ''}`);
  },
  obterPorId: async (id: number | string) => {
    return api.get(`/clientes/${id}`);
  },
  criar: async (data: any) => {
    return api.post(`/clientes`, data);
  },
  atualizar: async (id: number | string, data: any) => {
    return api.put(`/clientes/${id}`, data);
  },
  excluir: async (id: number | string) => {
    return api.delete(`/clientes/${id}`);
  }
};
