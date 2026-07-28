"use client";

import { useState, useEffect } from "react";
import { Joyride, STATUS } from "react-joyride";
import type { CallBackProps, Step } from "react-joyride";

export default function Tour() {
  const [run, setRun] = useState(false);

  const steps: Step[] = [
    {
      target: ".tour-dashboard-cards",
      content: "Bem-vindo ao Dashboard! Aqui você terá um resumo financeiro e métricas de desempenho em tempo real.",
      disableBeacon: true,
    },
    {
      target: ".tour-menu-clientes",
      content: "Nesta aba, você pode cadastrar e gerenciar toda a sua carteira de clientes.",
    },
    {
      target: ".tour-menu-vendas",
      content: "Acompanhe todas as assinaturas e faturamentos gerados aqui.",
    },
    {
      target: ".tour-user-profile",
      content: "Acesse as configurações da sua conta ou faça logout por este menu.",
    }
  ];

  useEffect(() => {
    // Só roda o Tour depois que o Onboarding for concluído (ou se já tiver sido visto)
    const hasSeenOnboarding = localStorage.getItem("basileia_onboarding_seen");
    const hasSeenTour = localStorage.getItem("basileia_tour_seen");

    if (hasSeenOnboarding && !hasSeenTour) {
      setRun(true);
    }

    // Escuta o evento do componente Onboarding
    const handleOnboardingComplete = () => {
      if (!hasSeenTour) {
        // Um pequeno delay para dar tempo do Modal fechar suavemente
        setTimeout(() => setRun(true), 500);
      }
    };

    window.addEventListener("onboardingComplete", handleOnboardingComplete);
    return () => window.removeEventListener("onboardingComplete", handleOnboardingComplete);
  }, []);

  const handleJoyrideCallback = (data: CallBackProps) => {
    const { status } = data;
    const finishedStatuses: string[] = [STATUS.FINISHED, STATUS.SKIPPED];

    if (finishedStatuses.includes(status)) {
      setRun(false);
      localStorage.setItem("basileia_tour_seen", "true");
    }
  };

  return (
    <Joyride
      callback={handleJoyrideCallback}
      continuous
      hideCloseButton
      run={run}
      scrollToFirstStep
      showProgress
      showSkipButton
      steps={steps}
      styles={{
        options: {
          zIndex: 10000,
          primaryColor: '#7c3aed', // purple-600
          textColor: '#1f2937', // gray-800
          backgroundColor: '#ffffff',
          arrowColor: '#ffffff',
        },
        buttonNext: {
          backgroundColor: '#7c3aed',
          borderRadius: '8px',
          padding: '8px 16px',
        },
        buttonBack: {
          color: '#6b7280',
          marginRight: '8px',
        },
        buttonSkip: {
          color: '#6b7280',
        },
        tooltip: {
          borderRadius: '12px',
          padding: '20px',
        }
      }}
      locale={{
        back: 'Voltar',
        close: 'Fechar',
        last: 'Finalizar',
        next: 'Próximo',
        skip: 'Pular Tour',
      }}
    />
  );
}
