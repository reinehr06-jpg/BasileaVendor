import { api } from '@/lib/api';

export const DepartamentosService = {
  listar: async (params?: { search?: string; page?: number }) => {
    return api.get('/departamentos', { params });
  },

  obterPorId: async (id: string | number) => {
    return api.get(`/departamentos/${id}`);
  },

  criar: async (data: any) => {
    return api.post('/departamentos', data);
  },

  atualizar: async (id: string | number, data: any) => {
    return api.put(`/departamentos/${id}`, data);
  },

  excluir: async (id: string | number) => {
    return api.delete(`/departamentos/${id}`);
  }
};
