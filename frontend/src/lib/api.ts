
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
      "Accept": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options?.headers || {}),
    },
    ...options,
  });
  
  // Lê o corpo com segurança: nunca deixa res.json() estourar em corpo vazio
  // ou não-JSON (que no Safari/WebKit vira "The string did not match the
  // expected pattern."). Sempre lemos como texto e tentamos parsear.
  const parseBody = async (): Promise<any> => {
    const text = await res.text().catch(() => "");
    if (!text) return {};
    try {
      return JSON.parse(text);
    } catch {
      // Resposta não-JSON (ex.: página de erro HTML). Devolve como mensagem.
      return { message: text };
    }
  };

  if (res.status === 401) {
    if (typeof document !== 'undefined') document.cookie = 'auth_token=; Max-Age=0; path=/';
    if (typeof localStorage !== 'undefined') localStorage.removeItem("auth_token");
    if (typeof window !== 'undefined') window.location.href = "/";
    throw new Error("Sessão expirada");
  }

  if (res.status === 422) {
    const body = await parseBody();
    // Formata erros de validação do Laravel em texto legível.
    if (body.errors && typeof body.errors === 'object') {
      const msgs = Object.values(body.errors).flat().join('\n');
      throw new Error(msgs || body.message || "Dados inválidos");
    }
    throw new Error(body.message || "Dados inválidos");
  }

  if (!res.ok) {
    const body = await parseBody();
    throw new Error(body.message ?? `Erro ${res.status}`);
  }

  // Respostas sem corpo (ex.: 204 No Content no DELETE)
  if (res.status === 204) return {} as T;

  return (await parseBody()) as T;
}

export const api = {
  get:    <T = any>(path: string, options?: any) => request<T>(path, options),
  post:   <T = any>(path: string, body: unknown, options?: any) => request<T>(path, { method: "POST", body: JSON.stringify(body), ...options }),
  put:    <T = any>(path: string, body: unknown, options?: any) => request<T>(path, { method: "PUT",  body: JSON.stringify(body), ...options }),
  delete: <T = any>(path: string, options?: any) => request<T>(path, { method: "DELETE", ...options }),
};
