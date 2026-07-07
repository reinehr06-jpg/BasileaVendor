import api from '@/lib/api';

export const DespesasService = {
  listar: async (params?: { search?: string; page?: number }) => {
    return api.get('/despesas', { params });
  },

  obterPorId: async (id: string | number) => {
    return api.get(`/despesas/${id}`);
  },

  criar: async (data: any) => {
    return api.post('/despesas', data);
  },

  atualizar: async (id: string | number, data: any) => {
    return api.put(`/despesas/${id}`, data);
  },

  excluir: async (id: string | number) => {
    return api.delete(`/despesas/${id}`);
  }
};
