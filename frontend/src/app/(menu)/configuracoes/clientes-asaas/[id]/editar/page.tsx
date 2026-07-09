"use client";

import React, { useState, useEffect } from "react";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  Users,
  User,
  CreditCard,
  Briefcase,
  Link as LinkIcon,
  ChevronDown,
  ChevronUp,
  Save
} from "lucide-react";
import { use } from "react";
import { api } from "@/lib/api";
import { toast } from "sonner";

type SectionType = "dados-pessoais" | "pagamento" | "classificacao" | "faturas" | null;

export default function EditarClienteAsaasPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const { t } = useTranslation();
  const [openSection, setOpenSection] = useState<SectionType>("dados-pessoais");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  // Form states
  const [nome, setNome] = useState("");
  const [email, setEmail] = useState("");
  const [cpfCnpj, setCpfCnpj] = useState("");
  const [telefone, setTelefone] = useState("");

  const [tipoCobranca, setTipoCobranca] = useState("");
  const [valorMensal, setValorMensal] = useState("");
  const [parcelasTotal, setParcelasTotal] = useState("");
  const [parcelasPagas, setParcelasPagas] = useState("");
  const [primeiroPagamento, setPrimeiroPagamento] = useState("");
  const [ultimoPagamento, setUltimoPagamento] = useState("");
  const [proximoVencimento, setProximoVencimento] = useState("");

  const [status, setStatus] = useState("");
  const [tipoComissao, setTipoComissao] = useState("recorrencia");
  const [vendedorId, setVendedorId] = useState("sem");
  
  const [faturas, setFaturas] = useState("");

  // Vendedores do backend
  const [vendedores, setVendedores] = useState<{
    id: number;
    nome: string;
    comissao_inicial?: number;
    comissao_recorrencia?: number;
    comissao_gestor_primeira?: number;
    comissao_gestor_recorrencia?: number;
  }[]>([]);

  useEffect(() => {
    // Buscar vendedores
    api.get<any>('/vendedores').then((res) => {
      if (Array.isArray(res)) {
        setVendedores(res);
      }
    }).catch(() => {});

    // Buscar dados do cliente
    api.get<any>(`/clientes-asaas/${id}`).then((res) => {
      if (res.success && res.data) {
        const c = res.data;
        setNome(c.nome || c.nome_igreja || "");
        setEmail(c.email || "");
        setCpfCnpj(c.documento || "");
        setTelefone(c.telefone || "");
        setTipoCobranca(c.tipo_cobranca || "");
        setValorMensal(c.valor_plano_mensal || "");
        setParcelasTotal(c.parcelas_total?.toString() || "");
        setParcelasPagas(c.parcelas_pagas?.toString() || "");
        setPrimeiroPagamento(c.primeiro_pagamento_at || "");
        setUltimoPagamento(c.ultimo_pagamento_at || "");
        setProximoVencimento(c.proximo_vencimento_at || "");
        setStatus(c.diagnostico_status?.toLowerCase() || "");
        setVendedorId(c.vendedor_id?.toString() || "sem");
      }
      setLoading(false);
    }).catch(() => setLoading(false));
  }, [id]);

  const handleSave = async () => {
    setSaving(true);
    const toastId = toast.loading("Salvando...");
    try {
      const res = await api.put<any>(`/clientes-asaas/${id}`, {
        nome,
        email,
        documento: cpfCnpj,
        telefone,
        tipo_cobranca: tipoCobranca,
        valor_plano_mensal: valorMensal,
        parcelas_total: parcelasTotal ? Number(parcelasTotal) : null,
        parcelas_pagas: parcelasPagas ? Number(parcelasPagas) : null,
        primeiro_pagamento_at: primeiroPagamento || null,
        ultimo_pagamento_at: ultimoPagamento || null,
        proximo_vencimento_at: proximoVencimento || null,
        diagnostico_status: status?.toUpperCase(),
        vendedor_id: vendedorId === "sem" ? null : Number(vendedorId),
      });
      if (res.success) {
        toast.success("Cliente atualizado com sucesso!", { id: toastId });
      } else {
        toast.error(res.message || "Erro ao salvar", { id: toastId });
      }
    } catch (e) {
      toast.error("Erro de comunicação", { id: toastId });
    } finally {
      setSaving(false);
    }
  };

  const vendedorOptions = [
    { label: "— Sem Vendedor —", value: "sem" },
    ...vendedores.map(v => ({ label: v.nome, value: v.id.toString() }))
  ];

  let comissaoVendedorVal = 0;
  let comissaoGestorVal = 0;

  if (vendedorId !== "sem" && valorMensal) {
    const v = vendedores.find((vd) => vd.id.toString() === vendedorId);
    if (v) {
      // Clean string (e.g., "1.548,00" -> "1548.00" or "129.00" -> "129.00")
      let cleanVal = String(valorMensal).trim();
      if (cleanVal.includes(',')) {
        cleanVal = cleanVal.replace(/\./g, '').replace(',', '.');
      }
      let baseVal = parseFloat(cleanVal) || 0;
      
      const isAntecipada = tipoComissao === 'unica' || tipoComissao === '1_pagamento';
      
      const pVendedor = isAntecipada 
        ? (v.comissao_inicial || 0) 
        : (v.comissao_recorrencia || 0);
        
      comissaoVendedorVal = (baseVal * pVendedor) / 100;
      
      const pGestor = isAntecipada 
        ? (v.comissao_gestor_primeira || 0)
        : (v.comissao_gestor_recorrencia || 0);
      
      comissaoGestorVal = (baseVal * pGestor) / 100;
    }
  }

  const formatCurrency = (val: number) => {
    return val.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  };

  const InputField = ({ label, type = "text", placeholder = "", required = false, value, onChange, icon }: any) => (
    <div className="flex flex-col gap-[6px]">
      <label className="text-[13px] font-[600] text-[#4B5563]">
        {label} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
      </label>
      <div className="relative">
        <input 
          type={type} 
          placeholder={placeholder}
          value={value}
          onChange={onChange ? (e) => onChange(e.target.value) : undefined}
          className={`w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] ${icon ? 'pr-[36px]' : ''}`}
        />
        {icon && (
          <div className="absolute inset-y-0 right-[12px] flex items-center pointer-events-none text-[#6B7280] font-[600]">
            {icon}
          </div>
        )}
      </div>
    </div>
  );

  if (loading) {
    return (
      <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
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

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300 relative pb-[80px]">
        <Topbar />

        <main className="p-[24px_32px_32px_32px] flex-1 flex flex-col">
          
          <div className="w-full flex flex-col">
            
            {/* CABEÇALHO DA PÁGINA */}
            <div className="flex items-start gap-[12px] mb-[24px]">
              <Users className="w-[24px] h-[24px] text-[#4B5563] mt-[2px]" strokeWidth={2} />
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Editar Cliente")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Atualize as informações cadastrais e comerciais do cliente.")}</p>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS */}
            <div className="flex flex-col gap-[16px]">

              {/* SEÇÃO 1: DADOS PESSOAIS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("dados-pessoais")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <User className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados Pessoais")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Informações de acesso e contato.")}</p>
                    </div>
                  </div>
                  {openSection === "dados-pessoais" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "dados-pessoais" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField label={t("Nome completo")} required value={nome} onChange={setNome} />
                      <InputField type="email" label={t("E-mail")} required value={email} onChange={setEmail} />
                      <InputField label={t("CPF/CNPJ")} required value={cpfCnpj} onChange={setCpfCnpj} />
                      <InputField label={t("Telefone")} value={telefone} onChange={setTelefone} />
                    </div>
                  </div>
                )}
              </div>

              {/* SEÇÃO 2: DADOS DE PAGAMENTO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("pagamento")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <CreditCard className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Dados de Pagamento")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Tipo, valores e cronograma do Asaas.")}</p>
                    </div>
                  </div>
                  {openSection === "pagamento" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "pagamento" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        {t("Tipo de Cobrança")} <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      <CustomSelect
                        options={[
                          { label: "Assinatura Recorrente", value: "subscription" },
                          { label: "Parcelamento", value: "installment" },
                          { label: "Cobrança Avulsa", value: "avulso" }
                        ]}
                        value={tipoCobranca}
                        onChange={setTipoCobranca}
                        placeholder="Selecione..."
                        triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                      />
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-[20px]">
                      <InputField label={t("Valor Plano Mensal")} required value={valorMensal} onChange={setValorMensal} />
                      <InputField type="number" label={t("Parcelas Total")} required value={parcelasTotal} onChange={setParcelasTotal} />
                      <InputField type="number" label={t("Parcelas Pagas")} required value={parcelasPagas} onChange={setParcelasPagas} />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-[20px]">
                      <InputField type="date" label={t("1º Pagamento (Início)")} value={primeiroPagamento} onChange={setPrimeiroPagamento} />
                      <InputField type="date" label={t("Últ. Pagamento Confirmado")} value={ultimoPagamento} onChange={setUltimoPagamento} />
                      <InputField type="date" label={t("Próximo Vencimento")} value={proximoVencimento} onChange={setProximoVencimento} />
                    </div>
                  </div>
                )}
              </div>

              {/* SEÇÃO 3: CLASSIFICAÇÃO */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("classificacao")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Briefcase className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Classificação")} <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Status, Vendedor e projeção de comissões.")}</p>
                    </div>
                  </div>
                  {openSection === "classificacao" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "classificacao" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Status (Diagnóstico)")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          options={[
                            { label: "ATIVO - Pagamentos em dia", value: "ativo" },
                            { label: "CHURN - Tem cobrança vencida", value: "churn" },
                            { label: "CANCELADO - Assinatura encerrada", value: "cancelado" }
                          ]}
                          value={status}
                          onChange={setStatus}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          {t("Tipo de Comissão")} <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          options={[
                            { label: "Recorrência", value: "recorrencia" },
                            { label: "1 pagamento", value: "1_pagamento" },
                            { label: "Única", value: "unica" },
                            { label: "Sem comissão já paga!", value: "sem_comissao_ja_paga" }
                          ]}
                          value={tipoComissao}
                          onChange={setTipoComissao}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                    </div>

                    <div className="flex flex-col gap-[6px] mt-[8px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        {t("Vendedor Responsável")} <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      <CustomSelect
                        options={vendedorOptions}
                        value={vendedorId}
                        onChange={setVendedorId}
                        placeholder="Selecione..."
                        triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                      />
                    </div>

                    <div className="bg-[#F0FDF4] border border-[#BBF7D0] rounded-[8px] p-[16px] flex flex-col gap-[8px] mt-[8px]">
                      <span className="text-[11px] font-[800] text-[#059669] uppercase tracking-wider">
                        Comissão Calculada
                      </span>
                      <div className="flex items-center justify-between">
                        <span className="text-[14px] font-[500] text-[#047857]">Vendedor:</span>
                        <span className="text-[16px] font-[700] text-[#047857]">{formatCurrency(comissaoVendedorVal)}</span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-[14px] font-[500] text-[#047857]">Gestor:</span>
                        <span className="text-[16px] font-[700] text-[#047857]">{formatCurrency(comissaoGestorVal)}</span>
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 4: FATURAS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden mb-[30px]">
                <button 
                  onClick={() => toggleSection("faturas")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <LinkIcon className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        {t("Múltiplas Faturas Asaas")}
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">{t("Vincule cartões ou faturas separadas do mesmo produto.")}</p>
                    </div>
                  </div>
                  {openSection === "faturas" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "faturas" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[16px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        {t("IDs de Cobrança / Assinatura (um por linha)")}
                      </label>
                      <textarea 
                        rows={4}
                        placeholder={"sub_...\npay_..."}
                        value={faturas}
                        onChange={(e) => setFaturas(e.target.value)}
                        className="w-full p-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB] resize-y font-mono"
                      ></textarea>
                      <p className="text-[12px] text-[#6B7280] mt-[4px]">
                        * Use isso se o cliente tiver dois cartões ou faturas separadas no Asaas para o mesmo produto.
                      </p>
                    </div>
                  </div>
                )}
              </div>

            </div>
          </div>
        </main>

        {/* BARRA INFERIOR FLUTUANTE (FIXA) PARA SALVAR */}
        <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] px-[32px] flex items-center justify-between z-40">
          <p className="text-[13px] text-[#6B7280] hidden md:block">
            {t("Preencha as informações obrigatórias (")} <span className="text-[#EF4444] font-[700]">*</span> {t(") antes de salvar.")}
          </p>
          <div className="flex items-center gap-[12px] ml-auto">
            <Link 
              href={`/configuracoes/clientes-asaas/${id}`}
              className="h-[44px] px-[20px] bg-white border border-[#E5E7EB] text-[#374151] font-[600] text-[14px] rounded-[8px] hover:bg-[#F9FAFB] hover:text-[#111827] transition-colors flex items-center justify-center"
            >
              {t("Cancelar")}
            </Link>
            <button 
              disabled={saving}
              onClick={handleSave}
              className="h-[44px] px-[24px] bg-[#6D28D9] text-white font-[600] text-[14px] rounded-[8px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center gap-[8px] disabled:opacity-70"
            >
              <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
              {saving ? t("Salvando...") : t("Salvar Alterações")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
