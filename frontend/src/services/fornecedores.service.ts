import { api } from "@/lib/api";

export const FornecedoresService = {
  listar: async (params?: { page?: number; search?: string }) => {
    const queryParams = new URLSearchParams();
    if (params?.page) queryParams.append('page', params.page.toString());
    if (params?.search) queryParams.append('search', params.search);
    const queryString = queryParams.toString();
    
    return api.get(`/fornecedores${queryString ? `?${queryString}` : ''}`);
  },
  obterPorId: async (id: number | string) => {
    return api.get(`/fornecedores/${id}`);
  },
  criar: async (data: any) => {
    return api.post(`/fornecedores`, data);
  },
  atualizar: async (id: number | string, data: any) => {
    return api.put(`/fornecedores/${id}`, data);
  },
  excluir: async (id: number | string) => {
    return api.delete(`/fornecedores/${id}`);
  }
};
