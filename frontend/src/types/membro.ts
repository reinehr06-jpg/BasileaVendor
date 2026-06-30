// ============================================================
// MAPA DO TESOURO — Tipos Membro
// ============================================================
// PROPÓSITO:
//   Interfaces TypeScript para entidade de membro e retornos paginados.
//
// #arq08
// ============================================================

export interface Membro {
  id: string;
  nome: string;
  email: string;
  telefone: string;
  cpf?: string;
  dataNascimento?: string; // ISO 8601
  ministerioId?: string;
  redeId?: string;
  cargo?: string;
  status: "ativo" | "inativo" | "visitante";
  createdAt: string;
}

export interface MembroPayload extends Omit<Membro, 'id' | 'createdAt'> {}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
  };
}

export function qs(params?: Record<string, any>) {
  if (!params) return '';
  return new URLSearchParams(Object.entries(params).filter(([_, v]) => v !== undefined && v !== '') as string[][]).toString();
}
