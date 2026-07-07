import { api } from "@/lib/api";

export const ContasService = {
  listar: async (params?: any): Promise<any> => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, nome: "Conta Corrente Itaú", banco: "Itaú", agencia: "1234", conta: "12345-6", saldo: "R$ 15.000,00", status: "Ativa" },
              { id: 2, nome: "Caixa Físico", banco: "Interno", agencia: "-", conta: "-", saldo: "R$ 1.200,00", status: "Ativa" }
            ],
            meta: { total: 2, last_page: 1 }
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
              { id: 1, type: "Movimentações", date: "20/05/2024", time: "14:30", title: "Dízimo Recebido — R$ 1.500,00", desc: "Receita registrada automaticamente via integração bancária.", author: "Sistema", authorName: "Registro automático", icon: "up", color: "#10B981", bgTag: "#ECFDF5", textTag: "#059669" },
              { id: 2, type: "Movimentações", date: "19/05/2024", time: "09:00", title: "Pagamento Enel — R$ 450,00", desc: "Despesa #1031 paga via débito automático.", author: "Sistema", authorName: "Registro automático", icon: "down", color: "#DC2626", bgTag: "#FEE2E2", textTag: "#DC2626" },
              { id: 3, type: "Conciliações", date: "01/05/2024", time: "08:30", title: "Conciliação bancária realizada", desc: "Lote OFX processado com 42 lançamentos conciliados. Saldo atualizado.", author: "Financeiro", authorName: "Por Maria Santos", icon: "refresh", color: "#3B82F6", bgTag: "#EFF6FF", textTag: "#2563EB" },
              { id: 4, type: "Alterações cadastrais", date: "01/01/2024", time: "10:00", title: "Abertura de conta no sistema", desc: "Conta cadastrada com saldo inicial de R$ 0,00.", author: "Admin", authorName: "Usuário principal", icon: "check", color: "#8B5CF6", bgTag: "#F4EEFF", textTag: "#6D28D9" }
            ]
          }
        });
      }, 500);
    });
  },

  extrato: async (contaId?: string, inicio?: string, fim?: string) => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, data: "20/05/2024", historico: "Dízimos e Ofertas (Culto Domingo)", doc: "REC-001", cat: "Dízimos", tipo: "receita", valor: "R$ 4.500,00", saldo: "R$ 14.500,00" },
              { id: 2, data: "20/05/2024", historico: "Conta de Energia (Enel)", doc: "NF-0442", cat: "Energia", tipo: "despesa", valor: "R$ 450,00", saldo: "R$ 14.050,00" },
              { id: 3, data: "21/05/2024", historico: "Transferência para Caixa Físico", doc: "TRF-991", cat: "Transferência", tipo: "despesa", valor: "R$ 1.000,00", saldo: "R$ 13.050,00" },
              { id: 4, data: "22/05/2024", historico: "Doação Anônima", doc: "REC-002", cat: "Ofertas", tipo: "receita", valor: "R$ 200,00", saldo: "R$ 13.250,00" }
            ]
          }
        });
      }, 500);
    });
  }
};

export const ContasBancariasService = ContasService;
