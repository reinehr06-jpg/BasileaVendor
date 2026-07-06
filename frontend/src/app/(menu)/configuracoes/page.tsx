"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import {
  Search,
  User,
  Shield,
  Wallet,
  Link as LinkIcon,
  Coins,
  CreditCard,
  Cloud,
  Store
} from "lucide-react";

export default function ConfiguracoesPage() {
  const { t } = useTranslation();
  const [busca, setBusca] = useState("");

  const modulos = [
    {
      id: "conta",
      title: "Perfil & Conta",
      description: "Alterar seu nome, e-mail e dados de acesso.",
      icon: <User className="w-[18px] h-[18px] text-[#2563EB]" />,
      iconBg: "bg-[#EFF6FF]",
      highlight: false
    },
    {
      id: "seguranca",
      title: "Segurança",
      description: "Gerenciar sua senha e proteção de conta.",
      icon: <Shield className="w-[18px] h-[18px] text-[#DC2626]" />,
      iconBg: "bg-[#FEF2F2]",
      highlight: false
    },
    {
      id: "integracoes",
      title: "Integrações",
      description: "Asaas, Email, Checkout e outras configurações.",
      icon: <Wallet className="w-[18px] h-[18px] text-[#059669]" />,
      iconBg: "bg-[#ECFDF5]",
      highlight: false
    },
    {
      id: "clientes-asaas",
      title: "Clientes Asaas",
      description: "Importe vendas e identifique o primeiro mês.",
      icon: <Cloud className="w-[18px] h-[18px] text-[#EA580C]" />,
      iconBg: "bg-[#FFF7ED]",
      highlight: true,
      badge: "MARÇO"
    },
    {
      id: "monitor",
      title: "Monitor de Vendas",
      description: "Logs de Webhook e status geral de cobranças.",
      icon: <Store className="w-[18px] h-[18px] text-[#0369A1]" />,
      iconBg: "bg-[#F0F9FF]",
      highlight: false
    }
  ];

  const filteredModulos = modulos.filter(m => 
    m.title.toLowerCase().includes(busca.toLowerCase()) || 
    m.description.toLowerCase().includes(busca.toLowerCase())
  );

  return (
    <div className="flex h-screen font-inter bg-[#F5F5F7] overflow-hidden">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col h-screen transition-all duration-300">
        
        {/* TOPBAR */}
        <Topbar />

        {/* CONTENT */}
        <main className="p-[30px_28px_20px_28px] flex-1 flex flex-col items-center overflow-y-auto">
          
          <div className="w-full max-w-[1100px] flex flex-col items-center flex-1">
            
            {/* Título Centralizado */}
            <h1 className="text-[28px] font-[700] text-[#1A1A2E] mb-[20px]">
              {t("Configurações")}
            </h1>

            {/* Barra de Busca */}
            <div className="w-full max-w-[480px] mb-[30px]">
              <div className="relative flex items-center w-full h-[44px] bg-white border border-[#E5E7EB] rounded-[10px] px-[16px] shadow-sm transition-all focus-within:border-[#7C3AED] focus-within:ring-1 focus-within:ring-[#7C3AED] focus-within:shadow-md hover:border-[#D1D5DB]">
                <Search className="text-[#9CA3AF] w-[20px] h-[20px] mr-[10px] shrink-0" strokeWidth={2.2} />
                <input
                  type="text"
                  value={busca}
                  onChange={(e) => setBusca(e.target.value)}
                  placeholder={t("Localizar uma configuração...")}
                  className="bg-transparent border-none outline-none text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] w-full h-full"
                />
              </div>
            </div>

            <div className="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-[16px]">
              {filteredModulos.map((modulo) => (
                <Link
                  key={modulo.id}
                  href={`/configuracoes/${modulo.id}`}
                  className={`
                    relative group flex flex-col items-start p-[20px] rounded-[16px] bg-white text-left transition-all duration-200
                    ${modulo.highlight 
                      ? "border-[2px] border-[#7C3AED] shadow-[0_4px_12px_rgba(124,58,237,0.12)]" 
                      : "border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:border-[#D1D5DB] hover:shadow-[0_4px_12px_rgba(0,0,0,0.05)]"
                    }
                  `}
                >
                  {/* Ícone */}
                  <div className={`w-[36px] h-[36px] rounded-[10px] ${modulo.iconBg} flex items-center justify-center mb-[14px]`}>
                    {modulo.icon}
                  </div>

                  {/* Título e Badge */}
                  <div className="flex items-center gap-[8px] mb-[6px] w-full">
                    <h3 className="text-[15px] font-[700] text-[#111827] group-hover:text-[#7C3AED] transition-colors truncate">
                      {t(modulo.title)}
                    </h3>
                    {modulo.badge && (
                      <span className="inline-flex items-center px-[6px] py-[2px] text-[9px] font-[800] rounded-[4px] bg-[#EA580C] text-white uppercase tracking-wider leading-none shrink-0">
                        {modulo.badge}
                      </span>
                    )}
                  </div>

                  {/* Descrição */}
                  <p className="text-[13px] text-[#6B7280] leading-relaxed w-full">
                    {t(modulo.description)}
                  </p>
                </Link>
              ))}

              {filteredModulos.length === 0 && (
                <div className="col-span-full flex flex-col items-center justify-center p-[40px]">
                  <p className="text-[#6B7280] text-[13px]">{t("Nenhuma configuração encontrada para sua busca.")}</p>
                </div>
              )}
            </div>

          </div>
        </main>
      </div>
    </div>
  );
}
