import { api } from "@/lib/api";

export const TermosService = {
  listar: async () => {
    return await api.get('/termos');
  },
  obter: async (id: number) => {
    return await api.get(`/termos/${id}`);
  },
  criar: async (dados: any) => {
    return await api.post('/termos', dados);
  },
  atualizar: async (id: number, dados: any) => {
    return await api.put(`/termos/${id}`, dados);
  },
  excluir: async (id: number) => {
    return await api.delete(`/termos/${id}`);
  }
};
