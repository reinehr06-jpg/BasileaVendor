import { api } from "@/lib/api";

export const CentrosService = {
  listar: async (params?: { search?: string; page?: number; status?: string }): Promise<any> => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, nome: "Operacional Sede", filial: "Sede - Curitiba", responsavel: "Pr. Marcos", orcamento: "R$ 15.000,00", despesasVinculadas: "R$ 12.350,00", status: "Ativo" },
              { id: 2, nome: "Departamento de Jovens", filial: "Sede - Curitiba", responsavel: "Lucas Almeida", orcamento: "R$ 5.000,00", despesasVinculadas: "R$ 1.200,00", status: "Ativo" },
              { id: 3, nome: "Manutenção Filial SP", filial: "Filial - São Paulo", responsavel: "José Carlos", orcamento: "R$ 8.000,00", despesasVinculadas: "R$ 7.950,00", status: "Alerta" }
            ],
            meta: { total: 3, last_page: 1 }
          }
        });
      }, 500);
    });
  },
  historico: async (id: string): Promise<any> => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, type: "Orçamento", date: "15/06/2024", time: "16:20", title: "Orçamento atualizado", desc: "Limite alterado de R$ 10.000,00 para R$ 15.000,00 pelo Pr. Marcos.", author: "Pr. Marcos", authorName: "Pastor responsável", icon: "edit", color: "#8B5CF6", bgTag: "#F4EEFF", textTag: "#6D28D9" },
              { id: 2, type: "Despesas vinculadas", date: "01/06/2024", time: "10:30", title: "Despesa vinculada: Conta de Luz Maio/24", desc: "Lançamento #1035 de R$ 780,00 alocado neste centro de custo.", author: "Sistema", authorName: "Registro automático", icon: "dollar", color: "#DC2626", bgTag: "#FEE2E2", textTag: "#DC2626" },
              { id: 3, type: "Alterações cadastrais", date: "10/02/2024", time: "09:00", title: "Centro de Custo criado", desc: "Estrutura cadastrada no sistema para organização de despesas da sede.", author: "Admin", authorName: "Usuário principal", icon: "check", color: "#3B82F6", bgTag: "#EFF6FF", textTag: "#2563EB" }
            ]
          }
        });
      }, 500);
    });
  }
};

export const CentrosDeCustoService = CentrosService;
