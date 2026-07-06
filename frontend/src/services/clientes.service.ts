import { api } from "@/lib/api";

export const MOCK_CLIENTES = [
  { id: 1, nome: "Empresa Alpha Ltda", cpfCnpj: "12.345.678/0001-90", responsavel: "João Pedro", vendedor: "Bruno Santana da Hora", financeiro: "Em dia", status: "Ativo" },
  { id: 2, nome: "Marcos Antônio Rodrigues", cpfCnpj: "123.456.789-01", responsavel: "Marcos Antônio", vendedor: "Carolina de Souza", financeiro: "Em dia", status: "Ativo" },
  { id: 3, nome: "Tech Solutions ME", cpfCnpj: "33.444.555/0001-66", responsavel: "Fernanda Silva", vendedor: "Roger Guilherme", financeiro: "Em Atraso", status: "Ativo" },
  { id: 4, nome: "Amanda Vasconcelos", cpfCnpj: "987.654.321-00", responsavel: "Amanda V.", vendedor: "Guilherme Guth Betim", financeiro: "Inadimplente", status: "Inativo" },
  { id: 5, nome: "Startup Beta SA", cpfCnpj: "11.222.333/0001-44", responsavel: "Lucas Oliveira", vendedor: "Ainara Perez Diaz", financeiro: "Em dia", status: "Ativo" },
];

export const ClientesService = {
  listar: async () => {
    return Promise.resolve(MOCK_CLIENTES);
  }
};
