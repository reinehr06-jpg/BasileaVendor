import { api } from "@/lib/api";

export const TermosService = {
  listar: async () => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              {
                id: 1,
                tipo: "USO",
                titulo: "Contrato padrao",
                versao: "2.0",
                criadoEm: "05/05/2026",
                status: "Ativo"
              }
            ]
          }
        });
      }, 500);
    });
  }
};
