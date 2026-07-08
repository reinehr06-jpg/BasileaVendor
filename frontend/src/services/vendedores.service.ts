import { api } from "@/lib/api";

export interface Vendedor {
  id: number;
  nome: string;
  email: string;
  telefone: string;
  is_gestor?: boolean;
  status: string;
  equipe_id?: number | null;
  percentual_comissao?: number | null;
  comissao_inicial?: number | null;
  comissao_recorrencia?: number | null;
  comissao_gestor_primeira?: number | null;
  comissao_gestor_recorrencia?: number | null;
  created_at?: string;
  updated_at?: string;
  perfil?: string;
  
  // Relações e helpers do front antigo
  equipe?: any;
  gestor?: any;
  vendas?: string | number;
  avatarColor?: string;
  cpfCnpj?: string;
}

export const VendedoresService = {
  listar: async (): Promise<Vendedor[]> => {
    try {
      return await api.get<Vendedor[]>('/vendedores');
    } catch (err) {
      console.error(err);
      return [];
    }
  },

  obter: async (id: number): Promise<Vendedor | null> => {
    try {
      return await api.get<Vendedor>(`/vendedores/${id}`);
    } catch (err) {
      console.error(err);
      return null;
    }
  },

  criar: async (dados: Partial<Vendedor> & { senha?: string }): Promise<{ success: boolean; id?: number; message?: string }> => {
    return await api.post('/vendedores', dados);
  },

  atualizar: async (id: number, dados: Partial<Vendedor> & { senha?: string }): Promise<{ success: boolean; message?: string }> => {
    return await api.put(`/vendedores/${id}`, dados);
  },

  excluir: async (id: number): Promise<{ success: boolean; message?: string }> => {
    return await api.delete(`/vendedores/${id}`);
  }
};
