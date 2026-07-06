import { api } from "@/lib/api";

export const MOCK_VENDAS = [
  { id: 1, cliente: "Marcos Antônio", plano: "Plano Anual", status: "Aprovado", comissao: "R$ 150,00", vendedor: "Bruno Santana da Hora", equipe: "Equipe Alpha", data: "12/05/2026", metodo: "Cartão", tipo: "Novo" },
  { id: 2, cliente: "Amanda Vasconcelos", plano: "Plano Mensal", status: "Pendente", comissao: "R$ 45,00", vendedor: "Carolina de Souza", equipe: "Vendas Corporativas", data: "10/05/2026", metodo: "Boleto", tipo: "Novo" },
  { id: 3, cliente: "Tech Solutions ME", plano: "Plano Enterprise", status: "Aprovado", comissao: "R$ 450,00", vendedor: "Roger Guilherme", equipe: "Equipe Alpha", data: "08/05/2026", metodo: "Pix", tipo: "Upgrade" },
  { id: 4, cliente: "João Pedro Silva", plano: "Plano Semestral", status: "Cancelado", comissao: "R$ 0,00", vendedor: "Ainara Perez Diaz", equipe: "Varejo B2B", data: "05/05/2026", metodo: "Cartão", tipo: "Novo" },
  { id: 5, cliente: "Startup Beta SA", plano: "Plano Anual", status: "Aprovado", comissao: "R$ 150,00", vendedor: "Bruno Santana da Hora", equipe: "Equipe Alpha", data: "01/05/2026", metodo: "Pix", tipo: "Novo" },
];

export const VendasService = {
  listar: async () => {
    return Promise.resolve(MOCK_VENDAS);
  },
  listarPorEquipe: async (equipe: string) => {
    const vendas = await VendasService.listar();
    return vendas.filter(v => v.equipe === equipe);
  },
  listarPorVendedor: async (vendedor: string) => {
    const vendas = await VendasService.listar();
    return vendas.filter(v => v.vendedor === vendedor);
  }
};
