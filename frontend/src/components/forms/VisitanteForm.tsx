"use client";

import React, { useState, useEffect, Suspense } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";
import CustomSelect, { SelectOption } from "@/components/CustomSelect";
import CustomDatePicker from "@/components/CustomDatePicker";
import { useTranslation } from "react-i18next";
import {
  UserPlus,
  UserRound,
  ClipboardList,
  ChevronDown,
  Save,
  History,
  Clock,
  LogOut,
  AlertCircle
} from "lucide-react";

// ============================================================
// MAPA DO TESOURO — Cadastros / Visitantes (Novo)
// ============================================================
// PROPÓSITO:
//   Formulário em acordeão para cadastro completo de um novo
//   visitante da igreja, dividido em seções básicas e adicionais.
//
// INPUTS / FORMULÁRIOS:
//   - Input nome completo (texto)
//   - Input telefone/WhatsApp (texto)
//   - DatePicker data de nascimento
//   - Select sexo (Masculino/Feminino)
//   - Select é estrangeiro (Sim/Não)
//   - Select nacionalidade (buscável, condicional)
//   - Select célula (buscável)
//   - Select rede (buscável)
//   - Select aceitou Jesus (Sim/Não)
//   - Select reconciliou com Jesus (Sim/Não)
//   - Select veio de outra igreja (Sim/Não)
//   - Input qual igreja (condicional)
//   - TextArea como conheceu a igreja
//   - Select país, input CEP, rua, número, complemento, bairro,
//     cidade, select estado
//
// BOTÕES DE AÇÃO:
//   - Acordeões: Informações Básicas / Informações Adicionais
//   - Cancelar → redireciona para /cadastros/visitantes
//   - Salvar visitante → submete o formulário
//
// COMPORTAMENTOS:
//   - Acordeões abrem/fecham individualmente (apenas um por vez)
//   - Campos condicionais aparecem conforme seleção
//     (estrangeiro → nacionalidade, veio de outra igreja → qual)
//   - Validação de campos obrigatórios (*)
//
// INTEGRAÇÃO BACKEND:
//   - POST /api/visitantes (salvar novo visitante)
//   - GET /api/celulas (carregar lista de células)
//   - GET /api/redes (carregar lista de redes)
//
// #pag26 — Visitantes (Novo)
// ============================================================

export default function NovoVisitantePage({ editId }: { editId?: string } = {}) {
  return (
    <Suspense fallback={<div className="p-8 text-center text-[#6B7280]">Carregando formulário...</div>}>
      <NovoVisitanteContent editId={editId} />
    </Suspense>
  );
}

function NovoVisitanteContent({ editId }: { editId?: string }) {
  const { t } = useTranslation();
  const searchParams = useSearchParams();
  
  // Começa com os dropdowns fechados conforme solicitado
  const [openSection, setOpenSection] = useState<string | null>(null);

  // Estados Condicionais
  const [isForeign, setIsForeign] = useState("nao");
  const [aceitouJesus, setAceitouJesus] = useState("nao");
  const [reconciliou, setReconciliou] = useState("nao");
  const [veioDeOutraIgreja, setVeioDeOutraIgreja] = useState("nao");

  // Estados de dados
  const [nome, setNome] = useState("");
  const [telefone, setTelefone] = useState("");
  const [dataQueAceitouJesus, setDataQueAceitouJesus] = useState("");
  const [isAtivo, setIsAtivo] = useState(true);
  const [motivoInativacao, setMotivoInativacao] = useState("");
  const [descricaoInativacao, setDescricaoInativacao] = useState("");

  useEffect(() => {
    const nomeParam = searchParams.get("nome");
    const telefoneParam = searchParams.get("telefone");
    const dataAceitouParam = searchParams.get("dataAceitou");

    if (nomeParam) setNome(nomeParam);
    if (telefoneParam) setTelefone(telefoneParam);
    if (dataAceitouParam) {
      setAceitouJesus("sim");
      setDataQueAceitouJesus(dataAceitouParam);
      setOpenSection("obrigatorias");
    }
  }, [searchParams]);

  const toggleSection = (section: string) => {
    setOpenSection(openSection === section ? null : section);
  };

  // Componente interno para padronizar inputs
  const InputField = ({ 
    label, 
    type = "text", 
    placeholder = "", 
    disabled = false, 
    options = [], 
    value,
    onChange,
    multiple = false,
    searchable = false
  }: any) => (
    <div className="flex flex-col gap-[4px] group">
      <label className="text-[13px] font-[600] text-[#4B5563]">
        {label}
      </label>
      {type === "select" ? (
        <CustomSelect 
          options={options}
          value={value}
          onChange={onChange}
          placeholder={placeholder || "Selecione"}
          disabled={disabled}
          multiple={multiple}
          searchable={searchable}
        />
      ) : type === "date" ? (
        <CustomDatePicker
          value={value}
          onChange={onChange}
          placeholder={placeholder || "DD/MM/AAAA"}
          disabled={disabled}
        />
      ) : type === "textarea" ? (
        <textarea 
          placeholder={placeholder}
          disabled={disabled}
          value={value}
          onChange={onChange}
          className="min-h-[80px] p-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none  transition-all hover:border-[#D1D5DB] resize-y"
        />
      ) : (
        <input 
          type={type} 
          placeholder={placeholder}
          disabled={disabled}
          value={value}
          onChange={onChange}
          className="h-[38px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[14px] text-[#1A1A2E] placeholder-[#9CA3AF] outline-none  transition-all hover:border-[#D1D5DB] disabled:bg-[#F3F4F6] disabled:text-[#9CA3AF] disabled:cursor-not-allowed"
        />
      )}
    </div>
  );

  return (
    <div className="flex min-h-screen font-inter bg-[#F5F5F7]">
      {/* MAPA DO TESOURO: Sidebar navigation */}
      <Sidebar />

      <div className="flex-1 ml-[240px] flex flex-col min-h-screen transition-all duration-300">
        
        {/* MAPA DO TESOURO: Topbar with user info */}
        <Topbar />

        <main className="p-[0_24px_24px_24px] flex-1 flex flex-col">

          {/* CABEÇALHO DA PÁGINA */}
          <div className="flex items-center gap-[14px] py-[16px] pb-[16px]">
            {/* MAPA DO TESOURO: Page header icon - UserPlus */}
            <div className="w-[44px] h-[44px] rounded-[10px] bg-[#F4EEFF] flex items-center justify-center shrink-0 shadow-inner shadow-white/50">
              <UserPlus className="w-[20px] h-[20px] text-[#6D28D9]" strokeWidth={2.2} />
            </div>
            <div className="flex flex-col justify-center">
              {/* MAPA DO TESOURO: Page title "Novo visitante" */}
              <h1 className="text-[24px] font-[700] text-[#1A1A2E] leading-tight">{editId ? t("Editar visitante") : t("Novo visitante")}</h1>
              {/* MAPA DO TESOURO: Page subtitle description */}
              <p className="text-[13px] text-[#6B7280] mt-0.5">{editId ? t("Edite as informações cadastrais do visitante.") : t("Cadastre um novo visitante da sua igreja.")}</p>
            </div>
          </div>

          {/* ACCORDIONS CONTAINER */}
          {/* MAPA DO TESOURO: Main card with accordion sections */}
          <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[16px] flex flex-col gap-[12px]">

            {/* SEÇÃO 1: INFORMAÇÕES BÁSICAS */}
            {/* MAPA DO TESOURO: Accordion section "Informações Básicas" */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "obrigatorias" ? "border-[#6D28D9]/30 shadow-[0_4px_20px_rgba(124,58,237,0.05)] overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              {/* MAPA DO TESOURO: Accordion toggle button for basic info */}
              <button 
                onClick={() => toggleSection("obrigatorias")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  {/* MAPA DO TESOURO: Accordion icon - UserRound */}
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "obrigatorias" ? "bg-gradient-to-br from-[#6D28D9] to-[#6D28D9] shadow-md shadow-[#6D28D9]/20" : "bg-[#F4EEFF] group-hover:bg-[#EAE0FF]"}`}>
                    <UserRound className={`w-[18px] h-[18px] transition-colors ${openSection === "obrigatorias" ? "text-white" : "text-[#6D28D9]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    {/* MAPA DO TESOURO: Accordion title "Informações Básicas" (obrigatório) */}
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">
                      {t("Informações Básicas")} <span className="text-[#EF4444] ml-0.5">*</span>
                    </h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Dados essenciais para o cadastro.")}</p>
                  </div>
                </div>
                {/* MAPA DO TESOURO: Accordion expand/collapse chevron */}
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "obrigatorias" ? "rotate-180 text-[#6D28D9]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              {/* MAPA DO TESOURO: Expandable content area for basic info fields */}
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "obrigatorias" ? "max-h-[2000px] opacity-100 overflow-visible" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px]">
                    {/* MAPA DO TESOURO: Input - Nome completo (obrigatório) */}
                    <div className="col-span-2 lg:col-span-2"><InputField label={t("Nome completo")} placeholder={t("Ex: João da Silva")} value={nome} onChange={(e: any) => setNome(e.target.value)} /></div>
                    {/* MAPA DO TESOURO: Input - Telefone / WhatsApp */}
                    <InputField label={t("Telefone / WhatsApp")} placeholder="(00) 00000-0000" value={telefone} onChange={(e: any) => setTelefone(e.target.value)} />
                    {/* MAPA DO TESOURO: DatePicker - Data de nascimento */}
                    <InputField label={t("Data de nascimento")} type="date" value="" />
                    {/* MAPA DO TESOURO: Select - Sexo (Masculino/Feminino) */}
                    <InputField label={t("Sexo")} type="select" options={[{value:"M", label:"Masculino"}, {value:"F", label:"Feminino"}]} />
                  </div>
                </div>
              </div>
            </div>

            {/* SEÇÃO 2: INFORMAÇÕES ADICIONAIS */}
            {/* MAPA DO TESOURO: Accordion section "Informações Adicionais" */}
            <div className={`border rounded-[10px] bg-white transition-all duration-300 ${openSection === "adicionais" ? "border-[#6D28D9]/30 shadow-[0_4px_20px_rgba(124,58,237,0.05)] overflow-visible" : "border-[#E5E7EB] hover:border-[#D1D5DB] hover:shadow-sm overflow-hidden"}`}>
              {/* MAPA DO TESOURO: Accordion toggle button for additional info */}
              <button 
                onClick={() => toggleSection("adicionais")}
                className="w-full flex items-center justify-between p-[14px_16px] min-h-[72px] focus:outline-none group"
              >
                <div className="flex items-center gap-[14px]">
                  {/* MAPA DO TESOURO: Accordion icon - ClipboardList */}
                  <div className={`w-[40px] h-[40px] rounded-[8px] flex items-center justify-center shrink-0 transition-colors ${openSection === "adicionais" ? "bg-gradient-to-br from-[#6D28D9] to-[#6D28D9] shadow-md shadow-[#6D28D9]/20" : "bg-[#F4EEFF] group-hover:bg-[#EAE0FF]"}`}>
                    <ClipboardList className={`w-[18px] h-[18px] transition-colors ${openSection === "adicionais" ? "text-white" : "text-[#6D28D9]"}`} strokeWidth={2.2} />
                  </div>
                  <div className="flex flex-col items-start text-left">
                    {/* MAPA DO TESOURO: Accordion title "Informações Adicionais" */}
                    <h2 className="text-[15px] font-[700] text-[#1A1A2E]">{t("Informações Adicionais")}</h2>
                    <p className="text-[13px] text-[#6B7280] mt-0.5">{t("Endereço, célula, redes e histórico.")}</p>
                  </div>
                </div>
                {/* MAPA DO TESOURO: Accordion expand/collapse chevron */}
                <div className="shrink-0 ml-4">
                  <ChevronDown className={`w-[18px] h-[18px] text-[#9CA3AF] transition-transform duration-300 ${openSection === "adicionais" ? "rotate-180 text-[#6D28D9]" : "group-hover:text-[#6B7280]"}`} strokeWidth={2.4} />
                </div>
              </button>
              
              {/* MAPA DO TESOURO: Expandable content area for additional info fields */}
              <div className={`transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] ${openSection === "adicionais" ? "max-h-[2000px] opacity-100 overflow-visible" : "max-h-0 opacity-0 overflow-hidden"}`}>
                <div className="border-t border-[#F1F1F4] p-[16px_20px_20px_20px] bg-[#FCFCFD]">
                  
                  {/* Bloco Demográfico e Vínculos */}
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px] mb-[20px]">
                    {/* MAPA DO TESOURO: Select - É estrangeiro? (controla visibilidade de Nacionalidade) */}
                    <InputField 
                      label={t("É estrangeiro?")} 
                      type="select" 
                      value={isForeign} 
                      onChange={setIsForeign} 
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {/* MAPA DO TESOURO: Conditional - Nacionalidade (aparece se estrangeiro) */}
                    {isForeign === "sim" && (
                      <div className="animate-in fade-in slide-in-from-top-1">
                        <InputField label={t("Nacionalidade")} type="select" searchable options={[{value:"us", label:"Americano"}, {value:"ar", label:"Argentino"}]} />
                      </div>
                    )}
                    {/* MAPA DO TESOURO: Select - Célula que faz parte (buscável) */}
                    <InputField label={t("Célula que faz parte")} type="select" searchable options={[{value:"1", label:"Célula Centro"}, {value:"2", label:"Célula Jovens"}]} placeholder={t("Buscar célula...")} />
                    {/* MAPA DO TESOURO: Select - Rede que faz parte (buscável) */}
                    <InputField label={t("Rede que faz parte")} type="select" searchable options={[{value:"1", label:"Rede Esperança"}, {value:"2", label:"Rede Família"}]} placeholder={t("Buscar rede...")} />
                  </div>

                  <div className="h-px bg-[#E5E7EB] w-full mb-[20px]"></div>

                  {/* Bloco Espiritual e Histórico */}
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px] mb-[20px]">
                    {/* MAPA DO TESOURO: Select - Aceitou Jesus? */}
                    <InputField 
                      label={t("Aceitou Jesus?")} 
                      type="select" 
                      value={aceitouJesus}
                      onChange={setAceitouJesus}
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {/* MAPA DO TESOURO: Conditional - Data que aceitou Jesus */}
                    {aceitouJesus === "sim" && (
                      <div className="animate-in fade-in slide-in-from-top-1">
                        <InputField label={t("Data que aceitou Jesus")} type="date" value={dataQueAceitouJesus} onChange={setDataQueAceitouJesus} />
                      </div>
                    )}
                    {/* MAPA DO TESOURO: Select - Reconciliou com Jesus? */}
                    <InputField 
                      label={t("Reconciliou com Jesus?")} 
                      type="select" 
                      value={reconciliou}
                      onChange={setReconciliou}
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {/* MAPA DO TESOURO: Select - Veio de outra igreja? (controla visibilidade de Qual igreja?) */}
                    <InputField 
                      label={t("Veio de outra igreja?")} 
                      type="select" 
                      value={veioDeOutraIgreja}
                      onChange={setVeioDeOutraIgreja}
                      options={[{value:"nao", label:"Não"}, {value:"sim", label:"Sim"}]} 
                    />
                    {/* MAPA DO TESOURO: Conditional - Qual igreja? (aparece se veio de outra igreja) */}
                    {veioDeOutraIgreja === "sim" && (
                      <div className="animate-in fade-in slide-in-from-top-1">
                        <InputField label={t("Qual igreja?")} placeholder={t("Nome da igreja anterior")} />
                      </div>
                    )}
                  </div>
                  
                  {/* MAPA DO TESOURO: TextArea - Como conheceu a igreja? */}
                  <div className="grid grid-cols-1 mb-[20px]">
                    <InputField label={t("Como conheceu a igreja?")} type="textarea" placeholder={t("Descreva brevemente como o visitante chegou até a igreja...")} />
                  </div>

                  {/* MAPA DO TESOURO: Address section divider title */}
                  <div className="flex items-center gap-[8px] mt-[24px] mb-[12px]">
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                    <h3 className="text-[12px] font-[700] text-[#9CA3AF] uppercase tracking-wider">{t("Endereço Completo")}</h3>
                    <div className="h-px bg-[#E5E7EB] flex-1"></div>
                  </div>

                  {/* Bloco Endereço */}
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-x-[16px] gap-y-[14px]">
                    {/* MAPA DO TESOURO: Select - País */}
                    <InputField label={t("País")} type="select" searchable options={[{value:"BR", label:"Brasil"}, {value:"US", label:"Estados Unidos"}]} />
                    {/* MAPA DO TESOURO: Input - CEP */}
                    <InputField label="CEP" placeholder="00000-000" />
                    {/* MAPA DO TESOURO: Input - Rua */}
                    <div className="col-span-2"><InputField label={t("Rua")} placeholder={t("Ex: Av. Paulista")} /></div>
                    {/* MAPA DO TESOURO: Input - Número */}
                    <InputField label={t("Número")} placeholder="Ex: 1000" />
                    {/* MAPA DO TESOURO: Input - Complemento */}
                    <InputField label={t("Complemento")} placeholder={t("Ex: Apto 42")} />
                    {/* MAPA DO TESOURO: Input - Bairro */}
                    <InputField label={t("Bairro")} placeholder={t("Ex: Bela Vista")} />
                    {/* MAPA DO TESOURO: Inputs - Cidade / Estado */}
                    <div className="grid grid-cols-2 gap-[12px]">
                      <InputField label={t("Cidade")} />
                      <InputField label={t("Estado")} type="select" searchable options={[{value:"SP", label:"São Paulo"}, {value:"RJ", label:"Rio de Janeiro"}]} />
                    </div>
                  </div>

                </div>
              </div>
            </div>

            {/* SEPARADOR E BOTÕES */}
            <div className="mt-4 flex items-center justify-between">
              {/* MAPA DO TESOURO: Required fields hint */}
              <span className="text-[12px] text-[#9CA3AF]">
                <span className="text-[#EF4444]">*</span> {t("Campos obrigatórios para salvar.")}
              </span>
              <div className="flex items-center gap-[12px]">
                {/* MAPA DO TESOURO: Cancel link - redirects to /cadastros/visitantes */}
                <Link href="/cadastros/visitantes" className="px-[20px] py-[10px] bg-white border border-[#E5E7EB] text-[#374151] text-[14px] font-[600] rounded-[8px] hover:bg-[#F9FAFB] transition-colors">
                  {t("Cancelar")}
                </Link>
                {/* MAPA DO TESOURO: Save button - submits via POST /api/visitantes */}
                <button className="flex items-center gap-[6px] px-[20px] py-[10px] bg-gradient-to-r from-[#6D28D9] to-[#6D28D9] hover:opacity-95 text-white text-[14px] font-[600] rounded-[8px] shadow-[0_4px_14px_rgba(109,40,217,0.22)] transition-all">
                  <Save className="w-[16px] h-[16px]" strokeWidth={2.2} />
                  Salvar visitante
                </button>
              </div>
            </div>

          </div>

          {/* SESSÃO DE HISTÓRICO E INATIVAÇÃO (Somente na Edição) */}
          {editId && (
            <div className="bg-white rounded-[16px] border border-[#E5E7EB] shadow-[0_4px_16px_rgba(0,0,0,0.02)] p-[24px] flex flex-col gap-[20px] mt-[24px]">
              
              {/* Bloco de Status */}
              <div className="flex flex-col gap-[8px]">
                <span className="text-[13px] font-[600] text-[#4B5563]">{t("Status do Visitante")}</span>
                <button 
                  type="button"
                  onClick={() => setIsAtivo(!isAtivo)}
                  className="flex items-center gap-[12px] w-fit focus:outline-none group"
                >
                  <div className={`relative w-[44px] h-[24px] rounded-full transition-colors duration-300 ${isAtivo ? 'bg-[#10B981]' : 'bg-[#E5E7EB]'}`}>
                    <div className={`absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full shadow-sm transition-transform duration-300 ${isAtivo ? 'translate-x-[20px]' : 'translate-x-0'}`} />
                  </div>
                  <span className={`text-[14px] font-[600] transition-colors ${isAtivo ? 'text-[#10B981]' : 'text-[#6B7280]'}`}>
                    {isAtivo ? "Visitante Ativo" : "Visitante Inativo"}
                  </span>
                </button>
              </div>

              {/* Campos Condicionais de Inativação */}
              {!isAtivo && (
                <div className="flex flex-col gap-[16px] animate-in fade-in slide-in-from-top-2 duration-300 p-[16px] bg-[#FFF1F2] border border-[#FECDD3] rounded-[8px]">
                  <div className="flex items-center gap-2 text-[#BE123C] mb-2">
                    <AlertCircle className="w-[16px] h-[16px]" strokeWidth={2.4} />
                    <span className="text-[13px] font-[700] uppercase tracking-wider">Motivo da Desativação</span>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-[20px]">
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">{t("Motivo")} <span className="text-[#EF4444]">*</span></label>
                      <CustomSelect 
                        value={motivoInativacao}
                        onChange={setMotivoInativacao}
                        options={[
                          {value: "mudanca", label: "Mudou de Igreja"},
                          {value: "afastamento", label: "Afastamento"},
                          {value: "outro", label: "Outro Motivo"}
                        ]}
                      />
                    </div>
                    <div className="flex flex-col gap-[6px]">
                      <label className="text-[13px] font-[600] text-[#4B5563]">{t("Descrição do Motivo")} <span className="text-[#EF4444]">*</span></label>
                      <input 
                        type="text" 
                        value={descricaoInativacao} 
                        onChange={(e) => setDescricaoInativacao(e.target.value)}
                        placeholder="Descreva o motivo" 
                        className="h-[42px] px-[12px] bg-white border border-[#E5E7EB] rounded-[8px] text-[13px] outline-none transition-all" 
                      />
                    </div>
                  </div>
                </div>
              )}

              <div className="h-px bg-[#E5E7EB] w-full my-2"></div>

              {/* Título Histórico */}
              <div className="flex items-center gap-[8px]">
                <History className="w-[20px] h-[20px] text-[#4B5563]" strokeWidth={2.2} />
                <h2 className="text-[18px] font-[700] text-[#1A1A2E]">{t("Histórico de Atividades")}</h2>
              </div>

              {/* Informações de Tempo / Acesso */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <Clock className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Tempo Cadastrado</span>
                  </div>
                  <p className="text-[20px] font-[700] text-[#1A1A2E]">4 meses</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <LogOut className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Última Visita</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">22/05/2026</p>
                </div>
                <div className="bg-[#F9FAFB] p-[16px] rounded-[12px] border border-[#F1F1F4]">
                  <div className="flex items-center gap-[8px] text-[#6B7280] mb-[4px]">
                    <AlertCircle className="w-[16px] h-[16px]" />
                    <span className="text-[13px] font-[600]">Data de Criação</span>
                  </div>
                  <p className="text-[16px] font-[600] text-[#1A1A2E]">10/02/2026</p>
                </div>
              </div>

              {/* Tabela de Ações / Timeline */}
              <div className="mt-2 border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                <div className="bg-[#FCFCFD] px-[20px] py-[12px] border-b border-[#E5E7EB] grid grid-cols-[1fr_2fr_1fr] gap-[16px]">
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Data / Hora</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider">Ação Realizada</span>
                  <span className="text-[12px] font-[700] text-[#6B7280] uppercase tracking-wider text-right">Usuário</span>
                </div>
                <div className="flex flex-col">
                  {/* Item 1 */}
                  <div className="px-[20px] py-[16px] border-b border-[#F1F1F4] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">12/06/2026 14:32</span>
                    <span className="text-[13px] text-[#1A1A2E]">Atualização de dados cadastrais</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">João da Silva</span>
                  </div>
                  {/* Item 2 */}
                  <div className="px-[20px] py-[16px] grid grid-cols-[1fr_2fr_1fr] gap-[16px] items-center bg-white hover:bg-[#F9FAFB] transition-colors">
                    <span className="text-[13px] font-[500] text-[#4B5563]">10/02/2026 18:15</span>
                    <span className="text-[13px] text-[#1A1A2E]">Cadastro inicial do visitante</span>
                    <span className="text-[13px] font-[500] text-[#9CA3AF] text-right">Maria Souza</span>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* RODAPÉ COPYRIGHT */}
          {/* MAPA DO TESOURO: Footer copyright */}
          <div className="mt-[16px] pb-[12px]">
            <p className="text-[13px] text-[#6B7280]">
              {t("COPYRIGHT © 2026")} <span className="font-[700] text-[#6D28D9]">{t("Basiléia")}</span>{t(", Todos os direitos reservados")}
            </p>
          </div>

        </main>
      </div>
    </div>
  );
}
