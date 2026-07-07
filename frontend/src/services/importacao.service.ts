import { api } from "@/lib/api";

export const ImportacaoService = {
  uploadOfx: async (file: File) => {
    // Simulando delay de upload
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, data: "20/05/2024", desc: "PIX RECEBIDO - JOAO SILVA", valor: "500,00", tipo: "receita", acaoDefault: "novo", sugerido: "Dízimos", status: "pendente", duplicado: false },
              { id: 2, data: "21/05/2024", desc: "PAG BOLETO - SABESP", valor: "150,00", tipo: "despesa", acaoDefault: "vincular", sugerido: "Energia/Água", status: "pendente", duplicado: false, match: "Despesa #1042 (R$ 150,00)" },
              { id: 3, data: "21/05/2024", desc: "PAG BOLETO - SABESP", valor: "150,00", tipo: "despesa", acaoDefault: "", sugerido: "", status: "duplicado", duplicado: true },
              { id: 4, data: "22/05/2024", desc: "TARIFA BANCARIA", valor: "19,90", tipo: "despesa", acaoDefault: "novo", sugerido: "Taxas Bancárias", status: "pendente", duplicado: false },
            ]
          }
        });
      }, 800);
    });
  },

  salvarConciliacao: async (conciliados: any[]) => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({ data: { success: true } });
      }, 800);
    });
  }
};
