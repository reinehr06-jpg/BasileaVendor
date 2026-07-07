import { api } from "@/lib/api";

export const ComprasService = {
  listar: async (params?: any): Promise<any> => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, numero: "COMP-2024-001", status: "Pendente", fornecedor: { nome: "Kalunga" }, valor: 450.00 }
            ],
            meta: { total: 1, last_page: 1 }
          }
        });
      }, 500);
    });
  },
  criar: async (data: any): Promise<any> => {
    return new Promise<any>((resolve) => resolve({ data: { id: 2, ...data } }));
  },
  obterPorId: async (id: string) => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            id,
            numero: "COMP-2024-001",
            status: "Pendente",
            solicitante: "Lucas Almeida",
            fornecedor: { nome: "Kalunga" },
            data_solicitacao: "2024-05-20",
            valor: 450.00
          }
        });
      }, 500);
    });
  },
  atualizar: async (id: string, data: any) => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            ...data
          }
        });
      }, 500);
    });
  },
  historico: async (id: string) => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, type: "Fluxo de aprovação", date: "20/05/2024", time: "11:00", title: "Enviado para aprovação", desc: "O pedido está aguardando a aprovação do Pastor Presidente ou Diretor Financeiro devido ao valor estar acima de R$ 300,00.", author: "Sistema", authorName: "Fluxo automático", icon: "clock", color: "#F59E0B", bgTag: "#FEF3C7", textTag: "#B45309", isWarning: true },
              { id: 2, type: "Cotações", date: "20/05/2024", time: "10:50", title: "Orçamento anexado: Kalunga", desc: "Arquivo Orcamento_Kalunga.pdf enviado por Lucas Almeida para análise.", author: "Lucas Almeida", authorName: "Solicitante", icon: "edit", color: "#3B82F6", bgTag: "#EFF6FF", textTag: "#2563EB" },
              { id: 3, type: "Fluxo de aprovação", date: "20/05/2024", time: "10:45", title: "Pedido de compra solicitado", desc: "Solicitação criada.", author: "Lucas Almeida", authorName: "Solicitante", icon: "check", color: "#8B5CF6", bgTag: "#F4EEFF", textTag: "#6D28D9" }
            ]
          }
        });
      }, 500);
    });
  }
};
