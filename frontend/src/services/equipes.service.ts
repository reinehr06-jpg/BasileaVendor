import { api } from "@/lib/api";

export interface Equipe {
  id: number;
  nome: string;
  lider: string;
  membros: number;
  vendas: string;
  meta: string;
  status: string;
}

export const EquipesService = {
  listar: async (): Promise<Equipe[]> => {
    try {
      return await api.get<Equipe[]>('/equipes');
    } catch (err) {
      console.error(err);
      return [];
    }
  }
};
