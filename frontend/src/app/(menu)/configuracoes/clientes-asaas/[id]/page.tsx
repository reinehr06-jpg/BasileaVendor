"use client";

import React, { useState, useEffect, use } from "react";
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
import { api } from "@/lib/api";
import { toast } from "sonner";
import { format, parseISO } from "date-fns";

export default function ClienteAsaasDetalhesPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const [loading, setLoading] = useState(true);
  
  // States
  const [cliente, setCliente] = useState<any>(null);
  const [vendedorId, setVendedorId] = useState("sem");
  const [comissaoVendedor, setComissaoVendedor] = useState("0");
  const [comissaoGestor, setComissaoGestor] = useState("0");
  const [vendedores, setVendedores] = useState<{id: number, nome: string}[]>([]);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    // Buscar vendedores
    api.get<any>('/vendedores').then((res) => {
      if (Array.isArray(res)) {
        setVendedores(res.map((v: any) => ({ id: v.id, nome: v.nome })));
      }
    }).catch(() => {});

    // Buscar dados do cliente
    api.get<any>(`/clientes-asaas/${id}`).then((res) => {
      if (res.success && res.data) {
        setCliente(res.data);
        setVendedorId(res.data.vendedor_id?.toString() || "sem");
      }
      setLoading(false);
    }).catch(() => setLoading(false));
  }, [id]);

  const handleSave = async () => {
    setSaving(true);
    const toastId = toast.loading("Salvando atribuição...");
    try {
      const res = await api.put<any>(`/clientes-asaas/${id}`, {
        vendedor_id: vendedorId === "sem" ? null : Number(vendedorId),
      });
      if (res.success) {
        toast.success("Atribuição atualizada com sucesso!", { id: toastId });
      } else {
        toast.error(res.message || "Erro ao salvar", { id: toastId });
      }
    } catch (e) {
      toast.error("Erro de comunicação", { id: toastId });
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex min-h-screen font-inter bg-[#F8FAFC]">
        <Sidebar />
        <div className="flex-1 ml-[240px] flex flex-col min-h-screen">
          <Topbar />
          <main className="flex-1 flex items-center justify-center">
            <div className="w-[30px] h-[30px] border-[3px] border-[#6D28D9] border-t-transparent rounded-full animate-spin"></div>
          </main>
        </div>
      </div>
    );
  }

  if (!cliente) return null;

  const vendedorOptions = [
    { label: "— Sem Vendedor —", value: "sem" },
    ...vendedores.map(v => ({ label: v.nome, value: v.id.toString() }))
  ];

  const formatDate = (dateStr: string) => {
    if (!dateStr) return "—";
    try {
      // asaas dates could be 'YYYY-MM-DD' or full ISO
      if (dateStr.length === 10) {
          const [y,m,d] = dateStr.split('-');
          return `${d}/${m}/${y}`;
      }
      return format(parseISO(dateStr), 'dd/MM/yyyy');
    } catch (e) {
      return dateStr;
    }
  };

  const formatCurrency = (val: number | string) => {
    if (val === null || val === undefined) return "R$ 0,00";
    const num = typeof val === 'string' ? parseFloat(val) : val;
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(num);
  };

  const getStatusBadge = (status: string) => {
    const s = status?.toUpperCase() || "";
    if (s === "ATIVO") return <span className="px-[8px] py-[4px] rounded-[6px] text-[11px] font-[700] uppercase tracking-wide bg-[#065F46] text-[#6EE7B7] flex items-center gap-[4px]"><CheckCircle2 className="w-[12px] h-[12px]" />Ativo</span>;
    if (s === "CHURN") return <span className="px-[8px] py-[4px] rounded-[6px] text-[11px] font-[700] uppercase tracking-wide bg-[#7F1D1D] text-[#FCA5A5] flex items-center gap-[4px]"><AlertTriangle className="w-[12px] h-[12px]" />Churn</span>;
    return <span className="px-[8px] py-[4px] rounded-[6px] text-[11px] font-[700] uppercase tracking-wide bg-[#475569] text-[#CBD5E1]">{s || "N/A"}</span>;
  };

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
                      {cliente.nome || cliente.nome_igreja || "Sem nome"}
                    </h1>
                    <span className="px-[8px] py-[4px] rounded-[6px] text-[11px] font-[700] uppercase tracking-wide bg-[#1E3A8A] text-[#93C5FD]">
                      {cliente.tipo_cobranca || "N/A"}
                    </span>
                    {getStatusBadge(cliente.diagnostico_status)}
                  </div>
                  <p className="text-[14px] text-[#A5B4FC] font-[500] leading-snug">
                    {cliente.email || "Sem e-mail"}
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
                      <span className="text-[14px] font-[500] text-[#1E293B]">{cliente.email || "—"}</span>
                    </div>
                    <div className="flex flex-col gap-[4px]">
                      <span className="text-[12px] font-[600] text-[#94A3B8] uppercase tracking-wider flex items-center gap-[6px]">
                        <CreditCard className="w-[14px] h-[14px]" /> CPF/CNPJ
                      </span>
                      <span className="text-[14px] font-[500] text-[#1E293B]">{cliente.documento || "—"}</span>
                    </div>
                    <div className="flex flex-col gap-[4px]">
                      <span className="text-[12px] font-[600] text-[#94A3B8] uppercase tracking-wider flex items-center gap-[6px]">
                        <Phone className="w-[14px] h-[14px]" /> Telefone
                      </span>
                      <span className="text-[14px] font-[500] text-[#1E293B]">{cliente.telefone || "—"}</span>
                    </div>
                    <div className="flex flex-col gap-[4px]">
                      <span className="text-[12px] font-[600] text-[#94A3B8] uppercase tracking-wider flex items-center gap-[6px]">
                        <Fingerprint className="w-[14px] h-[14px]" /> ID Asaas (Cliente)
                      </span>
                      <span className="text-[13px] font-[500] text-[#64748B] bg-[#F1F5F9] px-[8px] py-[4px] rounded-[6px] w-fit">
                        {cliente.asaas_customer_id || "—"}
                      </span>
                    </div>
                  </div>
                </div>

                {/* DADOS DE PAGAMENTO */}
                <div className="bg-white rounded-[12px] border border-[#E2E8F0] shadow-sm p-[20px]">
                  <h2 className="text-[15px] font-[700] text-[#1E293B] mb-[16px] flex items-center gap-[8px]">
                    <DollarSign className="w-[18px] h-[18px] text-[#10B981]" />
                    Dados de Pagamento
                  </h2>
                  
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                    <div className="flex flex-col p-[12px_16px] bg-[#F8FAFC] rounded-[10px] border border-[#E2E8F0]">
                      <span className="text-[11px] font-[700] text-[#64748B] uppercase tracking-wider flex items-center gap-[6px] mb-[6px]">
                        <Calendar className="w-[12px] h-[12px]" /> 1º Pagamento
                      </span>
                      <span className="text-[15px] font-[600] text-[#1E293B]">{formatDate(cliente.primeiro_pagamento_at)}</span>
                    </div>
                    
                    <div className="flex flex-col p-[12px_16px] bg-[#F8FAFC] rounded-[10px] border border-[#E2E8F0]">
                      <span className="text-[11px] font-[700] text-[#64748B] uppercase tracking-wider flex items-center gap-[6px] mb-[6px]">
                        <Clock className="w-[12px] h-[12px]" /> Último Pagamento
                      </span>
                      <span className="text-[15px] font-[600] text-[#1E293B]">{formatDate(cliente.ultimo_pagamento_at)}</span>
                    </div>

                    <div className="flex flex-col p-[12px_16px] bg-[#F0FDF4] rounded-[10px] border border-[#BBF7D0]">
                      <span className="text-[11px] font-[700] text-[#059669] uppercase tracking-wider flex items-center gap-[6px] mb-[6px]">
                        <DollarSign className="w-[12px] h-[12px]" /> Valor Plano
                      </span>
                      <span className="text-[18px] font-[700] text-[#047857]">{formatCurrency(cliente.valor_plano_mensal)}</span>
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
                        value={vendedorId}
                        onChange={(val) => setVendedorId(val)}
                        placeholder="— Selecionar Vendedor —"
                        triggerClassName="w-full h-[38px] bg-white border border-[#E2E8F0] rounded-[8px] px-[12px] text-[13px] text-[#1E293B] outline-none hover:border-[#6366F1]"
                      />
                    </div>

                    {/* Botão Salvar */}
                    <button 
                      onClick={handleSave}
                      disabled={saving}
                      className="w-full flex items-center justify-center gap-[6px] h-[38px] bg-[#6D28D9] hover:bg-[#5B21B6] text-white rounded-[8px] text-[13px] font-[600] transition-colors shadow-sm mt-[4px] disabled:opacity-70"
                    >
                      <Save className="w-[14px] h-[14px]" />
                      {saving ? "Salvando..." : "Salvar Atribuição"}
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
