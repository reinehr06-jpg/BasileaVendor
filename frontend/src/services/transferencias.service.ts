import api from '@/lib/api';

export const TransferenciasService = {
  listar: async (params?: { search?: string; page?: number }) => {
    return api.get('/transferencias', { params });
  },

  obterPorId: async (id: string | number) => {
    return api.get(`/transferencias/${id}`);
  },

  criar: async (data: any) => {
    return api.post('/transferencias', data);
  },

  atualizar: async (id: string | number, data: any) => {
    return api.put(`/transferencias/${id}`, data);
  },

  excluir: async (id: string | number) => {
    return api.delete(`/transferencias/${id}`);
  }
};
