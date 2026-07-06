import { api } from "@/lib/api";

export const MOCK_EQUIPES = [
  { id: 1, nome: "Equipe Alpha", lider: "Bruno Santana da Hora", membros: 5, vendas: "R$ 145.200,00", meta: "92%", status: "Ativo" },
  { id: 2, nome: "Vendas Corporativas", lider: "Carolina de Souza", membros: 3, vendas: "R$ 88.500,00", meta: "75%", status: "Ativo" },
  { id: 3, nome: "Varejo B2B", lider: "Roger Guilherme", membros: 8, vendas: "R$ 212.000,00", meta: "110%", status: "Ativo" },
  { id: 4, nome: "Parcerias", lider: "Ainara Perez Diaz", membros: 2, vendas: "R$ 45.000,00", meta: "60%", status: "Atencao" },
  { id: 5, nome: "Expansão Sul", lider: "Guilherme Guth Betim", membros: 4, vendas: "R$ 95.000,00", meta: "85%", status: "Ativo" },
];

export const EquipesService = {
  listar: async () => {
    return Promise.resolve(MOCK_EQUIPES);
  }
};
