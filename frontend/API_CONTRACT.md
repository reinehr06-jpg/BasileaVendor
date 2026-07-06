# Contrato de API — Basileia Church OS

Base URL: `NEXT_PUBLIC_API_URL/api`  
Auth: Bearer Token no header `Authorization` e Cookie `auth_token` HttpOnly.

## Status de implementação
| Módulo | Método | Endpoint | Body/Params | Status |
|--------|--------|----------|-------------|--------|
| Auth | POST | /auth/login | LoginPayload | ⬜ pendente |
| Auth | POST | /auth/register | RegisterPayload | ⬜ pendente |
| Auth | POST | /auth/logout | — | ⬜ pendente |
| Auth | GET | /auth/me | — | ⬜ pendente |
| Membros | GET | /membros | ?page&search&ministerioId | ⬜ pendente |
| Membros | POST | /membros | MembroPayload | ⬜ pendente |
| Membros | GET | /membros/:id | — | ⬜ pendente |
| Membros | PUT | /membros/:id | Partial<MembroPayload> | ⬜ pendente |
| Membros | DELETE | /membros/:id | — | ⬜ pendente |
| Cultos | GET | /cultos/agenda | ?mes&ano | ⬜ pendente |
| Cultos | POST | /cultos/presenca | PresencaPayload | ⬜ pendente |
| Células | GET | /celulas | ?page&search | ⬜ pendente |
| Células | GET | /celulas/metricas | ?periodo | ⬜ pendente |
| Eventos | GET | /eventos | ?page | ⬜ pendente |
| Eventos | GET | /eventos/:id/inscritos | — | ⬜ pendente |
