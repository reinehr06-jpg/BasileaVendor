import api from '@/lib/api';

export const ReceitasService = {
  listar: async (params?: { search?: string; page?: number }) => {
    return api.get('/receitas', { params });
  },

  obterPorId: async (id: string | number) => {
    return api.get(`/receitas/${id}`);
  },

  criar: async (data: any) => {
    return api.post('/receitas', data);
  },

  atualizar: async (id: string | number, data: any) => {
    return api.put(`/receitas/${id}`, data);
  },

  excluir: async (id: string | number) => {
    return api.delete(`/receitas/${id}`);
  }
};
