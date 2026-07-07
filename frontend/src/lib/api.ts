
// ============================================================
// MAPA DO TESOURO — Cliente HTTP Base
// ============================================================
// PROPÓSITO:
//   Interceptador central de todas as requisições para o Backend.
//   Anexa tokens JWT e trata erros 401 e 422 automaticamente.
//
// #arq01
// ============================================================

// Usa sempre caminho relativo para que o Next.js proxy (next.config.ts) assuma o roteamento
const BASE_URL = "/api";

// Função helper simples para pegar cookies do lado do cliente se necessário
function getCookie(name: string) {
  if (typeof document === 'undefined') return null;
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift();
  return null;
}

async function request<T = any>(path: string, options?: RequestInit): Promise<T> {
  const token = getCookie("auth_token") || localStorage.getItem("auth_token");
  
  if (process.env.NODE_ENV === "development") {
    console.group(`🌐 API ${options?.method ?? "GET"} ${path}`);
    if (options?.body) console.log("Body:", JSON.parse(options.body as string));
    console.groupEnd();
  }

  const res = await fetch(`${BASE_URL}${path}`, {
    headers: {
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options?.headers || {}),
    },
    ...options,
  });
  
  if (res.status === 401) {
    if (typeof document !== 'undefined') document.cookie = 'auth_token=; Max-Age=0; path=/';
    if (typeof localStorage !== 'undefined') localStorage.removeItem("auth_token");
    if (typeof window !== 'undefined') window.location.href = "/";
    throw new Error("Sessão expirada");
  }

  if (res.status === 422) {
    const body = await res.json();
    throw new Error(JSON.stringify(body.errors || body.message));
  }

  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.message ?? `Erro ${res.status}`);
  }
  
  // Para respostas vazias (ex: 204 No Content no DELETE)
  if (res.status === 204) return {} as T;
  
  return res.json();
}

export const api = {
  get:    <T = any>(path: string, options?: any) => request<T>(path, options),
  post:   <T = any>(path: string, body: unknown, options?: any) => request<T>(path, { method: "POST", body: JSON.stringify(body), ...options }),
  put:    <T = any>(path: string, body: unknown, options?: any) => request<T>(path, { method: "PUT",  body: JSON.stringify(body), ...options }),
  delete: <T = any>(path: string, options?: any) => request<T>(path, { method: "DELETE", ...options }),
};
