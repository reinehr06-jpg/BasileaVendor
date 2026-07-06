"use client";

import React from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Search,
  CreditCard,
  DollarSign,
  MonitorSmartphone,
  MessageCircle,
  Mail,
  Bot,
  Code,
  MoreVertical,
  Activity,
  Clock,
  CheckCircle2,
  AlertCircle,
  Filter,
  BarChart2
} from "lucide-react";
import { toast } from "sonner";

export default function IntegracoesPage() {

  // Componente de Estatística no Topo
  const StatItem = ({ icon, count, label, colorClass }: any) => (
    <div className="flex items-center gap-[12px] px-[16px]">
      <div className={`w-[36px] h-[36px] rounded-[10px] flex items-center justify-center ${colorClass}`}>
        {icon}
      </div>
      <div className="flex flex-col">
        <span className="text-[16px] font-[800] text-[#111827] leading-tight">{count}</span>
        <span className="text-[12px] text-[#6B7280] leading-tight">{label}</span>
      </div>
    </div>
  );

  // Componente de Linha de Integração
  const IntegrationRow = ({ 
    icon, 
    title, 
    subtitle, 
    status, 
    statusColor,
    activityTitle,
    activitySubtitle,
    buttonText = "Configurar",
    href
  }: any) => {
    
    const isAvailable = status === "Disponível";
    const dotColor = statusColor === 'green' ? 'bg-[#10B981]' : statusColor === 'yellow' ? 'bg-[#F59E0B]' : 'bg-[#9CA3AF]';
    const textColor = statusColor === 'green' ? 'text-[#10B981]' : statusColor === 'yellow' ? 'text-[#F59E0B]' : 'text-[#6B7280]';

    const ActionButton = () => {
      const baseClass = "px-[16px] py-[8px] bg-white border border-[#E5E7EB] hover:bg-[#F3F4F6] transition-colors rounded-[8px] text-[13px] font-[600] text-[#374151] shadow-sm";
      if (href) {
        return (
          <Link href={href} className={baseClass}>
            {buttonText}
          </Link>
        );
      }
      return (
        <button 
          onClick={() => toast.info(`Configurar ${title}`)}
          className={baseClass}
        >
          {buttonText}
        </button>
      );
    };

    return (
      <div className="flex items-center justify-between p-[20px] bg-white border-b border-[#F3F4F6] last:border-b-0 hover:bg-[#F9FAFB] transition-colors group">
        
        {/* Coluna 1: Info (Ícone + Textos) */}
        <div className="flex items-center gap-[16px] w-[300px]">
          <div className="w-[44px] h-[44px] rounded-full bg-[#F9FAFB] border border-[#E5E7EB] flex items-center justify-center shrink-0 text-[#4B5563]">
            {icon}
          </div>
          <div className="flex flex-col">
            <span className="text-[14px] font-[700] text-[#111827] leading-tight mb-[4px]">{title}</span>
            <span className="text-[12px] text-[#6B7280] leading-tight">{subtitle}</span>
          </div>
        </div>

        {/* Coluna 2: Status */}
        <div className="flex items-center gap-[8px] w-[140px]">
          <div className={`w-[8px] h-[8px] rounded-full ${dotColor}`}></div>
          <span className={`text-[13px] font-[600] ${textColor}`}>{status}</span>
        </div>

        {/* Coluna 3: Atividade */}
        <div className="flex flex-col w-[200px]">
          <span className={`text-[13px] font-[500] leading-tight mb-[4px] ${isAvailable ? 'text-[#9CA3AF]' : 'text-[#4B5563]'}`}>
            {activityTitle}
          </span>
          <span className="text-[12px] text-[#9CA3AF] leading-tight">{activitySubtitle}</span>
        </div>

        {/* Coluna 4: Ações */}
        <div className="flex items-center gap-[12px] w-[140px] justify-end">
          <ActionButton />
          <button className="w-[32px] h-[32px] flex items-center justify-center text-[#9CA3AF] hover:text-[#111827] hover:bg-[#F3F4F6] rounded-[8px] transition-colors">
            <MoreVertical className="w-[18px] h-[18px]" />
          </button>
        </div>

      </div>
    );
  };

  return (
    <div className="flex h-screen w-screen overflow-hidden font-inter bg-[#F8F9FA]">
      <Sidebar />
      
      <div className="flex-1 ml-[240px] flex flex-col h-screen overflow-hidden">
        <Topbar />
        
        <main className="flex-1 flex flex-col w-full mx-auto overflow-y-auto custom-scrollbar">
          
          <div className="w-full max-w-[1100px] mx-auto p-[40px_32px_80px_32px] flex flex-col gap-[32px]">
            
            {/* 🏷️ HEADER E STATUS BAR */}
            <div className="flex items-start justify-between w-full">
              <div className="flex flex-col">
                <h1 className="text-[28px] font-[800] text-[#111827] leading-tight mb-[8px]">Integrações</h1>
                <p className="text-[14px] text-[#6B7280]">Conecte serviços e automatize seus processos.</p>
              </div>

              {/* Status Bar (Right) */}
              <div className="bg-white rounded-[16px] border border-[#E5E7EB] p-[8px] flex items-center shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <StatItem 
                  icon={<CheckCircle2 className="w-[18px] h-[18px] text-[#10B981]" strokeWidth={2.5} />} 
                  count="5" 
                  label="Conectadas" 
                  colorClass="bg-[#ECFDF5]" 
                />
                <div className="w-[1px] h-[32px] bg-[#F3F4F6]"></div>
                <StatItem 
                  icon={<AlertCircle className="w-[18px] h-[18px] text-[#F59E0B]" strokeWidth={2.5} />} 
                  count="2" 
                  label="Pendentes" 
                  colorClass="bg-[#FEF3C7]" 
                />
                <div className="w-[1px] h-[32px] bg-[#F3F4F6]"></div>
                <StatItem 
                  icon={<Clock className="w-[18px] h-[18px] text-[#6B7280]" strokeWidth={2.5} />} 
                  count="3" 
                  label="Disponíveis" 
                  colorClass="bg-[#F3F4F6]" 
                />
                <div className="w-[1px] h-[32px] bg-[#F3F4F6]"></div>
                <StatItem 
                  icon={<BarChart2 className="w-[18px] h-[18px] text-[#8B5CF6]" strokeWidth={2.5} />} 
                  count="10" 
                  label="Total" 
                  colorClass="bg-[#F5F3FF]" 
                />
              </div>
            </div>

            {/* 🔍 BARRA DE PESQUISA E FILTRO */}
            <div className="flex items-center justify-between w-full">
              <div className="relative w-[340px]">
                <Search className="w-[18px] h-[18px] text-[#9CA3AF] absolute left-[16px] top-1/2 -translate-y-1/2" />
                <input 
                  type="text" 
                  placeholder="Buscar integrações..." 
                  className="w-full h-[44px] bg-white border border-[#E5E7EB] rounded-[10px] pl-[44px] pr-[44px] text-[14px] text-[#111827] outline-none focus:border-[#8B5CF6] focus:ring-1 focus:ring-[#8B5CF6] transition-all shadow-[0_2px_8px_rgba(0,0,0,0.02)] placeholder-[#9CA3AF]"
                />
                <div className="absolute right-[12px] top-1/2 -translate-y-1/2 px-[6px] py-[2px] bg-[#F3F4F6] border border-[#E5E7EB] rounded-[4px] text-[10px] font-[600] text-[#6B7280]">
                  ⌘ K
                </div>
              </div>

              <button className="flex items-center gap-[8px] h-[44px] px-[16px] bg-white border border-[#E5E7EB] rounded-[10px] text-[14px] font-[600] text-[#4B5563] hover:bg-[#F9FAFB] transition-colors shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                Todas as categorias <Filter className="w-[16px] h-[16px] text-[#9CA3AF]" />
              </button>
            </div>

            {/* 📦 LISTA DE INTEGRAÇÕES POR CATEGORIA */}
            <div className="flex flex-col gap-[40px]">
              
              {/* CATEGORIA 1: Core Financeiro */}
              <div className="flex flex-col gap-[16px]">
                <div className="flex flex-col px-[8px]">
                  <h2 className="text-[16px] font-[800] text-[#111827] mb-[4px]">Core Financeiro</h2>
                  <p className="text-[13px] text-[#6B7280]">Integrações essenciais para gestão financeira.</p>
                </div>
                
                <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden">
                  <IntegrationRow 
                    icon={<CreditCard strokeWidth={2} />}
                    title="Asaas Gateway"
                    subtitle="API, Webhook e Ambiente"
                    status="Conectado"
                    statusColor="green"
                    activityTitle="Sincronização ativa"
                    activitySubtitle="Última atividade há 2 min"
                    buttonText="Configurar"
                    href="/configuracoes/integracoes/asaas"
                  />
                  <IntegrationRow 
                    icon={<DollarSign strokeWidth={2} />}
                    title="Split & Repasse"
                    subtitle="Comissões automáticas"
                    status="Ativo"
                    statusColor="green"
                    activityTitle="Tudo funcionando"
                    activitySubtitle="Última atividade há 15 min"
                    buttonText="Configurar"
                    href="/configuracoes/integracoes/split"
                  />
                  <IntegrationRow 
                    icon={<MonitorSmartphone strokeWidth={2} />}
                    title="Checkout Externo"
                    subtitle="URL de pagamento"
                    status="Configurado"
                    statusColor="green"
                    activityTitle="Recebendo pagamentos"
                    activitySubtitle="Última atividade há 1 hora"
                    buttonText="Configurar"
                    href="/configuracoes/integracoes/checkout"
                  />
                </div>
              </div>

              {/* CATEGORIA 2: Leads & Comunicação */}
              <div className="flex flex-col gap-[16px]">
                <div className="flex flex-col px-[8px]">
                  <h2 className="text-[16px] font-[800] text-[#111827] mb-[4px]">Leads & Comunicação</h2>
                  <p className="text-[13px] text-[#6B7280]">Integrações para captura e comunicação com leads.</p>
                </div>
                
                <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden">
                  <IntegrationRow 
                    icon={<MessageCircle strokeWidth={2} />}
                    title="Google & Meta"
                    subtitle="Chat e captura de leads"
                    status="Conectado"
                    statusColor="green"
                    activityTitle="Recebendo mensagens"
                    activitySubtitle="Última atividade há 2 min"
                    buttonText="Configurar"
                    href="/configuracoes/integracoes/leads"
                  />
                  <IntegrationRow 
                    icon={<Mail strokeWidth={2} />}
                    title="Email Remetente"
                    subtitle="Envio de comunicados"
                    status="Pendente"
                    statusColor="gray"
                    activityTitle="Aguardando configuração"
                    activitySubtitle="Nenhum email enviado"
                    buttonText="Configurar"
                    href="/configuracoes/integracoes/email"
                  />
                  <IntegrationRow 
                    icon={<MessageCircle strokeWidth={2} />}
                    title="Chat E Leads"
                    subtitle="Webhooks e automações"
                    status="Disponível"
                    statusColor="gray"
                    activityTitle="Não configurado"
                    activitySubtitle="Nenhuma atividade"
                    buttonText="Configurar"
                  />
                </div>
              </div>

              {/* CATEGORIA 3: Automação & IA */}
              <div className="flex flex-col gap-[16px]">
                <div className="flex flex-col px-[8px]">
                  <h2 className="text-[16px] font-[800] text-[#111827] mb-[4px]">Automação & IA</h2>
                  <p className="text-[13px] text-[#6B7280]">Ferramentas para automação e inteligência artificial.</p>
                </div>
                
                <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden">
                  <IntegrationRow 
                    icon={<Bot strokeWidth={2} />}
                    title="Inteligência Artificial"
                    subtitle="IA, Machine Learning e Automação"
                    status="Ativo"
                    statusColor="green"
                    activityTitle="Processos ativos"
                    activitySubtitle="Última atividade há 10 min"
                    buttonText="Configurar"
                    href="/configuracoes/integracoes/ia"
                  />
                  <IntegrationRow 
                    icon={<Code strokeWidth={2} />}
                    title="Webhooks"
                    subtitle="Endpoints e integrações"
                    status="Disponível"
                    statusColor="gray"
                    activityTitle="Não configurado"
                    activitySubtitle="Nenhuma atividade"
                    buttonText="Configurar"
                    href="/configuracoes/integracoes/webhooks"
                  />
                </div>
              </div>

            </div>
          </div>

        </main>
      </div>
    </div>
  );
}
