# 🌳 ÁRVORE DE NAVEGAÇÃO — Basileia Church OS (Fluxo do Usuário)

## 🔐 1. Autenticação (Login / Cadastro)
```text
src/app/page.tsx              ← Login - #pag00
src/app/page.tsx              ← Novo Cadastro — Tela 1 - #pag01
src/app/page.tsx              ← Novo Cadastro — Tela 2 - #pag02
src/app/page.tsx              ← Novo Cadastro — Tela 3 - #pag03
src/app/register/             ← Telas arquivadas de registro - #pag04
```

## 🏠 2. Dashboard
```text
src/app/dashboard/             ← [Tela] - #pag05
```

## ⛪ 3. Cultos
```text
src/app/cultos/                ← [Tela] - #pag06
```

## 🧩 14. Componentes UI (Reutilizáveis)
```text
src/components/Topbar.tsx       ← Barra superior global
src/components/Sidebar.tsx      ← Menu lateral responsivo
src/components/ThemeToggle.tsx  ← Toggle de tema claro/escuro
src/components/CustomSelect.tsx ← Select unificado (Padrão de UI)
src/components/Pagination.tsx   ← Paginação unificada
src/components/ExportDropdown.tsx ← Botão de exportação de dados
src/components/auth/Captcha.tsx ← Validação visual de login
```

## ⚙️ 11. Arquitetura e Integração (Serviços)
```text
src/lib/api.ts                  ← Cliente HTTP e interceptors - #arq01
src/middleware.ts               ← Proteção de rotas do Next.js - #arq02
src/context/AuthContext.tsx     ← Estado Global de Autenticação - #arq03
src/lib/sanitize.ts             ← Limpador de máscaras (CPF, DDI) - #arq04
src/services/auth.service.ts    ← API: Endpoints de Autenticação - #arq05
src/services/membros.service.ts ← API: Endpoints de Membros - #arq06
src/types/auth.ts               ← Types: Contratos de Auth - #arq07
src/types/membro.ts             ← Types: Contratos de Membros - #arq08
src/mocks/MockProvider.tsx      ← MSW: Ativador de Servidor Fake - #arq09
```

## 📝 4. Cadastros Base
```text
src/app/cadastros/             ← [Tela] - #pag10
src/app/cadastros/cargos/      ← [Tela] - #pag11
src/app/cadastros/cargos/novo/ ← [Tela] - #pag12
src/app/cadastros/membros/     ← [Tela] - #pag13
src/app/cadastros/membros/[id]/ ← [Tela] - #pag14
src/app/cadastros/membros/anotacoes/ ← [Tela] - #pag15
src/app/cadastros/membros/editar/ ← [Tela] - #pag16
src/app/cadastros/membros/novo/ ← [Tela] - #pag17
src/app/cadastros/ministerios/ ← [Tela] - #pag18
src/app/cadastros/ministerios/[id]/ ← [Tela] - #pag19
src/app/cadastros/ministerios/editar/ ← [Tela] - #pag20
src/app/cadastros/ministerios/novo/ ← [Tela] - #pag21
src/app/cadastros/redes/       ← [Tela] - #pag22
src/app/cadastros/redes/[id]/  ← [Tela] - #pag23
src/app/cadastros/redes/novo/  ← [Tela] - #pag24
src/app/cadastros/visitantes/  ← [Tela] - #pag25
src/app/cadastros/visitantes/novo/ ← [Tela] - #pag26
```

## 👥 5. Células
```text
src/app/celulas/               ← [Tela] - #pag27
src/app/celulas/cadastro/      ← [Tela] - #pag28
src/app/celulas/cadastro/[id]/ ← [Tela] - #pag29
src/app/celulas/cadastro/novo/ ← [Tela] - #pag30
src/app/celulas/metricas/      ← [Tela] - #pag31
src/app/celulas/metricas/novo/ ← [Tela] - #pag32
src/app/celulas/perfil/        ← [Tela] - #pag33
src/app/celulas/perfil/novo/   ← [Tela] - #pag34
```

## 🎓 6. Cursos
```text
src/app/cursos/                ← [Tela] - #pag35
src/app/cursos/novo/           ← [Tela] - #pag36
src/app/cursos/[id]/           ← [Tela] - #pag37
src/app/cursos/[id]/inscritos/ ← [Tela] - #pag38
src/app/cursos/inscritos/      ← [Tela] - #pag39
src/app/cursos/inscritos/novo/ ← [Tela] - #pag40
```

## 🎪 7. Eventos
```text
src/app/eventos/               ← [Tela] - #pag41
src/app/eventos/novo/          ← [Tela] - #pag42
src/app/eventos/[id]/          ← [Tela] - #pag43
src/app/eventos/[id]/inscritos/ ← [Tela] - #pag44
src/app/eventos/inscritos/     ← [Tela] - #pag45
src/app/eventos/inscritos/novo/ ← [Tela] - #pag46
```

## 💚 8. Atendimentos
```text
src/app/atendimentos/          ← [Tela] - #pag47
```

## ✝️ 9. Aceitou Jesus
```text
src/app/cultos/aceitou-jesus/  ← [Tela] - #pag48
src/app/cultos/aceitou-jesus/config/ ← [Tela] - #pag49
src/app/cultos/aceitou-jesus/registros/ ← [Tela] - #pag50
```

## 🤖 10. Assistente Virtual
```text
src/app/assistente/            ← [Tela] - #pag51
src/app/assistente/notificacoes/ ← [Tela] - #pag52
src/app/assistente/perfil/     ← [Tela] - #pag53
```

## 👤 11. Usuários do Sistema
```text
src/app/usuarios/              ← [Tela] - #pag54
src/app/usuarios/novo/         ← [Tela] - #pag55
```

## ⚙️ 12. Configurações Gerais
```text
src/app/configuracoes/         ← [Tela] - #pag56
src/app/configuracoes/conta/   ← [Tela] - #pag57
src/app/configuracoes/seguranca/ ← [Tela] - #pag58
src/app/configuracoes/planos/  ← [Tela] - #pag59
src/app/configuracoes/conexoes/ ← [Tela] - #pag60
src/app/configuracoes/conexoes/novo/ ← [Tela] - #pag61
src/app/configuracoes/relatorios/ ← [Tela] - #pag62
src/app/configuracoes/relatorios/novo/ ← [Tela] - #pag63
src/app/configuracoes/logs/    ← [Tela] - #pag64
src/app/configuracoes/termos/  ← [Tela] - #pag65
```

## 🎧 13. HelpDesk (Módulo Separado)
```text
src/app/help/                  ← [Tela] - #pag66
src/app/help/dashboard/        ← [Tela] - #pag67
src/app/help/chat/             ← [Tela] - #pag68
src/app/help/atendidos/        ← [Tela] - #pag69
src/app/help/monitoramento/    ← [Tela] - #pag70
src/app/help/cadastros/categorias/ ← [Tela] - #pag71
src/app/help/configuracoes/conta/ ← [Tela] - #pag72
src/app/help/configuracoes/seguranca/ ← [Tela] - #pag73
src/app/help/configuracoes/relatorios/ ← [Tela] - #pag74
src/app/help/configuracoes/logs/ ← [Tela] - #pag75
src/app/help/configuracoes/termos/ ← [Tela] - #pag76
```

## 📱 QR Codes (Transversal)
```text
src/app/qr-codes/              ← [Tela] - #pag77
src/app/qr-codes/novo/         ← [Tela] - #pag78
src/app/qr-codes/[id]/         ← [Tela] - #pag79
```
