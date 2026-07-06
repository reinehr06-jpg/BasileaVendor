"use client";

import React, { useState, use } from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import Link from "next/link";
import { 
  ArrowLeft,
  User,
  Mail,
  CreditCard,
  Phone,
  Fingerprint,
  Calendar,
  DollarSign,
  Briefcase,
  Percent,
  CheckCircle2,
  AlertTriangle,
  Edit2,
  Save,
  Clock
} from "lucide-react";
import CustomSelect from "@/components/CustomSelect";

export default function ClienteAsaasDetalhesPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const [vendedor, setVendedor] = useState("");
  const [comissaoVendedor, setComissaoVendedor] = useState("0");
  const [comissaoGestor, setComissaoGestor] = useState("0");

  const vendedorOptions = [
    { value: "1", label: "João Silva" },
    { value: "2", label: "Maria Souza" }
  ];

  return (
    <div className="flex min-h-screen font-inter bg-[#F8FAFC]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        <Topbar />

        <main className="p-[16px_24px_24px_24px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col max-w-[1200px] mx-auto gap-[16px]">
            
            {/* VOLTAR */}
            <Link 
              href="/configuracoes/clientes-asaas"
              className="flex items-center gap-[8px] text-[14px] font-[600] text-[#64748B] hover:text-[#0F172A] transition-colors w-fit"
            >
              <ArrowLeft className="w-[16px] h-[16px]" />
              Voltar para Clientes
            </Link>

            {/* HEADER PREMIUM */}
            <div className="relative overflow-hidden rounded-[12px] bg-[#1E1B4B] p-[20px_24px] flex flex-col md:flex-row md:items-center justify-between shadow-sm">
              <div className="absolute top-0 right-0 w-[200px] h-[200px] bg-white opacity-[0.03] rounded-full blur-[40px] -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
              
              <div className="flex items-start gap-[16px] relative z-10">
                <div className="w-[48px] h-[48px] bg-white/10 rounded-[12px] flex items-center justify-center border border-white/10 shrink-0 mt-[4px]">
                  <User className="w-[24px] h-[24px] text-white" strokeWidth={1.5} />
                </div>
                <div className="flex flex-col">
                  <div className="flex items-center gap-[12px] mb-[4px]">
                    <h1 className="text-[24px] font-[800] text-white tracking-tight leading-none">
                      Tabernáculo Church
                    </h1>
                    <span className="px-[8px] py-[4px] rounded-[6px] text-[11px] font-[700] uppercase tracking-wide bg-[#1E3A8A] text-[#93C5FD]">
                      Assinatura
                    </span>
                    <span className="px-[8px] py-[4px] rounded-[6px] text-[11px] font-[700] uppercase tracking-wide bg-[#7F1D1D] text-[#FCA5A5] flex items-center gap-[4px]">
                      <AlertTriangle className="w-[12px] h-[12px]" />
                      Churn
                    </span>
                  </div>
                  <p className="text-[14px] text-[#A5B4FC] font-[500] leading-snug">
                    associacaotabernaculo2023@gmail.com
                  </p>
                </div>
              </div>

              <div className="flex items-center gap-[12px] mt-[16px] md:mt-0 relative z-10">
                <Link 
                  href={`/configuracoes/clientes-asaas/${id}/editar`}
                  className="flex items-center gap-[8px] px-[16px] py-[8px] bg-[#F59E0B] hover:bg-[#D97706] text-white transition-all rounded-[8px] text-[13px] font-[600] shadow-sm"
                >
                  <Edit2 className="w-[16px] h-[16px]" />
                  Editar Dados
                </Link>
              </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-[16px]">
              
              {/* COLUNA ESQUERDA: Dados do Cliente e Pagamento */}
              <div className="lg:col-span-2 flex flex-col gap-[16px]">
                
                {/* DADOS DO CLIENTE */}
                <div className="bg-white rounded-[12px] border border-[#E2E8F0] shadow-sm p-[20px]">
                  <h2 className="text-[15px] font-[700] text-[#1E293B] mb-[16px] flex items-center gap-[8px]">
                    <User className="w-[18px] h-[18px] text-[#6366F1]" />
                    Informações do Cliente
                  </h2>
                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-y-[20px] gap-x-[32px]">
                    <div className="flex flex-col gap-[4px]">
                      <span className="text-[12px] font-[600] text-[#94A3B8] uppercase tracking-wider flex items-center gap-[6px]">
                        <Mail className="w-[14px] h-[14px]" /> E-mail
                      </span>
                      <span className="text-[14px] font-[500] text-[#1E293B]">associacaotabernaculo2023@gmail.com</span>
                    </div>
                    <div className="flex flex-col gap-[4px]">
                      <span className="text-[12px] font-[600] text-[#94A3B8] uppercase tracking-wider flex items-center gap-[6px]">
                        <CreditCard className="w-[14px] h-[14px]" /> CPF/CNPJ
                      </span>
                      <span className="text-[14px] font-[500] text-[#1E293B]">13.485.831/0001-42</span>
                    </div>
                    <div className="flex flex-col gap-[4px]">
                      <span className="text-[12px] font-[600] text-[#94A3B8] uppercase tracking-wider flex items-center gap-[6px]">
                        <Phone className="w-[14px] h-[14px]" /> Telefone
                      </span>
                      <span className="text-[14px] font-[500] text-[#1E293B]">(11) 94776-7855</span>
                    </div>
                    <div className="flex flex-col gap-[4px]">
                      <span className="text-[12px] font-[600] text-[#94A3B8] uppercase tracking-wider flex items-center gap-[6px]">
                        <Fingerprint className="w-[14px] h-[14px]" /> ID Asaas (Cliente)
                      </span>
                      <span className="text-[13px] font-[500] text-[#64748B] bg-[#F1F5F9] px-[8px] py-[4px] rounded-[6px] w-fit">
                        cus_000152888781
                      </span>
                    </div>
                  </div>
                </div>

                {/* DADOS DE PAGAMENTO */}
                <div className="bg-white rounded-[12px] border border-[#E2E8F0] shadow-sm p-[20px]">
                  <h2 className="text-[15px] font-[700] text-[#1E293B] mb-[16px] flex items-center gap-[8px]">
                    <DollarSign className="w-[18px] h-[18px] text-[#10B981]" />
                    Dados de Pagamento <span className="text-[#64748B] font-[500] text-[13px]">(Julho/2026)</span>
                  </h2>
                  
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                    <div className="flex flex-col p-[12px_16px] bg-[#F8FAFC] rounded-[10px] border border-[#E2E8F0]">
                      <span className="text-[11px] font-[700] text-[#64748B] uppercase tracking-wider flex items-center gap-[6px] mb-[6px]">
                        <Calendar className="w-[12px] h-[12px]" /> 1º Pagamento
                      </span>
                      <span className="text-[15px] font-[600] text-[#1E293B]">16/12/2025</span>
                    </div>
                    
                    <div className="flex flex-col p-[12px_16px] bg-[#F8FAFC] rounded-[10px] border border-[#E2E8F0]">
                      <span className="text-[11px] font-[700] text-[#64748B] uppercase tracking-wider flex items-center gap-[6px] mb-[6px]">
                        <Clock className="w-[12px] h-[12px]" /> Último Pagamento
                      </span>
                      <span className="text-[15px] font-[600] text-[#1E293B]">24/01/2026</span>
                    </div>

                    <div className="flex flex-col p-[12px_16px] bg-[#F0FDF4] rounded-[10px] border border-[#BBF7D0]">
                      <span className="text-[11px] font-[700] text-[#059669] uppercase tracking-wider flex items-center gap-[6px] mb-[6px]">
                        <DollarSign className="w-[12px] h-[12px]" /> Pago em Julho
                      </span>
                      <span className="text-[18px] font-[700] text-[#047857]">R$ 0,00</span>
                    </div>
                  </div>
                </div>

              </div>

              {/* COLUNA DIREITA: Atribuição Comercial */}
              <div className="lg:col-span-1">
                <div className="bg-white rounded-[12px] border border-[#C7D2FE] shadow-[0_4px_20px_-4px_rgba(79,70,229,0.1)] overflow-hidden flex flex-col sticky top-[100px]">
                  
                  <div className="bg-gradient-to-r from-[#EEF2FF] to-[#E0E7FF] p-[16px_20px] border-b border-[#C7D2FE]">
                    <h2 className="text-[15px] font-[700] text-[#3730A3] flex items-center gap-[8px]">
                      <Briefcase className="w-[16px] h-[16px]" />
                      Atribuição Comercial
                    </h2>
                    <p className="text-[12px] text-[#4F46E5] mt-[2px] font-[500]">
                      Gerencie o vendedor e suas comissões.
                    </p>
                  </div>

                  <div className="p-[20px] flex flex-col gap-[16px]">
                    
                    {/* Select Vendedor */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[11px] font-[700] text-[#64748B] uppercase tracking-wider">
                        Vendedor Responsável
                      </label>
                      <CustomSelect 
                        options={vendedorOptions}
                        value={vendedor}
                        onChange={(val) => setVendedor(val)}
                        placeholder="— Selecionar Vendedor —"
                        triggerClassName="w-full h-[38px] bg-white border border-[#E2E8F0] rounded-[8px] px-[12px] text-[13px] text-[#1E293B] outline-none hover:border-[#6366F1]"
                      />
                    </div>

                    {/* Percentuais */}
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[11px] font-[700] text-[#64748B] uppercase tracking-wider">
                        Percentuais de Comissão (%)
                      </label>
                      <div className="flex items-center gap-[12px]">
                        <div className="flex-1 flex flex-col gap-[4px]">
                          <span className="text-[11px] font-[500] text-[#64748B]">Vendedor %</span>
                          <div className="relative">
                            <input 
                              type="number" 
                              value={comissaoVendedor}
                              onChange={(e) => setComissaoVendedor(e.target.value)}
                              className="w-full h-[38px] bg-white border border-[#E2E8F0] rounded-[8px] pl-[12px] pr-[32px] text-[13px] font-[600] text-[#1E293B] outline-none focus:border-[#6366F1] focus:ring-1 focus:ring-[#6366F1] transition-all text-center"
                            />
                            <Percent className="w-[12px] h-[12px] text-[#94A3B8] absolute right-[10px] top-1/2 -translate-y-1/2" />
                          </div>
                        </div>
                        <div className="flex-1 flex flex-col gap-[4px]">
                          <span className="text-[11px] font-[500] text-[#64748B]">Gestor %</span>
                          <div className="relative">
                            <input 
                              type="number" 
                              value={comissaoGestor}
                              onChange={(e) => setComissaoGestor(e.target.value)}
                              className="w-full h-[38px] bg-white border border-[#E2E8F0] rounded-[8px] pl-[12px] pr-[32px] text-[13px] font-[600] text-[#1E293B] outline-none focus:border-[#6366F1] focus:ring-1 focus:ring-[#6366F1] transition-all text-center"
                            />
                            <Percent className="w-[12px] h-[12px] text-[#94A3B8] absolute right-[10px] top-1/2 -translate-y-1/2" />
                          </div>
                        </div>
                      </div>
                    </div>

                    {/* Valores Projetados */}
                    <div className="bg-[#F8FAFC] border border-[#E2E8F0] rounded-[10px] p-[12px_16px] flex flex-col gap-[8px]">
                      <span className="text-[10px] font-[800] text-[#64748B] uppercase tracking-wider border-b border-[#E2E8F0] pb-[6px]">
                        Comissões Projetadas (Julho)
                      </span>
                      
                      <div className="flex items-center justify-between">
                        <span className="text-[12px] font-[500] text-[#475569]">Vendedor:</span>
                        <span className="text-[13px] font-[700] text-[#10B981]">R$ 0,00</span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-[12px] font-[500] text-[#475569]">Gestor:</span>
                        <span className="text-[13px] font-[700] text-[#3B82F6]">R$ 0,00</span>
                      </div>
                      
                      <div className="w-full h-[1px] bg-[#E2E8F0] my-[2px]"></div>
                      
                      <div className="flex items-center justify-between">
                        <span className="text-[11px] font-[500] text-[#64748B]">Tipo Comissão:</span>
                        <span className="text-[10px] font-[700] text-[#475569] uppercase tracking-wide bg-[#E2E8F0] px-[6px] py-[2px] rounded-[4px]">
                          Recorrência
                        </span>
                      </div>
                    </div>

                    {/* Botão Salvar */}
                    <button className="w-full flex items-center justify-center gap-[6px] h-[38px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white rounded-[8px] text-[13px] font-[600] transition-colors shadow-sm mt-[4px]">
                      <Save className="w-[14px] h-[14px]" />
                      Salvar Atribuição
                    </button>

                  </div>
                </div>
              </div>

            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
