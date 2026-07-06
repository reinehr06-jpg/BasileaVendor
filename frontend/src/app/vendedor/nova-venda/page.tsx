"use client";

import React, { useState, useEffect } from "react";
import Link from "next/link";
import CustomSelect from "@/components/CustomSelect";
import { useTranslation } from "react-i18next";
import {
  ShoppingBag,
  Building2,
  Tag,
  ChevronDown,
  ChevronUp,
  Save,
  X,
  Calendar,
  PieChart,
  Headset,
  AlertCircle,
  CreditCard as CreditCardIcon,
  Smartphone,
  FileText
} from "lucide-react";

type SectionType = "identificacao" | "dados-comerciais" | null;

export default function VendedorNovaVendaPage() {
  const { t } = useTranslation();
  const [openSection, setOpenSection] = useState<SectionType>("identificacao");

  // Dados Identificação
  const [moeda, setMoeda] = useState("BRL");
  const [membros, setMembros] = useState<number | "">("");

  // Dados Comerciais
  const [planoSelecionado, setPlanoSelecionado] = useState("");
  const [periodoSelecionado, setPeriodoSelecionado] = useState("");
  const [formaPagamento, setFormaPagamento] = useState("");
  
  // Detalhes Cartão Anual
  const [parcelasCartao, setParcelasCartao] = useState<number>(1);
  const [dividirDoisCartoes, setDividirDoisCartoes] = useState(false);
  const [cartao1Valor, setCartao1Valor] = useState<number | "">("");
  const [cartao1Parcelas, setCartao1Parcelas] = useState<number>(1);
  const [cartao2Valor, setCartao2Valor] = useState<number | "">("");
  const [cartao2Parcelas, setCartao2Parcelas] = useState<number>(1);

  const toggleSection = (section: SectionType) => {
    setOpenSection((prev) => (prev === section ? null : section));
  };

  // Regra de Bloqueio de Planos por Membros
  const isStartBlocked = membros !== "" && membros > 100;
  const isBasicBlocked = membros !== "" && membros > 300;
  const isPlusBlocked = membros !== "" && membros > 500;

  useEffect(() => {
    if (membros === "") return;
    if (membros <= 100) setPlanoSelecionado("Start");
    else if (membros <= 300) setPlanoSelecionado("Basic");
    else if (membros <= 500) setPlanoSelecionado("Plus");
    else setPlanoSelecionado("Performance");
  }, [membros]);

  useEffect(() => {
    setFormaPagamento("");
    setDividirDoisCartoes(false);
  }, [periodoSelecionado]);

  const getValorTotal = () => {
    let preco = 0;
    if (planoSelecionado === "Start") preco = 197;
    if (planoSelecionado === "Basic") preco = 297;
    if (planoSelecionado === "Plus") preco = 397;
    if (planoSelecionado === "Performance") return 0;

    if (periodoSelecionado === "Anual") preco = preco * 12;
    return preco;
  };

  const valorTotal = getValorTotal();

  const InputField = ({ label, type = "text", placeholder = "", required = false, subtitle, value, onChange }: any) => (
    <div className="flex flex-col gap-[6px]">
      <label className="text-[13px] font-[600] text-[#4B5563]">
        {label} {required && <span className="text-[#EF4444] ml-0.5">*</span>}
      </label>
      <input 
        type={type} 
        placeholder={placeholder}
        value={value !== undefined ? value : undefined}
        onChange={onChange}
        className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
      />
      {subtitle && <span className="text-[11px] text-[#9CA3AF] mt-1">{subtitle}</span>}
    </div>
  );

  return (
    <>
      <main className="p-[32px] flex-1 flex flex-col">
          
      <div className="w-full flex flex-col gap-[24px]">
            
            {/* CABEÇALHO PADRÃO DO ADMIN (NADA DE CAIXA ROXA) */}
            <div className="flex items-start gap-[12px]">
              <div className="w-[40px] h-[40px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0">
                <ShoppingBag className="w-[20px] h-[20px] text-[#7C3AED]" strokeWidth={2.2} />
              </div>
              <div className="flex flex-col">
                <h1 className="text-[24px] font-[700] text-[#111827] leading-tight mb-[4px]">{t("Nova Venda")}</h1>
                <p className="text-[14px] text-[#6B7280]">{t("Cadastre uma nova venda para seu cliente.")}</p>
              </div>
            </div>

            {/* INFO BAR READ-ONLY (ESTILO ADMIN) */}
            <div className="bg-white rounded-[12px] border border-[#E5E7EB] p-[20px] grid grid-cols-2 md:grid-cols-4 gap-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
              <div className="flex flex-col">
                <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-widest mb-1">Vendedor</span>
                <span className="text-[14px] font-[700] text-[#6D28D9]">Vendedor de Testes</span>
              </div>
              <div className="flex flex-col">
                <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-widest mb-1">Data</span>
                <span className="text-[14px] font-[600] text-[#111827]">05/07/2026</span>
              </div>
              <div className="flex flex-col">
                <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-widest mb-1">Status</span>
                <span className="text-[14px] font-[700] text-[#059669]">Aguardando pagamento</span>
              </div>
              <div className="flex flex-col">
                <span className="text-[11px] font-[700] text-[#6B7280] uppercase tracking-widest mb-1">Origem</span>
                <span className="text-[14px] font-[600] text-[#374151]">Manual</span>
              </div>
            </div>

            {/* ÁREA DE ACCORDIONS */}
            <div className="flex flex-col gap-[16px]">

              {/* SEÇÃO 1: IDENTIFICAÇÃO DO CLIENTE */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("identificacao")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Building2 className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Identificação do Cliente <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Dados cadastrais e endereço.</p>
                    </div>
                  </div>
                  {openSection === "identificacao" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "identificacao" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[20px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField label="Nome da Igreja" placeholder="Digite o nome completo da igreja" required />
                      <InputField label="Nome do Pastor" placeholder="Digite o nome do pastor responsável" required />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField label="Localidade" placeholder="Cidade, estado ou país" required />
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Moeda <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <CustomSelect
                          options={[{ label: "🇧🇷 BRL - Real", value: "BRL" }]}
                          value={moeda}
                          onChange={setMoeda}
                          placeholder="Selecione..."
                          triggerClassName="h-[40px] bg-white border-[#E5E7EB] text-[14px]"
                        />
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <InputField 
                        type="number"
                        label="Quantidade de Membros" 
                        placeholder="Número de membros da igreja" 
                        required 
                        value={membros}
                        onChange={(e: any) => setMembros(e.target.value === "" ? "" : Number(e.target.value))}
                        subtitle="O sistema sugere planos automaticamente com base na quantidade."
                      />
                      <InputField label="CNPJ da Igreja ou CPF do Pastor" placeholder="Digite o documento" required />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Whatsapp de Contato <span className="text-[#EF4444] ml-0.5">*</span>
                        </label>
                        <div className="flex h-[40px]">
                          <div className="flex items-center justify-center bg-white border border-[#E5E7EB] border-r-0 rounded-l-[8px] px-[12px] shrink-0 text-[14px] text-[#6B7280] gap-[8px]">
                            <span role="img" aria-label="BR">🇧🇷</span>
                            <span>+55</span>
                          </div>
                          <input 
                            type="text" 
                            placeholder="(00) 00000-0000"
                            className="w-full h-full bg-white border border-[#E5E7EB] rounded-r-[8px] border-l-0 px-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                          />
                        </div>
                      </div>
                      <InputField type="email" label="E-mail do Cliente" placeholder="email@igreja.com" required />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-[1fr_2fr_1fr] gap-[20px] mt-[8px]">
                      <InputField label="CEP" placeholder="00000-000" required />
                      <InputField label="Endereço" placeholder="Avenida Brasil" required />
                      <InputField label="Número" placeholder="123" required />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-[1fr_1fr_1.5fr_80px] gap-[20px]">
                      <InputField label="Complemento" placeholder="Sala 4, Bloco B" />
                      <InputField label="Bairro" placeholder="Centro" required />
                      <InputField label="Cidade" placeholder="São Paulo" required />
                      <InputField label="UF" placeholder="SP" required />
                    </div>

                  </div>
                )}
              </div>

              {/* SEÇÃO 2: DADOS COMERCIAIS */}
              <div className="bg-white rounded-[12px] border border-[#E5E7EB] overflow-hidden">
                <button 
                  onClick={() => toggleSection("dados-comerciais")}
                  className="w-full flex items-center justify-between p-[24px] bg-white hover:bg-[#F9FAFB] transition-colors"
                >
                  <div className="flex items-center gap-[16px]">
                    <div className="w-[40px] h-[40px] rounded-[10px] bg-[#1E293B] flex items-center justify-center shrink-0">
                      <Tag className="w-[20px] h-[20px] text-white" strokeWidth={2} />
                    </div>
                    <div className="flex flex-col items-start">
                      <h2 className="text-[16px] font-[700] text-[#111827] flex items-center gap-[4px]">
                        Dados Comerciais <span className="text-[#EF4444]">*</span>
                      </h2>
                      <p className="text-[13px] text-[#6B7280] mt-[2px]">Planos, valores e formas de pagamento.</p>
                    </div>
                  </div>
                  {openSection === "dados-comerciais" ? (
                    <ChevronUp className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  ) : (
                    <ChevronDown className="w-[20px] h-[20px] text-[#9CA3AF]" />
                  )}
                </button>
                
                {openSection === "dados-comerciais" && (
                  <div className="p-[0_24px_24px_24px] flex flex-col gap-[28px] animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className="w-full h-[1px] bg-[#F3F4F6] mb-[4px]"></div>
                    
                    {/* PLANOS */}
                    <div className="flex flex-col gap-[12px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Selecione o Plano <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      <div className="grid grid-cols-1 md:grid-cols-4 gap-[16px]">
                        
                        <button 
                          onClick={() => setPlanoSelecionado("Start")} 
                          disabled={isStartBlocked}
                          className={`border rounded-[12px] p-[24px] flex flex-col items-center justify-center text-center transition-all ${
                            isStartBlocked ? "border-[#E5E7EB] bg-[#F9FAFB] opacity-50 cursor-not-allowed" : 
                            planoSelecionado === "Start" ? "border-[#6D28D9] bg-[#F4EEFF] shadow-sm" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB] cursor-pointer"
                          }`}
                        >
                          <h3 className="text-[14px] font-[700] text-[#6D28D9] mb-1">Basileia Start</h3>
                          <p className="text-[12px] text-[#9CA3AF] mb-3">Até 100 membros</p>
                          <span className="text-[20px] font-[700] text-[#111827] leading-none">R$ 197,00</span>
                          <span className="text-[11px] text-[#9CA3AF] mt-1">por mês</span>
                        </button>

                        <button 
                          onClick={() => setPlanoSelecionado("Basic")} 
                          disabled={isBasicBlocked}
                          className={`border rounded-[12px] p-[24px] flex flex-col items-center justify-center text-center transition-all ${
                            isBasicBlocked ? "border-[#E5E7EB] bg-[#F9FAFB] opacity-50 cursor-not-allowed" : 
                            planoSelecionado === "Basic" ? "border-[#6D28D9] bg-white shadow-sm ring-1 ring-[#6D28D9]" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB] cursor-pointer"
                          }`}
                        >
                          <h3 className="text-[14px] font-[700] text-[#6D28D9] mb-1">Basileia Basic</h3>
                          <p className="text-[12px] text-[#9CA3AF] mb-3">Até 300 membros</p>
                          <span className="text-[20px] font-[700] text-[#111827] leading-none">R$ 297,00</span>
                          <span className="text-[11px] text-[#9CA3AF] mt-1">por mês</span>
                        </button>

                        <button 
                          onClick={() => setPlanoSelecionado("Plus")} 
                          disabled={isPlusBlocked}
                          className={`border rounded-[12px] p-[24px] flex flex-col items-center justify-center text-center transition-all ${
                            isPlusBlocked ? "border-[#E5E7EB] bg-[#F9FAFB] opacity-50 cursor-not-allowed" : 
                            planoSelecionado === "Plus" ? "border-[#6D28D9] bg-[#F4EEFF] shadow-sm" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB] cursor-pointer"
                          }`}
                        >
                          <h3 className="text-[14px] font-[700] text-[#6D28D9] mb-1">Basileia Plus</h3>
                          <p className="text-[12px] text-[#9CA3AF] mb-3">Até 500 membros</p>
                          <span className="text-[20px] font-[700] text-[#111827] leading-none">R$ 397,00</span>
                          <span className="text-[11px] text-[#9CA3AF] mt-1">por mês</span>
                        </button>

                        <button 
                          onClick={() => setPlanoSelecionado("Performance")} 
                          className={`border rounded-[12px] p-[24px] flex flex-col items-center justify-center text-center transition-all ${
                            planoSelecionado === "Performance" ? "border-[#6D28D9] bg-[#F4EEFF] shadow-sm ring-1 ring-[#6D28D9]" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB] cursor-pointer"
                          }`}
                        >
                          <h3 className="text-[14px] font-[700] text-[#6D28D9] mb-1">Basileia Performance</h3>
                          <p className="text-[12px] text-[#9CA3AF] mb-3">Acima de 500 membros</p>
                          <div className="flex items-center gap-2 mt-1">
                            <Headset className="w-4 h-4 text-[#6D28D9]" />
                            <span className="text-[15px] font-[700] text-[#111827] leading-none">Negociar</span>
                          </div>
                          <span className="text-[11px] text-[#9CA3AF] mt-1">valor personalizado</span>
                        </button>

                      </div>
                      
                      {/* AVISO PLANO PERFORMANCE */}
                      {planoSelecionado === "Performance" && (
                        <div className="mt-2 flex items-start gap-[12px] p-[16px] bg-[#FFFBEB] border border-[#FDE68A] rounded-[8px]">
                          <AlertCircle className="w-[18px] h-[18px] text-[#D97706] shrink-0 mt-[1px]" />
                          <div className="flex flex-col">
                            <span className="text-[13px] font-[600] text-[#92400E]">Atenção: Negociação Performance</span>
                            <span className="text-[12px] text-[#B45309]">Todas as negociações para o plano Performance passarão por aprovação da gerência antes de disponibilizar a validação e ativação do plano.</span>
                          </div>
                        </div>
                      )}

                      <span className="text-[11px] text-[#9CA3AF]">O plano ideal é selecionado ou bloqueado automaticamente com base no número de membros.</span>
                    </div>

                    {/* PERÍODO DE CONTRATO */}
                    <div className="flex flex-col gap-[12px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Período de Contrato <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
                        
                        <div onClick={() => setPeriodoSelecionado("Mensal")} className={`border rounded-[12px] p-[20px] cursor-pointer flex flex-col items-center justify-center text-center transition-all ${periodoSelecionado === "Mensal" ? "border-[#6D28D9] bg-[#F4EEFF] shadow-sm" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB]"}`}>
                          <Calendar className={`w-6 h-6 mb-2 ${periodoSelecionado === "Mensal" ? "text-[#6D28D9]" : "text-[#9CA3AF]"}`} />
                          <h3 className="text-[14px] font-[700] text-[#111827] mb-1">Mensal</h3>
                          <p className="text-[12px] text-[#6B7280]">Cobrança recorrente</p>
                        </div>

                        <div onClick={() => setPeriodoSelecionado("Anual")} className={`border rounded-[12px] p-[20px] cursor-pointer flex flex-col items-center justify-center text-center transition-all ${periodoSelecionado === "Anual" ? "border-[#6D28D9] bg-[#F4EEFF] shadow-sm" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB]"}`}>
                          <Calendar className={`w-6 h-6 mb-2 ${periodoSelecionado === "Anual" ? "text-[#6D28D9]" : "text-[#9CA3AF]"}`} />
                          <h3 className="text-[14px] font-[700] text-[#111827] mb-1">Anual</h3>
                          <p className="text-[12px] text-[#6B7280]">Pagamento único (12 meses)</p>
                        </div>

                      </div>
                    </div>

                    {/* FORMA DE PAGAMENTO DINÂMICA */}
                    <div className="flex flex-col gap-[12px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">
                        Forma de Pagamento <span className="text-[#EF4444] ml-0.5">*</span>
                      </label>
                      
                      {!periodoSelecionado ? (
                        <span className="text-[13px] text-[#6B7280] italic">Selecione o período de contrato primeiro.</span>
                      ) : (
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                          {/* PIX */}
                          <div 
                            onClick={() => setFormaPagamento("Pix")} 
                            className={`border rounded-[12px] p-[16px] cursor-pointer flex flex-col items-center justify-center text-center transition-all relative ${formaPagamento === "Pix" ? "border-[#059669] bg-[#ECFDF5] shadow-sm" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB]"}`}
                          >
                            <Smartphone className={`w-5 h-5 mb-2 ${formaPagamento === "Pix" ? "text-[#059669]" : "text-[#9CA3AF]"}`} />
                            <h3 className="text-[13px] font-[700] text-[#111827]">Pix</h3>
                            {periodoSelecionado === "Mensal" && (
                              <span className="absolute top-2 right-2 px-2 py-0.5 bg-[#FEF3C7] text-[#D97706] text-[9px] font-[700] uppercase rounded-full tracking-wide">Requer Aprovação</span>
                            )}
                          </div>

                          {/* BOLETO (Só no Anual) */}
                          {periodoSelecionado === "Anual" && (
                            <div 
                              onClick={() => setFormaPagamento("Boleto")} 
                              className={`border rounded-[12px] p-[16px] cursor-pointer flex flex-col items-center justify-center text-center transition-all ${formaPagamento === "Boleto" ? "border-[#6D28D9] bg-[#F4EEFF] shadow-sm" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB]"}`}
                            >
                              <FileText className={`w-5 h-5 mb-2 ${formaPagamento === "Boleto" ? "text-[#6D28D9]" : "text-[#9CA3AF]"}`} />
                              <h3 className="text-[13px] font-[700] text-[#111827]">Boleto Bancário</h3>
                            </div>
                          )}

                          {/* CARTÃO DE CRÉDITO */}
                          <div 
                            onClick={() => setFormaPagamento("Cartao")} 
                            className={`border rounded-[12px] p-[16px] cursor-pointer flex flex-col items-center justify-center text-center transition-all ${formaPagamento === "Cartao" ? "border-[#2563EB] bg-[#EFF6FF] shadow-sm" : "border-[#E5E7EB] bg-white hover:border-[#D1D5DB]"}`}
                          >
                            <CreditCardIcon className={`w-5 h-5 mb-2 ${formaPagamento === "Cartao" ? "text-[#2563EB]" : "text-[#9CA3AF]"}`} />
                            <h3 className="text-[13px] font-[700] text-[#111827]">Cartão de Crédito</h3>
                          </div>
                        </div>
                      )}
                    </div>

                    {/* OPÇÕES EXTRAS PARA CARTÃO NO PLANO ANUAL */}
                    {periodoSelecionado === "Anual" && formaPagamento === "Cartao" && planoSelecionado !== "Performance" && (
                      <div className="bg-[#F9FAFB] border border-[#E5E7EB] rounded-[8px] p-[20px] flex flex-col gap-[20px] animate-in fade-in duration-300">
                        <div className="flex items-center justify-between">
                          <h3 className="text-[14px] font-[700] text-[#111827]">Detalhes do Parcelamento</h3>
                          <label className="flex items-center gap-[8px] cursor-pointer">
                            <input 
                              type="checkbox" 
                              checked={dividirDoisCartoes} 
                              onChange={(e) => setDividirDoisCartoes(e.target.checked)}
                              className="w-4 h-4 rounded text-[#6D28D9] focus:ring-[#6D28D9] border-gray-300"
                            />
                            <span className="text-[13px] font-[600] text-[#4B5563]">Dividir em 2 Cartões</span>
                          </label>
                        </div>

                        {!dividirDoisCartoes ? (
                          <div className="flex flex-col gap-[6px]">
                            <label className="text-[13px] font-[600] text-[#4B5563]">
                              Em quantas vezes?
                            </label>
                            <select 
                              value={parcelasCartao}
                              onChange={(e) => setParcelasCartao(Number(e.target.value))}
                              className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED]"
                            >
                              {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map((p) => (
                                <option key={p} value={p}>
                                  {p}x de {(valorTotal / p).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}
                                </option>
                              ))}
                            </select>
                          </div>
                        ) : (
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-[24px]">
                            {/* CARTÃO 1 */}
                            <div className="flex flex-col gap-[12px]">
                              <span className="text-[12px] font-[700] text-[#6D28D9] uppercase tracking-widest">Cartão 1</span>
                              <InputField 
                                type="number" 
                                label="Valor cobrado (R$)" 
                                value={cartao1Valor}
                                onChange={(e: any) => setCartao1Valor(e.target.value === "" ? "" : Number(e.target.value))}
                                placeholder="0,00"
                              />
                              <div className="flex flex-col gap-[6px]">
                                <label className="text-[13px] font-[600] text-[#4B5563]">
                                  Parcelas (Cartão 1)
                                </label>
                                <select 
                                  value={cartao1Parcelas}
                                  onChange={(e) => setCartao1Parcelas(Number(e.target.value))}
                                  className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED]"
                                >
                                  {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map((p) => {
                                    const val = typeof cartao1Valor === "number" ? cartao1Valor : 0;
                                    return (
                                      <option key={p} value={p}>
                                        {p}x de {(val / p).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}
                                      </option>
                                    )
                                  })}
                                </select>
                              </div>
                            </div>
                            {/* CARTÃO 2 */}
                            <div className="flex flex-col gap-[12px]">
                              <span className="text-[12px] font-[700] text-[#6D28D9] uppercase tracking-widest">Cartão 2</span>
                              <InputField 
                                type="number" 
                                label="Valor cobrado (R$)" 
                                value={cartao2Valor}
                                onChange={(e: any) => setCartao2Valor(e.target.value === "" ? "" : Number(e.target.value))}
                                placeholder="0,00"
                              />
                              <div className="flex flex-col gap-[6px]">
                                <label className="text-[13px] font-[600] text-[#4B5563]">
                                  Parcelas (Cartão 2)
                                </label>
                                <select 
                                  value={cartao2Parcelas}
                                  onChange={(e) => setCartao2Parcelas(Number(e.target.value))}
                                  className="w-full h-[40px] bg-white border border-[#E5E7EB] rounded-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED]"
                                >
                                  {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map((p) => {
                                    const val = typeof cartao2Valor === "number" ? cartao2Valor : 0;
                                    return (
                                      <option key={p} value={p}>
                                        {p}x de {(val / p).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}
                                      </option>
                                    )
                                  })}
                                </select>
                              </div>
                            </div>
                          </div>
                        )}
                      </div>
                    )}

                    <div className="grid grid-cols-1 md:grid-cols-[1fr_3fr] gap-[20px]">
                      {/* DESCONTO */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Desconto (%)
                        </label>
                        <div className="flex h-[40px]">
                          <input 
                            type="number" 
                            defaultValue={0}
                            min={0}
                            max={100}
                            className="w-full h-full bg-white border border-[#E5E7EB] border-r-0 rounded-l-[8px] px-[12px] text-[14px] text-[#1A1A2E] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all"
                          />
                          <div className="flex items-center justify-center bg-[#F9FAFB] border border-[#E5E7EB] rounded-r-[8px] px-[16px] shrink-0 text-[14px] font-[600] text-[#6B7280]">
                            %
                          </div>
                        </div>
                        <span className="text-[11px] text-[#9CA3AF] mt-1">Máximo: 100%. Acima de 5% requer aprovação.</span>
                      </div>

                      {/* OBSERVAÇÃO */}
                      <div className="flex flex-col gap-[6px]">
                        <label className="text-[13px] font-[600] text-[#4B5563]">
                          Observação
                        </label>
                        <textarea 
                          placeholder="Informações adicionais sobre a venda..."
                          className="w-full h-[80px] resize-none bg-white border border-[#E5E7EB] rounded-[8px] p-[12px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none focus:border-[#7C3AED] focus:ring-1 focus:ring-[#7C3AED] transition-all hover:border-[#D1D5DB]"
                        ></textarea>
                      </div>
                    </div>

                  </div>
                )}
              </div>

            </div>
          </div>
        </main>
        
        {/* BARRA INFERIOR PADRÃO */}
        <div className="fixed bottom-0 left-[240px] right-0 h-[80px] bg-white border-t border-[#E5E7EB] shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] px-[32px] flex items-center justify-end z-40">
          <div className="flex items-center gap-[12px]">
            <Link 
              href="/vendedor/minhas-vendas"
              className="h-[40px] px-[16px] bg-white border border-[#E5E7EB] text-[#374151] font-[600] text-[13px] rounded-[8px] hover:bg-[#F9FAFB] hover:text-[#111827] transition-colors flex items-center justify-center gap-2"
            >
              <X className="w-[16px] h-[16px]" />
              Cancelar
            </Link>
            <button 
              className="h-[40px] px-[20px] bg-[#4C1D95] text-white font-[600] text-[13px] rounded-[8px] hover:bg-[#5B21B6] transition-colors flex items-center justify-center gap-[8px]"
            >
              <Save className="w-[16px] h-[16px]" strokeWidth={2.5} />
              Gerar Cobrança e Salvar
            </button>
          </div>
        </div>

      </>
  );
}
