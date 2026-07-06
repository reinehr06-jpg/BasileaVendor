# Guia de Integração - Basileia Church OS

Este guia mapeia a Camada de Serviços do Frontend com a API esperada no Backend. 
O desenvolvedor backend deve seguir estritamente estes contratos para garantir a integração.

## Configuração Global
- Base URL esperada: Configurada no `.env` como `NEXT_PUBLIC_API_URL`
- Autenticação: Bearer Token via Header `Authorization: Bearer <token>` ou Cookies HttpOnly.

---

## Módulo: Autenticação (auth.service.ts)

### POST /api/auth/login
- **Body:** `{ email: string, password: string, remember: boolean }`
- **Retorno esperado:** `{ token: string, user: { id, nome, email, role } }`
- **Regras:** Usar JWT. Token expira em 7 dias (remember=true) ou 8h (false).

### POST /api/auth/register
- **Body:** Variável conforme campos da tela.
- **Retorno esperado:** `{ token: string, user: User }`

### GET /api/auth/me
- **Retorno esperado:** `User`

### POST /api/auth/logout
- **Retorno esperado:** Vazio (Apenas limpar a sessão/token do DB se houver).

---

## Módulo: Membros (membros.service.ts)

### GET /api/membros
- **Query Params:** `?page=1&search=João`
- **Retorno esperado:** `PaginatedResponse<Membro>` (Ver `src/types/membro.ts`)

### GET /api/membros/{id}
- **Retorno esperado:** `Membro`

### POST /api/membros
- **Body:** `MembroPayload`
- **Retorno esperado:** `Membro` (Com ID e CreatedAt inseridos).

### PUT /api/membros/{id}
- **Body:** `Partial<MembroPayload>`
- **Retorno esperado:** `Membro` atualizado.

### DELETE /api/membros/{id}
- **Retorno esperado:** Vazio (204 No Content).

---

## Módulo: Cultos (cultos.service.ts)
(Endpoints devem seguir o padrão CRUD RESTful similar a Membros).

## Módulo: Células (celulas.service.ts)
(Endpoints devem seguir o padrão CRUD RESTful similar a Membros).

## Módulo: Eventos (eventos.service.ts)
(Endpoints devem seguir o padrão CRUD RESTful similar a Membros).
