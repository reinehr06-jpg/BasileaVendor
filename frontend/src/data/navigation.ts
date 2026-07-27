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
  LayoutDashboard,
  DollarSign,
  Link as LinkIcon
} from "lucide-react";

export const navSections = [
  {
    title: "DASHBOARD",
    items: [
      { label: "Painel", icon: LayoutDashboard, href: "/dashboard" },
    ],
  },
  {
    title: "GESTÃO COMERCIAL",
    items: [
      { label: "Vendas", icon: ShoppingCart, href: "/vendas" },
      { label: "Clientes", icon: Users, href: "/clientes" },
      { label: "Comissões", icon: DollarSign, href: "/comissoes" },
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

export const sellerNavSections = navSections;
export const gestorNavSections = navSections;
