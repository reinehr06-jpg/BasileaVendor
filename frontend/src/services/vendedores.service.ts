import { api } from "@/lib/api";

export const MOCK_VENDEDORES = [
  { id: 1, nome: "Bruno Santana da Hora", email: "bruno@basileia.global", telefone: "(11) 98765-4321", equipe: "Equipe Alpha", gestor: "Ana Silva", status: "Ativo", vendas: "45", avatarColor: "bg-[#7C3AED]" },
  { id: 2, nome: "Carolina de Souza", email: "carolina.souza@basileia.global", telefone: "(11) 91234-5678", equipe: "Vendas Corporativas", gestor: "Carlos Maia", status: "Ativo", vendas: "38", avatarColor: "bg-[#059669]" },
  { id: 3, nome: "Roger Guilherme", email: "roger.g@basileia.global", telefone: "(21) 97777-8888", equipe: "Equipe Alpha", gestor: "Ana Silva", status: "Inativo", vendas: "12", avatarColor: "bg-[#DC2626]" },
  { id: 4, nome: "Ainara Perez Diaz", email: "ainara.perez@basileia.global", telefone: "(11) 95555-4444", equipe: "Varejo B2B", gestor: "Mariana Costa", status: "Ativo", vendas: "28", avatarColor: "bg-[#D97706]" },
  { id: 5, nome: "Guilherme Guth Betim", email: "guilherme.guth@basileia.global", telefone: "(41) 99999-1111", equipe: "Parcerias", gestor: "Ricardo Nunes", status: "Ativo", vendas: "52", avatarColor: "bg-[#2563EB]" },
];

export const VendedoresService = {
  listar: async () => {
    return Promise.resolve(MOCK_VENDEDORES);
  },
  obter: async (id: number) => {
    const vendedores = await VendedoresService.listar();
    return vendedores.find(v => v.id === id);
  }
};
