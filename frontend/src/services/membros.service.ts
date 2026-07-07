// ============================================================
// MAPA DO TESOURO — API Membros
// ============================================================
// PROPÓSITO:
//   Contratos de consumo dos endpoints de membros.
//
// #arq06
// ============================================================

import { api } from "@/lib/api";
import { Membro, MembroPayload, PaginatedResponse, qs } from "@/types/membro";

export const MembrosService = {
  list:    (params?: { page?: number; search?: string }) => {
    return api.get<PaginatedResponse<Membro>>(`/membros?${qs(params)}`); 
  },
  getById: (id: string)          => api.get<Membro>(`/membros/${id}`),
  create:  (data: MembroPayload) => api.post<Membro>("/membros", data),
  update:  (id: string, data: Partial<MembroPayload>) => api.put<Membro>(`/membros/${id}`, data),
  remove:  (id: string)          => api.delete<void>(`/membros/${id}`),
};
