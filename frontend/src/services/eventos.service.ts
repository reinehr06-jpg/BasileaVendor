import { api } from "@/lib/api";
import { PaginatedResponse, qs } from "@/types/membro";

export const EventosService = {
  list:    (params?: { page?: number; search?: string }) => api.get<PaginatedResponse<any>>(`/eventos?${qs(params)}`),
  getById: (id: string)          => api.get<any>(`/eventos/${id}`),
  create:  (data: any)           => api.post<any>("/eventos", data),
  update:  (id: string, data: any) => api.put<any>(`/eventos/${id}`, data),
  remove:  (id: string)          => api.delete<void>(`/eventos/${id}`),
};
