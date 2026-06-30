// ============================================================
// MAPA DO TESOURO — Menu de Navegação (Sidebar)
// ============================================================
// PROPÓSITO:
//   Lista estática das seções e rotas que aparecem no menu
//   lateral (Sidebar). Desacoplado da UI para facilitar a 
//   adição de novas telas ou controle de permissão (RBAC) no futuro.
//
// ============================================================

import {
  LayoutDashboard,
  Users,
  Target,
  ShoppingCart,
  MessageSquare,
  Bot,
  Settings,
  ShieldPlus,
  CreditCard,
  Calendar
} from "lucide-react";

export const navSections = [
  {
    title: "COMERCIAL",
    items: [
      { label: "Dashboard", icon: LayoutDashboard, href: "/dashboard" },
      { label: "Leads", icon: Target, href: "/leads" },
      { label: "Vendas Globais", icon: ShoppingCart, href: "/vendas" },
      { label: "Clientes Asaas", icon: Users, href: "/clientes" },
      { label: "Checkout", icon: CreditCard, href: "/checkout" },
    ],
  },
  {
    title: "GESTÃO COMERCIAL",
    items: [
      { label: "Vendedores", icon: Users, href: "/vendedores" },
      { label: "Aprovações", icon: ShieldPlus, href: "/aprovacoes" },
      { label: "Calendário", icon: Calendar, href: "/calendario" },
    ],
  },
  {
    title: "ATENDIMENTO & IA",
    items: [
      { label: "IA Lab", icon: Bot, href: "/ia-lab" },
      { label: "Chat WhatsApp", icon: MessageSquare, href: "/chat" },
    ],
  },
  {
    title: "SISTEMA",
    items: [
      { label: "Configurações", icon: Settings, href: "/configuracoes" },
    ],
  },
];
