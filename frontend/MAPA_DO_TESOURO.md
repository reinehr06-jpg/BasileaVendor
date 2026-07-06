# 🗺️ Mapa do Tesouro — Basileia Finance OS

Este documento é o seu guia definitivo sobre a arquitetura, estrutura de arquivos e organização de todo o ecossistema financeiro e contábil do projeto Basileia. 

Todas as telas construídas seguem um padrão visual unificado focado em usabilidade, densidade de informações (layout compacto para listas) e visão gerencial (dashboards).

---

## 🏗️ 1. Organização de Rotas (Next.js App Router)

O projeto usa a estrutura moderna do Next.js (App Router), onde cada pasta dentro de `src/app` representa uma rota do sistema. 

O grupo `(menu)` engloba todas as páginas que compartilham o **Sidebar** (Menu Lateral) e o **Topbar**.

### 📊 A. Dashboard Principal
```text
src/app/(menu)/dashboard/
└── page.tsx         ← Visão geral da saúde financeira e atalhos rápidos.
```

### 💰 B. Gestão Financeira (Módulo Principal)
O coração operacional do sistema. Focado em fluxo de caixa, pagamentos e recebimentos.

```text
src/app/(menu)/gestao-financeira/
├── receitas/        ← [Tela] Tudo o que JÁ ENTROU no caixa (Extrato e Resumo).
│   └── nova/        ← [Tela] Registrar nova receita efetivada.
├── contas-a-receber/← [Tela] Previsões (O que VAI ENTRAR). Layout Dashboard/Lista.
├── despesas/        ← [Tela] Tudo o que JÁ SAIU (Custos operacionais fixos/variáveis).
│   └── nova/        ← [Tela] Registrar nova despesa efetivada.
├── contas-a-pagar/  ← [Tela] Previsões (O que VAI SAIR). Layout Dashboard/Lista.
├── compras/         ← [Tela] Gestão de requisições e compras.
│   └── nova/        ← [Tela] Solicitar nova compra.
├── transferencias/  ← [Tela] Movimentações entre contas bancárias/caixas.
│   └── nova/        ← [Tela] Registrar transferência.
└── importacao/      ← [Tela] Importação de OFX e extratos bancários.
```

### 📈 C. Análises e Contábil
Módulo focado em fechamentos, balanços e visão estratégica.

```text
src/app/(menu)/analises-e-contabil/
├── dashboard/       ← [Tela] Indicadores de liquidez, solvência e margens.
├── dre/             ← [Tela] Demonstração do Resultado do Exercício.
├── relatorios/      ← [Tela] Central de geração de relatórios PDF/Excel.
│   └── novo/        ← [Tela] Montar relatório customizado.
```

### 📝 D. Cadastros Base
Estrutura de dados auxiliares que alimentam o financeiro.

```text
src/app/(menu)/cadastros/
├── contas/          ← Gestão de Contas Bancárias e Caixas.
│   └── historico/   ← Extrato completo de uma conta específica.
├── categorias/      ← Plano de Contas (Receitas, Despesas, Custos).
├── centros-de-custo/← Projetos, departamentos ou congregações.
├── departamentos/   ← Áreas internas da instituição.
├── fornecedores/    ← Cadastro de PJs e credores.
└── funcionarios/    ← Folha de pagamento e colaboradores.
```

### ⚙️ E. Configurações
Gestão de regras de negócio, permissões e setup da igreja/instituição.

```text
src/app/(menu)/configuracoes/
├── igreja/          ← Dados institucionais (CNPJ, Endereço, Logo).
├── conta/           ← Gestão de usuários e permissões do sistema.
├── assinaturas/     ← Fluxo de aprovação de pagamentos.
├── documentos/      ← Tipos de documentos fiscais.
├── filiais/         ← Gestão multi-congregações/matriz.
└── preferencias/    ← Parâmetros globais (moeda, fuso horário, alertas).
```

---

## 🧩 2. Componentes UI (Reutilizáveis)

Os componentes ficam na raiz de `src/components/` e são os blocos construtores que mantêm a identidade visual intacta (Roxo, Clean, Tailwind).

- **`Topbar.tsx`**: Barra de busca global, perfil e notificações.
- **`Sidebar.tsx`**: Menu lateral esquerdo expansível e responsivo.
- **`CustomSelect.tsx`**: Dropdown moderno (usado em filtros e formulários).
- **`Pagination.tsx`**: Componente de navegação em tabelas de dados longos.
- **`ExportDropdown.tsx`**: Opções de download de dados (CSV, Excel, PDF).

---

## 🎨 3. Padrões Visuais e de UI Aplicados

1. **Visão Dupla (Dashboard / Lista)**: Implementada em Contas a Pagar/Receber e Despesas/Receitas. Permite ao usuário alternar entre visão gráfica gerencial (Top KPIs) e tabela densa (Operacional).
2. **Cores Semânticas**: 
   - **Roxo (`#7C3AED`)**: Cor primária para ações (`botões`, `links ativos`, `gráficos`).
   - **Vermelho (`#EF4444`)**: Alertas de "Atrasados" ou saídas financeiras.
   - **Verde/Azul (`#10B981` / `#3B82F6`)**: Entradas e status positivos.
   - **Amarelo (`#F59E0B`)**: Vencimentos ou previsões para o dia atual ("Hoje").

---

## 🚀 4. Stack Tecnológico

- **Framework**: Next.js 14+ (App Router)
- **Estilização**: Tailwind CSS (Utilitário para design rápido)
- **Ícones**: Lucide-React
- **Gráficos**: Recharts
- **Linguagem**: TypeScript (Strict)
