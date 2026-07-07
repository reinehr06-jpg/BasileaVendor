import { api } from "@/lib/api";

export const AprovacoesService = {
  listar: async () => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: "#00012", vendedor: "Bruno Santana da Hora", cliente: "Empresa Alpha Ltda", tipo: "Desconto", valor: "15.00%", status: "Pendente", por: "-", data: "02/07/2026 10:30" },
              { id: "#00011", vendedor: "Carolina de Souza", cliente: "Marcos Antônio Rodrigues", tipo: "Plano Especial", valor: "R$ 900,00", status: "Aprovado", por: "Administrador", data: "01/07/2026 14:15" },
              { id: "#00010", vendedor: "Roger Guilherme", cliente: "Tech Solutions ME", tipo: "Desconto", valor: "20.00%", status: "Rejeitado", por: "Diretor Comercial", data: "29/06/2026 09:45" },
              { id: "#00009", vendedor: "Guilherme Guth Betim", cliente: "Amanda Vasconcelos", tipo: "Isenção Adesão", valor: "100%", status: "Aprovado", por: "Administrador", data: "28/06/2026 16:20" },
              { id: "#00008", vendedor: "Vendedor de Testes", cliente: "Teste Final de PIX", tipo: "Desconto", valor: "97.40%", status: "Aprovado", por: "Administrador Master", data: "28/04/2026 15:05" },
            ]
          }
        });
      }, 500);
    });
  }
};
