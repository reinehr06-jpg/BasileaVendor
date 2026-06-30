// ============================================================
// MAPA DO TESOURO — Proteção de Rotas
// ============================================================
// PROPÓSITO:
//   Middleware do Next.js que roda antes de qualquer tela.
//   Verifica se o cookie "auth_token" existe. Se não existir, 
//   expulsa o usuário para o Login.
//
// #arq02
// ============================================================

import { NextRequest, NextResponse } from "next/server";

const PUBLIC_ROUTES = ["/", "/register"];

export function middleware(req: NextRequest) {
  const token = req.cookies.get("auth_token")?.value;
  const pathname = req.nextUrl.pathname;
  
  // Evitar loop infinito caso o config.matcher seja ignorado pelo Next.js 16+
  if (
    pathname.startsWith("/_next") || 
    pathname.startsWith("/images") || 
    pathname === "/favicon.ico" ||
    pathname === "/mockServiceWorker.js"
  ) {
    return NextResponse.next();
  }

  const isPublic = PUBLIC_ROUTES.includes(pathname);

  if (!token && !isPublic) {
    return NextResponse.redirect(new URL("/", req.url));
  }
  if (token && isPublic) {
    return NextResponse.redirect(new URL("/dashboard", req.url));
  }
  return NextResponse.next();
}

export const config = {
  matcher: ["/((?!_next/static|_next/image|favicon.ico|images/).*)"],
};
