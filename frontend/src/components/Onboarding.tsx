"use client";

import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";

const onboardingSteps = [
  {
    title: "Bem-vindo ao Basiléia Vendor OS! 🎉",
    description: "Sua plataforma definitiva para gestão de vendas, clientes e assinaturas. Nós construímos isso para escalar o seu negócio.",
    image: "📊"
  },
  {
    title: "Gestão Centralizada",
    description: "Acompanhe todos os seus Vendedores, Comissões e Faturamentos em um único painel. Sem planilhas, sem confusão.",
    image: "👥"
  },
  {
    title: "Pronto para decolar?",
    description: "Siga o tour guiado que aparecerá a seguir para entender os menus principais e começar a operar o sistema.",
    image: "🚀"
  }
];

export default function Onboarding() {
  const [isOpen, setIsOpen] = useState(false);
  const [currentStep, setCurrentStep] = useState(0);

  useEffect(() => {
    // Checa se o usuário já viu o onboarding
    const hasSeenOnboarding = localStorage.getItem("basileia_onboarding_seen");
    if (!hasSeenOnboarding) {
      setIsOpen(true);
    }
  }, []);

  const handleNext = () => {
    if (currentStep < onboardingSteps.length - 1) {
      setCurrentStep(currentStep + 1);
    } else {
      handleClose();
    }
  };

  const handleClose = () => {
    setIsOpen(false);
    localStorage.setItem("basileia_onboarding_seen", "true");
    
    // Dispara um evento para o Tour iniciar (caso o Tour esteja aguardando o Onboarding terminar)
    window.dispatchEvent(new Event("onboardingComplete"));
  };

  if (!isOpen) return null;

  return (
    <AnimatePresence>
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <motion.div 
          initial={{ opacity: 0, scale: 0.9, y: 20 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          exit={{ opacity: 0, scale: 0.9, y: 20 }}
          className="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 relative overflow-hidden"
        >
          {/* Progress dots */}
          <div className="flex justify-center space-x-2 mb-6">
            {onboardingSteps.map((_, idx) => (
              <div 
                key={idx} 
                className={`h-2 rounded-full transition-all duration-300 ${idx === currentStep ? "w-6 bg-purple-600" : "w-2 bg-gray-200 dark:bg-gray-700"}`}
              />
            ))}
          </div>

          <div className="text-center min-h-[220px] flex flex-col justify-center">
            <motion.div
              key={currentStep}
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
              transition={{ duration: 0.3 }}
            >
              <div className="text-6xl mb-4">{onboardingSteps[currentStep].image}</div>
              <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                {onboardingSteps[currentStep].title}
              </h2>
              <p className="text-gray-600 dark:text-gray-400">
                {onboardingSteps[currentStep].description}
              </p>
            </motion.div>
          </div>

          <div className="mt-8 flex justify-between items-center">
            <button 
              onClick={handleClose}
              className="text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
            >
              Pular
            </button>
            <button 
              onClick={handleNext}
              className="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors shadow-md shadow-purple-600/20"
            >
              {currentStep === onboardingSteps.length - 1 ? "Começar" : "Próximo"}
            </button>
          </div>
        </motion.div>
      </div>
    </AnimatePresence>
  );
}
