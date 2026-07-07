import { api } from "@/lib/api";

export const FiliaisService = {
  listar: async () => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, nome: "Igreja Sede - Central", doc: "00.000.000/0001-00", local: "São Paulo, SP", contas: 3, responsaveis: 2, status: "Ativa" },
              { id: 2, nome: "Filial Zona Sul", doc: "00.000.000/0002-00", local: "São Paulo, SP", contas: 1, responsaveis: 1, status: "Ativa" },
              { id: 3, nome: "Missão Nordeste", doc: "-", local: "Recife, PE", contas: 1, responsaveis: 1, status: "Inativa" },
            ]
          }
        });
      }, 500);
    });
  }
};
