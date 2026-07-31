import { api } from "@/lib/api";

export const AprovacoesService = {
  listar: async () => {
    return await api.get('/aprovacoes');
  },
  aprovar: async (id: number) => {
    return await api.post(`/aprovacoes/${id}/aprovar`, {});
  },
  rejeitar: async (id: number, motivo: string = "") => {
    return await api.post(`/aprovacoes/${id}/rejeitar`, { motivo });
  }
};
