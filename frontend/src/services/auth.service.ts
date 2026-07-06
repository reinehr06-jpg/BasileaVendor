// ============================================================
// MAPA DO TESOURO — API Auth
// ============================================================
// PROPÓSITO:
//   Contratos de consumo dos endpoints de autenticação.
//
// #arq05
// ============================================================

import { api } from "@/lib/api";
import { LoginPayload, RegisterPayload, AuthResponse, User } from "@/types/auth";

export const AuthService = {
  login:    (data: LoginPayload)   => api.post<AuthResponse>("/auth/login", data),
  register: (data: RegisterPayload) => api.post<AuthResponse>("/auth/register", data),
  logout:   ()                     => api.post<void>("/auth/logout", {}),
  me:       ()                     => api.get<User>("/auth/me"),
};
