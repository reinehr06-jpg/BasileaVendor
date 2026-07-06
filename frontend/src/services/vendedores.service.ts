import { api } from "@/lib/api";

export interface Vendedor {
  id: number;
  nome: string;
  email: string;
  telefone: string;
  equipe: string;
  gestor: string;
  status: string;
  vendas: string | number;
  avatarColor: string;
  cpfCnpj?: string;
}

export const VendedoresService = {
  listar: async (): Promise<Vendedor[]> => {
    try {
      const response = await api.get('/vendedores');
      return response.data;
    } catch (err) {
      console.error(err);
      return [];
    }
  },
  obter: async (id: number) => {
    const vendedores = await VendedoresService.listar();
    return vendedores.find(v => v.id === id);
  }
};
