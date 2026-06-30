"use client";
// ============================================================
// MAPA DO TESOURO — Estado Global de Autenticação
// ============================================================
// PROPÓSITO:
//   Armazena na memória do App quem é o usuário logado e qual o JWT atual.
//   Também é responsável pela função oficial de `login()` e `logout()`.
//
// #arq03
// ============================================================

import React, { createContext, useContext, useState, ReactNode, useEffect } from "react";
import { AuthService } from "@/services/auth.service";
import { User, LoginPayload } from "@/types/auth";
import { useRouter } from "next/navigation";

interface AuthContextType {
  user: User | null;
  token: string | null;
  login: (data: LoginPayload) => Promise<void>;
  logout: () => Promise<void>;
  isLoading: boolean;
}

export const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const router = useRouter();

  // Load token from local storage on mount
  useEffect(() => {
    const storedToken = localStorage.getItem("auth_token");
    if (storedToken) {
      setToken(storedToken);
      // Opcional: Chamar AuthService.me() para validar o token e popular o User
      AuthService.me()
        .then(u => setUser(u))
        .catch(() => {
          localStorage.removeItem("auth_token");
          document.cookie = "auth_token=; path=/; max-age=0";
          setToken(null);
        })
        .finally(() => setIsLoading(false));
    } else {
      setIsLoading(false);
    }
  }, []);

  const login = async (data: LoginPayload) => {
    const res = await AuthService.login(data);
    setToken(res.token);
    setUser(res.user);
    // Guarda no localStorage também, ou usa cookies via backend HttpOnly
    localStorage.setItem("auth_token", res.token);
    document.cookie = `auth_token=${res.token}; path=/; max-age=86400`;
  };

  const logout = async () => {
    try {
      await AuthService.logout();
    } catch (e) {
      console.warn("Logout request failed, cleaning local session anyway");
    }
    setUser(null);
    setToken(null);
    localStorage.removeItem("auth_token");
    document.cookie = "auth_token=; path=/; max-age=0";
    router.push("/");
  };

  return (
    <AuthContext.Provider value={{ user, token, login, logout, isLoading }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth deve ser usado dentro de AuthProvider");
  return ctx;
};
