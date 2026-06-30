import { api } from "@/lib/api";
import { PaginatedResponse, qs } from "@/types/membro";

export const CelulasService = {
  list:    (params?: { page?: number; search?: string }) => api.get<PaginatedResponse<any>>(`/celulas?${qs(params)}`),
  getById: (id: string)          => api.get<any>(`/celulas/${id}`),
  create:  (data: any)           => api.post<any>("/celulas", data),
  update:  (id: string, data: any) => api.put<any>(`/celulas/${id}`, data),
  remove:  (id: string)          => api.delete<void>(`/celulas/${id}`),
};
