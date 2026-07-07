"use client";

import React, { useState } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import { useTranslation } from "react-i18next";
import {
  FileText,
  Pencil,
  CheckCircle2,
  AlertTriangle,
  MessageCircle,
  UserX,
  Search,
  ChevronDown,
  Phone,
  Briefcase,
  Users,
  Calendar,
  ExternalLink
} from "lucide-react";
import { VendedoresService, Vendedor } from "@/services/vendedores.service";
import ModalDesativar from "@/components/ModalDesativar";
export default function VendedorProfilePage({ params }: { params: Promise<{ id: string }> }) {
  const resolvedParams = React.use(params);
  const id = Number(resolvedParams.id);

  const { t } = useTranslation();
  const [activeTab, setActiveTab] = useState("Todos");
  const [modalDesativarOpen, setModalDesativarOpen] = useState(false);
  
  const [vendedor, setVendedor] = useState<Vendedor | null>(null);
  const [loading, setLoading] = useState(true);

  React.useEffect(() => {
    VendedoresService.obter(id).then(res => {
      setVendedor(res);
      setLoading(false);
    });
  }, [id]);

  const tabs = ["Todos", "Alterações cadastrais", "Vendas", "Comissões", "Ocorrências", "Contratos"];

  const handleDesativar = (motivo: string) => {
    alert(`Vendedor desativado com sucesso!\nMotivo registrado no histórico: ${motivo}`);
    setModalDesativarOpen(false);
  };

  if (loading) {
    return <div className="p-8 text-center">Carregando perfil...</div>;
  }

  if (!vendedor) {
    return <div className="p-8 text-center text-red-500">Vendedor não encontrado.</div>;
  }

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1200px] mx-auto">
            
            {/* Breadcrumb */}
            <div className="flex items-center text-[13px] text-[#6B7280] mb-[20px]">
              <Link href="/gestao-comercial/vendedores" className="hover:text-[#6D28D9] transition-colors">{t("Vendedores")}</Link>
              <span className="mx-[8px]">/</span>
              <span className="text-[#1A1A2E] font-[600]">{vendedor.nome}</span>
            </div>

            {/* HEADER DA PÁGINA */}
            <div className="flex items-center justify-between mb-[24px]">
              <div className="flex items-center gap-[16px]">
                <div className="w-[48px] h-[48px] rounded-[12px] bg-[#F4EEFF] flex items-center justify-center shrink-0 border border-[#E9D5FF] shadow-sm">
                  <FileText className="w-[24px] h-[24px] text-[#7C3AED]" strokeWidth={2.2} />
                </div>
                <div className="flex flex-col justify-center">
                  <h1 className="text-[24px] font-[800] text-[#1A1A2E] leading-tight tracking-tight">{t("Histórico do vendedor")}</h1>
                  <p className="text-[14px] text-[#6B7280] mt-1">{t("Acompanhe todas as alterações, interações, movimentações e registros relacionados a este vendedor.")}</p>
                </div>
              </div>
              <div className="flex items-center gap-[12px]">
                <Link 
                  href={`/gestao-comercial/vendedores/${id}/editar`}
                  className="h-[40px] px-[20px] bg-[#6D28D9] text-white font-[600] text-[13px] rounded-[8px] hover:bg-[#5B21B6] transition-colors shadow-sm flex items-center gap-[8px]"
                >
                  <Pencil className="w-[16px] h-[16px]" strokeWidth={2.5} />
                  {t("Editar vendedor")}
                </Link>
              </div>
            </div>

            {/* PROFILE CARD */}
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] p-[24px] mb-[32px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col lg:flex-row items-center justify-between gap-[24px]">
              
              {/* Profile Info Left */}
              <div className="flex items-center gap-[20px]">
                <img 
                  src={`https://ui-avatars.com/api/?name=${encodeURIComponent(vendedor.nome)}&background=F3F4F6&color=1A1A2E&size=128`} 
                  alt="Avatar" 
                  className="w-[80px] h-[80px] rounded-full object-cover border-4 border-[#F9FAFB] shadow-sm"
                />
                <div className="flex flex-col">
                  <div className="flex items-center gap-[12px] mb-[8px]">
                    <h2 className="text-[20px] font-[800] text-[#1A1A2E]">{vendedor.nome}</h2>
                    <span className={`flex items-center gap-[4px] px-[8px] py-[2px] text-[10px] font-[800] uppercase tracking-wide rounded-[4px] ${vendedor.status?.toLowerCase() === 'ativo' ? 'bg-[#ECFDF5] text-[#059669]' : 'bg-[#FEF2F2] text-[#DC2626]'}`}>
                      <div className={`w-[6px] h-[6px] rounded-full ${vendedor.status?.toLowerCase() === 'ativo' ? 'bg-[#059669]' : 'bg-[#DC2626]'}`}></div>
                      {t(vendedor.status || 'Ativo')}
                    </span>
                  </div>
                  
                  <div className="flex flex-wrap items-center gap-x-[32px] gap-y-[8px]">
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <Phone className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      {vendedor.telefone || 'Sem telefone'}
                    </div>
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <Users className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      {vendedor.equipe_id ? `Equipe #${vendedor.equipe_id}` : 'Sem Equipe'}
                    </div>
                    <div className="flex items-center gap-[6px] text-[#4B5563] text-[13px] font-[500]">
                      <Briefcase className="w-[14px] h-[14px] text-[#9CA3AF]" />
                      {vendedor.is_gestor ? 'Gestor' : 'Vendedor'}
                    </div>
                  </div>
                </div>
              </div>

              {/* Metrics Right */}
              <div className="flex items-center gap-[16px] shrink-0">
                <div className="bg-[#F9FAFB] border border-[#F1F1F4] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#6B7280] uppercase tracking-wider mb-[4px]">{t("Tempo de Equipe")}</p>
                  <p className="text-[18px] font-[800] text-[#1A1A2E]">4a 2m</p>
                </div>
                <div className="bg-[#F0FDF4] border border-[#DCFCE7] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#059669] uppercase tracking-wider mb-[4px]">{t("Metas 3M")}</p>
                  <div className="flex items-baseline gap-[6px]">
                    <p className="text-[18px] font-[800] text-[#059669]">82%</p>
                    <span className="text-[12px] font-[600] text-[#34D399]">32/39</span>
                  </div>
                </div>
                <div className="bg-[#FFFBEB] border border-[#FEF3C7] rounded-[12px] p-[12px_16px] min-w-[140px]">
                  <p className="text-[10px] font-[700] text-[#D97706] uppercase tracking-wider mb-[4px]">{t("Pendências")}</p>
                  <div className="flex items-baseline gap-[6px]">
                    <p className="text-[18px] font-[800] text-[#D97706]">2</p>
                    <span className="text-[12px] font-[600] text-[#F59E0B] hover:text-[#D97706] cursor-pointer">{t("Visualizar")}</span>
                  </div>
                </div>
              </div>

            </div>

            {/* FILTERS & CONTENT AREA */}
            <div className="flex flex-col lg:flex-row gap-[32px] items-start">
              
              {/* Left Column: Timeline */}
              <div className="flex-1 w-full flex flex-col">
                
                {/* Tabs */}
                <div className="flex items-center gap-[12px] mb-[24px] overflow-x-auto pb-2 scrollbar-hide">
                  {tabs.map((tab) => (
                    <button
                      key={tab}
                      onClick={() => setActiveTab(tab)}
                      className={`h-[36px] px-[16px] rounded-full text-[13px] font-[600] whitespace-nowrap transition-colors border ${
                        activeTab === tab 
                          ? 'bg-[#6D28D9] text-white border-[#6D28D9]' 
                          : 'bg-white text-[#4B5563] border-[#E5E7EB] hover:bg-[#F9FAFB]'
                      }`}
                    >
                      {tab}
                    </button>
                  ))}
                </div>

                {/* Search Bar */}
                <div className="flex items-center gap-[16px] mb-[32px]">
                  <div className="relative flex-1">
                    <Search className="absolute left-[16px] top-1/2 -translate-y-1/2 w-[16px] h-[16px] text-[#9CA3AF]" />
                    <input 
                      type="text" 
                      placeholder={t("Pesquisar no histórico...")}
                      className="w-full h-[44px] bg-white border border-[#E5E7EB] rounded-[8px] pl-[44px] pr-[16px] text-[14px] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all"
                    />
                  </div>
                  <div className="relative shrink-0">
                    <select className="h-[44px] bg-white border border-[#E5E7EB] rounded-[8px] pl-[16px] pr-[36px] text-[14px] font-[500] text-[#374151] appearance-none outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all cursor-pointer">
                      <option>Últimos 30 dias</option>
                      <option>Últimos 3 meses</option>
                      <option>Este ano</option>
                      <option>Tudo</option>
                    </select>
                    <ChevronDown className="absolute right-[12px] top-1/2 -translate-y-1/2 w-[16px] h-[16px] text-[#6B7280] pointer-events-none" />
                  </div>
                </div>

                {/* TIMELINE */}
                <div className="flex flex-col relative before:absolute before:inset-y-0 before:left-[108px] before:w-[2px] before:bg-[#E5E7EB]">
                  
                  {/* Item 1 */}
                  <div className="flex items-start gap-[16px] relative mb-[24px]">
                    <div className="w-[84px] shrink-0 text-right pt-[12px]">
                      <p className="text-[12px] font-[700] text-[#1A1A2E]">24/05/2026</p>
                      <p className="text-[11px] text-[#6B7280]">20:14</p>
                    </div>
                    
                    <div className="relative shrink-0 mt-[8px]">
                      <div className="w-[32px] h-[32px] rounded-full bg-white border-[2px] border-[#C4B5FD] flex items-center justify-center z-10 relative">
                        <Pencil className="w-[14px] h-[14px] text-[#6D28D9]" />
                      </div>
                    </div>

                    <div className="flex-1 bg-white border border-[#E5E7EB] rounded-[12px] p-[16px] shadow-sm hover:shadow-md transition-shadow">
                      <div className="flex items-start justify-between mb-[8px]">
                        <div className="flex items-center gap-[12px]">
                          <span className="px-[8px] py-[2px] bg-[#F4EEFF] text-[#6D28D9] text-[10px] font-[800] uppercase tracking-wide rounded-[4px]">
                            {t("Alterações Cadastrais")}
                          </span>
                          <h3 className="text-[14px] font-[700] text-[#1A1A2E]">{t("Telefone atualizado")}</h3>
                        </div>
                        <span className="text-[12px] font-[600] text-[#6D28D9] hover:underline cursor-pointer">{t("Ver detalhes")}</span>
                      </div>
                      <div className="flex items-end justify-between">
                        <p className="text-[13px] text-[#4B5563]">{t("O número foi alterado de (47) 99111-2233 para (47) 99987-1234.")}</p>
                        <div className="text-right">
                          <p className="text-[12px] font-[600] text-[#1A1A2E]">{t("Secretaria")}</p>
                          <p className="text-[10px] text-[#9CA3AF]">{t("Por Ana Souza")}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Item 2 */}
                  <div className="flex items-start gap-[16px] relative mb-[24px]">
                    <div className="w-[84px] shrink-0 text-right pt-[12px]">
                      <p className="text-[12px] font-[700] text-[#1A1A2E]">24/05/2026</p>
                      <p className="text-[11px] text-[#6B7280]">19:05</p>
                    </div>
                    
                    <div className="relative shrink-0 mt-[8px]">
                      <div className="w-[32px] h-[32px] rounded-full bg-white border-[2px] border-[#6EE7B7] flex items-center justify-center z-10 relative">
                        <CheckCircle2 className="w-[14px] h-[14px] text-[#059669]" />
                      </div>
                    </div>

                    <div className="flex-1 bg-white border border-[#E5E7EB] rounded-[12px] p-[16px] shadow-sm hover:shadow-md transition-shadow">
                      <div className="flex items-start justify-between mb-[8px]">
                        <div className="flex items-center gap-[12px]">
                          <span className="px-[8px] py-[2px] bg-[#ECFDF5] text-[#059669] text-[10px] font-[800] uppercase tracking-wide rounded-[4px]">
                            {t("Venda")}
                          </span>
                          <h3 className="text-[14px] font-[700] text-[#1A1A2E]">{t("Nova venda registrada")}</h3>
                        </div>
                        <span className="text-[12px] font-[600] text-[#6D28D9] hover:underline cursor-pointer">{t("Ver detalhes")}</span>
                      </div>
                      <div className="flex items-end justify-between">
                        <p className="text-[13px] text-[#4B5563]">{t("Venda do Plano Enterprise para cliente Tech Corp (R$ 15.000,00).")}</p>
                        <div className="text-right">
                          <p className="text-[12px] font-[600] text-[#1A1A2E]">{t("Sistema")}</p>
                          <p className="text-[10px] text-[#9CA3AF]">{t("Registro automático")}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Item 3 */}
                  <div className="flex items-start gap-[16px] relative mb-[24px]">
                    <div className="w-[84px] shrink-0 text-right pt-[12px]">
                      <p className="text-[12px] font-[700] text-[#1A1A2E]">22/05/2026</p>
                      <p className="text-[11px] text-[#6B7280]">16:30</p>
                    </div>
                    
                    <div className="relative shrink-0 mt-[8px]">
                      <div className="w-[32px] h-[32px] rounded-full bg-white border-[2px] border-[#93C5FD] flex items-center justify-center z-10 relative">
                        <MessageCircle className="w-[14px] h-[14px] text-[#2563EB]" />
                      </div>
                    </div>

                    <div className="flex-1 bg-white border border-[#E5E7EB] rounded-[12px] p-[16px] shadow-sm hover:shadow-md transition-shadow">
                      <div className="flex items-start justify-between mb-[8px]">
                        <div className="flex items-center gap-[12px]">
                          <span className="px-[8px] py-[2px] bg-[#EFF6FF] text-[#2563EB] text-[10px] font-[800] uppercase tracking-wide rounded-[4px]">
                            {t("Suporte")}
                          </span>
                          <h3 className="text-[14px] font-[700] text-[#1A1A2E]">{t("Acompanhamento realizado")}</h3>
                        </div>
                        <span className="text-[12px] font-[600] text-[#6D28D9] hover:underline cursor-pointer">{t("Ver detalhes")}</span>
                      </div>
                      <div className="flex items-end justify-between">
                        <p className="text-[13px] text-[#4B5563]">{t("Conversa com gestor registrada com observação interna.")}</p>
                        <div className="text-right">
                          <p className="text-[12px] font-[600] text-[#1A1A2E]">{t("Carlos Silva")}</p>
                          <p className="text-[10px] text-[#9CA3AF]">{t("Gestor responsável")}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Item 4 */}
                  <div className="flex items-start gap-[16px] relative mb-[24px]">
                    <div className="w-[84px] shrink-0 text-right pt-[12px]">
                      <p className="text-[12px] font-[700] text-[#1A1A2E]">13/05/2026</p>
                      <p className="text-[11px] text-[#6B7280]">10:00</p>
                    </div>
                    
                    <div className="relative shrink-0 mt-[8px]">
                      <div className="w-[32px] h-[32px] rounded-full bg-white border-[2px] border-[#FCD34D] flex items-center justify-center z-10 relative">
                        <AlertTriangle className="w-[14px] h-[14px] text-[#D97706]" />
                      </div>
                    </div>

                    <div className="flex-1 bg-white border border-[#E5E7EB] rounded-[12px] p-[16px] shadow-sm hover:shadow-md transition-shadow">
                      <div className="flex items-start justify-between mb-[8px]">
                        <div className="flex items-center gap-[12px]">
                          <span className="px-[8px] py-[2px] bg-[#FFFBEB] text-[#D97706] text-[10px] font-[800] uppercase tracking-wide rounded-[4px]">
                            {t("Ocorrência")}
                          </span>
                          <h3 className="text-[14px] font-[700] text-[#1A1A2E]">{t("Baixa produtividade")}</h3>
                        </div>
                        <span className="text-[12px] font-[600] text-[#D97706] hover:underline cursor-pointer">{t("Ver detalhes")}</span>
                      </div>
                      <div className="flex items-end justify-between">
                        <p className="text-[13px] text-[#4B5563]">{t("Não atingiu a cota mínima de contatos nas últimas 2 semanas.")}</p>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              {/* Right Column: Resumo do Histórico */}
              <div className="w-full lg:w-[320px] shrink-0 flex flex-col gap-[16px]">
                
                {/* Resumo Card */}
                <div className="bg-white border border-[#E5E7EB] rounded-[16px] p-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                  <h3 className="text-[16px] font-[800] text-[#1A1A2E] mb-[20px]">{t("Resumo do histórico")}</h3>
                  
                  <div className="flex flex-col gap-[16px]">
                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <FileText className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Total de registros")}
                      </div>
                      <span className="text-[14px] font-[800] text-[#1A1A2E]">128</span>
                    </div>
                    
                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <Pencil className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Alterações cadastrais")}
                      </div>
                      <span className="text-[14px] font-[800] text-[#1A1A2E]">18</span>
                    </div>

                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <MessageCircle className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Suporte e Atendimentos")}
                      </div>
                      <span className="text-[14px] font-[800] text-[#1A1A2E]">12</span>
                    </div>

                    <div className="flex items-center justify-between pb-[12px] border-b border-[#F1F1F4]">
                      <div className="flex items-center gap-[8px] text-[13px] text-[#4B5563] font-[500]">
                        <CheckCircle2 className="w-[14px] h-[14px] text-[#9CA3AF]" />
                        {t("Vendas convertidas")}
                      </div>
                      <span className="text-[14px] font-[800] text-[#1A1A2E]">32</span>
                    </div>

                    <div className="flex items-center justify-between pt-[4px]">
                      <Link href="#" className="flex items-center gap-[6px] text-[13px] font-[700] text-[#6D28D9] hover:underline">
                        {t("Ver relatório completo")}
                        <ExternalLink className="w-[14px] h-[14px]" />
                      </Link>
                    </div>
                  </div>
                </div>

                {/* Ações Rápidas */}
                <button className="w-full h-[48px] bg-[#22C55E] text-white font-[700] text-[14px] rounded-[10px] hover:bg-[#16A34A] transition-colors shadow-sm flex items-center justify-center gap-[8px]">
                  <MessageCircle className="w-[18px] h-[18px]" strokeWidth={2.5} />
                  {t("Conversar no WhatsApp")}
                </button>
                
                <button 
                  onClick={() => setModalDesativarOpen(true)}
                  className="w-full h-[48px] bg-white border border-[#EF4444] text-[#EF4444] font-[700] text-[14px] rounded-[10px] hover:bg-[#FEF2F2] transition-colors flex items-center justify-center gap-[8px]"
                >
                  <UserX className="w-[18px] h-[18px]" strokeWidth={2.5} />
                  {t("Desativar vendedor")}
                </button>

              </div>

            </div>

          </div>
        </main>
      </div>

      <ModalDesativar
        isOpen={modalDesativarOpen}
        itemName="João Pedro Silva"
        title="Desativar Vendedor"
        description="Ao desativar este vendedor, ele perderá imediatamente o acesso ao sistema. O histórico de vendas será mantido, mas ele não poderá registrar novas movimentações."
        onClose={() => setModalDesativarOpen(false)}
        onConfirm={handleDesativar}
      />
    </div>
  );
}
