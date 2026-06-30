// ============================================================
// MAPA DO TESOURO — Cliente HTTP Base
// ============================================================
// PROPÓSITO:
//   Interceptador central de todas as requisições para o Backend.
//   Anexa tokens JWT e trata erros 401 e 422 automaticamente.
//
// #arq01
// ============================================================

export const USE_MOCK = process.env.NEXT_PUBLIC_USE_MOCK === "true";
const BASE_URL = USE_MOCK ? "/api" : (process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api");

// Função helper simples para pegar cookies do lado do cliente se necessário
function getCookie(name: string) {
  if (typeof document === 'undefined') return null;
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift();
  return null;
}

async function request<T>(path: string, options?: RequestInit): Promise<T> {
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
  get:    <T>(path: string) => request<T>(path),
  post:   <T>(path: string, body: unknown) => request<T>(path, { method: "POST", body: JSON.stringify(body) }),
  put:    <T>(path: string, body: unknown) => request<T>(path, { method: "PUT",  body: JSON.stringify(body) }),
  delete: <T>(path: string) => request<T>(path, { method: "DELETE" }),
};
