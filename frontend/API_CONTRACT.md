# Contrato de API — Basileia Vendor OS

Base URL: `NEXT_PUBLIC_API_URL/api`  
Auth: Bearer Token no header `Authorization` e Cookie `auth_token` HttpOnly.

## Status de implementação
| Módulo | Método | Endpoint | Body/Params | Status |
|--------|--------|----------|-------------|--------|
| Auth | POST | /login | LoginPayload | ✅ implementado |
| Auth | POST | /logout | - | ✅ implementado |
| Vendas | GET | /vendas | ?page&search | ✅ implementado |
| Vendas | POST | /vendas | VendaPayload | ✅ implementado |
| Clientes | GET | /clientes | ?page&search | ✅ implementado |
| Clientes | POST | /clientes | ClientePayload | ✅ implementado |
| Comissões | GET | /financeiro/comissoes | ?mes&ano | ✅ implementado |
| Dashboard | GET | /dashboard | - | ✅ implementado |
