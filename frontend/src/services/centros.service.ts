import api from '@/lib/api';

export const CentrosDeCustoService = {
  listar: async (params?: { search?: string; page?: number }) => {
    return api.get('/centros-custo', { params });
  },

  obterPorId: async (id: string | number) => {
    return api.get(`/centros-custo/${id}`);
  },

  criar: async (data: any) => {
    return api.post('/centros-custo', data);
  },

  atualizar: async (id: string | number, data: any) => {
    return api.put(`/centros-custo/${id}`, data);
  },

  excluir: async (id: string | number) => {
    return api.delete(`/centros-custo/${id}`);
  }
};
