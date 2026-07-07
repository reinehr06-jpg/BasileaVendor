import { api } from "@/lib/api";

export const FuncionariosService = {
  listar: async () => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, nome: "Carlos Almeida", documento: "123.456.789-00", cargo: "Zelador", salario: "R$ 2.500,00", pgtoData: "05/05/2024", conta: "Itaú - CC 1234", forma: "PIX", status: "Pago", filial: "Igreja Sede" },
              { id: 2, nome: "Mariana Souza", documento: "987.654.321-11", cargo: "Secretária", salario: "R$ 3.200,00", pgtoData: "05/05/2024", conta: "Itaú - CC 1234", forma: "Transferência", status: "Pendente", filial: "Igreja Sede" },
              { id: 3, nome: "Pr. João Silva", documento: "444.555.666-77", cargo: "Pastor Auxiliar", salario: "R$ 4.500,00", pgtoData: "05/05/2024", conta: "Caixa Físico", forma: "Dinheiro", status: "Pago", filial: "Filial Zona Sul" }
            ]
          }
        });
      }, 500);
    });
  }
};
