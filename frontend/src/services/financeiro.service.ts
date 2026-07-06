import { api } from "@/lib/api";

export const MOCK_COMISSOES = [
  { id: 1, vendedor: "Anthony Cardoso", email: "anthony.cardoso@basileia.global", vendas: "0", comissao: "R$ 0,00", meta: "0%", notas: "-", avatarColor: "bg-[#7C3AED]" },
  { id: 2, vendedor: "Valmir", email: "valmir_605@hotmail.com", vendas: "0", comissao: "R$ 0,00", meta: "0%", notas: "-", avatarColor: "bg-[#4F46E5]" },
  { id: 3, vendedor: "Ana Paula", email: "anapaularobertobugs21@gmail.com", vendas: "0", comissao: "R$ 0,00", meta: "0%", notas: "-", avatarColor: "bg-[#7C3AED]" },
  { id: 4, vendedor: "Wilza", email: "wilzaconceicaosilva@gmail.com", vendas: "0", comissao: "R$ 0,00", meta: "0%", notas: "-", avatarColor: "bg-[#4F46E5]" },
  { id: 5, vendedor: "Selma", email: "selmasobreira@gmail.com", vendas: "0", comissao: "R$ 0,00", meta: "0%", notas: "-", avatarColor: "bg-[#7C3AED]" },
  { id: 6, vendedor: "Bruno Santana da Hora", email: "bruno@basileia.global", vendas: "12", comissao: "R$ 1.450,00", meta: "85%", notas: "12", avatarColor: "bg-[#059669]" },
];

export const FinanceiroService = {
  listarComissoes: async () => {
    return Promise.resolve(MOCK_COMISSOES);
  }
};
