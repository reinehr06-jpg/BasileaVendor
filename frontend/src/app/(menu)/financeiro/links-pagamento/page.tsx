"use client";

import React from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { 
  Link2,
  ShoppingCart,
  UsersRound,
  TrendingUp,
  Tag,
  DollarSign,
  Info,
  Zap,
  Link2Off
} from "lucide-react";
import { useTranslation } from "react-i18next";
import Link from "next/link";

export default function LinksPagamentoPage() {
  const { t } = useTranslation();

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col gap-[24px] max-w-[1200px] mx-auto">
            
            {/* KPI CARDS */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[20px]">
              
              {/* Card 1 */}
              <div className="bg-white rounded-[12px] p-[24px] flex flex-col border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F3E8FF] flex items-center justify-center mb-[16px]">
                  <Link2 className="w-[20px] h-[20px] text-[#8B5CF6]" strokeWidth={2.5} />
                </div>
                <h3 className="text-[28px] font-[700] text-[#111827] leading-none mb-[8px]">0</h3>
                <span className="text-[11px] font-[700] text-[#9CA3AF] tracking-wider uppercase">LINKS ATIVOS</span>
              </div>

              {/* Card 2 */}
              <div className="bg-white rounded-[12px] p-[24px] flex flex-col border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <div className="w-[40px] h-[40px] rounded-[10px] bg-[#DCFCE7] flex items-center justify-center mb-[16px]">
                  <ShoppingCart className="w-[20px] h-[20px] text-[#22C55E]" strokeWidth={2.5} />
                </div>
                <h3 className="text-[28px] font-[700] text-[#111827] leading-none mb-[8px]">0</h3>
                <span className="text-[11px] font-[700] text-[#9CA3AF] tracking-wider uppercase">VENDAS TOTAIS</span>
              </div>

              {/* Card 3 */}
              <div className="bg-white rounded-[12px] p-[24px] flex flex-col border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <div className="w-[40px] h-[40px] rounded-[10px] bg-[#FFEDD5] flex items-center justify-center mb-[16px]">
                  <UsersRound className="w-[20px] h-[20px] text-[#F97316]" strokeWidth={2.5} />
                </div>
                <h3 className="text-[28px] font-[700] text-[#111827] leading-none mb-[8px]">0%</h3>
                <span className="text-[11px] font-[700] text-[#9CA3AF] tracking-wider uppercase">OCUPAÇÃO MÉDIA</span>
              </div>

              {/* Card 4 */}
              <div className="bg-white rounded-[12px] p-[24px] flex flex-col border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <div className="w-[40px] h-[40px] rounded-[10px] bg-[#E0F2FE] flex items-center justify-center mb-[16px]">
                  <TrendingUp className="w-[20px] h-[20px] text-[#0284C7]" strokeWidth={2.5} />
                </div>
                <h3 className="text-[28px] font-[700] text-[#111827] leading-none mb-[8px]">R$ 0</h3>
                <span className="text-[11px] font-[700] text-[#9CA3AF] tracking-wider uppercase">RECEITA GERADA</span>
              </div>

            </div>

            {/* MONITORAMENTO DE LINKS (TABLE) */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col min-h-[400px]">
              
              {/* Table Header / Title */}
              <div className="p-[24px] border-b border-[#E5E7EB]">
                <h2 className="text-[18px] font-[700] text-[#111827]">Monitoramento de Links</h2>
              </div>

              {/* Table Columns Header */}
              <div className="grid grid-cols-[2fr_1fr_1.5fr_1fr_1fr_1fr] bg-[#F9FAFB] border-b border-[#E5E7EB] px-[24px] py-[12px]">
                <div className="flex items-center gap-[6px]">
                  <Tag className="w-[14px] h-[14px] text-[#6B7280]" />
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">PRODUTO / EVENTO</span>
                </div>
                <div className="flex items-center gap-[6px]">
                  <DollarSign className="w-[14px] h-[14px] text-[#6B7280]" />
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">VALOR</span>
                </div>
                <div className="flex items-center gap-[6px]">
                  <UsersRound className="w-[14px] h-[14px] text-[#6B7280]" />
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">PERFORMANCE / VAGAS</span>
                </div>
                <div className="flex items-center gap-[6px]">
                  <Info className="w-[14px] h-[14px] text-[#6B7280]" />
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">STATUS</span>
                </div>
                <div className="flex items-center gap-[6px]">
                  <Link2 className="w-[14px] h-[14px] text-[#6B7280]" />
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">CHECKOUT</span>
                </div>
                <div className="flex items-center gap-[6px]">
                  <Zap className="w-[14px] h-[14px] text-[#6B7280]" />
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">AÇÕES</span>
                </div>
              </div>

              {/* Empty State Body */}
              <div className="flex-1 flex flex-col items-center justify-center p-[40px]">
                <Link2Off className="w-[64px] h-[64px] text-[#D1D5DB] mb-[16px]" strokeWidth={1.5} />
                <p className="text-[15px] font-[500] text-[#6B7280] mb-[24px]">Nenhum link ativo encontrado.</p>
                
                <Link 
                  href="/financeiro/links-pagamento/novo"
                  className="h-[40px] px-[20px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white transition-all rounded-[8px] flex items-center justify-center text-[14px] font-[600] shadow-sm whitespace-nowrap"
                >
                  Criar meu primeiro link
                </Link>
              </div>

            </div>
          </div>

        </main>
      </div>
    </div>
  );
}
