"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import {
  Contact,
  MapPin,
  Briefcase,
  Phone,
  Users,
  Calendar,
  User,
  ExternalLink,
  ShieldOff,
  ShoppingBag,
  CreditCard,
  Search,
  ChevronDown
} from "lucide-react";
import ModalDesativar from "@/components/ModalDesativar";

export default function ClienteProfilePage() {
  const { t } = useTranslation();
  const [activeTab, setActiveTab] = useState("Vendas");
  const [modalDesativarOpen, setModalDesativarOpen] = useState(false);

  const tabs = ["Histórico de Vendas", "Faturas e Pagamentos"];

  const handleDesativar = (motivo: string) => {
    alert(`Cliente desativado com sucesso!\nMotivo registrado no histórico: ${motivo}`);
    setModalDesativarOpen(false);
  };

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1200px] mx-auto">
            
            {/* Breadcrumb */}
            <div className="flex items-center text-[13px] text-[#6B7280] mb-[20px]">
              <Link href="/gestao-comercial/clientes" className="hover:text-[#6D28D9] transition-colors">{t("Clientes")}</Link>
              <span className="mx-[8px]">/</span>
              <span className="text-[#1A1A2E] font-[600]">Comunidade Evangelica Atos 1</span>
            </div>

            {/* HEADER DA PÁGINA */}
            <div className="flex items-center justify-between mb-[24px]">
              <div className="flex items-center gap-[16px]">
                <div className="w-[48px] h-[48px] rounded-[12px] bg-[#F4EEFF] flex items-center justify-center shrink-0 border border-[#E9D5FF] shadow-sm">
                  <Contact className="w-[24px] h-[24px] text-[#7C3AED]" strokeWidth={2.2} />
                </div>
                <div className="flex flex-col justify-center">
                  <h1 className="text-[24px] font-[800] text-[#1A1A2E] leading-tight tracking-tight">{t("Histórico do cliente")}</h1>
                  <p className="text-[14px] text-[#6B7280] mt-1">{t("Acompanhe a evolução, histórico de vendas e dados deste cliente.")}</p>
                </div>
              </div>
            </div>

            {/* PROFILE CARD HORIZONTAL (Padrão Novo) */}
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] p-[24px] mb-[32px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col lg:flex-row items-center justify-between gap-[24px]">
              
              {/* Info Left */}
              <div className="flex items-center gap-[20px]">
                <div className="w-[80px] h-[80px] rounded-[16px] bg-[#F4EEFF] flex items-center justify-center border-4 border-[#F9FAFB] shadow-sm text-[28px] font-[800] text-[#6D28D9]">
                  C
                </div>
                <div className="flex flex-col">
                  <div className="flex items-center gap-[12px] mb-[8px]">
                    <h2 className="text-[20px] font-[800] text-[#1A1A2E]">Comunidade Evangelica Atos 1</h2>
                    <span className="flex items-center gap-[4px] px-[8px] py-[2px] bg-[#ECFDF5] text-[#059669] text-[10px] font-[800] uppercase tracking-wide rounded-[4px]">
                      <div className="w-[6px] h-[6px] rounded-full bg-[#059669]"></div>
                      {t("Ativo")}
                    </span>
                  </div>
                  
                  <div className="flex flex-wrap items-center gap-x-[32px] gap-y-[8px]">
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <MapPin className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      Localidade não informada
                    </div>
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <Briefcase className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      CNPJ: 33.878.959/0001-83
                    </div>
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <Phone className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      (35) 99773-4256
                    </div>
                  </div>
                </div>
              </div>

              {/* Metrics Right (Do Print) */}
              <div className="flex items-center gap-[16px] shrink-0">
                <div className="bg-[#F4EEFF] border border-[#E9D5FF] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#6D28D9] uppercase tracking-wider mb-[4px]">{t("Total de Vendas")}</p>
                  <p className="text-[18px] font-[800] text-[#5B21B6]">1</p>
                </div>
                <div className="bg-[#F0FDF4] border border-[#DCFCE7] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#059669] uppercase tracking-wider mb-[4px]">{t("Valor Total Pago")}</p>
                  <p className="text-[18px] font-[800] text-[#059669]">R$ 197,00</p>
                </div>
                <div className="bg-[#FFFBEB] border border-[#FEF3C7] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#D97706] uppercase tracking-wider mb-[4px]">{t("Ticket Médio")}</p>
                  <p className="text-[18px] font-[800] text-[#D97706]">R$ 197,00</p>
                </div>
              </div>

            </div>

            {/* FILTERS & CONTENT AREA */}
            <div className="flex flex-col lg:flex-row gap-[32px] items-start">
              
              {/* Left Column: Timeline / Tables */}
              <div className="flex-1 w-full flex flex-col">
                
                {/* Tabs */}
                <div className="flex items-center gap-[12px] mb-[24px] overflow-x-auto pb-2 scrollbar-hide">
                  {tabs.map((tab, idx) => (
                    <button
                      key={tab}
                      onClick={() => setActiveTab(idx === 0 ? "Vendas" : "Faturas")}
                      className={`h-[36px] px-[16px] rounded-full text-[13px] font-[600] whitespace-nowrap transition-colors border ${
                        (activeTab === "Vendas" && idx === 0) || (activeTab === "Faturas" && idx === 1)
                          ? 'bg-[#6D28D9] text-white border-[#6D28D9]' 
                          : 'bg-white text-[#4B5563] border-[#E5E7EB] hover:bg-[#F9FAFB]'
                      }`}
                    >
                      {tab}
                    </button>
                  ))}
                </div>

                {/* Content Area (Table instead of Timeline for Vendas) */}
                {activeTab === "Vendas" && (
                  <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden shadow-sm">
                    <div className="grid grid-cols-[100px_1fr_1.5fr_1.5fr_120px_120px_100px] items-center px-[20px] h-[48px] bg-[#FCFCFD] border-b border-[#F1F1F4]">
                      <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("ID Venda")}</span>
                      <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Data")}</span>
                      <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Plano")}</span>
                      <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Vendedor")}</span>
                      <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Recorrência")}</span>
                      <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider">{t("Valor")}</span>
                      <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-wider text-right">{t("Status")}</span>
                    </div>
                    <div className="grid grid-cols-[100px_1fr_1.5fr_1.5fr_120px_120px_100px] items-center px-[20px] h-[64px] bg-white border-b border-[#F1F1F4] hover:bg-[#F9FAFB] transition-colors">
                      <span className="text-[13px] font-[700] text-[#1A1A2E]">#00016</span>
                      <span className="text-[12px] text-[#6B7280]">Data não<br/>informada</span>
                      <span className="text-[13px] font-[600] text-[#1A1A2E]">Personalizado</span>
                      <span className="text-[13px] text-[#4B5563]">Vendedor<br/>de Testes</span>
                      <span className="text-[13px] text-[#4B5563]">Mensal</span>
                      <span className="text-[13px] font-[700] text-[#1A1A2E]">R$ 197,00</span>
                      <div className="text-right">
                        <span className="inline-flex items-center px-[8px] py-[2px] bg-[#ECFDF5] text-[#059669] text-[11px] font-[700] rounded-[6px]">
                          Pago
                        </span>
                      </div>
                    </div>
                  </div>
                )}

                {activeTab === "Faturas" && (
                  <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[40px] text-center shadow-sm">
                    <p className="text-[14px] text-[#6B7280]">{t("Nenhuma fatura encontrada.")}</p>
                  </div>
                )}

              </div>

              {/* Right Column: Resumo do Histórico */}
              <div className="w-full lg:w-[320px] shrink-0 flex flex-col gap-[16px]">
                
                {/* Outras Infos Card */}
                <div className="bg-white border border-[#E5E7EB] rounded-[16px] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                  <h3 className="text-[16px] font-[800] text-[#1A1A2E] mb-[20px]">{t("Detalhes Cadastrais")}</h3>
                  
                  <div className="flex flex-col gap-[16px]">
                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <User className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Responsável")}
                      </div>
                      <span className="text-[13px] font-[600] text-[#1A1A2E]">Não informado</span>
                    </div>

                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <Users className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Membros")}
                      </div>
                      <span className="text-[13px] font-[600] text-[#1A1A2E]">0</span>
                    </div>

                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <Calendar className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Cadastrado em")}
                      </div>
                      <span className="text-[13px] font-[600] text-[#1A1A2E]">02/07/2026</span>
                    </div>

                    <div className="flex flex-col gap-[4px] pt-[4px]">
                      <span className="text-[10px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Integração Asaas")}</span>
                      <span className="text-[13px] font-[600] text-[#1A1A2E] bg-[#F9FAFB] p-[8px] rounded-[6px] border border-[#F1F1F4]">cus_000169148649</span>
                    </div>
                  </div>
                </div>
                
                <button 
                  onClick={() => setModalDesativarOpen(true)}
                  className="w-full h-[48px] bg-white border border-[#EF4444] text-[#EF4444] font-[700] text-[14px] rounded-[10px] hover:bg-[#FEF2F2] transition-colors flex items-center justify-center gap-[8px]"
                >
                  <ShieldOff className="w-[18px] h-[18px]" strokeWidth={2.5} />
                  {t("Desativar cliente")}
                </button>

              </div>

            </div>

          </div>
        </main>
      </div>

      <ModalDesativar
        isOpen={modalDesativarOpen}
        itemName="Comunidade Evangelica Atos 1"
        title="Desativar Cliente"
        description="Ao desativar este cliente, o acesso dele à área logada será suspenso. O histórico financeiro e de atendimentos será mantido intacto no sistema."
        onClose={() => setModalDesativarOpen(false)}
        onConfirm={handleDesativar}
      />
    </div>
  );
}
