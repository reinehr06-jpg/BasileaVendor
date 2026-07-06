import {
  PieChart,
  User,
  Users,
  ShoppingCart,
  Contact,
  ClipboardCheck,
  CreditCard,
  Percent,
  Target,
  Settings,
  FileText,
  UploadCloud,
  Link as LinkIcon
} from "lucide-react";

export const navSections = [
  {
    title: "VISÃO GERAL",
    items: [
      { label: "Painel de Controle", icon: PieChart, href: "/dashboard" },
    ],
  },
  {
    title: "GESTÃO COMERCIAL",
    items: [
      { label: "Vendedores", icon: User, href: "/gestao-comercial/vendedores" },
      { label: "Equipes", icon: Users, href: "/gestao-comercial/equipes" },
      { 
        label: "Vendas", 
        icon: ShoppingCart, 
        isAccordion: true, 
        subItems: [
          { label: "Métricas de Vendas", href: "/gestao-comercial/metricas-vendas" },
          { label: "Todas as Vendas", href: "/gestao-comercial/vendas" }
        ]
      },
      { label: "Clientes", icon: Contact, href: "/gestao-comercial/clientes" },
      { label: "Aprovações", icon: ClipboardCheck, href: "/gestao-comercial/aprovacoes" },
    ],
  },
  {
    title: "FINANCEIRO",
    items: [
      { label: "Pagamentos", icon: CreditCard, href: "/financeiro/pagamentos" },
      { label: "Links de Pagamento", icon: LinkIcon, href: "/financeiro/links-pagamento" },
      { label: "Comissões", icon: Percent, href: "/financeiro/comissoes" },
      { label: "Metas", icon: Target, href: "/financeiro/metas" },
    ],
  },
  {
    title: "SISTEMA",
    items: [
      { label: "Configurações", icon: Settings, href: "/configuracoes" },
      { label: "Termos de Uso", icon: FileText, href: "/termos" },

    ],
  }
];

export const sellerNavSections = [
  {
    title: "VENDAS",
    items: [
      { label: "Minhas Vendas", icon: ShoppingCart, href: "/vendedor/minhas-vendas" },
      { label: "Meus Clientes", icon: Users, href: "/vendedor/clientes" },
      { label: "Pagamentos", icon: CreditCard, href: "/vendedor/pagamentos" },
    ],
  },
  {
    title: "FINANCEIRO",
    items: [
      { label: "Comissões", icon: Percent, href: "/vendedor/comissoes" },
      { label: "Configurações", icon: Settings, href: "/vendedor/configuracoes" },
    ],
  }
];

export const gestorNavSections = [
  {
    title: "EQUIPE",
    items: [
      { label: "Vendedores", icon: Users, href: "/gestor/vendedores" },
      { 
        label: "Vendas", 
        icon: ShoppingCart, 
        isAccordion: true,
        subItems: [
          { label: "Métricas de Vendas", href: "/gestor/metricas-vendas" },
          { label: "Vendas da Equipe", href: "/gestor/vendas" },
          { label: "Minhas Vendas", href: "/gestor/minhas-vendas" }
        ]
      },
      { label: "Carteira de Clientes", icon: Contact, href: "/gestor/clientes" },
    ],
  },
  {
    title: "FINANCEIRO",
    items: [
      { label: "Comissões", icon: Percent, href: "/gestor/comissoes" },
      { label: "Configurações", icon: Settings, href: "/gestor/configuracoes" },
    ],
  }
];
