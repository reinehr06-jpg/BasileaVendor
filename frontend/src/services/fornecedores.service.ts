import { api } from "@/lib/api";

export const FornecedoresService = {
  listar: async (params?: any): Promise<any> => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              { id: 1, nome: "Limpeza & Cia", doc: "11.222.333/0001-44", status: "Ativo" }
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
            nome: "Limpeza & Cia",
            doc: "11.222.333/0001-44",
            email: "contato@limpezacia.com",
            telefone: "(11) 99999-9999",
            endereco: "Rua das Flores, 123",
            cidade: "São Paulo",
            estado: "SP",
            status: "Ativo"
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
              { id: 1, type: "Alterações cadastrais", date: "20/06/2024", time: "14:30", title: "Endereço atualizado", desc: "Logradouro atualizado.", author: "Financeiro", authorName: "Por Maria Santos", icon: "edit", color: "#8B5CF6", bgTag: "#F4EEFF", textTag: "#6D28D9" },
              { id: 4, type: "Alterações cadastrais", date: "15/03/2024", time: "10:00", title: "Fornecedor criado no sistema", desc: "Cadastro inicial realizado.", author: "Admin", authorName: "Usuário principal", icon: "check", color: "#3B82F6", bgTag: "#EFF6FF", textTag: "#2563EB" }
            ]
          }
        });
      }, 500);
    });
  }
};
