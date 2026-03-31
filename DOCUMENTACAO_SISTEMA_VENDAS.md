# MANUAL DE ENGENHARIA E OPERAÇÃO – SISTEMA BASILÉIA VENDAS (100% EXAUSTIVO)

Este documento é a especificação exaustiva de cada tela, campo e regra de negócio do ecossistema Basiléia Vendas.

---

## 1. MÓDULO: VENDEDORES

### 1.1 Posição no sistema
Menu principal → Vendedores

### 1.2 Estrutura do módulo
1. Painel de Gestão (Listagem)
2. Cadastro de Vendedor (Modal)
3. Edição e Configuração de Split

### 4.1 Submenu: Gestão de Vendedores

#### 4.1.1 Objetivo
Gerenciar a força de vendas, definir perfis de acesso e autorizar recebimentos automáticos (Wallet IDs).

#### 4.1.2 Regra estrutural
- **Segurança**: Sanatização `Anti-BOM` em chaves de API e Wallet IDs.
- **Hierarquia**: Vendedores sem equipe vinculados diretamente ao Master.

#### 4.1.3 Tela de listagem
**Botão: Novo Vendedor**

**Campo de busca**
● Tipo: Texto
● Placeholder: "Buscar por nome, e-mail ou telefone..."

**Filtro de Status**
● Tipo: Select
● Opções: Todos, Ativo, Inativo, Bloqueado

**Tabela**
● Vendedor (Avatar + Nome + Telefone)
● E-mail (Acesso ao painel)
● Perfil (Badge: Vendedor/Gestor)
● Comissão (%) (Recorrência e Inicial)
● Split (Status Asaas: Validado/Pendente)
● Status (Badge: Ativo/Bloqueado/Inativo)
● Ações (Ver Detalhes, Editar, Inativar)

#### 4.1.4 Cadastro de Vendedor (Modal)
● **Nome Completo**: Texto | Obrigatório: Sim | Placeholder: "Ex: João da Silva"
● **E-mail (Acesso)**: Email | Obrigatório: Sim | Placeholder: "vendedor@email.com"
● **Telefone**: Texto | Placeholder: "(11) 99999-9999"
● **Perfil**: Select | Obrigatório: Sim | Opções: Vendedor, Gestor de Equipe
● **Comissão Inicial (%)**: Número | Obrigatório: Sim | Placeholder: "10.00"
● **Comissão Recorrência (%)**: Número | Obrigatório: Sim | Placeholder: "5.00"

---

## 2. MÓDULO: EQUIPES

### 4.1 Submenu: Gestão de Equipe

#### 4.1.3 Tela de listagem
**Cards de Equipe**
● Cabeçalho: Nome + Dot Color (Hexadecimal) + Badge Status.
● KPIs: Vendedores no Time, Vendas Mês, Recebido (R$).
● Barra de Progresso Meta: % Meta vs. Valor Realizado.

#### 4.1.4 Cadastro de Equipe (Modal)
● **Nome da Equipe**: Texto | Obrigatório: Sim | Placeholder: "Ex: Equipe Anthony"
● **Gestor Responsável**: Select | Obrigatório: Sim | Placeholder: "Selecione o gestor"
● **Meta Mensal (R$)**: Número | Placeholder: "0.00"
● **Cor da Equipe**: Color Picker | Padrão: #4C1D95

---

## 3. MÓDULO: VENDAS GLOBAIS

### 4.1 Submenu: Controle de Transações

#### 4.1.2 Regra estrutural
- **Desconto 5%**: Vendas com desconto superior a 5.00% aguardam aprovação manual.
- **Sincronização**: Atualização automática via Webhook Asaas.

#### 4.1.3 Listagem e Ações
● **Tabela**: Cliente, Vendedor, Plano (Badge), Valor (R$), Status (Badge).
● **Botão Boleto**: Download direto em nova aba.
● **Botão Pix**: Exibição de QR Code Modal.
● **Botão Cartão**: Link Seguro para Checkout Externo.

---

## 4. MÓDULO: CONFIGURAÇÕES (ABAS)

### 4.1 Submenu: Integrações

#### 4.1.2 Campos
● **Checkout Externo**: URL | Placeholder: "https://seucheckout.com/pagar?id="
● **Asaas API Key**: Password | Chave de produção ou sandbox.
● **Asaas Webhook Token**: Password | Chave de segurança de autenticação.
● **Church Sync URL**: URL | Endpoint de retorno Basiléia Church.

### 4.2 Submenu: Regras de Planos
● **Tabela de Repasse Fixo**: Vendedor (1ª / Rec.) e Gestor (1ª / Rec.) por categoria de plano.

---

## REGRAS TÉCNICAS E SEGURANÇA (DETALHE FINAL)

● **Anti-BOM**: Limpeza de caracteres invisíveis em chaves de API.
● **Checkout Shaft**: Segurança via Query String SHA256 no redirecionamento.

---
**FIM DO MANUAL SUPREMO – BASILÉIA VENDAS 100% EXAUSTIVO**
