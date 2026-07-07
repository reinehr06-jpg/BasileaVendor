import { api } from "@/lib/api";

export const RelatoriosService = {
  listar: async () => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, desc: "Conta de Luz", valor: "R$ 450,00", venc: "15/05/2024", pagto: "12/05/2024", cat: "Energia" },
              { id: 2, desc: "Internet", valor: "R$ 120,00", venc: "20/05/2024", pagto: "20/05/2024", cat: "Telecom" },
              { id: 3, desc: "Aluguel", valor: "R$ 3.500,00", venc: "05/05/2024", pagto: "04/05/2024", cat: "Imóveis" }
            ]
          }
        });
      }, 500);
    });
  }
};
