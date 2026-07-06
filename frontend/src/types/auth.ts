// ============================================================
// MAPA DO TESOURO — Tipos Auth
// ============================================================
// PROPÓSITO:
//   Interfaces TypeScript para dados de sessão e payload de login.
//
// #arq07
// ============================================================

export interface User {
  id: string;
  nome: string;
  email: string;
  role: string;
  avatar?: string;
  createdAt: string;
}

export interface LoginPayload {
  email: string;
  password?: string;
  remember?: boolean;
}

export interface RegisterPayload {
  nome: string;
  email: string;
  telefone: string;
  password?: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}
