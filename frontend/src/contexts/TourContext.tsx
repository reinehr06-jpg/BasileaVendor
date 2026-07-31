"use client";

import React, { createContext, useContext, useState, useEffect } from "react";
import { useRouter, usePathname } from "next/navigation";
import { X } from "lucide-react";
import { useAuth } from "@/context/AuthContext";

type TourStep = {
  id: string;
  targetId: string;
  title: string;
  description: string;
  page: string;
  position?: "top" | "bottom" | "left" | "right" | "center";
};

const MASTER_TOUR_STEPS: TourStep[] = [
  {
    id: "step-1-dashboard",
    targetId: "tour-dashboard-kpi",
    title: "Bem-vindo ao Dashboard!",
    description: "Aqui você acompanha o resumo das suas vendas, metas e comissões do mês.",
    page: "/dashboard",
    position: "center"
  },
  {
    id: "step-2-menu-vendedores",
    targetId: "tour-menu-vendedores",
    title: "Gestão Comercial",
    description: "Este é o menu lateral principal. Tudo sobre a sua operação de vendas começa aqui em Vendedores.",
    page: "/dashboard",
    position: "right"
  },
  {
    id: "step-3-lista-vendedores",
    targetId: "tour-lista-vendedores-tabela",
    title: "Lista de Vendedores",
    description: "Nesta tela você gerencia toda a sua equipe. Pode ver quem está ativo, líder direto e comissões.",
    page: "/gestao-comercial/vendedores",
    position: "top"
  },
  {
    id: "step-4-novo-vendedor-btn",
    targetId: "tour-novo-vendedor-btn",
    title: "Adicionar Vendedor",
    description: "Clique aqui sempre que precisar incluir um novo membro no seu time.",
    page: "/gestao-comercial/vendedores",
    position: "bottom"
  },
  {
    id: "step-5-novo-vendedor-dados-pessoais",
    targetId: "tour-novo-vendedor-dados-pessoais",
    title: "Dados Pessoais",
    description: "Primeiro, insira os dados de contato e acesso do novo membro.",
    page: "/gestao-comercial/vendedores/novo",
    position: "bottom"
  },
  {
    id: "step-6-novo-vendedor-funcao-equipe",
    targetId: "tour-novo-vendedor-funcao-equipe",
    title: "Função e Liderança",
    description: "Defina o nível de acesso (gestor/vendedor) e a qual equipe pertence.",
    page: "/gestao-comercial/vendedores/novo",
    position: "bottom"
  },
  {
    id: "step-7-novo-vendedor-comissoes",
    targetId: "tour-novo-vendedor-comissoes",
    title: "Regras de Comissão",
    description: "Configure exatamente as taxas e repasses deste vendedor por cada transação.",
    page: "/gestao-comercial/vendedores/novo",
    position: "top"
  },
  {
    id: "step-8-menu-equipes",
    targetId: "tour-menu-equipes",
    title: "Gestão de Equipes",
    description: "Para organizar sua operação em times, clique no menu Equipes.",
    page: "/gestao-comercial/vendedores/novo",
    position: "right"
  },
  {
    id: "step-9-tabela-equipes",
    targetId: "tour-lista-equipes",
    title: "Suas Equipes",
    description: "Aqui ficam listados os seus esquadrões de vendas, metas coletivas e gestores.",
    page: "/gestao-comercial/equipes",
    position: "top"
  },
  {
    id: "step-10-nova-equipe-btn",
    targetId: "tour-nova-equipe-btn",
    title: "Criar Nova Equipe",
    description: "Basta clicar aqui para formar novos grupos e designar um líder.",
    page: "/gestao-comercial/equipes",
    position: "bottom"
  },
  {
    id: "step-11-nova-equipe-form",
    targetId: "tour-nova-equipe-form",
    title: "Definição de Equipe",
    description: "Você vincula um gestor e define uma meta financeira mensal para o time.",
    page: "/gestao-comercial/equipes/nova",
    position: "top"
  },
  {
    id: "step-12-menu-vendas",
    targetId: "tour-menu-vendas",
    title: "Menu de Vendas",
    description: "Acompanhamento detalhado das transações e performance.",
    page: "/gestao-comercial/equipes/nova",
    position: "right"
  },
  {
    id: "step-13-menu-metricas",
    targetId: "tour-menu-métricas-de-vendas",
    title: "Submenu de Métricas",
    description: "Visão estratégica de conversão.",
    page: "/gestao-comercial/equipes/nova",
    position: "right"
  },
  {
    id: "step-14-tela-metricas",
    targetId: "tour-metricas-view",
    title: "Análise de Vendas",
    description: "Gráficos de evolução, comparativos e ticket médio da sua operação.",
    page: "/gestao-comercial/metricas-vendas",
    position: "top"
  },
  {
    id: "step-15-menu-todas-vendas",
    targetId: "tour-menu-todas-as-vendas",
    title: "Submenu Todas as Vendas",
    description: "Acesso ao extrato bruto de transações.",
    page: "/gestao-comercial/metricas-vendas",
    position: "right"
  },
  {
    id: "step-16-tela-vendas",
    targetId: "tour-vendas-tabela",
    title: "Histórico de Transações",
    description: "Lista completa e auditável de todas as vendas realizadas.",
    page: "/gestao-comercial/vendas",
    position: "top"
  },
  {
    id: "step-17-menu-clientes",
    targetId: "tour-menu-clientes",
    title: "Sua Carteira de Clientes",
    description: "Menu para gestão de contato e faturamento por cliente.",
    page: "/gestao-comercial/vendas",
    position: "right"
  },
  {
    id: "step-18-tela-clientes",
    targetId: "tour-clientes-tabela",
    title: "CRM e Status",
    description: "Verifique o status de cada cliente, quem vendeu para ele e risco financeiro.",
    page: "/gestao-comercial/clientes",
    position: "top"
  },
  {
    id: "step-19-menu-aprovacoes",
    targetId: "tour-menu-aprovações",
    title: "Central de Aprovações",
    description: "Operações que requerem chancela do master.",
    page: "/gestao-comercial/clientes",
    position: "right"
  },
  {
    id: "step-20-tela-aprovacoes",
    targetId: "tour-aprovacoes-tabela",
    title: "Aprovações Pendentes",
    description: "Negociações especiais e exceções cairão nesta tela para sua avaliação.",
    page: "/gestao-comercial/aprovacoes",
    position: "top"
  },
  {
    id: "step-21-menu-pagamentos",
    targetId: "tour-menu-pagamentos",
    title: "Módulo Financeiro",
    description: "Vamos aos pagamentos e caixa.",
    page: "/gestao-comercial/aprovacoes",
    position: "right"
  },
  {
    id: "step-22-tela-pagamentos",
    targetId: "tour-pagamentos-tabela",
    title: "Gestão de Recebimentos",
    description: "O coração do financeiro! Acompanhe recebimentos via PIX, Boletos e Cartão integrados ao Asaas.",
    page: "/financeiro/pagamentos",
    position: "top"
  },
  {
    id: "step-23-menu-links",
    targetId: "tour-menu-links-de-pagamento",
    title: "Links de Pagamento",
    description: "Cobranças expressas fora do fluxo padrão.",
    page: "/financeiro/pagamentos",
    position: "right"
  },
  {
    id: "step-24-novo-link",
    targetId: "tour-novo-link-btn",
    title: "Novo Link",
    description: "Gere links para assinaturas ou vendas diretas de forma instantânea e copie para o WhatsApp.",
    page: "/financeiro/links-pagamento",
    position: "bottom"
  },
  {
    id: "step-25-menu-comissoes",
    targetId: "tour-menu-comissões",
    title: "Menu Comissões",
    description: "Contas a pagar para a equipe.",
    page: "/financeiro/links-pagamento",
    position: "right"
  },
  {
    id: "step-26-tela-comissoes",
    targetId: "tour-comissoes-tabela",
    title: "Repasses e Fechamentos",
    description: "O relatório oficial de todas as comissões devidas aos seus vendedores no mês atual (últimos 30 dias).",
    page: "/financeiro/comissoes",
    position: "top"
  },
  {
    id: "step-27-menu-metas",
    targetId: "tour-menu-metas",
    title: "Menu Metas",
    description: "Gerenciamento de objetivos.",
    page: "/financeiro/comissoes",
    position: "right"
  },
  {
    id: "step-28-nova-meta",
    targetId: "tour-nova-meta-btn",
    title: "Definição de Metas",
    description: "Acompanhe os alvos de faturamento e clique aqui para estipular uma nova meta para os vendedores.",
    page: "/financeiro/metas",
    position: "bottom"
  },
  {
    id: "step-29-menu-config",
    targetId: "tour-menu-configurações",
    title: "Menu de Configurações",
    description: "Tudo relacionado às engrenagens da plataforma e conta bancária.",
    page: "/financeiro/metas",
    position: "right"
  },
  {
    id: "step-30-config-cards",
    targetId: "tour-config-cards",
    title: "Integrações e Setup",
    description: "Vincule o Asaas, monitore os logs (Webhooks) e atualize os dados da sua empresa clicando nestes cards.",
    page: "/configuracoes",
    position: "top"
  },
  {
    id: "step-31-menu-termos",
    targetId: "tour-menu-termos-de-uso",
    title: "Termos Jurídicos",
    description: "Controle os acordos legais da empresa.",
    page: "/configuracoes",
    position: "right"
  },
  {
    id: "step-32-novo-termo",
    targetId: "tour-novo-termo-btn",
    title: "Adicionar Termos",
    description: "Suba novos contratos rapidamente com versões atualizadas para toda a base. Aproveite a plataforma!",
    page: "/termos",
    position: "bottom"
  }
];


const VENDEDOR_TOUR_STEPS: TourStep[] = [
  {
    id: "v-step-1-dashboard",
    targetId: "tour-dashboard-kpi",
    title: "Bem-vindo, Vendedor!",
    description: "Aqui você acompanha suas vendas, metas atingidas e comissões do mês.",
    page: "/vendedor",
    position: "center"
  },
  {
    id: "v-step-2-menu-minhas-vendas",
    targetId: "tour-menu-minhas-vendas",
    title: "Suas Vendas",
    description: "Veja todo o histórico e status das suas negociações.",
    page: "/vendedor/minhas-vendas",
    position: "right"
  },
  {
    id: "v-step-3-tela-minhas-vendas",
    targetId: "tour-vendedor-minhas-vendas-view",
    title: "Extrato de Vendas",
    description: "Tabela completa com cada transação realizada por você.",
    page: "/vendedor/minhas-vendas",
    position: "top"
  },
  {
    id: "v-step-4-menu-clientes",
    targetId: "tour-menu-meus-clientes",
    title: "Seus Clientes",
    description: "Acesse rapidamente os contatos da sua carteira.",
    page: "/vendedor/clientes",
    position: "right"
  },
  {
    id: "v-step-5-tela-clientes",
    targetId: "tour-vendedor-clientes-view",
    title: "Gestão de Clientes",
    description: "Informações de contato e faturamento de quem já comprou com você.",
    page: "/vendedor/clientes",
    position: "top"
  },
  {
    id: "v-step-6-menu-pagamentos",
    targetId: "tour-menu-pagamentos",
    title: "Pagamentos",
    description: "Consulte o status das cobranças e links gerados.",
    page: "/vendedor/pagamentos",
    position: "right"
  },
  {
    id: "v-step-7-tela-pagamentos",
    targetId: "tour-vendedor-pagamentos-view",
    title: "Links e Recebimentos",
    description: "Histórico financeiro das suas operações ativas.",
    page: "/vendedor/pagamentos",
    position: "top"
  },
  {
    id: "v-step-8-menu-comissoes",
    targetId: "tour-menu-comissoes",
    title: "Suas Comissões",
    description: "O mais importante: seu faturamento!",
    page: "/vendedor/comissoes",
    position: "right"
  },
  {
    id: "v-step-9-tela-comissoes",
    targetId: "tour-vendedor-comissoes-view",
    title: "Extrato de Repasses",
    description: "Valores devidos a você referentes ao mês atual.",
    page: "/vendedor/comissoes",
    position: "top"
  },
  {
    id: "v-step-10-menu-config",
    targetId: "tour-menu-configuracoes",
    title: "Suas Configurações",
    description: "Ajuste seus dados pessoais e senha.",
    page: "/vendedor/configuracoes",
    position: "right"
  }
];

const GESTOR_TOUR_STEPS: TourStep[] = [
  {
    id: "g-step-1-dashboard",
    targetId: "tour-dashboard-kpi",
    title: "Bem-vindo, Gestor!",
    description: "Aqui você acompanha o desempenho da sua equipe.",
    page: "/gestor",
    position: "center"
  },
  {
    id: "g-step-2-menu-vendedores",
    targetId: "tour-menu-vendedores",
    title: "Sua Equipe",
    description: "Consulte os vendedores sob sua liderança.",
    page: "/gestor/vendedores",
    position: "right"
  },
  {
    id: "g-step-3-tela-vendedores",
    targetId: "tour-gestor-vendedores-view",
    title: "Gestão do Time",
    description: "Lista de todos os membros e suas performances individuais.",
    page: "/gestor/vendedores",
    position: "top"
  },
  {
    id: "g-step-4-menu-metricas",
    targetId: "tour-menu-metricas-de-vendas",
    title: "Métricas da Equipe",
    description: "Visão analítica de como o grupo está performando.",
    page: "/gestor/metricas-vendas",
    position: "right"
  },
  {
    id: "g-step-5-tela-metricas",
    targetId: "tour-gestor-metricas-view",
    title: "Gráficos e Conversão",
    description: "Ticket médio e evolução de toda a equipe.",
    page: "/gestor/metricas-vendas",
    position: "top"
  },
  {
    id: "g-step-6-menu-vendas-equipe",
    targetId: "tour-menu-vendas-da-equipe",
    title: "Vendas da Equipe",
    description: "Extrato bruto das negociações do seu time.",
    page: "/gestor/vendas",
    position: "right"
  },
  {
    id: "g-step-7-tela-vendas-equipe",
    targetId: "tour-gestor-vendas-view",
    title: "Histórico Completo",
    description: "Todas as transações registradas pelos seus vendedores.",
    page: "/gestor/vendas",
    position: "top"
  },
  {
    id: "g-step-7b-menu-minhas-vendas",
    targetId: "tour-menu-minhas-vendas",
    title: "Minhas Vendas Pessoais",
    description: "Se você atuar também como vendedor, aqui ficam as suas vendas próprias.",
    page: "/gestor/minhas-vendas",
    position: "right"
  },
  {
    id: "g-step-7c-tela-minhas-vendas",
    targetId: "tour-gestor-minhas-vendas-view",
    title: "Extrato Pessoal",
    description: "Seu próprio faturamento e histórico de comissões.",
    page: "/gestor/minhas-vendas",
    position: "top"
  },
  {
    id: "g-step-8-menu-clientes",
    targetId: "tour-menu-carteira-de-clientes",
    title: "Clientes da Equipe",
    description: "Acompanhe os clientes atendidos pelo seu grupo.",
    page: "/gestor/clientes",
    position: "right"
  },
  {
    id: "g-step-9-tela-clientes",
    targetId: "tour-gestor-clientes-view",
    title: "CRM e Risco",
    description: "Status e faturamento de cada cliente da carteira.",
    page: "/gestor/clientes",
    position: "top"
  },
  {
    id: "g-step-10-menu-comissoes",
    targetId: "tour-menu-comissoes",
    title: "Comissões",
    description: "Extrato de repasses para você e sua equipe.",
    page: "/gestor/comissoes",
    position: "right"
  },
  {
    id: "g-step-11-tela-comissoes",
    targetId: "tour-gestor-comissoes-view",
    title: "Fechamentos",
    description: "Valores devidos referentes ao mês atual.",
    page: "/gestor/comissoes",
    position: "top"
  },
  {
    id: "g-step-12-menu-config",
    targetId: "tour-menu-configuracoes",
    title: "Configurações",
    description: "Acesse seus dados e preferências da conta.",
    page: "/gestor/configuracoes",
    position: "right"
  }
];

type TourContextData = {
  currentStepIndex: number;
  isActive: boolean;
  startTour: () => void;
  nextStep: () => void;
  skipTour: () => void;
};

const TourContext = createContext<TourContextData>({} as TourContextData);

export function TourProvider({ children }: { children: React.ReactNode }) {
  const [isActive, setIsActive] = useState(false);
  const [currentStepIndex, setCurrentStepIndex] = useState(0);
  const [showSkipPopup, setShowSkipPopup] = useState(false);
  const [skipTimer, setSkipTimer] = useState(15);
  

  const router = useRouter();
  const pathname = usePathname();
  const { user } = useAuth();

  const currentTourSteps = React.useMemo(() => {
    if (!user) return MASTER_TOUR_STEPS;
    if (user.perfil === "Vendedor" || user.gestor === "Sim" || user.is_gestor === false) {
      // Need a strong check for profile
      if (user.perfil === "Vendedor") return VENDEDOR_TOUR_STEPS;
      if (user.perfil === "Gestor") return GESTOR_TOUR_STEPS;
    }
    // Simplest logic based on route since they are isolated
    if (pathname.startsWith("/vendedor")) return VENDEDOR_TOUR_STEPS;
    if (pathname.startsWith("/gestor")) return GESTOR_TOUR_STEPS;
    return MASTER_TOUR_STEPS;
  }, [user, pathname]);


  useEffect(() => {
    const checkStartTour = () => {
      const hasSeenTour = localStorage.getItem("@VendorOS:tourSeen");
      const hasAcceptedTermos = localStorage.getItem("termos_aceitos");
      
      if (!hasSeenTour && hasAcceptedTermos) {
        setIsActive(true);
        setCurrentStepIndex(0);
      }
    };

    checkStartTour();

    window.addEventListener("termos_aceitos", checkStartTour);
    return () => window.removeEventListener("termos_aceitos", checkStartTour);
  }, []);

  useEffect(() => {
    if (isActive && currentTourSteps[currentStepIndex]) {
      const step = currentTourSteps[currentStepIndex];
      if (pathname !== step.page) {
        router.push(step.page);
      }
    }
  }, [isActive, currentStepIndex, pathname, router]);

  useEffect(() => {
    let interval: any;
    if (showSkipPopup && skipTimer > 0) {
      interval = setInterval(() => {
        setSkipTimer((t) => t - 1);
      }, 1000);
    } else if (skipTimer === 0) {
      setShowSkipPopup(false);
    }
    return () => clearInterval(interval);
  }, [showSkipPopup, skipTimer]);

  const startTour = () => {
    setIsActive(true);
    setCurrentStepIndex(0);
    setShowSkipPopup(false);
  };

  const nextStep = () => {
    if (currentStepIndex < currentTourSteps.length - 1) {
      setCurrentStepIndex(currentStepIndex + 1);
    } else {
      endTour();
    }
  };

  const skipTour = () => {
    setIsActive(false);
    localStorage.setItem("@VendorOS:tourSeen", "true");
    setSkipTimer(15);
    setShowSkipPopup(true);
  };

  const endTour = () => {
    setIsActive(false);
    localStorage.setItem("@VendorOS:tourSeen", "true");
  };

  return (
    <TourContext.Provider value={{ currentStepIndex, isActive, startTour, nextStep, skipTour }}>
      {children}
      {isActive && currentTourSteps[currentStepIndex] && pathname === currentTourSteps[currentStepIndex].page && (
        <TourOverlay 
          steps={currentTourSteps}
          step={currentTourSteps[currentStepIndex]} 
          onNext={nextStep} 
          onSkip={skipTour} 
          isLast={currentStepIndex === currentTourSteps.length - 1} 
        />
      )}
      {showSkipPopup && (
        <div className="fixed inset-0 z-[999999] flex items-center justify-center bg-black/60 backdrop-blur-sm animate-in fade-in">
          <div className="bg-white rounded-[20px] p-[32px] max-w-[420px] w-full text-center shadow-2xl relative animate-in zoom-in-95">
            <button onClick={() => setShowSkipPopup(false)} className="absolute top-4 right-4 text-[#9CA3AF] hover:text-[#111827] transition-colors">
              <X className="w-5 h-5" />
            </button>
            <div className="w-[80px] h-[80px] bg-[#F5F3FF] border border-[#E0E7FF] rounded-full flex items-center justify-center mx-auto mb-[24px]">
              <span className="text-[32px] font-[800] text-[#6D28D9] leading-none">{skipTimer}</span>
            </div>
            <h3 className="text-[20px] font-[800] text-[#111827] mb-[12px]">Tour Oculto!</h3>
            <p className="text-[14px] text-[#4B5563] mb-[24px] leading-relaxed">
              Você pode acessar o manual interativo a qualquer momento clicando no ícone de ajuda (<strong>?</strong>) no topo da tela, ou baixá-lo no menu do seu perfil.
            </p>
            <button 
              onClick={() => setShowSkipPopup(false)}
              disabled={skipTimer > 0}
              className={`w-full text-white rounded-[12px] py-[14px] font-[700] transition-colors shadow-md 
                ${skipTimer > 0 
                  ? "bg-[#D1D5DB] cursor-not-allowed opacity-70" 
                  : "bg-[#6D28D9] hover:bg-[#5B21B6] hover:shadow-lg"
                }`}
            >
              {skipTimer > 0 ? `Aguarde ${skipTimer}s` : "Entendi"}
            </button>
          </div>
        </div>
      )}
    </TourContext.Provider>
  );
}

const TourOverlay = ({ steps, step, onNext, onSkip, isLast }: { steps: TourStep[], step: TourStep, onNext: () => void, onSkip: () => void, isLast: boolean }) => {
  const [targetRect, setTargetRect] = useState<DOMRect | null>(null);

  useEffect(() => {
    const findTarget = () => {
      const el = document.querySelector(`[data-tour="${step.targetId}"]`);
      if (el) {
        setTargetRect(el.getBoundingClientRect());
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        setTargetRect(null);
      }
    };
    
    findTarget();
    const interval = setInterval(findTarget, 500);
    
    return () => clearInterval(interval);
  }, [step]);

  return (
    <div className="fixed inset-0 z-[999999] pointer-events-none">
      {/* Background Dim with hole cutout */}
      <div 
        className="absolute inset-0 bg-[#0F172A]/70 backdrop-blur-[2px] transition-all duration-300 pointer-events-auto" 
        style={{
          clipPath: targetRect 
            ? `polygon(0% 0%, 0% 100%, ${Math.max(0, targetRect.left - 12)}px 100%, ${Math.max(0, targetRect.left - 12)}px ${Math.max(0, targetRect.top - 12)}px, ${Math.min(window.innerWidth, targetRect.right + 12)}px ${Math.max(0, targetRect.top - 12)}px, ${Math.min(window.innerWidth, targetRect.right + 12)}px ${Math.min(window.innerHeight, targetRect.bottom + 12)}px, ${Math.max(0, targetRect.left - 12)}px ${Math.min(window.innerHeight, targetRect.bottom + 12)}px, ${Math.max(0, targetRect.left - 12)}px 100%, 100% 100%, 100% 0%)`
            : 'none'
        }}
      />
      
      {/* Popover */}
      {targetRect ? (
        <div 
          className="absolute bg-white rounded-[16px] shadow-[0_12px_40px_-10px_rgba(0,0,0,0.3)] p-[24px] pointer-events-auto transition-all duration-500 ease-out w-[320px] border border-[#E5E7EB]"
          style={{
            top: step.position === 'bottom' ? Math.min(window.innerHeight - 200, targetRect.bottom + 20) : 
                 step.position === 'top' ? Math.max(20, targetRect.top - 200) : 
                 step.position === 'center' ? '50%' : targetRect.top,
            left: step.position === 'right' ? Math.min(window.innerWidth - 340, targetRect.right + 20) : 
                  step.position === 'left' ? Math.max(20, targetRect.left - 340) : 
                  step.position === 'center' ? '50%' : Math.min(window.innerWidth - 340, Math.max(20, targetRect.left)),
            transform: step.position === 'center' ? 'translate(-50%, -50%)' : 'none'
          }}
        >
          <div className="absolute -top-3 -left-3 w-8 h-8 rounded-full bg-[#6D28D9] text-white flex items-center justify-center font-[800] text-[14px] shadow-lg border-2 border-white">
            {steps.findIndex(s => s.id === step.id) + 1}
          </div>
          <h4 className="text-[16px] font-[800] text-[#111827] mb-[8px] tracking-tight">{step.title}</h4>
          <p className="text-[14px] text-[#4B5563] mb-[24px] leading-relaxed">{step.description}</p>
          
          <div className="flex items-center justify-between mt-auto">
            <button onClick={onSkip} className="text-[13px] font-[600] text-[#9CA3AF] hover:text-[#4B5563] transition-colors">
              Pular tour
            </button>
            <button 
              onClick={onNext}
              className="px-[16px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-[13px] font-[700] rounded-[8px] transition-colors shadow-[0_4px_12px_rgba(109,40,217,0.3)]"
            >
              {isLast ? "Concluir 🎉" : "Próximo"}
            </button>
          </div>
        </div>
      ) : (
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-[16px] shadow-2xl p-[24px] pointer-events-auto w-[320px] animate-in zoom-in-95">
           <h4 className="text-[16px] font-[800] text-[#111827] mb-[8px] tracking-tight">{step.title}</h4>
           <p className="text-[14px] text-[#4B5563] mb-[24px] leading-relaxed">{step.description}</p>
           <div className="flex items-center justify-between mt-auto">
            <button onClick={onSkip} className="text-[13px] font-[600] text-[#9CA3AF] hover:text-[#4B5563] transition-colors">
              Pular tour
            </button>
            <button 
              onClick={onNext}
              className="px-[16px] py-[10px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-[13px] font-[700] rounded-[8px] transition-colors shadow-[0_4px_12px_rgba(109,40,217,0.3)]"
            >
              {isLast ? "Concluir 🎉" : "Próximo"}
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export const useTour = () => useContext(TourContext);
