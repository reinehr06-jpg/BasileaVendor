import { api } from "@/lib/api";
import { PaginatedResponse, qs } from "@/types/membro";

export const CultosService = {
  list:    (params?: { page?: number; search?: string }) => api.get<PaginatedResponse<any>>(`/cultos?${qs(params)}`),
  getById: (id: string)          => api.get<any>(`/cultos/${id}`),
  create:  (data: any)           => api.post<any>("/cultos", data),
  update:  (id: string, data: any) => api.put<any>(`/cultos/${id}`, data),
  remove:  (id: string)          => api.delete<void>(`/cultos/${id}`),
};
