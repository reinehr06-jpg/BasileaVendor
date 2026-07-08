import { api } from "@/lib/api";

export const AprovacoesService = {
  listar: async () => {
    return await api.get('/aprovacoes');
  }
};
