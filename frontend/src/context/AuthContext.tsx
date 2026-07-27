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
  login: (data: LoginPayload) => Promise<any>;
  logout: () => Promise<void>;
  isLoading: boolean;
}

export const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    // Agora o cookie é HttpOnly e não pode ser lido pelo JS.
    // Tentamos buscar o usuário (a API usará o cookie automaticamente).
    AuthService.me()
      .then(u => {
        setUser(u);
        setToken("http-only-token"); // Apenas marcando que temos token
      })
      .catch(() => {
        setToken(null);
      })
      .finally(() => setIsLoading(false));
  }, []);

  const login = async (data: LoginPayload) => {
    const res = await AuthService.login(data);
    
    if (res.requires_2fa || res.requires_2fa_setup) {
      return res;
    }
    
    setToken(res.token || "http-only-token");
    setUser(res.user);
    // document.cookie removido pois o backend agora envia como HttpOnly
    return res;
  };

  const logout = async () => {
    try {
      await AuthService.logout();
    } catch (e) {
      console.warn("Logout request failed, cleaning local session anyway");
    }
    setUser(null);
    setToken(null);
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
