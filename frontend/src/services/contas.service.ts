import api from '@/lib/api';

export const ContasBancariasService = {
  listar: async (params?: { search?: string; page?: number }) => {
    return api.get('/contas-bancarias', { params });
  },

  obterPorId: async (id: string | number) => {
    return api.get(`/contas-bancarias/${id}`);
  },

  criar: async (data: any) => {
    return api.post('/contas-bancarias', data);
  },

  atualizar: async (id: string | number, data: any) => {
    return api.put(`/contas-bancarias/${id}`, data);
  },

  excluir: async (id: string | number) => {
    return api.delete(`/contas-bancarias/${id}`);
  }
};
