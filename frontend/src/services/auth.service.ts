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
  login:    (data: LoginPayload)   => api.post<AuthResponse>("/login", data),
  register: (data: RegisterPayload) => api.post<AuthResponse>("/register", data),
  logout:   ()                     => api.post<void>("/logout", {}),
  me:       ()                     => api.get<{success: boolean, user: User}>("/me").then(res => res.user),
};
