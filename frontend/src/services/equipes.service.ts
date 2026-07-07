import { api } from "@/lib/api";

export interface Equipe {
  id: number;
  nome: string;
  gestor_id?: number | null;
  meta_mensal?: number | null;
  status: string;
  created_at?: string;
  updated_at?: string;
  gestor?: {
    id: number;
    name: string;
  };
  vendedores?: any[];
}

export const EquipesService = {
  listar: async (): Promise<Equipe[]> => {
    try {
      return await api.get<Equipe[]>('/equipes');
    } catch (err) {
      console.error(err);
      return [];
    }
  },

  obter: async (id: number): Promise<Equipe | null> => {
    try {
      return await api.get<Equipe>(`/equipes/${id}`);
    } catch (err) {
      console.error(err);
      return null;
    }
  },

  criar: async (dados: Partial<Equipe>): Promise<{ success: boolean; id?: number; message?: string }> => {
    return await api.post('/equipes', dados);
  },

  atualizar: async (id: number, dados: Partial<Equipe>): Promise<{ success: boolean; message?: string }> => {
    return await api.put(`/equipes/${id}`, dados);
  },

  excluir: async (id: number): Promise<{ success: boolean; message?: string }> => {
    return await api.delete(`/equipes/${id}`);
  }
};
