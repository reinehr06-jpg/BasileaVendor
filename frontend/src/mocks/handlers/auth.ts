import { http, HttpResponse } from "msw";

export const authHandlers = [
  http.post("/api/auth/login", async ({ request }) => {
    const body = await request.json() as any;
    if (!body.captcha || body.captcha === "") {
      return HttpResponse.json({ message: "Desafio CAPTCHA não validado. Prove que você é humano." }, { status: 422 });
    }
    if (body.email === "admin@basileia.com" && body.password === "123456") {
      return HttpResponse.json({
        token: "mock-jwt-token-abc123",
        user: { id: "1", nome: "Pr. João Silva", email: body.email, role: "admin" }
      });
    }
    return HttpResponse.json({ message: "Credenciais inválidas" }, { status: 401 });
  }),
  http.post("/api/auth/register", async ({ request }) => {
    const body = await request.json() as any;
    if (!body.captcha || body.captcha === "") {
      return HttpResponse.json({ message: "Desafio CAPTCHA não validado. Prove que você é humano." }, { status: 422 });
    }
    return HttpResponse.json({
      token: "mock-jwt-token-abc123",
      user: { id: "1", nome: body.responsibleName, email: body.responsibleEmail, role: "admin" }
    });
  }),
  http.post("/api/auth/logout", () => {
    return new HttpResponse(null, { status: 204 });
  }),
  http.get("/api/auth/me", () => {
    return HttpResponse.json({ id: "1", nome: "Pr. João Silva", email: "admin@basileia.com", role: "admin" });
  })
];
